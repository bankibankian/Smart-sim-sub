<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A Lien is a partial-amount hold, distinct from PND's all-or-nothing
     * debit block — it needs its own amount column, not reuse of the
     * existing `hold_amount` (that one drains automatically on every debit
     * regardless of cause, so it can't represent a standing admin-placed
     * hold). Mirrors the same audit-trail shape pnd_at/pnd_reason/pnd_by
     * already established.
     */
    public function up(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->decimal('lien_amount', 15, 2)->default(0)->after('hold_amount');
            $table->timestamp('lien_at')->nullable()->after('lien_amount');
            $table->string('lien_reason')->nullable()->after('lien_at');
            $table->foreignId('lien_by')->nullable()->after('lien_reason')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropForeign(['lien_by']);
            $table->dropColumn(['lien_amount', 'lien_at', 'lien_reason', 'lien_by']);
        });
    }
};
