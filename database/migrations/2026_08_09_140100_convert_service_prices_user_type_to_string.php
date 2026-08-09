<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Widens service_prices.user_type from a fixed ENUM to a plain string so
     * the new regional_manager / coordinator roles (and any future role) can
     * have per-category commission rows without another schema migration.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE service_prices MODIFY COLUMN user_type VARCHAR(255) NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE service_prices MODIFY COLUMN user_type ENUM('personal','agent','partner','business','staff','checker','super_admin') NULL");
    }
};
