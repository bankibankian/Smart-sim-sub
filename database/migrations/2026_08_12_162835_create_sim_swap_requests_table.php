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
        Schema::create('sim_swap_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sim_id')->constrained('sims')->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('from_holder_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('to_holder_id')->constrained('users')->cascadeOnDelete();
            // 'coordinator' or 'partner' — the tier this swap applies to, recorded
            // at request time so approval knows which FK column to touch without
            // re-deriving it from state that may have moved on by then.
            $table->string('holder_role');
            $table->string('status')->default('pending')->index(); // pending, approved, rejected
            $table->text('admin_notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sim_swap_requests');
    }
};
