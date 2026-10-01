<?php

namespace App\Services;

// use App\Models\Member;
use App\Models\DailyClosing;
use Illuminate\Support\Facades\DB;

class DailyClosingService
{
    
public function run($closingDate = null)
{
    
//     \Log::info('Daily Closing Started', [
//     'date' => $closingDate ?? now()->toDateString(),
// ]);

    $today = $closingDate ?? now()->toDateString();

    $alreadyRun = DailyClosing::whereDate('closing_date', $today)->exists();

    if ($alreadyRun) {
        return [
            'status' => false,
            'message' => "Today's Daily Closing has already been completed.",
        ];
    }

    DB::beginTransaction();

    try {

      $bonuses = DB::table('group_builtup_bonuses')
    ->whereDate('cycle_date', $today)
    ->where('status', 'pending')
    ->get();
    
  if ($bonuses->isEmpty()) {

    DB::rollBack();

    return [
        'status' => false,
        'message' => "No Daily Closing records are available to process for today.",
        'processed' => 0,
        'total_income' => 0,
    ];
}

        $processedCount = 0;
        $totalIncome = 0;

        foreach ($bonuses as $bonus) {

            DailyClosing::create([
                'member_id'      => $bonus->member_id,
                'group_bonus_id' => $bonus->id,
                'left_pv'        => $bonus->left_pv_before,
                'right_pv'       => $bonus->right_pv_before,
                'matched_pv'     => $bonus->matching_pv,
                'carry_left'     => $bonus->left_pv_after,
                'carry_right'    => $bonus->right_pv_after,
                'income'         => $bonus->payable_income,
                'cycle'          => 1, // kept for compatibility
                'closing_date'   => $today,
                'status'         => 'pending',
            ]);

            $processedCount++;
            $totalIncome += $bonus->payable_income;
        }
        
 DB::table('group_builtup_bonuses')
    ->whereDate('cycle_date', $today)
    ->where('status', 'pending')
    ->update([
        'status' => 'approved',
        'updated_at' => now(),
    ]);
    
        DB::commit();
        
//         \Log::info('Daily Closing Finished', [
//     'processed' => $processedCount,
//     'income' => $totalIncome,
// ]);

        return [
            'status' => true,
            'message' => 'Daily Closing completed successfully.',
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