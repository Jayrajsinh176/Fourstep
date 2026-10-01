<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('royalty_club_bonuses')) {
            return;
        }

        Schema::create('royalty_club_bonuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->string('month_key', 7)->index();
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->decimal('monthly_turnover', 14, 2)->default(0);
            $table->decimal('pool_percentage', 10, 4)->default(3.0);
            $table->decimal('royalty_pool_amount', 14, 2)->default(0);
            $table->unsignedInteger('eligible_users_count')->default(0);
            $table->decimal('bonus_amount', 14, 2)->default(0);
            $table->string('status', 40)->default('pending');
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();

            $table->unique(['member_id', 'month_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('royalty_club_bonuses');
    }
};
