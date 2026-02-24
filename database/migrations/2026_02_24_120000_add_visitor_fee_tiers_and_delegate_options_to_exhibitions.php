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
        Schema::table('exhibitions', function (Blueprint $table) {
            // Visitor fee tiers: early bird, standard, last minute (each: end_date + fee)
            $table->date('visitor_early_bird_end_date')->nullable()->after('vip_registration_fee');
            $table->decimal('visitor_early_bird_fee', 10, 2)->nullable()->after('visitor_early_bird_end_date');
            $table->date('visitor_standard_end_date')->nullable()->after('visitor_early_bird_fee');
            $table->decimal('visitor_standard_fee', 10, 2)->nullable()->after('visitor_standard_end_date');
            $table->date('visitor_last_minute_end_date')->nullable()->after('visitor_standard_fee');
            $table->decimal('visitor_last_minute_fee', 10, 2)->nullable()->after('visitor_last_minute_end_date');
            // Delegate: up to N members free, then additional fee per delegate
            $table->unsignedInteger('delegate_free_count')->default(2)->after('visitor_last_minute_fee');
            $table->decimal('delegate_additional_fee', 10, 2)->nullable()->after('delegate_free_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exhibitions', function (Blueprint $table) {
            $table->dropColumn([
                'visitor_early_bird_end_date', 'visitor_early_bird_fee',
                'visitor_standard_end_date', 'visitor_standard_fee',
                'visitor_last_minute_end_date', 'visitor_last_minute_fee',
                'delegate_free_count', 'delegate_additional_fee',
            ]);
        });
    }
};
