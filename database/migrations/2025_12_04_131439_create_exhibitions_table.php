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
        Schema::create('exhibitions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('venue');
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('country');
            $table->date('start_date');
            $table->date('end_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('floorplan_image')->nullable();
            $table->decimal('price_per_sqft', 10, 2)->default(0);
            $table->decimal('raw_price_per_sqft', 10, 2)->default(0);
            $table->decimal('orphand_price_per_sqft', 10, 2)->default(0);
            $table->decimal('side_1_open_percent', 5, 2)->default(0);
            $table->decimal('side_2_open_percent', 5, 2)->default(0);
            $table->decimal('side_3_open_percent', 5, 2)->default(0);
            $table->decimal('side_4_open_percent', 5, 2)->default(0);
            $table->decimal('premium_price', 10, 2)->default(0);
            $table->decimal('standard_price', 10, 2)->default(0);
            $table->decimal('economy_price', 10, 2)->default(0);
            $table->date('addon_services_cutoff_date')->nullable();
            $table->date('document_upload_deadline')->nullable();
            $table->decimal('initial_payment_percent', 5, 2)->default(10);
            $table->string('exhibition_manual_pdf')->nullable();
            $table->enum('status', ['draft', 'active', 'completed', 'cancelled'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exhibitions');
    }
};
