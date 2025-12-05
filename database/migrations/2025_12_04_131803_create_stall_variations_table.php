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
        Schema::create('stall_variations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exhibition_id')->constrained()->onDelete('cascade');
            $table->string('stall_type'); // e.g., "1 Side Open", "2 Sides Open"
            $table->integer('sides_open');
            $table->string('front_view')->nullable();
            $table->string('side_view_left')->nullable();
            $table->string('side_view_right')->nullable();
            $table->string('back_view')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stall_variations');
    }
};
