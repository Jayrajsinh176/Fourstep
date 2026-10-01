<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayoutBatch extends Model
{
    
 protected $fillable = [
    'batch_no',
    'payout_date',
    'total_members',
    'total_amount',
    'status',
    'sheet_file',
];

    public function details(): HasMany
    {
        return $this->hasMany(PayoutDetail::class, 'batch_id');
    }
}