<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiwaliBonus extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'bonus_year',
        'period_start',
        'period_end',
        'total_lapsed_pv',
        'bonus_percentage',
        'bonus_amount',
        'calculated_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'total_lapsed_pv' => 'decimal:2',
        'bonus_percentage' => 'decimal:2',
        'bonus_amount' => 'decimal:2',
        'calculated_at' => 'datetime',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
