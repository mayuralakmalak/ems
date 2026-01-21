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
        if (Schema::hasColumn('payments', 'additional_service_request_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropForeign(['additional_service_request_id']);
                $table->dropColumn('additional_service_request_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('additional_service_request_id')->nullable()->after('user_id')->constrained('additional_service_requests')->nullOnDelete();
        });
    }
};
