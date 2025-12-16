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
        // Only create if table doesn't exist (avoid duplicate create error)
        if (!Schema::hasTable('checklist_items')) {
            Schema::create('checklist_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('exhibition_id')->nullable()->constrained()->nullOnDelete();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('item_type', 30)->default('textbox');
                $table->boolean('is_required')->default(false);
                $table->integer('due_date_days_before')->nullable();
                $table->boolean('visible_to_user')->default(true);
                $table->boolean('visible_to_admin')->default(true);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        } else {
            // If table exists, just add missing columns
            Schema::table('checklist_items', function (Blueprint $table) {
                if (!Schema::hasColumn('checklist_items', 'item_type')) {
                    $table->string('item_type', 30)->default('textbox')->after('description');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checklist_items');
    }
};
