<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->unique();

            $table->unsignedBigInteger('sponsor_id')->nullable()->index();
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->enum('position', ['left', 'right'])->nullable();

            
            $table->string('fullname');
            $table->date('dob');
            $table->string('gender')->nullable();
            $table->string('pan_number')->unique()->nullable();
            $table->string('mobile_no')->unique();
            $table->string('email')->unique()->nullable();
            $table->string('password');

            $table->text('address')->nullable();
            $table->string('pin_code')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();

            $table->tinyInteger('status')->default(0)->index(); // 0 = inactive, 1 = active
            $table->timestamp('activation_date')->nullable();

            $table->timestamps();
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
