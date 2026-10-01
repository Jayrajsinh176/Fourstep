<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessMonitoringBonus extends Model
{
    use HasFactory;

    protected $table = 'business_monitoring_bonuses';

    protected $fillable = [
        'sponsor_member_id',
        'downline_member_id',
        'cycle_date',
        'matching_income',
        'bonus_percentage',
        'bonus_amount',
        'status',
        'calculated_at',
    ];

    protected $casts = [
        'cycle_date' => 'date',
        'matching_income' => 'decimal:2',
        'bonus_percentage' => 'decimal:2',
        'bonus_amount' => 'decimal:2',
        'calculated_at' => 'datetime',
    ];

    public function sponsor()
    {
        return $this->belongsTo(Member::class, 'sponsor_member_id');
    }

    public function downline()
    {
        return $this->belongsTo(Member::class, 'downline_member_id');
    }
}
