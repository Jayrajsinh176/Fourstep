<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RepurchaseWalletStatusController extends Controller
{
    private const CONSISTENCY_MONTHS = 4;
    private const MIN_MONTHLY_PURCHASE = 500.0;
    private const REWARD_PERCENTAGE = 25.0;

    public function index(Request $request)
    {
        $userId = $request->header('X-Auth-Member') ?: $request->query('user_id');

        if (!$userId) {
            return response()->json(['message' => 'User id missing'], 401);
        }

        $member = Member::where('user_id', $userId)->first();

        if (!$member) {
            return response()->json(['message' => 'Member not found'], 404);
        }

    $progress = DB::table('consistency_progress')
    ->where('member_id', $member->id)
    ->first();

$completedMonths = (int) ($progress->completed_months ?? 0);

$months = [];

for ($i = 1; $i <= self::CONSISTENCY_MONTHS; $i++) {

    $months[] = [
        'month_index' => $i,
        'label' => 'Month ' . $i,
        'month_key' => null,
        'purchase_amount' => 0,
        'minimum_required' => self::MIN_MONTHLY_PURCHASE,
        'completed' => $i <= $completedMonths,
        'status' => $i <= $completedMonths ? 'Completed' : 'Pending',
    ];
}

$rewardEarned = $completedMonths >= self::CONSISTENCY_MONTHS;

$rewardAmount = 0;
$totalPurchase = 0;

    $consistencyStatus = 'Not Started';

if ($completedMonths > 0) {
    $consistencyStatus = 'In Progress';
}

if ($completedMonths >= self::CONSISTENCY_MONTHS) {
    $consistencyStatus = 'Achieved';
}
        $transactions = [];
        if (Schema::hasTable('loyalty_bonuses')) {
            $bonusRows = DB::table('loyalty_bonuses')
                ->where('member_id', $member->id)
                ->where('type', 'consistency')
                ->orderByDesc('id')
                ->limit(100)
                ->get();

            foreach ($bonusRows as $row) {
                $transactions[] = [
                    'id' => $row->id,
                    'date' => $row->calculated_at ?: $row->created_at,
                    'description' => '4 Month Consistency Reward',
                    'credit_amount' => (float) ($row->bonus_amount ?? 0),
                    'debit_amount' => 0,
                    'balance_after' => null,
                ];
            }
        }

        return response()->json([
            'message' => 'Consistency status fetched',
            'data' => [
                'user_id' => $member->user_id,
                'fullname' => $member->fullname,
                'minimum_monthly_purchase' => self::MIN_MONTHLY_PURCHASE,
                'completed_months' => $completedMonths,
                'reward_percentage' => self::REWARD_PERCENTAGE,
                'reward_earned' => $rewardEarned,
                'reward_amount' => $rewardAmount,
                'consistency_status' => $consistencyStatus,
                'month_1_purchase' => $months[0]['purchase_amount'] ?? 0,
                'month_2_purchase' => $months[1]['purchase_amount'] ?? 0,
                'month_3_purchase' => $months[2]['purchase_amount'] ?? 0,
                'month_4_purchase' => $months[3]['purchase_amount'] ?? 0,
                'months' => $months,
                'transactions' => $transactions,
                'total_purchase' => $totalPurchase,
            ],
        ]);
    }

}