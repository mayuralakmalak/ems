<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ensure countries table has code/phone_code columns expected by the app.
     */
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            if (!Schema::hasColumn('countries', 'code')) {
                $table->string('code', 5)->nullable()->after('name')->index();
            }
            if (!Schema::hasColumn('countries', 'iso3')) {
                $table->string('iso3', 8)->nullable()->after('code')->index();
            }
            if (!Schema::hasColumn('countries', 'phone_code')) {
                $table->string('phone_code', 10)->nullable()->after('iso3');
            }
            if (!Schema::hasColumn('countries', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('phone_code');
            }
            if (!Schema::hasColumn('countries', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('is_active');
            }
        });
    }

    /**
     * Rollback additions.
     */
    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            if (Schema::hasColumn('countries', 'code')) {
                $table->dropColumn('code');
            }
            if (Schema::hasColumn('countries', 'iso3')) {
                $table->dropColumn('iso3');
            }
            if (Schema::hasColumn('countries', 'phone_code')) {
                $table->dropColumn('phone_code');
            }
            if (Schema::hasColumn('countries', 'is_active')) {
                $table->dropColumn('is_active');
            }
            if (Schema::hasColumn('countries', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
        });
    }
};

