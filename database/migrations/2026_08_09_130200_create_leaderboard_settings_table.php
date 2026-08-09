<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Single-row config table for the partner activation leaderboard.
     */
    public function up(): void
    {
        Schema::create('leaderboard_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(false);
            $table->string('period_type')->default('weekly'); // weekly, custom
            $table->date('period_start')->nullable(); // used when period_type = custom
            $table->date('period_end')->nullable(); // used when period_type = custom
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaderboard_settings');
    }
};
