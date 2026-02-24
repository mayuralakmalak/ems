<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_registration_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_registration_id')->constrained()->onDelete('cascade');
            $table->string('payment_number')->unique();
            $table->enum('payment_method', ['online', 'neft', 'rtgs', 'wallet', 'offline'])->default('offline');
            $table->decimal('amount', 10, 2);
            $table->decimal('gateway_charge', 10, 2)->default(0);
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('transaction_id')->nullable();
            $table->string('payment_proof_file')->nullable();
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('token', 64)->unique()->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_registration_payments');
    }
};
