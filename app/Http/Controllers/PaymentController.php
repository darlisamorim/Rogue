<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessPaymentWebhookJob;
use App\Models\CreditPackage;
use App\Models\PricingConfig;
use App\Models\Resume;
use App\Models\Transaction;
use App\Services\CreditService;
use App\Services\MercadoPagoGateway;
use App\Services\AsaasGateway;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
        private CreditService  $creditService,
    ) {}

    /**
     * Página de checkout (modal ou página dedicada).
     * Retorna os dados de preços e pacotes para o frontend decidir o fluxo.
     */
    public function checkout(Request $request): Response
    {
        $request->validate([
            'resume_id'  => 'nullable|integer|exists:resumes,id',
            'type'       => 'required|in:first_download,redownload,template_change',
        ]);

        $user     = Auth::user();
        $type     = $request->type;
        $resumeId = $request->resume_id;

        // Garante que o currículo pertence ao usuário
        if ($resumeId) {
            $resume = Resume::where('id', $resumeId)
                ->where('user_id', $user->id)
                ->firstOrFail();
        }

        $pricing         = PricingConfig::active()->get(['action_slug', 'label', 'price'])->keyBy('action_slug');
        $creditPackages  = CreditPackage::active()->get(['id', 'name', 'price', 'credits', 'bonus_percentage']);

        $actionSlug = match ($type) {
            'first_download'  => PricingConfig::ACTION_FIRST_DOWNLOAD,
            'redownload'      => PricingConfig::ACTION_REDOWNLOAD,
            'template_change' => PricingConfig::ACTION_TEMPLATE_CHANGE,
        };

        $price  = PricingConfig::getPrice($actionSlug);
        $hasCredits = $this->creditService->hasEnoughCredits($user, $price);

        return Inertia::render('Payment/Checkout', [
            'resumeId'       => $resumeId,
            'type'           => $type,
            'price'          => (float) $price,
            'hasCredits'     => $hasCredits,
            'creditBalance'  => (float) $user->credit_balance,
            'creditPackages' => $creditPackages,
        ]);
    }

    /**
     * Inicia o processo de pagamento. Retorna transação com dados do Pix
     * OU confirma via créditos imediatamente.
     */
    public function initiate(Request $request): JsonResponse
    {
        $request->validate([
            'type'       => 'required|in:' . implode(',', [
                Transaction::TYPE_DOWNLOAD,
                Transaction::TYPE_REDOWNLOAD,
                Transaction::TYPE_TEMPLATE_CHANGE,
                Transaction::TYPE_CREDIT_PURCHASE,
            ]),
            'resume_id'  => 'nullable|integer|exists:resumes,id',
            'package_id' => 'nullable|integer|exists:credit_packages,id',
        ]);

        $user      = Auth::user();
        $type      = $request->type;
        $resumeId  = $request->resume_id;
        $packageId = $request->package_id;

        // Garante que o currículo pertence ao usuário
        if ($resumeId) {
            Resume::where('id', $resumeId)
                ->where('user_id', $user->id)
                ->firstOrFail();
        }

        // Fluxo por créditos (somente para ações de download/template, não para compra de créditos)
        if ($type !== Transaction::TYPE_CREDIT_PURCHASE) {
            $price = $this->getPriceForType($type);

            if ($this->creditService->hasEnoughCredits($user, $price)) {
                $transaction = $this->creditService->debitForAction($user, $type, $resumeId);

                return response()->json([
                    'paid_with' => 'credits',
                    'status'    => 'confirmed',
                    'transaction_id' => $transaction->id,
                    'message'   => 'Pago com créditos. PDF liberado!',
                ]);
            }
        }

        // Fluxo por Pix
        try {
            $transaction = $this->paymentService->initiateCharge($user, $type, $resumeId, $packageId);

            return response()->json([
                'paid_with'      => 'pix',
                'status'         => 'pending',
                'transaction_id' => $transaction->id,
                'pix_qr_code'    => $transaction->pix_qr_code,
                'pix_copy_paste' => $transaction->pix_copy_paste,
                'expires_at'     => $transaction->expires_at?->toIso8601String(),
                'amount'         => (float) $transaction->amount,
                'gateway'        => $transaction->gateway,
            ]);
        } catch (\Exception $e) {
            Log::error('Payment initiation failed', ['error' => $e->getMessage()]);

            return response()->json([
                'error' => 'Não foi possível gerar o Pix. Tente novamente.',
            ], 422);
        }
    }

    /**
     * Polling: retorna o status atual de uma transação.
     * Se ainda estiver pendente, consulta diretamente na API do gateway (útil para desenvolvimento local sem webhooks).
     */
    public function status(int $transactionId, MercadoPagoGateway $mpGateway, AsaasGateway $asaasGateway): JsonResponse
    {
        $transaction = Transaction::where('id', $transactionId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Se ainda pendente, consulta o gateway diretamente (evita depender de webhook em dev)
        if (!$transaction->isConfirmed() && $transaction->gateway_transaction_id) {
            $gateway = $transaction->gateway === 'asaas' ? $asaasGateway : $mpGateway;

            if ($transaction->gateway === 'mercadopago') {
                $remote = $mpGateway->checkTransactionStatus($transaction->gateway_transaction_id);
            } else {
                $remote = null; // Asaas: usa webhook normalmente
            }

            if ($remote && $remote['status'] !== $transaction->status) {
                $transaction->update([
                    'status'       => $remote['status'],
                    'gross_amount' => $remote['gross_amount'] ?? $transaction->amount,
                    'fee_amount'   => $remote['fee_amount'] ?? 0,
                    'net_amount'   => $remote['net_amount'] ?? $transaction->amount,
                    'confirmed_at' => $remote['status'] === Transaction::STATUS_CONFIRMED ? now() : null,
                ]);

                if ($remote['status'] === Transaction::STATUS_CONFIRMED) {
                    \App\Events\PaymentConfirmed::dispatch($transaction->fresh());
                }

                $transaction->refresh();
            }
        }

        return response()->json([
            'status'     => $transaction->status,
            'confirmed'  => $transaction->isConfirmed(),
            'expires_at' => $transaction->expires_at?->toIso8601String(),
        ]);
    }

    /**
     * Webhook Mercado Pago.
     */
    public function webhookMercadoPago(Request $request, MercadoPagoGateway $gateway): JsonResponse
    {
        if (!$gateway->validateWebhookSignature($request)) {
            Log::warning('MercadoPago webhook: assinatura inválida.');
            return response()->json(['ok' => false], 401);
        }

        ProcessPaymentWebhookJob::dispatch(
            'mercadopago',
            $request->all(),
            $request->headers->all(),
        );

        return response()->json(['ok' => true]);
    }

    /**
     * Webhook Asaas.
     */
    public function webhookAsaas(Request $request, AsaasGateway $gateway): JsonResponse
    {
        if (!$gateway->validateWebhookSignature($request)) {
            Log::warning('Asaas webhook: assinatura inválida.');
            return response()->json(['ok' => false], 401);
        }

        ProcessPaymentWebhookJob::dispatch(
            'asaas',
            $request->all(),
            $request->headers->all(),
        );

        return response()->json(['ok' => true]);
    }

    private function getPriceForType(string $type): float
    {
        $slug = match ($type) {
            Transaction::TYPE_DOWNLOAD        => PricingConfig::ACTION_FIRST_DOWNLOAD,
            Transaction::TYPE_REDOWNLOAD      => PricingConfig::ACTION_REDOWNLOAD,
            Transaction::TYPE_TEMPLATE_CHANGE => PricingConfig::ACTION_TEMPLATE_CHANGE,
            default                           => PricingConfig::ACTION_FIRST_DOWNLOAD,
        };

        return PricingConfig::getPrice($slug);
    }
}
