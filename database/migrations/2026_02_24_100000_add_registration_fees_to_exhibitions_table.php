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
        Schema::table('exhibitions', function (Blueprint $table) {
            $table->decimal('visitor_fee', 10, 2)->nullable();
            $table->decimal('member_fee', 10, 2)->nullable();
            $table->decimal('delegate_fee', 10, 2)->nullable();
            $table->decimal('vip_registration_fee', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exhibitions', function (Blueprint $table) {
            $table->dropColumn(['visitor_fee', 'member_fee', 'delegate_fee', 'vip_registration_fee']);
        });
    }
};
