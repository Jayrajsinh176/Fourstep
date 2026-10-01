<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shoppee_Transaction extends Model
{
    use HasFactory;

    protected $table = 'shoppee_transactions';

    protected $fillable = [
        'member_id',
        'ref_id',
        'ref_type',
        'balance_type',
        'entry_type',
        'amount',
        'detail',
    ];
}