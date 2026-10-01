<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inbox extends Model
{
    protected $table = 'messages'; // database table name

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'subject',
        'message',
        'status'
    ];
}