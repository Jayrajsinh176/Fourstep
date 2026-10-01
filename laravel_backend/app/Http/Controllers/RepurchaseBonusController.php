<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class RepurchaseBonusController extends Controller
{
    public function calculateRepurchaseBonus()
    {
        if (
            !Schema::hasTable('purchases') ||
            !Schema::hasTable('matching_history') ||
            !Schema::hasTable('loyalty_bonuses')
        ) {
            return response()->json([
                'message' => 'Required tables missing'
            ], 422);
        }

        $today = now();

        $weekStart = $today->copy()
            ->startOfWeek(Carbon::MONDAY);

        $weekEnd = $today->copy()
            ->endOfWeek(Carbon::SUNDAY);

        $qualified = 0;
        $totalBonus = 0;

        $members = DB::table('members')
            ->where('status', 1)
            ->where('id', '<>', 1)
            ->get();

        DB::beginTransaction();

        try {

            foreach ($members as $member) {

            \Log::info('REPURCHASE MEMBER TEST', [
    'member_id' => $member->id,
    'user_id' => $member->user_id,
]);

                /*
                |--------------------------------------------------------------------------
                | 1. PERSONAL ACTIVATION
                |--------------------------------------------------------------------------
                | Member must have cumulative approved personal purchase >= 125 BV.
                |
                | Purchases below 125 BV remain pending for income eligibility.
                |--------------------------------------------------------------------------
                */

                $personalBV = (float) DB::table('purchases')
                    ->where('member_id', $member->id)
                    ->where('status', 'approved')
                    ->sum('total_bv');

                if ($personalBV < 125) {
                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | 2. DIRECT SPONSOR QUALIFICATION
                |--------------------------------------------------------------------------
                | One active direct on LEFT
                | One active direct on RIGHT
                |--------------------------------------------------------------------------
                */

                $leftDirect = DB::table('members')
                    ->where('sponsor_id', $member->id)
                    ->where('position', 'left')
                    ->where('status', 1)
                    ->exists();

                $rightDirect = DB::table('members')
                    ->where('sponsor_id', $member->id)
                    ->where('position', 'right')
                    ->where('status', 1)
                    ->exists();

                if (!$leftDirect || !$rightDirect) {
                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | 3. PREVENT DUPLICATE WEEKLY RECORD
                |--------------------------------------------------------------------------
                */

                $alreadyCalculated = DB::table('matching_history')
                    ->where('user_id', $member->id)
                    ->whereDate(
                        'week_start',
                        $weekStart->toDateString()
                    )
                    ->whereDate(
                        'week_end',
                        $weekEnd->toDateString()
                    )
                    ->exists();

                if ($alreadyCalculated) {
                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | 4. FIND ENTIRE LEFT NETWORK
                |--------------------------------------------------------------------------
                */

                $leftMemberIds = $this->getSideMemberIds(
                    $member->id,
                    'left'
                );


                /*
                |--------------------------------------------------------------------------
                | 5. FIND ENTIRE RIGHT NETWORK
                |--------------------------------------------------------------------------
                */

                $rightMemberIds = $this->getSideMemberIds(
                    $member->id,
                    'right'
                );


                /*
                |--------------------------------------------------------------------------
                | 6. CURRENT WEEK LEFT BV
                |--------------------------------------------------------------------------
                */

                $currentLeftBV = 0;

                if (!empty($leftMemberIds)) {

                    $currentLeftBV = (float) DB::table('purchases')
                        ->whereIn('member_id', $leftMemberIds)
                        ->whereBetween('purchase_date', [
                            $weekStart->toDateString(),
                            $weekEnd->toDateString(),
                        ])
                        ->where('status', 'approved')
                        ->sum('total_bv');
                }


                /*
                |--------------------------------------------------------------------------
                | 7. CURRENT WEEK RIGHT BV
                |--------------------------------------------------------------------------
                */

                $currentRightBV = 0;

                if (!empty($rightMemberIds)) {

                    $currentRightBV = (float) DB::table('purchases')
                        ->whereIn('member_id', $rightMemberIds)
                        ->whereBetween('purchase_date', [
                            $weekStart->toDateString(),
                            $weekEnd->toDateString(),
                        ])
                        ->where('status', 'approved')
                        ->sum('total_bv');
                }


                /*
                |--------------------------------------------------------------------------
                | 8. PREVIOUS WEEK CARRY FORWARD
                |--------------------------------------------------------------------------
                */

                $previous = DB::table('matching_history')
                    ->where('user_id', $member->id)
                    ->orderByDesc('id')
                    ->first();

                $previousCarryLeft = $previous
                    ? (float) $previous->carry_forward_left
                    : 0;

                $previousCarryRight = $previous
                    ? (float) $previous->carry_forward_right
                    : 0;


                /*
                |--------------------------------------------------------------------------
                | 9. TOTAL AVAILABLE BV
                |--------------------------------------------------------------------------
                */

                $leftBV = $previousCarryLeft + $currentLeftBV;

                $rightBV = $previousCarryRight + $currentRightBV;


                /*
                |--------------------------------------------------------------------------
                | 10. 100% BV MATCHING
                |--------------------------------------------------------------------------
                */

                $matchedBV = min(
                    $leftBV,
                    $rightBV
                );


                /*
                |--------------------------------------------------------------------------
                | 11. REPURCHASE BONUS
                |--------------------------------------------------------------------------
                |
                | 100% matching
                | ₹1 per matched BV
                |--------------------------------------------------------------------------
                */

                $repurchaseBonus = round(
                    $matchedBV,
                    2
                );


                /*
                |--------------------------------------------------------------------------
                | 12. CARRY FORWARD
                |--------------------------------------------------------------------------
                */

                $carryForwardLeft = max(
                    0,
                    $leftBV - $matchedBV
                );

                $carryForwardRight = max(
                    0,
                    $rightBV - $matchedBV
                );


                /*
                |--------------------------------------------------------------------------
                | 13. SAVE WEEKLY MATCHING HISTORY
                |--------------------------------------------------------------------------
                |
                | Save even when matched BV = 0.
                | This preserves the weekly state.
                |--------------------------------------------------------------------------
                */

                DB::table('matching_history')->insert([

                    'user_id' => $member->id,

                    'left_bv' => $leftBV,

                    'right_bv' => $rightBV,

                    'matched_bv' => $matchedBV,

                    'carry_forward_left' => $carryForwardLeft,

                    'carry_forward_right' => $carryForwardRight,

                    'income_generated' => $repurchaseBonus,

                    'match_date' => $today->toDateString(),

                    'week_start' => $weekStart->toDateString(),

                    'week_end' => $weekEnd->toDateString(),

                    'created_at' => now(),

                    'updated_at' => now(),
                ]);


                /*
                |--------------------------------------------------------------------------
                | 14. SAVE REPURCHASE BONUS ONLY IF MATCHED
                |--------------------------------------------------------------------------
                */

                if ($repurchaseBonus > 0) {

                    DB::table('loyalty_bonuses')->insert([

                        'member_id' => $member->id,

                        'month_key' => $weekStart->format('Y-m'),

                        'week_start' => $weekStart->toDateString(),

                        'week_end' => $weekEnd->toDateString(),

                        'purchase_amount' =>
                            $currentLeftBV + $currentRightBV,

                        'minimum_required' => 125,

                        'requirement_met' => 1,

                        'bonus_percentage' => 100,

                        'bonus_amount' => $repurchaseBonus,

                        'type' => 'repurchase',

                        'status' => 'pending',

                        'calculated_at' => now(),

                        'created_at' => now(),

                        'updated_at' => now(),
                    ]);

                    $qualified++;

                    $totalBonus += $repurchaseBonus;
                }
            }

            DB::commit();

            return response()->json([

                'message' =>
                    'Repurchase bonus calculated successfully.',

                'data' => [

                    'week_start' =>
                        $weekStart->toDateString(),

                    'week_end' =>
                        $weekEnd->toDateString(),

                    'qualified_members' =>
                        $qualified,

                    'total_bonus' =>
                        round($totalBonus, 2),
                ]

            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([

                'message' =>
                    'Repurchase bonus calculation failed.',

                'error' =>
                    $e->getMessage(),

            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | GET ENTIRE LEFT / RIGHT NETWORK
    |--------------------------------------------------------------------------
    */

    private function getSideMemberIds(
        int $memberId,
        string $side
    ): array {

        $direct = DB::table('members')
            ->where('parent_id', $memberId)
            ->where('position', $side)
            ->where('status', 1)
            ->first();

        if (!$direct) {
            return [];
        }

        $ids = [
            $direct->id
        ];

        $this->collectDownlineIds(
            $direct->id,
            $ids
        );

        return $ids;
    }


    /*
    |--------------------------------------------------------------------------
    | RECURSIVELY COLLECT COMPLETE DOWNLINE
    |--------------------------------------------------------------------------
    */

    private function collectDownlineIds(
        int $parentId,
        array &$ids
    ): void {

        $children = DB::table('members')
            ->where('parent_id', $parentId)
            ->where('status', 1)
            ->get();

        foreach ($children as $child) {

            $ids[] = $child->id;

            $this->collectDownlineIds(
                $child->id,
                $ids
            );
        }
    }
}