<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [

        'expense_date',

        'expense_type',

        'amount',

        'payment_mode',

        'remarks',

        'created_by',

    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}