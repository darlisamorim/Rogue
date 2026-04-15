<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('type'); // percentage|fixed
            $table->decimal('value', 10, 2);
            $table->string('applicable_to')->default('all'); // all|first_download|redownload|template_change|credit_purchase
            $table->unsignedInteger('max_uses')->nullable(); // null = ilimitado
            $table->unsignedInteger('current_uses')->default(0);
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
