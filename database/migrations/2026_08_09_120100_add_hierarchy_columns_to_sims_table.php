<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sims', function (Blueprint $table) {
            $table->foreignId('regional_manager_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            $table->foreignId('coordinator_id')->nullable()->after('regional_manager_id')->constrained('users')->nullOnDelete();
        });

        // Backfill the old 3-value status into the new 7-value vocabulary.
        DB::table('sims')->where('status', 'available')->update(['status' => 'UNASSIGNED']);
        DB::table('sims')->where('status', 'assigned')->update(['status' => 'ASSIGNED_TO_PARTNER']);
        DB::table('sims')->where('status', 'active')->update(['status' => 'ACTIVATED']);

        DB::statement("ALTER TABLE sims ALTER COLUMN status SET DEFAULT 'UNASSIGNED'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('sims')->where('status', 'UNASSIGNED')->update(['status' => 'available']);
        DB::table('sims')->whereIn('status', ['ASSIGNED_TO_RM', 'ASSIGNED_TO_COORDINATOR', 'ASSIGNED_TO_PARTNER'])->update(['status' => 'assigned']);
        DB::table('sims')->where('status', 'ACTIVATED')->update(['status' => 'active']);

        DB::statement("ALTER TABLE sims ALTER COLUMN status SET DEFAULT 'available'");

        Schema::table('sims', function (Blueprint $table) {
            $table->dropForeign(['regional_manager_id']);
            $table->dropColumn('regional_manager_id');
            $table->dropForeign(['coordinator_id']);
            $table->dropColumn('coordinator_id');
        });
    }
};
