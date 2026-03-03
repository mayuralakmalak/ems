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
            $table->decimal('usable_area_percentage', 5, 2)
                ->nullable()
                ->after('height_meters');
            $table->decimal('passage_area_percentage', 5, 2)
                ->nullable()
                ->after('usable_area_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('floors', function (Blueprint $table) {
            $table->dropColumn(['usable_area_percentage', 'passage_area_percentage']);
        });
    }
};

