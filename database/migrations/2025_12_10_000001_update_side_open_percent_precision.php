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
        Schema::table('exhibitions', function (Blueprint $table) {
            $table->decimal('side_1_open_percent', 10, 2)->default(0)->change();
            $table->decimal('side_2_open_percent', 10, 2)->default(0)->change();
            $table->decimal('side_3_open_percent', 10, 2)->default(0)->change();
            $table->decimal('side_4_open_percent', 10, 2)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exhibitions', function (Blueprint $table) {
            $table->decimal('side_1_open_percent', 5, 2)->default(0)->change();
            $table->decimal('side_2_open_percent', 5, 2)->default(0)->change();
            $table->decimal('side_3_open_percent', 5, 2)->default(0)->change();
            $table->decimal('side_4_open_percent', 5, 2)->default(0)->change();
        });
    }
};
