<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CapitalTransaction extends Model
{
    protected $table = 'capital_transactions';

    protected $fillable = [
        'transaction_date',
        'transaction_type',
         'category',
        'amount',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
    ];
}