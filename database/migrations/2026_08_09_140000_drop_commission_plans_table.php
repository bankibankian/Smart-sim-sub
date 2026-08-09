<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Retires the CommissionPlan table: Regional Manager / Coordinator
     * commissions are now read from ServicePrice (per category, per role),
     * the same admin-managed table every other role already uses — see
     * App\Listeners\AwardCommissions and /admin/services.
     */
    public function up(): void
    {
        Schema::table('commission_earnings', function (Blueprint $table) {
            $table->dropForeign(['commission_plan_id']);
            $table->dropColumn('commission_plan_id');
        });

        Schema::dropIfExists('commission_plans');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('commission_plans', function (Blueprint $table) {
            $table->id();
            $table->string('role')->index();
            $table->string('event_key')->default('sim_activation')->index();
            $table->string('category')->nullable()->index();
            $table->string('provider')->nullable();
            $table->string('tier')->nullable();
            $table->decimal('value', 15, 2)->default(0.00);
            $table->string('status')->default('active')->index();
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
        });

        Schema::table('commission_earnings', function (Blueprint $table) {
            $table->foreignId('commission_plan_id')->nullable()->constrained('commission_plans')->nullOnDelete();
        });
    }
};
