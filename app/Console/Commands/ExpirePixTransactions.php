<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpirePixTransactions extends Command
{
    protected $signature = 'payments:expire-pix';
    protected $description = 'Marca como failed as transações Pix pendentes com expires_at no passado';

    public function handle(): int
    {
        $expired = Transaction::pending()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->update(['status' => Transaction::STATUS_FAILED]);

        if ($expired > 0) {
            Log::info("ExpirePixTransactions: {$expired} transação(ões) expirada(s).");
            $this->info("{$expired} transação(ões) marcada(s) como failed.");
        } else {
            $this->info('Nenhuma transação expirada encontrada.');
        }

        return self::SUCCESS;
    }
}