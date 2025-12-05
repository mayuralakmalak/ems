<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('category')->nullable()->after('type');
            $table->string('price_unit')->default('per person')->after('price');
            $table->date('available_from')->nullable()->after('price_unit');
            $table->date('available_to')->nullable()->after('available_from');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['category', 'price_unit', 'available_from', 'available_to']);
        });
    }
};
