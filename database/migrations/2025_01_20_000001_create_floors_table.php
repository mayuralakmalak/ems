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
        Schema::create('floors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exhibition_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g., "Ground Floor", "First Floor", "Floor 1"
            $table->integer('floor_number')->default(1); // For ordering: 0, 1, 2, etc.
            $table->text('description')->nullable();
            $table->string('floorplan_image')->nullable(); // Single floorplan image (backward compatibility)
            $table->json('floorplan_images')->nullable(); // Multiple floorplan images
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0); // For custom ordering
            $table->timestamps();
            
            $table->index(['exhibition_id', 'floor_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('floors');
    }
};
