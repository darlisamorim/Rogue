<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    public function index(): Response
    {
        $transactions = Transaction::with('coupon')
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(15)
            ->through(fn ($t) => [
                'id'              => $t->id,
                'type'            => $t->type,
                'type_label'      => $this->typeLabel($t->type),
                'status'          => $t->status,
                'amount'          => (float) $t->amount,
                'discount_amount' => (float) $t->discount_amount,
                'gateway'         => $t->gateway,
                'coupon_code'     => $t->coupon?->code,
                'pix_copy_paste'  => $t->status === Transaction::STATUS_PENDING ? $t->pix_copy_paste : null,
                'expires_at'      => $t->expires_at?->toIso8601String(),
                'confirmed_at'    => $t->confirmed_at?->format('d/m/Y H:i'),
                'created_at'      => $t->created_at->format('d/m/Y H:i'),
            ]);

        return Inertia::render('Transactions/Index', [
            'transactions' => $transactions,
        ]);
    }

    private function typeLabel(string $type): string
    {
        return match ($type) {
            Transaction::TYPE_DOWNLOAD        => 'Download de currículo',
            Transaction::TYPE_REDOWNLOAD      => 'Re-download',
            Transaction::TYPE_TEMPLATE_CHANGE => 'Troca de template',
            Transaction::TYPE_CREDIT_PURCHASE => 'Compra de créditos',
            default                           => $type,
        };
    }
}
