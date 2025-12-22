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
        Schema::create('sponsorship_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sponsorship_booking_id')->constrained('sponsorship_bookings')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('payment_number')->unique();
            $table->enum('payment_method', ['online', 'offline', 'rtgs', 'neft', 'wallet'])->default('online');
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled', 'refunded'])->default('pending');
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->decimal('amount', 10, 2);
            $table->decimal('gateway_charge', 10, 2)->default(0);
            $table->string('transaction_id')->nullable();
            $table->string('receipt_file')->nullable();
            $table->string('invoice_file')->nullable();
            $table->text('payment_proof')->nullable();
            $table->string('payment_proof_file')->nullable();
            $table->date('due_date')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sponsorship_payments');
    }
};
