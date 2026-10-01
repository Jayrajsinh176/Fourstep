<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardAchiever extends Model
{
    protected $guarded = [];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function reward()
    {
        return $this->belongsTo(RankRewardTier::class, 'reward_tier_id');
    }
}