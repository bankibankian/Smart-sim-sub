<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Retires the generic commission-based leaderboard (snapshot + weekly
     * cron) in favor of an on-demand, admin-configurable activation-tier
     * leaderboard (see create_leaderboard_tiers_table / create_leaderboard_settings_table).
     */
    public function up(): void
    {
        Schema::dropIfExists('leaderboard_snapshots');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('leaderboard_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role')->index();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('total_commission', 15, 2)->default(0.00);
            $table->unsignedInteger('tasks_completed')->default(0);
            $table->unsignedInteger('rank')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'period_start'], 'leaderboard_user_period_unique');
            $table->index(['role', 'period_start', 'rank']);
        });
    }
};
