<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('members')) {
            return;
        }

        Schema::table('members', function (Blueprint $table) {
            if (!Schema::hasColumn('members', 'nominee_name')) {
                $table->string('nominee_name')->nullable()->after('shipping_pin_code');
            }
            if (!Schema::hasColumn('members', 'nominee_relation')) {
                $table->string('nominee_relation')->nullable()->after('nominee_name');
            }
            if (!Schema::hasColumn('members', 'nominee_mobile_no')) {
                $table->string('nominee_mobile_no')->nullable()->after('nominee_relation');
            }
            if (!Schema::hasColumn('members', 'nominee_address')) {
                $table->text('nominee_address')->nullable()->after('nominee_mobile_no');
            }
            if (!Schema::hasColumn('members', 'nominee_state')) {
                $table->string('nominee_state')->nullable()->after('nominee_address');
            }
            if (!Schema::hasColumn('members', 'nominee_city')) {
                $table->string('nominee_city')->nullable()->after('nominee_state');
            }
            if (!Schema::hasColumn('members', 'nominee_district')) {
                $table->string('nominee_district')->nullable()->after('nominee_city');
            }
            if (!Schema::hasColumn('members', 'nominee_pin_code')) {
                $table->string('nominee_pin_code')->nullable()->after('nominee_district');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('members')) {
            return;
        }

        Schema::table('members', function (Blueprint $table) {
            $dropCols = [];

            foreach ([
                'nominee_name',
                'nominee_relation',
                'nominee_mobile_no',
                'nominee_address',
                'nominee_state',
                'nominee_city',
                'nominee_district',
                'nominee_pin_code',
            ] as $col) {
                if (Schema::hasColumn('members', $col)) {
                    $dropCols[] = $col;
                }
            }

            if (!empty($dropCols)) {
                $table->dropColumn($dropCols);
            }
        });
    }
};
