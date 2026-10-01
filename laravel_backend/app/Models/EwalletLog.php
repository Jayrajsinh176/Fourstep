<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EwalletLog extends Model
{
    protected $fillable = [
        'member_id',
        'amount',
        'type',
        'remark',
        'status',
    ];
}