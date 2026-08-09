<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The activation bonus is chosen per SIM provider (mtn/airtel/glo/9mobile)
     * rather than a single blanket plan, so it can grant the right network's
     * data bundle. Converts the singleton row into one row per provider.
     */
    public function up(): void
    {
        Schema::table('activation_bonus_settings', function (Blueprint $table) {
            $table->string('provider')->nullable()->after('id');
        });

        // The one pre-existing row (if any) was configured against an MTN
        // plan before providers existed here — keep it as the mtn row.
        DB::table('activation_bonus_settings')->limit(1)->update(['provider' => 'mtn']);

        Schema::table('activation_bonus_settings', function (Blueprint $table) {
            $table->unique('provider');
        });
    }

    public function down(): void
    {
        Schema::table('activation_bonus_settings', function (Blueprint $table) {
            $table->dropUnique(['provider']);
            $table->dropColumn('provider');
        });
    }
};
