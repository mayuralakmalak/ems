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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exhibition_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('booth_id')->constrained()->onDelete('cascade');
            $table->string('booking_number')->unique();
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'replaced'])->default('pending');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->json('contact_emails')->nullable(); // Up to 5 emails
            $table->json('contact_numbers')->nullable(); // Up to 5 numbers
            $table->string('logo')->nullable();
            $table->boolean('possession_letter_issued')->default(false);
            $table->text('cancellation_reason')->nullable();
            $table->enum('cancellation_type', ['refund', 'wallet_credit'])->nullable();
            $table->decimal('cancellation_amount', 10, 2)->nullable();
            $table->text('account_details')->nullable(); // For refund
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
