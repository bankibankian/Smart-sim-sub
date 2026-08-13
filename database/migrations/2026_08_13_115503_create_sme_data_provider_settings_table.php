<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * One row per SME data vendor: its base URL/credentials, and whether
     * it's the currently-active vendor for the storefront.
     */
    public function up(): void
    {
        Schema::create('sme_data_provider_settings', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->unique(); // 'legacy', 'smeplug'
            $table->string('base_url')->nullable();
            $table->text('api_key')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sme_data_provider_settings');
    }
};
