<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the enum to include 'deleted'
        DB::statement("ALTER TABLE messages MODIFY COLUMN status ENUM('inbox', 'pending', 'completed', 'archived', 'deleted') DEFAULT 'inbox'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum (remove 'deleted')
        DB::statement("ALTER TABLE messages MODIFY COLUMN status ENUM('inbox', 'pending', 'completed', 'archived') DEFAULT 'inbox'");
    }
};
