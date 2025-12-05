<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checklist_items', function (Blueprint $table) {
            $table->boolean('is_required')->default(false)->after('description');
            $table->integer('due_date_days_before')->nullable()->after('is_required');
            $table->boolean('visible_to_user')->default(true)->after('due_date_days_before');
            $table->boolean('visible_to_admin')->default(true)->after('visible_to_user');
        });
    }

    public function down(): void
    {
        Schema::table('checklist_items', function (Blueprint $table) {
            $table->dropColumn(['is_required', 'due_date_days_before', 'visible_to_user', 'visible_to_admin']);
        });
    }
};
