<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RepurchaseWalletTransaction extends Model
{
    protected $table = 'repurchase_wallet_transactions';

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'description',
        'balance_after',
    ];
}