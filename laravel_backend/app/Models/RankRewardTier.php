<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RankRewardTier extends Model
{
    protected $table = 'rank_reward_tiers';

    protected $guarded = [];

    // ✅ ADD THIS HERE
    public function achievers()
    {
        return $this->hasMany(RewardAchiever::class, 'reward_tier_id');
    }
}