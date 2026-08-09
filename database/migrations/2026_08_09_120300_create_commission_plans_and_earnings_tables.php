<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('commission_plans', function (Blueprint $table) {
            $table->id();
            $table->string('role')->index(); // regional_manager, coordinator, partner, personal, agent, business
            $table->string('event_key')->default('sim_activation')->index();
            $table->string('category')->nullable()->index(); // POS SIM, CCTV, ROUTER SIM, ... — null = applies to every category
            $table->string('provider')->nullable(); // mtn, airtel, ... — null = applies to every network
            $table->string('tier')->nullable(); // reserved for future multi-tier/volume-based plans
            $table->decimal('value', 15, 2)->default(0.00);
            $table->string('status')->default('active')->index(); // active, inactive
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
        });

        Schema::create('commission_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role');
            $table->foreignId('sim_id')->constrained('sims')->cascadeOnDelete();
            $table->foreignId('commission_plan_id')->nullable()->constrained('commission_plans')->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->date('period_week'); // Monday of the ISO week the commission was earned, for weekly leaderboard aggregation
            $table->timestamps();

            $table->index(['user_id', 'period_week']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_earnings');
        Schema::dropIfExists('commission_plans');
    }
};
