<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The boolean flag itself is the existing `is_locked` column (already
     * documented as "blocks debits" but unused until now) — these three
     * columns just give a Post No Debit restriction the same audit trail
     * `suspended_at`/`suspension_reason`/`suspended_by` already gives a
     * suspension.
     */
    public function up(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->timestamp('pnd_at')->nullable()->after('is_locked');
            $table->string('pnd_reason')->nullable()->after('pnd_at');
            $table->foreignId('pnd_by')->nullable()->after('pnd_reason')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropForeign(['pnd_by']);
            $table->dropColumn(['pnd_at', 'pnd_reason', 'pnd_by']);
        });
    }
};
