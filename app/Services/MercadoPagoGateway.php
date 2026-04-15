<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MercadoPagoGateway implements PaymentGatewayInterface
{
    private string $accessToken;
    private string $webhookSecret;
    private string $baseUrl = 'https://api.mercadopago.com';

    public function __construct()
    {
        $this->accessToken   = config('services.mercadopago.access_token', '');
        $this->webhookSecret = config('services.mercadopago.webhook_secret', '');
    }

    public function createPixCharge(Transaction $transaction, User $user): array
    {
        $response = Http::withToken($this->accessToken)
            ->withHeaders(['X-Idempotency-Key' => 'rogue-txn-' . $transaction->id])
            ->post("{$this->baseUrl}/v1/payments", [
                'payment_method_id'  => 'pix',
                'transaction_amount' => (float) $transaction->amount,
                'description'        => $this->descriptionFor($transaction->type),
                'date_of_expiration' => now()->addMinutes(30)->toIso8601String(),
                'payer'              => [
                    'email'          => $user->email,
                    'first_name'     => explode(' ', $user->name)[0],
                    'last_name'      => implode(' ', array_slice(explode(' ', $user->name), 1)) ?: 'User',
                    'identification' => [
                        'type'   => 'CPF',
                        'number' => preg_replace('/\D/', '', $user->cpf ?? '00000000000'),
                    ],
                ],
                'metadata' => [
                    'transaction_id' => $transaction->id,
                    'user_id'        => $user->id,
                ],
            ]);

        $response->throw();
        $data = $response->json();

        $pixData = $data['point_of_interaction']['transaction_data'] ?? [];

        // Taxa Mercado Pago: ~0.99% para Pix
        $fee = round((float) $transaction->amount * 0.0099, 2);

        return [
            'gateway_transaction_id' => (string) $data['id'],
            'pix_qr_code'            => $pixData['qr_code_base64'] ?? '',
            'pix_copy_paste'         => $pixData['qr_code'] ?? '',
            'expires_at'             => now()->addMinutes(30)->toDateTimeString(),
            'fee_amount'             => $fee,
        ];
    }

    public function validateWebhookSignature(Request $request): bool
    {
        if (empty($this->webhookSecret)) {
            Log::warning('MercadoPago webhook secret not configured.');
            return true; // Em desenvolvimento permite sem validação
        }

        $xSignature = $request->header('x-signature', '');
        $xRequestId = $request->header('x-request-id', '');
        $dataId     = $request->input('data.id', '');

        // Formato da assinatura MercadoPago: "ts=...,v1=..."
        $parts = [];
        foreach (explode(',', $xSignature) as $part) {
            [$k, $v] = explode('=', $part, 2);
            $parts[trim($k)] = trim($v);
        }

        $ts   = $parts['ts'] ?? '';
        $hash = $parts['v1'] ?? '';

        $manifest = "id:{$dataId};request-id:{$xRequestId};ts:{$ts};";
        $expected = hash_hmac('sha256', $manifest, $this->webhookSecret);

        return hash_equals($expected, $hash);
    }

    public function parseWebhookPayload(Request $request): array
    {
        $type   = $request->input('type');
        $dataId = $request->input('data.id');

        // MercadoPago envia notificação e precisamos consultar o pagamento
        if ($type !== 'payment' || !$dataId) {
            return [];
        }

        try {
            $response = Http::withToken($this->accessToken)
                ->get("{$this->baseUrl}/v1/payments/{$dataId}");

            $response->throw();
            $payment = $response->json();

            $status = match ($payment['status'] ?? '') {
                'approved' => Transaction::STATUS_CONFIRMED,
                'rejected', 'cancelled' => Transaction::STATUS_FAILED,
                default => Transaction::STATUS_PENDING,
            };

            $gross     = (float) ($payment['transaction_amount'] ?? 0);
            $fee       = (float) ($payment['fee_details'][0]['amount'] ?? round($gross * 0.0099, 2));
            $net       = round($gross - $fee, 2);

            return [
                'gateway_transaction_id' => (string) $dataId,
                'status'                 => $status,
                'gross_amount'           => $gross,
                'fee_amount'             => $fee,
                'net_amount'             => $net,
            ];
        } catch (RequestException $e) {
            Log::error('MercadoPago webhook parse error', ['error' => $e->getMessage()]);
            return [];
        }
    }

    private function descriptionFor(string $type): string
    {
        return match ($type) {
            Transaction::TYPE_DOWNLOAD        => 'Download de currículo PDF — Rogue',
            Transaction::TYPE_REDOWNLOAD      => 'Re-download de currículo PDF — Rogue',
            Transaction::TYPE_TEMPLATE_CHANGE => 'Troca de template — Rogue',
            Transaction::TYPE_CREDIT_PURCHASE => 'Compra de créditos — Rogue',
            default                           => 'Pagamento — Rogue',
        };
    }
}
