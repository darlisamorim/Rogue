<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('professional_title')->nullable()->after('name');
            $table->string('phone')->nullable()->after('professional_title');
            $table->string('location')->nullable()->after('phone');
            $table->string('linkedin_url')->nullable()->after('location');
            $table->string('website_url')->nullable()->after('linkedin_url');
            $table->text('bio')->nullable()->after('website_url');
            $table->date('date_of_birth')->nullable()->after('bio');
            $table->string('nationality')->nullable()->after('date_of_birth');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'professional_title', 'phone', 'location',
                'linkedin_url', 'website_url', 'bio',
                'date_of_birth', 'nationality',
            ]);
        });
    }
};