<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discounts', function (Blueprint $table) {
            if (Schema::hasColumn('discounts', 'status')) {
                $table->dropColumn('status');
            }
            $dropColumns = [];
            foreach (['name', 'discount_percent', 'start_date', 'end_date', 'description'] as $column) {
                if (Schema::hasColumn('discounts', $column)) {
                    $dropColumns[] = $column;
                }
            }
            if (!empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });

        Schema::table('discounts', function (Blueprint $table) {
            if (!Schema::hasColumn('discounts', 'title')) {
                $table->string('title')->after('id');
            }

            if (!Schema::hasColumn('discounts', 'code')) {
                $table->string('code')->unique()->after('title');
            }

            if (!Schema::hasColumn('discounts', 'type')) {
                $table->enum('type', ['fixed', 'percentage'])->default('fixed')->after('code');
            }

            if (!Schema::hasColumn('discounts', 'amount')) {
                $table->decimal('amount', 10, 2)->default(0)->after('type');
            }

            $table->enum('status', ['active', 'inactive'])->default('active')->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('discounts', function (Blueprint $table) {
            if (Schema::hasColumn('discounts', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('discounts', 'amount')) {
                $table->dropColumn('amount');
            }
            if (Schema::hasColumn('discounts', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('discounts', 'title')) {
                $table->dropColumn('title');
            }

            $table->string('name')->nullable();
            $table->decimal('discount_percent', 5, 2)->nullable();
            $table->enum('status', ['active', 'completed', 'inactive'])->default('active');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('description')->nullable();
        });
    }
};
