<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A user's single saved cash-out destination bank account. The unique
     * user_id enforces "only one account" — saving again overwrites it via
     * updateOrCreate rather than adding a second row.
     */
    public function up(): void
    {
        Schema::create('withdrawal_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('bank_code');
            $table->string('bank_name');
            $table->string('account_no');
            $table->string('account_name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawal_accounts');
    }
};
