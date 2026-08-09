<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Converts the DB-level ENUMs on users.role / users.status to plain
     * VARCHAR so new roles (regional_manager, coordinator) and statuses
     * (invited) can be added without a schema migration every time.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role VARCHAR(255) NOT NULL DEFAULT 'personal'");
        DB::statement("ALTER TABLE users MODIFY COLUMN status VARCHAR(255) NOT NULL DEFAULT 'active'");

        Schema::table('users', function ($table) {
            $table->index('role');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function ($table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['status']);
        });

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('personal','agent','partner','business','staff','checker','super_admin') NOT NULL DEFAULT 'personal'");
        DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('active','suspended','inactive','banned') NOT NULL DEFAULT 'active'");
    }
};
