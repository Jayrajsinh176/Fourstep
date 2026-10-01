<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   
    public function up(): void
    {
        if (!Schema::hasTable('branches')) {
            return;
        }

        Schema::table('branches', function (Blueprint $table) {
            if (!Schema::hasColumn('branches', 'user_id')) {
                $table->string('user_id')->nullable()->index()->after('id');
            }
        });
    }

   
    public function down(): void
    {
        if (!Schema::hasTable('branches') || !Schema::hasColumn('branches', 'user_id')) {
            return;
        }

        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
};
