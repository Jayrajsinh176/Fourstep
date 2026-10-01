<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Member;
use App\Models\MatchingHistory;
use App\Models\Wallet;

class RunDailyMatching extends Command
{
    protected $signature = 'matching:daily';
    protected $description = 'Run Daily Matching Closing';

    public function handle()
    {
        $members = Member::where('is_active', 1)->get();

        foreach ($members as $user) {

            $leftBV = $this->getTotalBV($user->id, 'left');
            $rightBV = $this->getTotalBV($user->id, 'right');

            $leftTotal = $leftBV + $user->carry_forward_left;
            $rightTotal = $rightBV + $user->carry_forward_right;

            $matchedBV = min($leftTotal, $rightTotal);

            $income = $matchedBV; // 100% BV Matching

            $dailyCap = $this->getDailyCap($user->package_step);

            if ($income > $dailyCap) {
                $income = $dailyCap;
            }

            $carryLeft = $leftTotal - $matchedBV;
            $carryRight = $rightTotal - $matchedBV;

            MatchingHistory::create([
                'user_id' => $user->id,
                'left_bv' => $leftTotal,
                'right_bv' => $rightTotal,
                'matched_bv' => $matchedBV,
                'carry_forward_left' => $carryLeft,
                'carry_forward_right' => $carryRight,
                'income_generated' => $income,
                'match_date' => now()->toDateString(),
            ]);

            $user->carry_forward_left = $carryLeft;
            $user->carry_forward_right = $carryRight;
            $user->save();

            $wallet = Wallet::firstOrCreate(['user_id' => $user->id]);

            $wallet->matching_income += $income;
            $wallet->total_income += $income;
            $wallet->save();
        }

        $this->info('Daily Matching Completed Successfully');
    }

    private function getTotalBV($parentId, $position)
    {
        $members = Member::where('parent_id', $parentId)
            ->where('position', $position)
            ->where('is_active', 1)
            ->get();

        $total = 0;

        foreach ($members as $member) {
            $total += $member->pv;
            $total += $this->getTotalBV($member->id, 'left');
            $total += $this->getTotalBV($member->id, 'right');
        }

        return $total;
    }

    private function getDailyCap($step)
    {
        $caps = [
            1 => 2000,
            2 => 4000,
            3 => 6000,
            4 => 10000,
            5 => 10000,
            6 => 20000,
            7 => 40000,
        ];

        return $caps[$step] ?? 0;
    }
}