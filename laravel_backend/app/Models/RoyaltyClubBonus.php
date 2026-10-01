<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoyaltyClubBonus extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'month_key',
        'period_start',
        'period_end',
        'monthly_turnover',
        'pool_percentage',
        'royalty_pool_amount',
        'eligible_users_count',
        'bonus_amount',
        'status',
        'calculated_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'monthly_turnover' => 'decimal:2',
        'pool_percentage' => 'decimal:4',
        'royalty_pool_amount' => 'decimal:2',
        'bonus_amount' => 'decimal:2',
        'calculated_at' => 'datetime',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }
}
