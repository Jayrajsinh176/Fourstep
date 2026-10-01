<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BranchTurnoverBonus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BranchTurnoverBonusController extends Controller
{
    // Dynamic monthly branch bonus: percentage by branch, calculated from monthly sales/turnover.

    public function runMonth(string $month): array
    {
        $request = Request::create('/api/bonuses/branch-turnover/calculate', 'POST', [
            'month' => $month,
        ]);

        $response = $this->calculateMonthly($request);
        $payload = $response->getData(true);
        $data = $payload['data'] ?? [];

        return [
            'month' => $data['month'] ?? $month,
            'processed_branches' => (int) ($data['branches_processed'] ?? 0),
            'inserted_rows' => (int) ($data['rows_inserted'] ?? 0),
            'total_turnover' => (float) ($data['total_turnover'] ?? 0),
            'total_bonus_amount' => (float) ($data['total_bonus'] ?? 0),
        ];
    }

    
    public function index(Request $request)
    {
        $month = $request->month;
        $limit = $request->limit ?? 100;

        $query = BranchTurnoverBonus::with('branch');

        if ($month) {
            $query->where('month_key', $month);
        }

        $bonuses = $query->orderBy('id', 'desc')->limit($limit)->get();

        $data = [];

        foreach ($bonuses as $row) {

            $data[] = [
                "id" => $row->id,
                "transaction_id" => "BTB" . str_pad($row->id, 6, "0", STR_PAD_LEFT),
                "date" => $row->calculated_at ? $row->calculated_at->format('Y-m-d') : null,
                "branch_name" => $row->branch->name ?? "--",
                "turnover" => $row->total_turnover,
                "share_percentage" => $row->commission_percentage,
                "earned" => $row->bonus_amount,
                "status" => $row->status
            ];
        }

        return response()->json([
            "message" => "Branch Turnover Bonus History",
            "data" => $data
        ]);
    }



    public function calculateMonthly(Request $request)
    {
        $request->validate([
            "month" => "required|date_format:Y-m"
        ]);

        $month = $request->month;

        $monthStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $monthEnd = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

        $branches = Branch::where('is_active', 1)->get();

        $totalTurnover = 0;
        $totalBonus = 0;
        $inserted = 0;

        foreach ($branches as $branch) {

            $turnover = DB::table('branch_sales')
                ->where('branch_id', $branch->id)
                ->where('status', 'approved')
                ->whereBetween('sale_date', [$monthStart, $monthEnd])
                ->sum('sale_amount');

            if ($turnover <= 0) {
                continue;
            }

            $percentage = $branch->commission_percentage;
            $bonus = ($turnover * $percentage) / 100;

            BranchTurnoverBonus::updateOrCreate(
                [
                    "branch_id" => $branch->id,
                    "month_key" => $month,
                ],
                [
                    "total_turnover" => $turnover,
                    "commission_percentage" => $percentage,
                    "bonus_amount" => $bonus,
                    "status" => "pending",
                    "calculated_at" => now(),
                ]
            );

            $totalTurnover += $turnover;
            $totalBonus += $bonus;
            $inserted++;
        }

        return response()->json([
            "message" => "Monthly bonus calculated",
            "data" => [
                "month" => $month,
                "branches_processed" => count($branches),
                "rows_inserted" => $inserted,
                "total_turnover" => $totalTurnover,
                "total_bonus" => $totalBonus
            ]
        ]);
    }

}