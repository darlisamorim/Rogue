<?php

namespace App\Contracts;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    /**
     * Cria uma cobrança Pix e retorna os dados para exibição ao usuário.
     *
     * @return array{gateway_transaction_id: string, pix_qr_code: string, pix_copy_paste: string, expires_at: string, fee_amount: float}
     */
    public function createPixCharge(Transaction $transaction, User $user): array;

    /**
     * Valida a assinatura HMAC do webhook recebido.
     */
    public function validateWebhookSignature(Request $request): bool;

    /**
     * Extrai os dados relevantes do payload do webhook.
     *
     * @return array{gateway_transaction_id: string, status: string, net_amount: float, fee_amount: float}
     */
    public function parseWebhookPayload(Request $request): array;
}
