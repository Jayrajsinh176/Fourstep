<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('company_turnovers')) {
            return;
        }

        Schema::create('company_turnovers', function (Blueprint $table) {
            $table->id();
            $table->string('month_key', 7)->unique();
            $table->decimal('turnover_amount', 14, 2)->default(0);
            $table->string('source', 100)->default('manual');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_turnovers');
    }
};
