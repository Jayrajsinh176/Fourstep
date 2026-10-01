<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        if (Schema::hasTable('pv_matchings')) {
            return;
        }

        Schema::create('pv_matchings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->decimal('left_pv', 12, 2)->default(0);
            $table->decimal('right_pv', 12, 2)->default(0);
            $table->decimal('matched_pv', 12, 2)->default(0);
            $table->decimal('lapsed_matching_pv', 12, 2)->default(0);
            $table->decimal('matching_income', 12, 2)->default(0);
            $table->date('match_date')->nullable();
            $table->timestamps();

            $table->index(['member_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pv_matchings');
    }
};
