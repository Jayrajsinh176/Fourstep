<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashbackWallet extends Model
{
    protected $table = 'cashback_wallet';

    protected $fillable = [
        'user_id',
        'order_id',
        'detail',
        'credit',
        'debit',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'user_id');
    }
}