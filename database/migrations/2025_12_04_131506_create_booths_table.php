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
        Schema::create('booths', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exhibition_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g., D1, D2, D1D2 (merged)
            $table->string('category')->default('Standard'); // Premium, Standard, Economy
            $table->enum('booth_type', ['Raw', 'Orphand'])->default('Raw');
            $table->decimal('size_sqft', 10, 2);
            $table->integer('sides_open')->default(1); // 1, 2, 3, or 4
            $table->decimal('price', 10, 2);
            $table->boolean('is_free')->default(false);
            $table->boolean('is_available')->default(true);
            $table->boolean('is_booked')->default(false);
            $table->string('logo')->nullable(); // Exhibitor logo after booking
            $table->text('coordinates')->nullable(); // For floorplan positioning
            $table->json('merged_booths')->nullable(); // For merged booths [1, 2]
            $table->boolean('is_merged')->default(false);
            $table->boolean('is_split')->default(false);
            $table->foreignId('parent_booth_id')->nullable()->constrained('booths')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booths');
    }
};
