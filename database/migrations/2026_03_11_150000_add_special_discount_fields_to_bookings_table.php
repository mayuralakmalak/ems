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
            $table->enum('special_discount_type', ['fixed', 'percent'])
                ->nullable()
                ->after('discount_type');
            $table->decimal('special_discount_value', 10, 2)
                ->nullable()
                ->after('special_discount_type');
            $table->decimal('special_discount_amount', 10, 2)
                ->nullable()
                ->after('special_discount_value');
            $table->text('special_discount_note')
                ->nullable()
                ->after('special_discount_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'special_discount_type',
                'special_discount_value',
                'special_discount_amount',
                'special_discount_note',
            ]);
        });
    }
};

