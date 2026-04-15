<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resume_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); // download|redownload|template_change|credit_purchase
            $table->decimal('amount', 10, 2); // valor final cobrado do usuário
            $table->string('gateway'); // asaas|mercadopago
            $table->string('gateway_transaction_id')->nullable()->unique();
            $table->decimal('gross_amount', 10, 2)->nullable();
            $table->decimal('fee_amount', 10, 2)->nullable();
            $table->decimal('net_amount', 10, 2)->nullable();
            $table->string('status')->default('pending'); // pending|confirmed|failed
            $table->text('pix_qr_code')->nullable();
            $table->text('pix_copy_paste')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('gateway_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
