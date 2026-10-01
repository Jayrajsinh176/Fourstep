<?php

namespace App\Services;

use App\Models\Member;
use App\Models\RewardAchiever;
use App\Models\RankRewardTier;
use Illuminate\Support\Facades\DB;

class RewardQualificationService
{
    public static function check(Member $member)
    {
        
   $income = DB::table('weekly_closings')
    ->where('member_id', $member->id)
    ->sum('income');
        $tiers = RankRewardTier::where('is_active',1)
            ->orderBy('target_amount')
            ->get();

        foreach ($tiers as $tier) {

            if ($income >= $tier->target_amount) {

                RewardAchiever::firstOrCreate(
                    [
                        'member_id' => $member->id,
                        'reward_tier_id' => $tier->id,
                    ],
                    [
                        'achieved_at' => now(),
                        'status' => 'pending',
                    ]
                );
            }
        }
    }
}