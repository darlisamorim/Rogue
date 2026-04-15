<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Models\CreditPackage;
use App\Models\PricingConfig;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function __construct(
        private MercadoPagoGateway $mercadoPago,
        private AsaasGateway       $asaas,
    ) {}

    public function getActiveGateway(): PaymentGatewayInterface
    {
        $active = config('services.payment.active_gateway', 'mercadopago');

        return match ($active) {
            'asaas' => $this->asaas,
            default => $this->mercadoPago,
        };
    }

    public function getActiveGatewayName(): string
    {
        return config('services.payment.active_gateway', 'mercadopago');
    }

    /**
     * Cria uma transação pendente e gera o QR Code Pix.
     */
    public function initiateCharge(User $user, string $type, ?int $resumeId = null, ?int $packageId = null): Transaction
    {
        $amount = $this->getAmount($type, $packageId);
        $gateway = $this->getActiveGatewayName();

        return DB::transaction(function () use ($user, $type, $resumeId, $amount, $gateway) {
            // Cria a transação no banco primeiro
            $transaction = Transaction::create([
                'user_id'      => $user->id,
                'resume_id'    => $resumeId,
                'type'         => $type,
                'amount'       => $amount,
                'gross_amount' => $amount,
                'fee_amount'   => 0,
                'net_amount'   => $amount,
                'gateway'      => $gateway,
                'status'       => Transaction::STATUS_PENDING,
            ]);

            // Chama o gateway para gerar o Pix
            try {
                $chargeData = $this->getActiveGateway()->createPixCharge($transaction, $user);

                $transaction->update([
                    'gateway_transaction_id' => $chargeData['gateway_transaction_id'],
                    'pix_qr_code'            => $chargeData['pix_qr_code'],
                    'pix_copy_paste'         => $chargeData['pix_copy_paste'],
                    'expires_at'             => $chargeData['expires_at'],
                    'fee_amount'             => $chargeData['fee_amount'],
                    'net_amount'             => round($amount - $chargeData['fee_amount'], 2),
                ]);
            } catch (\Exception $e) {
                Log::error('Payment gateway error', [
                    'gateway'        => $gateway,
                    'transaction_id' => $transaction->id,
                    'error'          => $e->getMessage(),
                ]);
                $transaction->update(['status' => Transaction::STATUS_FAILED]);
                throw $e;
            }

            return $transaction->fresh();
        });
    }

    private function getAmount(string $type, ?int $packageId): float
    {
        if ($type === Transaction::TYPE_CREDIT_PURCHASE && $packageId) {
            $package = CreditPackage::findOrFail($packageId);
            return (float) $package->price;
        }

        $slug = match ($type) {
            Transaction::TYPE_DOWNLOAD        => PricingConfig::ACTION_FIRST_DOWNLOAD,
            Transaction::TYPE_REDOWNLOAD      => PricingConfig::ACTION_REDOWNLOAD,
            Transaction::TYPE_TEMPLATE_CHANGE => PricingConfig::ACTION_TEMPLATE_CHANGE,
            default                           => PricingConfig::ACTION_FIRST_DOWNLOAD,
        };

        return PricingConfig::getPrice($slug);
    }
}
