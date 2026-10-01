<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('loyalty_bonuses')) {
            return;
        }

        Schema::table('loyalty_bonuses', function (Blueprint $table) {
            if (!Schema::hasColumn('loyalty_bonuses', 'minimum_required')) {
                $table->decimal('minimum_required', 14, 2)->default(500)->after('purchase_amount');
            }

            if (!Schema::hasColumn('loyalty_bonuses', 'requirement_met')) {
                $table->boolean('requirement_met')->default(false)->after('minimum_required');
            }

            if (!Schema::hasColumn('loyalty_bonuses', 'calculated_at')) {
                $table->timestamp('calculated_at')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('loyalty_bonuses')) {
            return;
        }

        Schema::table('loyalty_bonuses', function (Blueprint $table) {
            if (Schema::hasColumn('loyalty_bonuses', 'calculated_at')) {
                $table->dropColumn('calculated_at');
            }

            if (Schema::hasColumn('loyalty_bonuses', 'requirement_met')) {
                $table->dropColumn('requirement_met');
            }

            if (Schema::hasColumn('loyalty_bonuses', 'minimum_required')) {
                $table->dropColumn('minimum_required');
            }
        });
    }
};
