<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('earning_balance_histories')) {
            Schema::create('earning_balance_histories', function (Blueprint $table) {
                $table->id();
                $table->string('user_id', 40)->index();
                $table->date('entry_date')->index();
                $table->string('description', 255);
                $table->decimal('credit_amount', 14, 2)->default(0);
                $table->decimal('debit_amount', 14, 2)->default(0);
                $table->decimal('balance_amount', 14, 2)->default(0);
                $table->string('status', 40)->default('Pending');
                $table->timestamps();
            });
        }

        if (Schema::hasTable('earning_balance_histories') && DB::table('earning_balance_histories')->count() === 0) {
            DB::table('earning_balance_histories')->insert([
                'user_id' => 'MAINDEV001',
                'entry_date' => '2026-02-17',
                'description' => 'Group Built-Up Bonus',
                'credit_amount' => 4000000,
                'debit_amount' => 120000,
                'balance_amount' => 3880000,
                'status' => 'Approved',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('earning_balance_histories');
    }
};
