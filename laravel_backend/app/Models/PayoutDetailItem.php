<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayoutDetailItem extends Model
{
    protected $fillable = [
        'payout_detail_id',
        'income_type',
        'source_table',
        'source_id',
        'amount',
    ];

    public function payoutDetail(): BelongsTo
    {
        return $this->belongsTo(PayoutDetail::class, 'payout_detail_id');
    }
}