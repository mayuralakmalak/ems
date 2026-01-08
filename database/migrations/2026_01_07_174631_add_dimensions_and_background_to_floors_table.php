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
        Schema::table('floors', function (Blueprint $table) {
            $table->decimal('width_meters', 10, 2)->nullable()->after('description');
            $table->decimal('height_meters', 10, 2)->nullable()->after('width_meters');
            $table->string('background_image')->nullable()->after('height_meters');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('floors', function (Blueprint $table) {
            $table->dropColumn(['width_meters', 'height_meters', 'background_image']);
        });
    }
};
