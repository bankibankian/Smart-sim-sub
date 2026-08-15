<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 9PSB requires the vendor's own price for the exact productId in the
     * purchase payload (used for verification on their end) — our other
     * price columns are our own role-based retail prices, not this.
     */
    public function up(): void
    {
        Schema::table('sme_data', function (Blueprint $table) {
            $table->decimal('vendor_amount', 15, 2)->nullable()->after('business_price');
        });
    }

    public function down(): void
    {
        Schema::table('sme_data', function (Blueprint $table) {
            $table->dropColumn('vendor_amount');
        });
    }
};
