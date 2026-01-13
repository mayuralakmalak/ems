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
        Schema::table('exhibition_booth_sizes', function (Blueprint $table) {
            $table->foreignId('size_type_id')->nullable()->after('size_sqft')->constrained('size_types')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exhibition_booth_sizes', function (Blueprint $table) {
            $table->dropForeign(['size_type_id']);
            $table->dropColumn('size_type_id');
        });
    }
};
