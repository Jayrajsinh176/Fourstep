<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Models\RankRewardTier;
use App\Models\RewardAchiever;

class RankRewardController extends Controller
{


    public function rankRewards(Request $request)
    {

        $userId = trim((string) ($request->header('X-Auth-Member') ?: $request->query('user_id','')));

        if($userId === ''){
            return response()->json(['message'=>'Missing member identifier'],401);
        }

      $member = Member::where('user_id',$userId)->first();

if(!$member){
    return response()->json(['message'=>'Member not found'],404);
}

\App\Services\RewardQualificationService::check($member);


        $achievedAmount = $this->resolveAchievedAmount($member,$userId);

        $rewards = $this->buildRewardRows($achievedAmount,$member);

        $summary = $this->buildSummary($rewards,$achievedAmount);

        return response()->json([
            'data'=>[
                'summary'=>$summary,
                'rewards'=>$rewards
            ],
            'total_target'=>$summary['total_target'],
            'total_achieved'=>$summary['total_achieved'],
            'ranks_achieved'=>$summary['ranks_achieved'],
            'total_ranks'=>$summary['total_ranks'],
            'rewards'=>$rewards
        ]);

    }

private function resolveAchievedAmount(Member $member, string $userId): float
{
    return (float) DB::table('weekly_closings')
        ->where('member_id', $member->id)
        ->sum('income');
}


    private function buildRewardRows(float $achievedAmount, Member $member): array
    {

        $rows = [];

        $joinDate = Carbon::parse($member->created_at);

       $tiers = RankRewardTier::where('is_active', 1)
    ->orderBy('sort_order')
    ->get();


$previousTarget = 0;


foreach ($tiers as $index => $tier) {

    $target = (float) $tier->target_amount;


$rewardRecord = RewardAchiever::where(
    'member_id',
    $member->id
)
->where(
    'reward_tier_id',
    $tier->id
)
->first();

$status = $rewardRecord
    ? ucfirst($rewardRecord->status)
    : 'Pending';

$tierRange = $target - $previousTarget;

$currentTierAchieved = max(
    0,
    min($achievedAmount - $previousTarget, $tierRange)
);

$tierPending = max(
    0,
    $tierRange - $currentTierAchieved
);

$progress = $tierRange > 0
    ? round(($currentTierAchieved / $tierRange) * 100, 2)
    : 0;
    
$rows[] = [
    'id' => $tier->id,
    'rank' => $tier->rank_name,
    'target' => $target,
    'achieved' => $currentTierAchieved,
    'pending' => $tierPending,
    'total_achieved' => $achievedAmount,
    'progress' => $progress,
    'image' => $tier->reward_image_path,
    'reward_description' => $tier->reward_description,
    'status' => $status,
    'achieved_at' => optional($rewardRecord)->achieved_at,
];
$previousTarget = $target;
}

        return $rows;

    }


   private function buildSummary(array $rewards,float $achievedAmount): array
{
$ranksAchieved = collect($rewards)
    ->filter(function ($reward) {
        return $reward['progress'] >= 100;
    })
    ->count();
       

return [
'total_target' => RankRewardTier::where('is_active',1)
    ->max('target_amount'),

'total_achieved' => round($achievedAmount, 2),

    'ranks_achieved' => $ranksAchieved,

    'total_ranks' => RankRewardTier::where('is_active',1)
        ->count(),
];
}

}