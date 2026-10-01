<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('earning_balance_withdrawals')) {
            Schema::create('earning_balance_withdrawals', function (Blueprint $table) {
                $table->id();
                $table->string('user_id', 40)->index();
                $table->date('payment_date')->index();
                $table->decimal('payment_amount', 14, 2)->default(0);
                $table->string('reference_no', 100)->nullable();
                $table->string('status', 40)->default('Pending');
                $table->timestamps();
            });
        }

        if (Schema::hasTable('earning_balance_withdrawals') && DB::table('earning_balance_withdrawals')->count() === 0) {
            DB::table('earning_balance_withdrawals')->insert([
                'user_id' => 'MAINDEV001',
                'payment_date' => '2026-02-17',
                'payment_amount' => 500,
                'reference_no' => 'DB50012',
                'status' => 'Approved',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('earning_balance_withdrawals');
    }
};
