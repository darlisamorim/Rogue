<?php

namespace App\Listeners;

use App\Events\PaymentConfirmed;
use App\Models\Resume;
use App\Models\Transaction;
use App\Services\CreditService;
use Illuminate\Support\Facades\Log;

class ReleasePdfDownload
{
    public function __construct(private CreditService $creditService) {}

    public function handle(PaymentConfirmed $event): void
    {
        $transaction = $event->transaction->load('user');
        $user        = $transaction->user;

        Log::info('PaymentConfirmed: processando', [
            'transaction_id' => $transaction->id,
            'type'           => $transaction->type,
            'user_id'        => $user->id,
        ]);

        match ($transaction->type) {
            Transaction::TYPE_DOWNLOAD,
            Transaction::TYPE_REDOWNLOAD      => $this->releaseDownload($transaction),
            Transaction::TYPE_TEMPLATE_CHANGE => $this->releaseTemplateChange($transaction),
            Transaction::TYPE_CREDIT_PURCHASE => $this->creditService->creditFromPackage($user, $transaction),
            default                           => null,
        };
    }

    private function releaseDownload(Transaction $transaction): void
    {
        if ($transaction->resume_id) {
            Resume::where('id', $transaction->resume_id)
                ->update(['is_downloaded' => true]);
        }

        // TODO: Fase 3 — disparar GeneratePdfJob
        // GeneratePdfJob::dispatch($transaction);
    }

    private function releaseTemplateChange(Transaction $transaction): void
    {
        // TODO: Fase 3 — liberar troca de template após pagamento
        Log::info('Template change payment confirmed', ['transaction_id' => $transaction->id]);
    }
}
