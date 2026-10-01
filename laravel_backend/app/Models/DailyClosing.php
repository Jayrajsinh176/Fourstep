<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyClosing extends Model
{
protected $fillable = [
    'member_id',
    'group_bonus_id',
    'left_pv',
    'right_pv',
    'matched_pv',
    'carry_left',
    'carry_right',
    'income',
    'cycle',
    'closing_date',
    'status',

    // Payout exclusion
    'payout_excluded',
    'payout_excluded_reason',
    'payout_excluded_by',
    'payout_excluded_at',
];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }
}