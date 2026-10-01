<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_member_id',
        'sender_user_id',
        'receiver_member_id',
        'receiver_user_id',
        'subject',
        'message_details',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];
}
