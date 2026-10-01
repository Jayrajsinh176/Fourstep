<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseBonusTransaction extends Model
{
    use HasFactory;

    protected $table = 'purchase_bonus_transactions';

    protected $fillable = [
        'member_id',
        'week_start',
        'week_end',
        'step_level',
        'left_bv_before',
        'right_bv_before',
        'matching_bv',
        'matched_pairs',
        'gross_income',
        'weekly_cap',
        'payable_income',
        'lapsed_income',
        'left_bv_after',
        'right_bv_after',
        'status',
        'calculated_at',
    ];

    protected $casts = [
        'week_start' => 'date',
        'week_end' => 'date',
        'calculated_at' => 'datetime',

        'left_bv_before' => 'decimal:2',
        'right_bv_before' => 'decimal:2',
        'matching_bv' => 'decimal:2',
        'gross_income' => 'decimal:2',
        'weekly_cap' => 'decimal:2',
        'payable_income' => 'decimal:2',
        'lapsed_income' => 'decimal:2',
        'left_bv_after' => 'decimal:2',
        'right_bv_after' => 'decimal:2',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }
}