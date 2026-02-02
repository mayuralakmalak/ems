<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exhibitions', function (Blueprint $table) {
            $table->decimal('member_discount_percent', 5, 2)->nullable()->after('full_payment_discount_percent');
            $table->decimal('maximum_discount_apply_percent', 5, 2)->nullable()->after('member_discount_percent');
        });
    }

    public function down(): void
    {
        Schema::table('exhibitions', function (Blueprint $table) {
            $table->dropColumn(['member_discount_percent', 'maximum_discount_apply_percent']);
        });
    }
};
