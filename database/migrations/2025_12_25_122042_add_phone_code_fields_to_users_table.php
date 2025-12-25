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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'mobile_number_phone_code')) {
                $table->string('mobile_number_phone_code', 10)->nullable()->after('mobile_number');
            }
            if (!Schema::hasColumn('users', 'phone_number_phone_code')) {
                $table->string('phone_number_phone_code', 10)->nullable()->after('phone_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'mobile_number_phone_code')) {
                $table->dropColumn('mobile_number_phone_code');
            }
            if (Schema::hasColumn('users', 'phone_number_phone_code')) {
                $table->dropColumn('phone_number_phone_code');
            }
        });
    }
};
