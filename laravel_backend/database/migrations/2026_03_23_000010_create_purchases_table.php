<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('purchases')) {
            return;
        }

        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->string('invoice_no', 100)->nullable()->index();
            $table->date('purchase_date')->index();
            $table->decimal('amount', 14, 2)->default(0);
            $table->decimal('cashback_amount', 14, 2)->default(0);
            $table->string('status', 40)->default('approved');
            $table->timestamps();

            $table->index(['member_id', 'purchase_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
