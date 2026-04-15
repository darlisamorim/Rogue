<?php

namespace App\Services;

use App\Models\CreditPackage;
use App\Models\CreditTransaction;
use App\Models\Resume;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreditService
{
    public function hasEnoughCredits(User $user, float $amount): bool
    {
        return (float) $user->credit_balance >= $amount;
    }

    /**
     * Debita créditos do usuário e marca a ação como realizada.
     * Retorna a transação gerada.
     */
    public function debitForAction(User $user, string $type, ?int $resumeId = null): Transaction
    {
        $amount = $this->getAmountForType($type);

        return DB::transaction(function () use ($user, $type, $resumeId, $amount) {
            // Debita o saldo
            $user->decrement('credit_balance', $amount);
            $user->refresh();

            // Registra a transação como confirmada via crédito
            $transaction = Transaction::create([
                'user_id'        => $user->id,
                'resume_id'      => $resumeId,
                'type'           => $type,
                'amount'         => $amount,
                'gross_amount'   => $amount,
                'fee_amount'     => 0,
                'net_amount'     => $amount,
                'gateway'        => 'credits',
                'status'         => Transaction::STATUS_CONFIRMED,
                'confirmed_at'   => now(),
            ]);

            // Registra no histórico de créditos
            CreditTransaction::create([
                'user_id'        => $user->id,
                'type'           => CreditTransaction::TYPE_DEBIT,
                'amount'         => $amount,
                'balance_after'  => $user->credit_balance,
                'reference_type' => 'transaction',
                'reference_id'   => $transaction->id,
            ]);

            // Marca o currículo como downloaded se for tipo download
            if ($resumeId && in_array($type, [Transaction::TYPE_DOWNLOAD, Transaction::TYPE_REDOWNLOAD])) {
                Resume::where('id', $resumeId)->update(['is_downloaded' => true]);
            }

            return $transaction;
        });
    }

    /**
     * Credita o saldo do usuário após confirmação de pagamento de pacote.
     */
    public function creditFromPackage(User $user, Transaction $transaction): void
    {
        $package = CreditPackage::active()
            ->where('price', $transaction->amount)
            ->first();

        $credits = $package ? (float) $package->credits : (float) $transaction->net_amount;

        DB::transaction(function () use ($user, $transaction, $credits) {
            $user->increment('credit_balance', $credits);
            $user->refresh();

            CreditTransaction::create([
                'user_id'        => $user->id,
                'type'           => CreditTransaction::TYPE_PURCHASE,
                'amount'         => $credits,
                'balance_after'  => $user->credit_balance,
                'reference_type' => 'transaction',
                'reference_id'   => $transaction->id,
            ]);
        });
    }

    private function getAmountForType(string $type): float
    {
        $slugMap = [
            Transaction::TYPE_DOWNLOAD        => \App\Models\PricingConfig::ACTION_FIRST_DOWNLOAD,
            Transaction::TYPE_REDOWNLOAD      => \App\Models\PricingConfig::ACTION_REDOWNLOAD,
            Transaction::TYPE_TEMPLATE_CHANGE => \App\Models\PricingConfig::ACTION_TEMPLATE_CHANGE,
        ];

        $slug = $slugMap[$type] ?? \App\Models\PricingConfig::ACTION_FIRST_DOWNLOAD;
        return \App\Models\PricingConfig::getPrice($slug);
    }
}
