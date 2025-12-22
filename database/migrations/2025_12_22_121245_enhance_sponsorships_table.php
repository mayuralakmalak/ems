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
        // Add columns only if they don't already exist to avoid duplicate column errors
        if (!Schema::hasColumn('sponsorships', 'tier') ||
            !Schema::hasColumn('sponsorships', 'max_available') ||
            !Schema::hasColumn('sponsorships', 'current_count') ||
            !Schema::hasColumn('sponsorships', 'display_order')) {

            Schema::table('sponsorships', function (Blueprint $table) {
                if (!Schema::hasColumn('sponsorships', 'tier')) {
                    $table->string('tier')->nullable()->after('is_active');
                }
                if (!Schema::hasColumn('sponsorships', 'max_available')) {
                    $table->integer('max_available')->nullable()->after('tier');
                }
                if (!Schema::hasColumn('sponsorships', 'current_count')) {
                    $table->integer('current_count')->default(0)->after('max_available');
                }
                if (!Schema::hasColumn('sponsorships', 'display_order')) {
                    $table->integer('display_order')->default(0)->after('current_count');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sponsorships', function (Blueprint $table) {
            if (Schema::hasColumn('sponsorships', 'tier')) {
                $table->dropColumn('tier');
            }
            if (Schema::hasColumn('sponsorships', 'max_available')) {
                $table->dropColumn('max_available');
            }
            if (Schema::hasColumn('sponsorships', 'current_count')) {
                $table->dropColumn('current_count');
            }
            if (Schema::hasColumn('sponsorships', 'display_order')) {
                $table->dropColumn('display_order');
            }
        });
    }
};
