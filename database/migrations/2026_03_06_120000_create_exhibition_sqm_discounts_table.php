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
        // Table may already exist on some environments; guard to avoid duplicate‑table errors.
        if (Schema::hasTable('exhibition_sqm_discounts')) {
            return;
        }

        Schema::create('exhibition_sqm_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exhibition_id')->constrained()->onDelete('cascade');
            $table->decimal('sqm', 10, 2);
            $table->string('operator', 2); // >, <, =, >=, <=
            $table->decimal('percentage', 5, 2);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['exhibition_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exhibition_sqm_discounts');
    }
};

