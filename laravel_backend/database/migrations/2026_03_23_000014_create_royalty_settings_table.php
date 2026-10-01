<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('royalty_settings')) {
            Schema::create('royalty_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key', 120)->unique();
                $table->decimal('value', 10, 4)->default(0);
                $table->string('description', 255)->nullable();
                $table->timestamps();
            });
        }

        $exists = DB::table('royalty_settings')->where('key', 'royalty_pool_percentage')->exists();

        if (!$exists) {
            DB::table('royalty_settings')->insert([
                'key' => 'royalty_pool_percentage',
                'value' => 3.0,
                'description' => 'Monthly royalty pool percentage from company turnover',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('royalty_settings')) {
            Schema::dropIfExists('royalty_settings');
        }
    }
};
