<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('payment_number')->unique();
            $table->enum('payment_type', ['initial', 'installment', 'full', 'refund', 'wallet_credit']);
            $table->enum('payment_method', ['online', 'offline', 'rtgs', 'neft', 'wallet']);
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->decimal('amount', 10, 2);
            $table->decimal('gateway_charge', 10, 2)->default(0); // 2.5% for online
            $table->string('transaction_id')->nullable();
            $table->string('receipt_file')->nullable();
            $table->string('invoice_file')->nullable();
            $table->text('payment_proof')->nullable(); // For RTGS/NEFT
            $table->date('due_date')->nullable();
            $table->date('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
