<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class LoyaltyBonusController extends Controller
{
    // Dynamic loyalty bonuses from monthly repurchase and consistency purchase rules.
    private const MIN_MONTHLY_PURCHASE = 2500.0;
    private const DEFAULT_MONTHLY_BONUS_PERCENTAGE = 10.0;

    public function repurchaseStatus(Request $request)
    {
        $userId = $request->header('X-Auth-Member') ?: $request->query('user_id');

        if (!$userId) {
            return response()->json(['message' => 'User id missing'], 401);
        }

        $member = Member::where('user_id', $userId)->first();

        if (!$member) {
            return response()->json(['message' => 'Member not found'], 404);
        }

        $month = $request->query('month') ?: now()->format('Y-m');
        $monthRange = $this->resolveMonthRange($month);

        if (!$monthRange) {
            return response()->json(['message' => 'Invalid month format, expected Y-m'], 422);
        }

     if (!Schema::hasTable('repurchase_wallet_transactions')) {
            return response()->json([
                'message' => 'Repurchase status fetched',
                'data' => [
                    'month' => $month,
                    'minimum_purchase_required' => self::MIN_MONTHLY_PURCHASE,
                    'monthly_purchase_amount' => 0,
                    'eligible' => false,
                    'cashback_eligible' => false,
                    'loyalty_bonus_eligible' => false,
                    'royalty_eligible' => false,
                    'bonus_percentage' => self::DEFAULT_MONTHLY_BONUS_PERCENTAGE,
                    'estimated_loyalty_bonus' => 0,
                    'purchases' => [],
                ],
            ]);
        }

  $purchaseRows = DB::table('repurchase_wallet_transactions')
    ->where('user_id', $member->id)
    ->where('type', 'debit')
    ->where('description', 'like', '%Repurchase%')
    ->whereBetween('created_at', [
        $monthRange['start']->startOfDay(),
        $monthRange['end']->endOfDay()
    ])
    ->orderByDesc('created_at')
    ->orderByDesc('id')
    ->get();

$monthlyPurchase = (float) $purchaseRows->sum('amount');
        $isEligible = $monthlyPurchase >= self::MIN_MONTHLY_PURCHASE;
        $estimatedBonus = round(($monthlyPurchase * self::DEFAULT_MONTHLY_BONUS_PERCENTAGE) / 100, 2);

$purchases = $purchaseRows->map(function ($row, $index) {
    return [
        'id' => $row->id,
        'sr_no' => $index + 1,
        'invoice_no' => '-',
        'purchase_date' => $row->created_at,
        'amount' => (float) $row->amount,
        'status' => 'approved',
    ];
})->values();

return response()->json([
    'message' => 'Repurchase status fetched',
    'data' => [
        'month' => $month,
        'minimum_purchase_required' => self::MIN_MONTHLY_PURCHASE,
        'monthly_purchase_amount' => round($monthlyPurchase, 2),
        'eligible' => $isEligible,
        'cashback_eligible' => $isEligible,
        'loyalty_bonus_eligible' => $isEligible,
        'royalty_eligible' => false,
        'bonus_percentage' => self::DEFAULT_MONTHLY_BONUS_PERCENTAGE,
        'estimated_loyalty_bonus' => $isEligible ? $estimatedBonus : 0,
        'purchases' => $purchases,
    ],
]);
    }

    public function index(Request $request)
    {
        $userId = $request->query('user_id');
        $type = $request->query('type');

        $query = DB::table('loyalty_bonuses')
            ->join('members', 'members.id', '=', 'loyalty_bonuses.member_id')
            ->select(
                'loyalty_bonuses.id',
                'loyalty_bonuses.month_key',
                'loyalty_bonuses.purchase_amount',
                'loyalty_bonuses.minimum_required',
                'loyalty_bonuses.requirement_met',
                'loyalty_bonuses.bonus_percentage',
                'loyalty_bonuses.bonus_amount',
                'loyalty_bonuses.type',
                'loyalty_bonuses.status',
                'loyalty_bonuses.calculated_at',
                'loyalty_bonuses.created_at',
                'members.user_id',
                'members.fullname'
            )
            ->orderBy('loyalty_bonuses.id', 'desc');

        if ($userId) {
            $query->where('members.user_id', $userId);
        }

        if ($type) {
            $query->where('loyalty_bonuses.type', $type);
        }

        $data = $query->get()->map(function ($row) {
            $date = $row->calculated_at ?: $row->created_at;

            return [
                'id' => $row->id,
                'transaction_id' => 'LB' . str_pad($row->id, 6, '0', STR_PAD_LEFT),
                'date' => $date ? Carbon::parse($date)->format('Y-m-d') : null,
                'month' => $row->month_key,
                'repurchase_amount' => $row->purchase_amount,
                'minimum_required' => (float) ($row->minimum_required ?? self::MIN_MONTHLY_PURCHASE),
                'requirement_met' => (bool) ($row->requirement_met ?? false),
                'percentage' => $row->bonus_percentage,
                'earned' => $row->bonus_amount,
                'type' => $row->type,
                'status' => $row->status,
                'user_id' => $row->user_id,
                'name' => $row->fullname
            ];
        });

        return response()->json([
            'message' => 'Loyalty bonus history fetched',
            'data' => $data
        ]);
    }



   
    public function calculateMonthly(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required|date_format:Y-m',
        ]);

        if (
    !Schema::hasTable('loyalty_bonuses') ||
    !Schema::hasTable('repurchase_wallet_transactions')
) {
            return response()->json([
                'message' => 'Required tables are missing. Please run migrations first.',
            ], 422);
        }

        $month = $validated['month'];

        $monthStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $monthEnd = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

        $minPurchase = self::MIN_MONTHLY_PURCHASE;
        $bonusPercent = self::DEFAULT_MONTHLY_BONUS_PERCENTAGE;

        $members = DB::table('members')
            ->where('status', 1)
            ->get();

        $processed = 0;
        $eligible = 0;
        $totalBonus = 0;

        foreach ($members as $member) {

            $processed++;

           $purchase = (float) DB::table('repurchase_wallet_transactions')
    ->where('user_id', $member->id)
    ->where('type', 'debit')
    ->where('description', 'like', '%Repurchase%')
    ->whereBetween('created_at', [$monthStart, $monthEnd])
    ->sum('amount');

            if ($purchase < $minPurchase) {
                continue;
            }

            $eligible++;

            $bonus = round(($purchase * $bonusPercent) / 100, 2);

            DB::table('loyalty_bonuses')->updateOrInsert(
                [
                    'member_id' => $member->id,
                    'month_key' => $month,
                    'type' => 'monthly',
                ],
                [
                    'purchase_amount' => $purchase,
                    'minimum_required' => $minPurchase,
                    'requirement_met' => true,
                    'bonus_percentage' => $bonusPercent,
                    'bonus_amount' => $bonus,
                    'status' => 'pending',
                    'calculated_at' => now(),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $totalBonus += $bonus;
        }

        return response()->json([
            'message' => 'Monthly loyalty bonus calculated',
            'data' => [
                'month' => $month,
                'processed_members' => $processed,
                'eligible_members' => $eligible,
                'total_bonus' => $totalBonus
            ]
        ]);
    }


public function calculateConsistencyBonus()
{
    if (
        !Schema::hasTable('consistency_wallet') ||
        !Schema::hasTable('consistency_progress') ||
        !Schema::hasTable('loyalty_bonuses')
    ) {
        return response()->json([
            'message' => 'Required tables missing'
        ], 422);
    }

    $bonusPercent = 25;
    $minPurchase = 2500;
    $maxBonus = 10000;

    $currentMonth = now()->startOfMonth();
    $currentMonthKey = $currentMonth->format('Y-m');

    $members = DB::table('members')
        ->where('status', 1)
        ->get();

    $qualified = 0;
    $totalBonus = 0;

    foreach ($members as $member) {

        /*
        |--------------------------------------------------------------------------
        | Get/Create consistency progress
        |--------------------------------------------------------------------------
        */

        $progress = DB::table('consistency_progress')
            ->where('member_id', $member->id)
            ->first();

        if (!$progress) {

            DB::table('consistency_progress')->insert([
                'member_id' => $member->id,
                'completed_months' => 0,
                'cycle_start_month' => null,
                'last_qualified_month' => null,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $completedMonths = 0;
            $cycleStartMonth = null;
            $lastQualifiedMonth = null;

        } else {

            $completedMonths = (int) $progress->completed_months;
            $cycleStartMonth = $progress->cycle_start_month;
            $lastQualifiedMonth = $progress->last_qualified_month;
        }


        /*
        |--------------------------------------------------------------------------
        | STEP 1
        | Check whether the previous month was the 4th qualifying month.
        |
        | If yes, current month is the 5th month and reward is generated.
        |--------------------------------------------------------------------------
        */

        if (
            $completedMonths >= 4 &&
            $cycleStartMonth &&
            $lastQualifiedMonth
        ) {

            $expectedRewardMonth = Carbon::createFromFormat(
                'Y-m',
                $lastQualifiedMonth
            )
                ->startOfMonth()
                ->addMonth()
                ->format('Y-m');


            if ($expectedRewardMonth === $currentMonthKey) {

                /*
                |--------------------------------------------------------------------------
                | Check duplicate reward
                |--------------------------------------------------------------------------
                */

                $alreadyGiven = DB::table('loyalty_bonuses')
                    ->where('member_id', $member->id)
                    ->where('month_key', $currentMonthKey)
                    ->where('type', 'consistency')
                    ->exists();

                if (!$alreadyGiven) {

                    /*
                    |--------------------------------------------------------------------------
                    | Calculate total purchase from the FOUR qualifying months
                    |--------------------------------------------------------------------------
                    */

                    $cycleStart = Carbon::createFromFormat(
                        'Y-m',
                        $cycleStartMonth
                    )->startOfMonth();

                    $cycleEnd = Carbon::createFromFormat(
                        'Y-m',
                        $lastQualifiedMonth
                    )->endOfMonth();


                 $totalPurchase = 0;

$qualifiedAllMonths = true;

$checkMonth = $cycleStart->copy();

for ($i = 0; $i < 4; $i++) {

    $monthStart = $checkMonth->copy()->startOfMonth();

    $month15 = $checkMonth->copy()
        ->startOfMonth()
        ->addDays(14)
        ->endOfDay();

    $monthlyPurchase = (float) DB::table('purchases')
        ->where('member_id', $member->id)
        ->where('status', 'approved')
        ->whereBetween('purchase_date', [
            $monthStart->toDateString(),
            $month15->toDateString()
        ])
        ->sum('amount');

    /*
     * Every one of the 4 months must have
     * minimum ₹2,500 purchase.
     */
    if ($monthlyPurchase < $minPurchase) {
        $qualifiedAllMonths = false;
        break;
    }

    $totalPurchase += $monthlyPurchase;

    $checkMonth->addMonth();
}

if (!$qualifiedAllMonths) {
    continue;
}


                    /*
                    |--------------------------------------------------------------------------
                    | Calculate 25%
                    |--------------------------------------------------------------------------
                    */

                    $calculatedBonus = round(
                        ($totalPurchase * $bonusPercent) / 100,
                        2
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Maximum free-product benefit = ₹10,000
                    |--------------------------------------------------------------------------
                    */

                    $finalBonus = min(
                        $calculatedBonus,
                        $maxBonus
                    );


                    if ($finalBonus > 0) {

                        DB::transaction(function () use (
                            $member,
                            $currentMonthKey,
                            $totalPurchase,
                            $minPurchase,
                            $bonusPercent,
                            $finalBonus
                        ) {

                            /*
                            |--------------------------------------------------------------------------
                            | Loyalty Bonus Record
                            |--------------------------------------------------------------------------
                            */

                            DB::table('loyalty_bonuses')->insert([
                                'member_id' => $member->id,
                                'month_key' => $currentMonthKey,
                                'purchase_amount' => $totalPurchase,
                                'minimum_required' => $minPurchase,
                                'requirement_met' => 1,
                                'bonus_percentage' => $bonusPercent,
                                'bonus_amount' => $finalBonus,
                                'type' => 'consistency',
                                'status' => 'approved',
                                'calculated_at' => now(),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);


                            /*
                            |--------------------------------------------------------------------------
                            | Credit Free Product / Consistency Wallet
                            |--------------------------------------------------------------------------
                            */

                            DB::table('consistency_wallet')->insert([
                                'user_id' => $member->id,
                                'credit' => $finalBonus,
                                'debit' => 0,
                                'detail' => 'Consistency Bonus - Free Product',
                                'created_at' => now(),
                            ]);
                        });


                        $qualified++;
                        $totalBonus += $finalBonus;
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | Reset after 5th-month reward
                |--------------------------------------------------------------------------
                */

                DB::table('consistency_progress')
                    ->where('member_id', $member->id)
                    ->update([
                        'completed_months' => 0,
                        'cycle_start_month' => null,
                        'last_qualified_month' => null,
                        'status' => 'active',
                        'updated_at' => now(),
                    ]);

                continue;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | STEP 2
        | Check current month's purchase.
        |
        | Purchase must be between 1st and 15th.
        |--------------------------------------------------------------------------
        */

        $monthStart = $currentMonth->copy()->startOfMonth();

        $month15 = $currentMonth->copy()
            ->startOfMonth()
            ->addDays(14)
            ->endOfDay();


       $currentPurchase = (float) DB::table('purchases')
    ->where('member_id', $member->id)
    ->where('status', 'approved')
    ->whereBetween('purchase_date', [
        $monthStart->toDateString(),
        $month15->toDateString()
    ])
    ->sum('amount');


        /*
        |--------------------------------------------------------------------------
        | STEP 3
        | If current month does not qualify, reset the cycle.
        |--------------------------------------------------------------------------
        */

        if ($currentPurchase < $minPurchase) {

            DB::table('consistency_progress')
                ->where('member_id', $member->id)
                ->update([
                    'completed_months' => 0,
                    'cycle_start_month' => null,
                    'last_qualified_month' => null,
                    'status' => 'active',
                    'updated_at' => now(),
                ]);

            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | STEP 4
        | Prevent same month from being counted twice.
        |--------------------------------------------------------------------------
        */

        if ($lastQualifiedMonth === $currentMonthKey) {
            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | STEP 5
        | Check consecutive month.
        |--------------------------------------------------------------------------
        */

        $newCount = 1;

        if (
            $completedMonths > 0 &&
            $lastQualifiedMonth
        ) {

            $expectedCurrentMonth = Carbon::createFromFormat(
                'Y-m',
                $lastQualifiedMonth
            )
                ->startOfMonth()
                ->addMonth()
                ->format('Y-m');


            if ($expectedCurrentMonth !== $currentMonthKey) {

                /*
                | Consecutive cycle broken
                */

                $newCount = 1;
                $cycleStartMonth = $currentMonthKey;

            } else {

                $newCount = $completedMonths + 1;
            }

        } else {

            $cycleStartMonth = $currentMonthKey;
        }


        /*
        |--------------------------------------------------------------------------
        | STEP 6
        | Save qualifying month
        |--------------------------------------------------------------------------
        */

        DB::table('consistency_progress')
            ->where('member_id', $member->id)
            ->update([
                'completed_months' => $newCount,
                'cycle_start_month' => $cycleStartMonth,
                'last_qualified_month' => $currentMonthKey,
                'status' => 'active',
                'updated_at' => now(),
            ]);


        /*
        |--------------------------------------------------------------------------
        | IMPORTANT
        |
        | Do NOT give bonus in month 4.
        |
        | Month 4 only completes the qualification.
        | Month 5 gives the reward.
        |--------------------------------------------------------------------------
        */

        continue;
    }


    return response()->json([
        'message' => 'Consistency bonus executed successfully',
        'data' => [
            'qualified_members' => $qualified,
            'total_bonus' => round($totalBonus, 2),
        ]
    ]);
}

    private function resolveMonthRange(string $month): ?array
    {
        try {
            $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            $end = (clone $start)->endOfMonth();

            return [
                'start' => $start,
                'end' => $end,
            ];
        } catch (\Throwable $exception) {
            return null;
        }
    }
    
    public function consistencyWallet(Request $request)
{
    $userId = $request->query('member_id');

    if (!$userId) {
        return response()->json([
            'message' => 'Member ID is required'
        ], 422);
    }

    $member = Member::where('user_id', $userId)->first();

    if (!$member) {
        return response()->json([
            'message' => 'Member not found'
        ], 404);
    }

    $balance = DB::table('consistency_wallet')
        ->where('user_id', $member->id)
        ->selectRaw('SUM(credit) - SUM(debit) as balance')
        ->value('balance');

    $totalCredit = DB::table('consistency_wallet')
        ->where('user_id', $member->id)
        ->sum('credit');

    $totalDebit = DB::table('consistency_wallet')
        ->where('user_id', $member->id)
        ->sum('debit');

    $transactions = DB::table('consistency_wallet')
        ->where('user_id', $member->id)
        ->orderByDesc('id')
        ->get()
        ->map(function ($row) {
            return [
                'id' => $row->id,
                'date' => $row->created_at,
                'detail' => $row->detail,
                'credit_amount' => $row->credit,
                'debit_amount' => $row->debit,
                'balance' => null,
            ];
        });

    $runningBalance = 0;

    $transactions = $transactions->reverse()->map(function ($item) use (&$runningBalance) {
        $runningBalance += ($item['credit_amount'] ?? 0);
        $runningBalance -= ($item['debit_amount'] ?? 0);
        $item['balance'] = $runningBalance;
        return $item;
    })->reverse()->values();

    return response()->json([
        'balance' => (float) ($balance ?? 0),
        'total_credit' => (float) $totalCredit,
        'total_debit' => (float) $totalDebit,
        'transactions' => $transactions,
    ]);
}

}