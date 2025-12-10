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
        Schema::create('exhibition_booth_size_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exhibition_booth_size_id')->constrained()->onDelete('cascade');
            $table->string('item_name')->nullable();
            $table->integer('quantity')->default(0);
            $table->json('images')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exhibition_booth_size_items');
    }
};
