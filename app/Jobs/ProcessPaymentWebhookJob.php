<?php

namespace App\Jobs;

use App\Events\PaymentConfirmed;
use App\Models\Transaction;
use App\Services\AsaasGateway;
use App\Services\MercadoPagoGateway;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPaymentWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 30;

    public function __construct(
        private readonly string $gateway,
        private readonly array  $payload,
        private readonly array  $headers,
    ) {}

    public function handle(MercadoPagoGateway $mercadoPago, AsaasGateway $asaas): void
    {
        $gatewayService = match ($this->gateway) {
            'asaas' => $asaas,
            default => $mercadoPago,
        };

        // Recria uma request fake para o gateway poder parsear
        $fakeRequest = \Illuminate\Http\Request::create(
            uri:     '/',
            method:  'POST',
            parameters: $this->payload,
        );

        foreach ($this->headers as $key => $value) {
            $fakeRequest->headers->set($key, $value);
        }

        $parsed = $gatewayService->parseWebhookPayload($fakeRequest);

        if (empty($parsed) || empty($parsed['gateway_transaction_id'])) {
            Log::info("Webhook {$this->gateway}: payload ignorado ou não reconhecido.");
            return;
        }

        $transaction = Transaction::where('gateway_transaction_id', $parsed['gateway_transaction_id'])
            ->where('gateway', $this->gateway)
            ->first();

        if (!$transaction) {
            Log::warning("Webhook {$this->gateway}: transação não encontrada.", $parsed);
            return;
        }

        if ($transaction->isConfirmed()) {
            Log::info("Webhook {$this->gateway}: transação já confirmada.", ['id' => $transaction->id]);
            return;
        }

        $transaction->update([
            'status'       => $parsed['status'],
            'gross_amount' => $parsed['gross_amount'] ?? $transaction->amount,
            'fee_amount'   => $parsed['fee_amount'] ?? 0,
            'net_amount'   => $parsed['net_amount'] ?? $transaction->amount,
            'confirmed_at' => $parsed['status'] === Transaction::STATUS_CONFIRMED ? now() : null,
        ]);

        if ($parsed['status'] === Transaction::STATUS_CONFIRMED) {
            PaymentConfirmed::dispatch($transaction->fresh());
        }
    }
}
