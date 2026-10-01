<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\PurchaseBonusTransaction;

class GroupBuiltupBonusController extends Controller
{
    
    
    public function getBonuses(Request $request)
{
    $userId = $request->query('user_id');

    if (!$userId) {
        return response()->json([
            'message' => 'User ID required',
            'data' => []
        ]);
    }

    $member = \DB::table('members')
        ->where('user_id', $userId)
        ->first();

    if (!$member) {
        return response()->json([
            'message' => 'Member not found',
            'data' => []
        ]);
    }

  $rows = PurchaseBonusTransaction::where('member_id', $member->id)
        ->orderByDesc('id')
        ->get()
        ->map(function ($row) {
            return [
    'id' => $row->id,
    'date' => $row->calculated_at,
    'transaction_id' => 'TXN-' . $row->id,
    'group_period' => $row->week_start . ' to ' . $row->week_end,
    'direct_active' => 0,
    'team_active' => 0,
    'group_amount' => (float) $row->matching_bv,
    'earned' => (float) $row->payable_income,
    'status' => $row->status,
];
        });

    return response()->json([
        'message' => 'success',
        'data' => $rows
    ]);
}


private const STEP_CONFIG = [
    1 => [
        'pair_bv' => 125,
        'weekly_cap' => 5000,
    ],
    2 => [
        'pair_bv' => 250,
        'weekly_cap' => 10000,
    ],
    3 => [
        'pair_bv' => 500,
        'weekly_cap' => 20000,
    ],
    4 => [
        'pair_bv' => 1000,
        'weekly_cap' => 50000,
    ],
];

public function calculateCycle(Request $request)
{
   $cycleDate = $request->cycle_date;

$weekStart = Carbon::parse($cycleDate)->startOfWeek(Carbon::MONDAY);
$weekEnd = Carbon::parse($cycleDate)->endOfWeek(Carbon::SUNDAY);
    
    \Log::info('Group Builtup Bonus Cron Started', [
    'cycle_date' => $cycleDate,
    'run_at' => now()->toDateTimeString(),
]);

$alreadyCalculated = PurchaseBonusTransaction::whereDate(
    'week_start',
    $weekStart->toDateString()
)
->whereDate(
    'week_end',
    $weekEnd->toDateString()
)
->exists();

    if ($alreadyCalculated) {
        return response()->json([
            'message' =>'Purchase Bonus has already been calculated for this week.',
        ], 400);
    }

    DB::beginTransaction();

    try {
$members = DB::table('members')
    ->where('status', 1)
    ->get();

$processed = 0;
$eligible = 0;
$totalIncome = 0;

    foreach ($members as $member) {
        
        $processed++;
        
        
/*
|--------------------------------------------------------------------------
| STEP 3 - Initial Sponsor Qualification
|--------------------------------------------------------------------------
| Requirement:
| 2 Left + 1 Right
| OR
| 1 Left + 2 Right
|--------------------------------------------------------------------------
*/

$leftDirects = DB::table('members')
    ->where('sponsor_id', $member->id)
    ->where('position', 'left')
    ->where('status', 1)
    ->count();

$rightDirects = DB::table('members')
    ->where('sponsor_id', $member->id)
    ->where('position', 'right')
    ->where('status', 1)
    ->count();

if (
    !(($leftDirects >= 2 && $rightDirects >= 1) ||
      ($leftDirects >= 1 && $rightDirects >= 2))
) {
    continue;
}


    
$leftBv = (float) $member->builtup_left_bv;
$rightBv = (float) $member->builtup_right_bv;

$step = max(1, (int) $member->package_step);

$config = self::STEP_CONFIG[$step] ?? self::STEP_CONFIG[1];

$pairBv = $config['pair_bv'];
$weeklyCap = $config['weekly_cap'];

if ($leftBv < $pairBv || $rightBv < $pairBv) {
    continue;
}
   /*
|--------------------------------------------------------------------------
| MATCH BASED ON STEP BV
|--------------------------------------------------------------------------
*/
$matchingBv = min($leftBv, $rightBv);

$pairs = floor($matchingBv / $pairBv);

if ($pairs <= 0) {
    continue;
}

$eligible++;

 /*
|--------------------------------------------------------------------------
| INCOME = MATCHED BV × ₹1
|--------------------------------------------------------------------------
*/
$matchedBv = $pairs * $pairBv;

$grossIncome = $matchedBv;

        /*
|--------------------------------------------------------------------------
| WEEKLY CAP CHECK
|--------------------------------------------------------------------------
*/

$weeklyIncome = DB::table('purchase_bonus_transactions')
    ->where('member_id', $member->id)
    ->whereDate('week_start', $weekStart->toDateString())
    ->whereDate('week_end', $weekEnd->toDateString())
    ->sum('payable_income');

$remainingWeekly = max(0, $weeklyCap - $weeklyIncome);

        /*
        |--------------------------------------------------------------------------
        | FINAL PAYABLE
        |--------------------------------------------------------------------------
        */
$payableIncome = min(
    $grossIncome,
    $remainingWeekly
);

        if ($payableIncome <= 0) {
            continue;
        }

        /*
        |--------------------------------------------------------------------------
        | FLUSH LOGIC
        |--------------------------------------------------------------------------
        */
        $lapsedIncome = max(0, $grossIncome - $payableIncome);

/*
|--------------------------------------------------------------------------
| CONSUME ONLY PAYABLE BV
|--------------------------------------------------------------------------
| If weekly cap is partially available, only the BV corresponding
| to the payable income is consumed.
|
| Example:
| Gross = 5000 BV
| Payable = 2000
| Consumed = 2000 BV
| Remaining = 3000 BV → carry forward
|--------------------------------------------------------------------------
*/

$consumedBv = min($matchedBv, $payableIncome);

$leftAfter = max(0, $leftBv - $consumedBv);
$rightAfter = max(0, $rightBv - $consumedBv);

        /*
        |--------------------------------------------------------------------------
        | BONUS ENTRY
        |--------------------------------------------------------------------------
        */
    PurchaseBonusTransaction::create([
    'member_id' => $member->id,

    'week_start' => $weekStart->toDateString(),
    'week_end' => $weekEnd->toDateString(),

    'step_level' => $step,

    'left_bv_before' => $leftBv,
    'right_bv_before' => $rightBv,

    'matching_bv' => $matchedBv,
    'matched_pairs' => $pairs,

    'gross_income' => $grossIncome,
    'weekly_cap' => $weeklyCap,
    'payable_income' => $payableIncome,

    'lapsed_income' => $lapsedIncome,

    'left_bv_after' => $leftAfter,
    'right_bv_after' => $rightAfter,

    'status' => 'pending',

    'calculated_at' => now(),
    'created_at' => now(),
    'updated_at' => now(),
]);

        /*
        |--------------------------------------------------------------------------
        | EWALLET ENTRY
        |--------------------------------------------------------------------------
        */
      DB::table('ewallet_logs')->insert([
    'member_id' => $member->id,
    'amount' => $payableIncome,
    'type' => 'binary_income',
    'remark' => 'Purchase Bonus',
    'status' => 'pending',
    'created_at' => now(),
]);

        /*
    |--------------------------------------------------------------------------
| UPDATE BUILT-UP BV
|--------------------------------------------------------------------------
        */
      DB::table('members')
    ->where('id', $member->id)
    ->update([
        'builtup_left_bv' => $leftAfter,
        'builtup_right_bv' => $rightAfter,
    ]);

        $totalIncome += $payableIncome;
    }

if ($eligible == 0) {

    DB::commit();

    return response()->json([
        'message' => 'No eligible members found for this cycle. Scheduler executed successfully.',
        'data' => [
            'week_start' => $weekStart->toDateString(),
'week_end' => $weekEnd->toDateString(),
            'processed_members' => $processed,
            'eligible_members' => 0,
            'total_income' => 0,
        ]
    ]);
}

DB::commit();



return response()->json([
    'message' => 'Bonus calculated successfully.',
    'data' => [
        'week_start' => $weekStart->toDateString(),
'week_end' => $weekEnd->toDateString(),
        'processed_members' => $processed,
        'eligible_members' => $eligible,
        'total_income' => $totalIncome,
    ]
]);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'message' => 'Bonus calculation failed.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

private function countActiveDownline(int $memberId): int
{
    $count = 0;

    $children = DB::table('members')
        ->where('parent_id', $memberId)
        ->get();

    foreach ($children as $child) {

        if ((int) $child->status === 1) {
            $count++;
        }

        $count += $this->countActiveDownline($child->id);
    }

    return $count;
}

}