<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = [
        'user_id',
        'matching_income',
        'repurchase_income',
        'royalty_income',
        'family_saver_income',
        'total_income',
    ];
}