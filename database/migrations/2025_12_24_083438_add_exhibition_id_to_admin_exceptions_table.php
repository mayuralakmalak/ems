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
        Schema::table('admin_exceptions', function (Blueprint $table) {
            $table->foreignId('exhibition_id')->nullable()->after('booking_id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_exceptions', function (Blueprint $table) {
            $table->dropForeign(['exhibition_id']);
            $table->dropColumn('exhibition_id');
        });
    }
};
