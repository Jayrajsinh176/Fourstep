<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyRepurchaseSummary extends Model
{
    protected $table = 'monthly_repurchase_summary';

    protected $fillable = [
        'user_id',
        'month',
        'year',
        'total_purchase',
        'is_eligible',
        'consistency_count',
    ];
}