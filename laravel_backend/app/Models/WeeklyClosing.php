<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklyClosing extends Model
{
    use HasFactory;

    protected $table = 'weekly_closings';

    protected $fillable = [
        'member_id',
        'purchase_bonus_id',
        'week_start',
        'week_end',
        'left_bv',
        'right_bv',
        'matched_bv',
        'carry_left',
        'carry_right',
        'income',
        'status',
    ];

    protected $casts = [
        'week_start' => 'date',
        'week_end' => 'date',
        'left_bv' => 'decimal:2',
        'right_bv' => 'decimal:2',
        'matched_bv' => 'decimal:2',
        'carry_left' => 'decimal:2',
        'carry_right' => 'decimal:2',
        'income' => 'decimal:2',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function purchaseBonus()
    {
        return $this->belongsTo(
            PurchaseBonusTransaction::class,
            'purchase_bonus_id'
        );
    }
}