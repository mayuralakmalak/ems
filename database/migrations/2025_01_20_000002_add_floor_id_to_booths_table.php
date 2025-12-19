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
            $table->foreignId('floor_id')
                ->nullable()
                ->after('exhibition_id')
                ->constrained('floors')
                ->onDelete('cascade');
            
            // Add index for better query performance
            $table->index(['exhibition_id', 'floor_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booths', function (Blueprint $table) {
            $table->dropForeign(['floor_id']);
            $table->dropIndex(['exhibition_id', 'floor_id']);
            $table->dropColumn('floor_id');
        });
    }
};
