<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // purchase|debit
            $table->decimal('amount', 10, 2);
            $table->decimal('balance_after', 10, 2);
            $table->string('reference_type')->nullable(); // transaction|resume
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_transactions');
    }
};
