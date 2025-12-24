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
        Schema::table('messages', function (Blueprint $table) {
            $table->string('thread_id')->nullable()->after('id');
            $table->index('thread_id');
        });
        
        // Generate thread_id for existing messages based on sender/receiver pair
        // This ensures existing messages are grouped properly
        \DB::statement("
            UPDATE messages 
            SET thread_id = CONCAT(
                LEAST(sender_id, receiver_id), 
                '_', 
                GREATEST(sender_id, receiver_id),
                '_',
                DATE_FORMAT(created_at, '%Y%m%d%H%i%s')
            )
            WHERE thread_id IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['thread_id']);
            $table->dropColumn('thread_id');
        });
    }
};
