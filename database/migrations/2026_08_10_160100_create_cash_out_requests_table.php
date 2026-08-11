<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A user's cash-out request, awaiting super_admin approval before the
     * PalmPay payout is actually sent. bank_code/bank_name/account_no/
     * account_name are a SNAPSHOT of the user's withdrawal_accounts row at
     * request time — a later account change must not retroactively alter a
     * pending or historical request.
     */
    public function up(): void
    {
        Schema::create('cash_out_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('transaction_ref')->index();
            $table->string('bank_code');
            $table->string('bank_name');
            $table->string('account_no');
            $table->string('account_name');
            $table->decimal('amount', 15, 2);
            $table->decimal('fee', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('total_charge', 15, 2);
            $table->string('status')->default('pending');
            $table->text('admin_notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_out_requests');
    }
};
