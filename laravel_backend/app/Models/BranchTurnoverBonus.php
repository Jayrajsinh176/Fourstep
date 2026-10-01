<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchTurnoverBonus extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'month_key',
        'total_turnover',
        'commission_percentage',
        'bonus_amount',
        'status',
        'calculated_at',
    ];

    protected $casts = [
        'total_turnover' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
        'bonus_amount' => 'decimal:2',
        'calculated_at' => 'datetime',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
