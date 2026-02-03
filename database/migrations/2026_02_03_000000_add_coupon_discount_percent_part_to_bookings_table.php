<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Part payment has no full-payment discount; coupon gets (max - member).
     * Store part-payment coupon % separately so UI shows correct breakdown when switching payment type.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('coupon_discount_percent_part', 5, 2)->nullable()->after('coupon_discount_percent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('coupon_discount_percent_part');
        });
    }
};
