<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AsaasGateway implements PaymentGatewayInterface
{
    private string $apiKey;
    private string $webhookSecret;
    private string $baseUrl = 'https://api.asaas.com/v3';

    public function __construct()
    {
        $this->apiKey        = config('services.asaas.api_key', '');
        $this->webhookSecret = config('services.asaas.webhook_secret', '');
    }

    public function createPixCharge(Transaction $transaction, User $user): array
    {
        $customerId = $this->findOrCreateCustomer($user);

        $response = Http::withHeaders(['access_token' => $this->apiKey])
            ->post("{$this->baseUrl}/payments", [
                'customer'    => $customerId,
                'billingType' => 'PIX',
                'value'       => (float) $transaction->amount,
                'dueDate'     => now()->addMinutes(30)->format('Y-m-d'),
                'description' => $this->descriptionFor($transaction->type),
                'externalReference' => 'rogue-txn-' . $transaction->id,
            ]);

        $response->throw();
        $payment = $response->json();

        // Busca QR code Pix
        $qrResponse = Http::withHeaders(['access_token' => $this->apiKey])
            ->get("{$this->baseUrl}/payments/{$payment['id']}/pixQrCode");

        $qrResponse->throw();
        $qrData = $qrResponse->json();

        // Taxa Asaas: ~R$0.49 fixo por Pix
        $fee = 0.49;

        return [
            'gateway_transaction_id' => $payment['id'],
            'pix_qr_code'            => $qrData['encodedImage'] ?? '',
            'pix_copy_paste'         => $qrData['payload'] ?? '',
            'expires_at'             => now()->addMinutes(30)->toDateTimeString(),
            'fee_amount'             => $fee,
        ];
    }

    public function validateWebhookSignature(Request $request): bool
    {
        if (empty($this->webhookSecret)) {
            Log::warning('Asaas webhook secret not configured.');
            return true;
        }

        $signature = $request->header('asaas-access-token', '');
        return hash_equals($this->webhookSecret, $signature);
    }

    public function parseWebhookPayload(Request $request): array
    {
        $event   = $request->input('event');
        $payment = $request->input('payment', []);

        if (empty($payment['id'])) {
            return [];
        }

        $status = match ($event) {
            'PAYMENT_RECEIVED', 'PAYMENT_CONFIRMED' => Transaction::STATUS_CONFIRMED,
            'PAYMENT_OVERDUE', 'PAYMENT_DELETED',
            'PAYMENT_REFUNDED', 'PAYMENT_REFUND_IN_PROGRESS' => Transaction::STATUS_FAILED,
            default => Transaction::STATUS_PENDING,
        };

        $gross = (float) ($payment['value'] ?? 0);
        $fee   = 0.49;
        $net   = round($gross - $fee, 2);

        return [
            'gateway_transaction_id' => $payment['id'],
            'status'                 => $status,
            'gross_amount'           => $gross,
            'fee_amount'             => $fee,
            'net_amount'             => $net,
        ];
    }

    private function findOrCreateCustomer(User $user): string
    {
        $cpf = preg_replace('/\D/', '', $user->cpf ?? '');

        if ($cpf) {
            // Tenta encontrar cliente existente pelo CPF
            try {
                $response = Http::withHeaders(['access_token' => $this->apiKey])
                    ->get("{$this->baseUrl}/customers", ['cpfCnpj' => $cpf]);

                if ($response->ok() && !empty($response->json('data'))) {
                    return $response->json('data.0.id');
                }
            } catch (\Exception) {
                // ignora e cria novo
            }
        }

        // Cria novo cliente
        $response = Http::withHeaders(['access_token' => $this->apiKey])
            ->post("{$this->baseUrl}/customers", [
                'name'     => $user->name,
                'email'    => $user->email,
                'cpfCnpj'  => $cpf ?: null,
                'phone'    => preg_replace('/\D/', '', $user->phone ?? ''),
            ]);

        $response->throw();
        return $response->json('id');
    }

    private function descriptionFor(string $type): string
    {
        return match ($type) {
            Transaction::TYPE_DOWNLOAD        => 'Download PDF - Rogue',
            Transaction::TYPE_REDOWNLOAD      => 'Re-download PDF - Rogue',
            Transaction::TYPE_TEMPLATE_CHANGE => 'Troca de template - Rogue',
            Transaction::TYPE_CREDIT_PURCHASE => 'Compra de créditos - Rogue',
            default                           => 'Pagamento - Rogue',
        };
    }
}
