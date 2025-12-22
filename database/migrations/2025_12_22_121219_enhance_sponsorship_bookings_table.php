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
        Schema::table('sponsorship_bookings', function (Blueprint $table) {
            $table->string('booking_number')->unique()->nullable()->after('id');
            $table->decimal('paid_amount', 10, 2)->default(0)->after('amount');
            $table->enum('payment_status', ['pending', 'partial', 'paid', 'refunded'])->default('pending')->after('status');
            $table->json('contact_emails')->nullable()->after('exhibition_id');
            $table->json('contact_numbers')->nullable()->after('contact_emails');
            $table->string('logo')->nullable()->after('contact_numbers');
            $table->text('notes')->nullable()->after('logo');
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending')->after('payment_status');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null')->after('approval_status');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('rejection_reason')->nullable()->after('approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sponsorship_bookings', function (Blueprint $table) {
            $table->dropColumn([
                'booking_number',
                'paid_amount',
                'payment_status',
                'contact_emails',
                'contact_numbers',
                'logo',
                'notes',
                'approval_status',
                'approved_by',
                'approved_at',
                'rejection_reason'
            ]);
        });
    }
};
