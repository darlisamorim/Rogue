<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resumes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->json('data'); // todos os campos do currículo
            $table->json('customization')->nullable(); // cor, fonte, tamanho, espaçamento, layout
            $table->unsignedInteger('current_version')->default(1);
            $table->boolean('is_downloaded')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resumes');
    }
};
