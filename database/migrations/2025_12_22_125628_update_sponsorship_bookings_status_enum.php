<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL doesn't support direct enum modification, so we use raw SQL
        DB::statement("ALTER TABLE `sponsorship_bookings` MODIFY COLUMN `status` ENUM('pending', 'confirmed', 'paid', 'cancelled') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE `sponsorship_bookings` MODIFY COLUMN `status` ENUM('pending', 'paid', 'cancelled') DEFAULT 'pending'");
    }
};
