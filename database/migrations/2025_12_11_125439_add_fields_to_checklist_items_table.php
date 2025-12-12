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
        Schema::table('checklist_items', function (Blueprint $table) {
            if (!Schema::hasColumn('checklist_items', 'exhibition_id')) {
                $table->foreignId('exhibition_id')->nullable()->after('id')->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('checklist_items', 'name')) {
                $table->string('name')->after('exhibition_id');
            }
            if (!Schema::hasColumn('checklist_items', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
            if (!Schema::hasColumn('checklist_items', 'item_type')) {
                $table->string('item_type', 30)->default('textbox')->after('description');
            }
            if (!Schema::hasColumn('checklist_items', 'is_required')) {
                $table->boolean('is_required')->default(false)->after('item_type');
            }
            if (!Schema::hasColumn('checklist_items', 'due_date_days_before')) {
                $table->integer('due_date_days_before')->nullable()->after('is_required');
            }
            if (!Schema::hasColumn('checklist_items', 'visible_to_user')) {
                $table->boolean('visible_to_user')->default(true)->after('due_date_days_before');
            }
            if (!Schema::hasColumn('checklist_items', 'visible_to_admin')) {
                $table->boolean('visible_to_admin')->default(true)->after('visible_to_user');
            }
            if (!Schema::hasColumn('checklist_items', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('visible_to_admin');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checklist_items', function (Blueprint $table) {
            // rollback only drops columns that were created by this migration
            foreach ([
                'exhibition_id',
                'name',
                'description',
                'item_type',
                'is_required',
                'due_date_days_before',
                'visible_to_user',
                'visible_to_admin',
                'is_active',
            ] as $column) {
                if (Schema::hasColumn('checklist_items', $column)) {
                    if ($column === 'exhibition_id') {
                        $table->dropConstrainedForeignId('exhibition_id');
                    } else {
                        $table->dropColumn($column);
                    }
                }
            }
        });
    }
};
