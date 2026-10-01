<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('member_outbox_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_message_id')->nullable()->constrained('member_messages')->nullOnDelete();
            $table->foreignId('sender_member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->string('sender_user_id')->index();
            $table->string('receiver_user_id')->index();
            $table->string('subject');
            $table->longText('message_details');
            $table->boolean('is_read')->default(false)->index();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('member_outbox_messages');
    }
};
