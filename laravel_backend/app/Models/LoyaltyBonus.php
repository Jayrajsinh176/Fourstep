<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoyaltyBonus extends Model
{
    protected $table = 'loyalty_bonuses';

    protected $guarded = [];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}