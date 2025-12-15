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
            $table->foreignId('exhibition_booth_size_id')
                ->nullable()
                ->after('parent_booth_id')
                ->constrained('exhibition_booth_sizes')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booths', function (Blueprint $table) {
            $table->dropConstrainedForeignId('exhibition_booth_size_id');
        });
    }
};

