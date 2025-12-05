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
        Schema::table('booths', function (Blueprint $table) {
            $table->decimal('position_x', 10, 2)->nullable()->after('coordinates'); // X position on floorplan
            $table->decimal('position_y', 10, 2)->nullable()->after('position_x'); // Y position on floorplan
            $table->decimal('width', 10, 2)->nullable()->after('position_y'); // Width in pixels
            $table->decimal('height', 10, 2)->nullable()->after('width'); // Height in pixels
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booths', function (Blueprint $table) {
            $table->dropColumn(['position_x', 'position_y', 'width', 'height']);
        });
    }
};
