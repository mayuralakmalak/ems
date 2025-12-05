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
        Schema::create('badge_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exhibition_id')->constrained()->onDelete('cascade');
            $table->enum('badge_type', ['Primary', 'Secondary', 'Additional']);
            $table->integer('quantity');
            $table->enum('pricing_type', ['Free', 'Paid']);
            $table->decimal('price', 10, 2)->default(0);
            $table->boolean('needs_admin_approval')->default(false);
            $table->json('access_permissions')->nullable(); // Entry Only, Lunch, Snacks
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('badge_configurations');
    }
};
