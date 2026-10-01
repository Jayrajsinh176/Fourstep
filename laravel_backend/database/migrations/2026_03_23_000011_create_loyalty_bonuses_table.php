<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('loyalty_bonuses')) {
            return;
        }

        Schema::create('loyalty_bonuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->string('month_key', 7)->index();
            $table->decimal('purchase_amount', 14, 2)->default(0);
            $table->decimal('minimum_required', 14, 2)->default(500);
            $table->boolean('requirement_met')->default(false);
            $table->decimal('bonus_percentage', 6, 2)->default(0);
            $table->decimal('bonus_amount', 14, 2)->default(0);
            $table->string('type', 40)->default('monthly')->index();
            $table->string('status', 40)->default('pending');
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();

            $table->unique(['member_id', 'month_key', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_bonuses');
    }
};
