<?php

namespace App\Services;

use App\Models\WeeklyClosing;
use App\Models\PurchaseBonusTransaction;
use App\Models\LoyaltyBonus;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WeeklyClosingService
{
    public function run($closingDate = null)
    {
        $date = $closingDate ?? now()->toDateString();

        $weekStart = Carbon::parse($date)->startOfWeek(Carbon::MONDAY);
$weekEnd = Carbon::parse($date)->endOfWeek(Carbon::SUNDAY);

        $alreadyRun = WeeklyClosing::whereDate('week_start', $weekStart->toDateString())
            ->whereDate('week_end', $weekEnd->toDateString())
            ->exists();

      if ($alreadyRun) {

    $repurchaseBonuses = LoyaltyBonus::whereDate(
        'week_start',
        $weekStart->toDateString()
    )
    ->whereDate(
        'week_end',
        $weekEnd->toDateString()
    )
    ->where('type', 'repurchase')
    ->where('status', 'pending')
    ->get();

    if ($repurchaseBonuses->isEmpty()) {
        return [
            'status' => false,
            'message' => 'This week\'s Weekly Closing has already been completed.',
        ];
    }

    DB::beginTransaction();

    try {

        foreach ($repurchaseBonuses as $bonus) {
            $bonus->status = 'approved';
            $bonus->save();
        }

        DB::commit();

return [
    'status' => true,
    'message' => 'Pending Repurchase Bonus approved successfully.',
    'processed' => $repurchaseBonuses->count(),
    'total_income' => (float) $repurchaseBonuses->sum('bonus_amount'),
];

    } catch (\Exception $e) {

        DB::rollBack();

        return [
            'status' => false,
            'message' => $e->getMessage(),
        ];
    }
}

        DB::beginTransaction();

        try {

            $bonuses = PurchaseBonusTransaction::whereDate(
                'week_start',
                $weekStart->toDateString()
            )
            ->whereDate(
                'week_end',
                $weekEnd->toDateString()
            )
            ->where('status', 'pending')
            ->get();

            $repurchaseBonuses = LoyaltyBonus::whereDate(
    'week_start',
    $weekStart->toDateString()
)
->whereDate(
    'week_end',
    $weekEnd->toDateString()
)
->where('type', 'repurchase')
->where('status', 'pending')
->get();

           if ($bonuses->isEmpty() && $repurchaseBonuses->isEmpty()) {

                DB::rollBack();

                return [
                    'status' => false,
                    'message' => 'No Weekly Closing records are available to process for this week.',
                    'processed' => 0,
                    'total_income' => 0,
                ];
            }

            $processedCount = 0;
            $totalIncome = 0;

            foreach ($bonuses as $bonus) {

                WeeklyClosing::create([
                    'member_id' => $bonus->member_id,
                    'purchase_bonus_id' => $bonus->id,

                    'week_start' => $bonus->week_start,
                    'week_end' => $bonus->week_end,

                    'left_bv' => $bonus->left_bv_before,
                    'right_bv' => $bonus->right_bv_before,
                    'matched_bv' => $bonus->matching_bv,

                    'carry_left' => $bonus->left_bv_after,
                    'carry_right' => $bonus->right_bv_after,

                    'income' => $bonus->payable_income,

                    'status' => 'pending',
                ]);

                $processedCount++;
                $totalIncome += (float) $bonus->payable_income;
            }

            foreach ($repurchaseBonuses as $bonus) {

    $processedCount++;
    $totalIncome += (float) $bonus->bonus_amount;

    $bonus->status = 'approved';
    $bonus->save();
}

            PurchaseBonusTransaction::whereDate(
                'week_start',
                $weekStart->toDateString()
            )
            ->whereDate(
                'week_end',
                $weekEnd->toDateString()
            )
            ->where('status', 'pending')
            ->update([
                'status' => 'approved',
                'updated_at' => now(),
            ]);

            DB::commit();

            return [
                'status' => true,
                'message' => 'Weekly Closing completed successfully.',
                'processed' => $processedCount,
                'total_income' => $totalIncome,
            ];

        } catch (\Exception $e) {

            DB::rollBack();

            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}