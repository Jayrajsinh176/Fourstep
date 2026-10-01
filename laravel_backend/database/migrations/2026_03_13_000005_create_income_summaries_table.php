<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('income_summaries')) {
            Schema::create('income_summaries', function (Blueprint $table) {
                $table->id();
                $table->string('user_id', 40)->index();
                $table->date('entry_date')->index();
                $table->string('description', 255);
                $table->decimal('credit_amount', 14, 2)->default(0);
                $table->decimal('debit_amount', 14, 2)->default(0);
                $table->decimal('balance_amount', 14, 2)->default(0);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('income_summaries') && DB::table('income_summaries')->count() === 0) {
            DB::table('income_summaries')->insert([
                'user_id' => 'MAINDEV001',
                'entry_date' => '2026-02-17',
                'description' => 'DB50012',
                'credit_amount' => 500,
                'debit_amount' => 0,
                'balance_amount' => 500,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('income_summaries');
    }
};
