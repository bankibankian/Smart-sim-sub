<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Single-row config for the silent post-activation data bonus: whether
     * it's on, and which SME data plan to grant.
     */
    public function up(): void
    {
        Schema::create('activation_bonus_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(false);
            $table->foreignId('sme_data_id')->nullable()->constrained('sme_data')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activation_bonus_settings');
    }
};
