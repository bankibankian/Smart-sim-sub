<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 9PSB needs a second credential (secret_key alongside api_key), a
     * separate auth base URL from its VAS base URL, and a settlement
     * debit_account — none of which the other two vendors use.
     */
    public function up(): void
    {
        Schema::table('sme_data_provider_settings', function (Blueprint $table) {
            $table->text('secret_key')->nullable()->after('api_key');
            $table->string('auth_base_url')->nullable()->after('base_url');
            $table->string('debit_account')->nullable()->after('secret_key');
        });
    }

    public function down(): void
    {
        Schema::table('sme_data_provider_settings', function (Blueprint $table) {
            $table->dropColumn(['secret_key', 'auth_base_url', 'debit_account']);
        });
    }
};
