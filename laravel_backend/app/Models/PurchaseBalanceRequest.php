<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseBalanceRequest extends Model
{
    protected $table = 'balance_requests';

    protected $fillable = [
    'member_id',
    'type',
    'amount',
    'mode_of_payment',
    'transaction_no',
    'payment_slip',
    'status',
];
}