<?php

use App\Http\Controllers\LegalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SocialAuthController;
use App\Models\CreditPackage;
use App\Models\PricingConfig;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin'       => Route::has('login'),
        'canRegister'    => Route::has('register'),
        'pricing'        => PricingConfig::active()->get(['action_slug', 'label', 'price']),
        'creditPackages' => CreditPackage::active()->get(['name', 'price', 'credits', 'bonus_percentage']),
    ]);
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('resumes', \App\Http\Controllers\ResumeController::class);
    Route::get('/transactions', [\App\Http\Controllers\TransactionController::class, 'index'])->name('transactions.index');

    // Pagamento
    Route::prefix('payment')->name('payment.')->group(function () {
        Route::get('/checkout', [PaymentController::class, 'checkout'])->name('checkout');
        Route::post('/initiate', [PaymentController::class, 'initiate'])->name('initiate');
        Route::get('/status/{transactionId}', [PaymentController::class, 'status'])->name('status');
        Route::post('/coupon/validate', [PaymentController::class, 'validateCoupon'])->name('coupon.validate');
    });
});

// Webhooks — fora do middleware CSRF
Route::post('/payment/webhook/mercadopago', [PaymentController::class, 'webhookMercadoPago'])
    ->name('payment.webhook.mercadopago')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

Route::post('/payment/webhook/asaas', [PaymentController::class, 'webhookAsaas'])
    ->name('payment.webhook.asaas')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// Social Login
Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])->name('social.redirect');
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('social.callback');

// Páginas legais
Route::get('/privacidade', [LegalController::class, 'privacy'])->name('legal.privacy');
Route::get('/termos', [LegalController::class, 'terms'])->name('legal.terms');

require __DIR__.'/auth.php';
