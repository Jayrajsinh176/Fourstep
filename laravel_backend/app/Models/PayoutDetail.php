<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayoutDetail extends Model
{
protected $fillable = [
    'batch_id',
    'member_id',

    'repurchase_bonus',
    'leadership_bonus',
    'cashback_bonus',
    'binary_bonus',
    'group_buildup_bonus',
    'royalty_bonus',
    'consistency_bonus',
    'business_monitoring_bonus',
    'rank_reward_bonus',
    'branch_turnover_bonus',
    'family_saver_bonus',

    'total_amount',

    // NEW FIELDS
    'gross_amount',
    'tds',
    'admin_charge',
    'net_amount',

    'reference_no',

    'payment_status',
    'transaction_no',
    'cheque_no',
    'paid_at',
];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(PayoutBatch::class, 'batch_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PayoutDetailItem::class, 'payout_detail_id');
    }
    
public function member()
{
    return $this->belongsTo(Member::class, 'member_id', 'user_id');
}

public function kyc()
{
    return $this->hasOne(MyKyc::class, 'user_id', 'member_id');
}
}