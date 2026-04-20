<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('payer_cpf', 14)->nullable()->after('gateway_transaction_id');
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete()->after('payer_cpf');
            $table->decimal('discount_amount', 8, 2)->default(0)->after('coupon_id');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['coupon_id']);
            $table->dropColumn(['payer_cpf', 'coupon_id', 'discount_amount']);
        });
    }
};