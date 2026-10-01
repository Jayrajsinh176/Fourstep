<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            if (!Schema::hasColumn('members', 'shipping_address')) {
                $table->text('shipping_address')->nullable()->after('district');
            }

            if (!Schema::hasColumn('members', 'shipping_state')) {
                $table->string('shipping_state')->nullable()->after('shipping_address');
            }

            if (!Schema::hasColumn('members', 'shipping_city')) {
                $table->string('shipping_city')->nullable()->after('shipping_state');
            }

            if (!Schema::hasColumn('members', 'shipping_district')) {
                $table->string('shipping_district')->nullable()->after('shipping_city');
            }

            if (!Schema::hasColumn('members', 'shipping_pin_code')) {
                $table->string('shipping_pin_code')->nullable()->after('shipping_district');
            }
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $dropCols = [];

            foreach (['shipping_address', 'shipping_state', 'shipping_city', 'shipping_district', 'shipping_pin_code'] as $col) {
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
