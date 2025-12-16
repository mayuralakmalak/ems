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
        Schema::table('services', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['exhibition_id']);
            
            // Drop columns
            $table->dropColumn([
                'exhibition_id',
                'category',
                'type',
                'price',
                'price_unit',
                'available_from',
                'available_to'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // Re-add columns in correct order
            $table->foreignId('exhibition_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            $table->string('type')->nullable()->after('description');
            $table->string('category')->nullable()->after('type');
            $table->decimal('price', 10, 2)->nullable()->after('category');
            $table->string('price_unit')->default('per person')->after('price');
            $table->date('available_from')->nullable()->after('price_unit');
            $table->date('available_to')->nullable()->after('available_from');
        });
    }
};
