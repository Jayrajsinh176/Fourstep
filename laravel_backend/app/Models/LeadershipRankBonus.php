<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadershipRankBonus extends Model
{
    use HasFactory;

    protected $fillable = [
        'upline_member_id',
        'source_member_id',
        'source_pv_matching_id',
        'generation_no',
        'cycle_date',
        'matching_income',
        'bonus_percentage',
        'bonus_amount',
        'rank_name',
        'rank_achieved_date',
        'status',
        'calculated_at',
    ];

    protected $casts = [
        'cycle_date' => 'date',
        'rank_achieved_date' => 'date',
        'matching_income' => 'decimal:2',
        'bonus_percentage' => 'decimal:2',
        'bonus_amount' => 'decimal:2',
        'calculated_at' => 'datetime',
    ];

    public function upline()
    {
        return $this->belongsTo(Member::class, 'upline_member_id');
    }

    public function sourceMember()
    {
        return $this->belongsTo(Member::class, 'source_member_id');
    }
}
