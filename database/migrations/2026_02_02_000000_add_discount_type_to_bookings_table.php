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
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('discount_type', 20)->nullable()->after('discount_percent'); // 'member', 'coupon', 'both'
            $table->decimal('member_discount_percent', 5, 2)->nullable()->after('discount_type');
            $table->decimal('coupon_discount_percent', 5, 2)->nullable()->after('member_discount_percent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'member_discount_percent', 'coupon_discount_percent']);
        });
    }
};
