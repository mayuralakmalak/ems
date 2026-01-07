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
            $table->foreignId('discount_id')
                ->nullable()
                ->after('exhibition_booth_size_id')
                ->constrained('discounts')
                ->onDelete('set null');

            $table->foreignId('discount_user_id')
                ->nullable()
                ->after('discount_id')
                ->constrained('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booths', function (Blueprint $table) {
            $table->dropConstrainedForeignId('discount_id');
            $table->dropConstrainedForeignId('discount_user_id');
        });
    }
};

