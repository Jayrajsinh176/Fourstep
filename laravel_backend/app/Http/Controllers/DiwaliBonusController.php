<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\DiwaliBonus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DiwaliBonusController extends Controller
{
    // Dynamic yearly festival bonus from lapsed matching PV within Diwali period.
    private const BONUS_PERCENTAGE = 5.0;
    private const REQUIRED_STEP = 4;


    
    public function index(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|string|exists:members,user_id',
        ]);

        $query = DiwaliBonus::with('member:id,user_id,fullname')
            ->orderByDesc('calculated_at')
            ->orderByDesc('id');

        if (!empty($validated['user_id'])) {

            $member = Member::where('user_id', $validated['user_id'])->first();

            if (!$member) {
                return response()->json([
                    "message" => "Member not found",
                    "data" => []
                ], 404);
            }

            $query->where('member_id', $member->id);
        }

        $data = $query->get()->map(function ($bonus) {
            $historyDate = $bonus->calculated_at ?? $bonus->created_at;
            $targetBusiness = round((float) $bonus->total_lapsed_pv, 2);
            $bonusPercentage = round((float) ($bonus->bonus_percentage ?? self::BONUS_PERCENTAGE), 2);
            $earnedAmount = round((float) ($bonus->bonus_amount ?? (($targetBusiness * $bonusPercentage) / 100)), 2);
            $memberStep = max((int) ($bonus->member->package_step ?? 0), (int) ($bonus->member->step_level ?? 0));
            $isIdActive4Step = (int) ($bonus->member->status ?? 0) === 1 && $memberStep >= self::REQUIRED_STEP;

            return [
                "id" => $bonus->id,
                "transaction_id" => "DB" . str_pad($bonus->id, 5, "0", STR_PAD_LEFT),
                "date" => $historyDate ? $historyDate->format('Y-m-d') : null,
                "festival_year" => $bonus->bonus_year,
                "lapsed_matching_turnover" => $targetBusiness,
                "percentage" => $bonusPercentage,
                "earned" => $earnedAmount,
                "id_active_4_step" => $isIdActive4Step,
                "matching_turnover_exists" => $targetBusiness > 0,
                "status" => $isIdActive4Step ? "Approved" : "Ineligible",
                "member_user_id" => $bonus->member->user_id ?? "--",
                "member_name" => $bonus->member->fullname ?? "--"
            ];
        })->values();

        return response()->json([
            "message" => "Diwali bonus history",
            "data" => $data
        ]);
    }

    public function status(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|string|exists:members,user_id',
            'year' => 'nullable|integer',
        ]);

        $year = (int) ($validated['year'] ?? now()->year);

        $member = Member::where('user_id', $validated['user_id'])->first();

        if (!$member) {
            return response()->json([
                'message' => 'Member not found',
                'data' => null,
            ], 404);
        }

        $period = $this->resolveDiwaliPeriod($year);

        $matchingRows = DB::table('pv_matchings')
            ->where('member_id', $member->id)
            ->where(function ($query) use ($period) {
                $query->whereBetween('match_date', [$period['start']->toDateString(), $period['end']->toDateString()])
                    ->orWhere(function ($subQuery) use ($period) {
                        $subQuery->whereNull('match_date')
                            ->whereBetween('created_at', [$period['start']->toDateTimeString(), $period['end']->toDateTimeString()]);
                    });
            })
            ->orderByDesc('match_date')
            ->orderByDesc('id')
            ->get();

        $lapsedTurnover = (float) $matchingRows->sum('lapsed_matching_pv');
        $memberStep = max((int) ($member->package_step ?? 0), (int) ($member->step_level ?? 0));
        $isIdActive4Step = (int) $member->status === 1 && $memberStep >= self::REQUIRED_STEP;
        $hasMatchingTurnover = $lapsedTurnover > 0;
        $isEligible = $isIdActive4Step && $hasMatchingTurnover;

        $rows = $matchingRows->map(function ($row, $index) {
            $lapsed = (float) ($row->lapsed_matching_pv ?? 0);

            return [
                'sr_no' => $index + 1,
                'date' => $row->match_date ?: optional(Carbon::parse($row->created_at))->format('Y-m-d'),
                'left_pv' => (float) ($row->left_pv ?? 0),
                'right_pv' => (float) ($row->right_pv ?? 0),
                'matching_turnover' => $lapsed,
                'income' => round(($lapsed * self::BONUS_PERCENTAGE) / 100, 2),
            ];
        })->values();

        return response()->json([
            'message' => 'Diwali celebration status',
            'data' => [
                'year' => $year,
                'bonus_percentage' => self::BONUS_PERCENTAGE,
                'required_step' => self::REQUIRED_STEP,
                'id_active_4_step' => $isIdActive4Step,
                'matching_turnover_exists' => $hasMatchingTurnover,
                'eligible' => $isEligible,
                'lapsed_matching_turnover' => round($lapsedTurnover, 2),
                'estimated_income' => round(($lapsedTurnover * self::BONUS_PERCENTAGE) / 100, 2),
                'rows' => $rows,
            ],
        ]);
    }


   
    public function calculateYearly(Request $request)
    {
        $request->validate([
            "year" => "required|integer"
        ]);

        $year = $request->year;

        $period = $this->resolveDiwaliPeriod($year);
        $startDate = $period['start'];
        $endDate = $period['end'];

        $bonusPercentage = self::BONUS_PERCENTAGE;

        $members = Member::where('status', 1)->get();

        $processed = 0;
        $totalBonus = 0;

        foreach ($members as $member) {

            $memberStep = max((int) ($member->package_step ?? 0), (int) ($member->step_level ?? 0));
            $isIdActive4Step = (int) $member->status === 1 && $memberStep >= self::REQUIRED_STEP;

            if (!$isIdActive4Step) {
                continue;
            }

            $lapsedPv = DB::table('pv_matchings')
                ->where('member_id', $member->id)
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('match_date', [$startDate->toDateString(), $endDate->toDateString()])
                        ->orWhere(function ($subQuery) use ($startDate, $endDate) {
                            $subQuery->whereNull('match_date')
                                ->whereBetween('created_at', [$startDate->toDateTimeString(), $endDate->toDateTimeString()]);
                        });
                })
                ->sum('lapsed_matching_pv');

            if ($lapsedPv <= 0) {
                continue;
            }

            // Calculate 5% bonus
            $bonusAmount = round(($lapsedPv * $bonusPercentage) / 100, 2);

            // Save Bonus
            DiwaliBonus::updateOrCreate(
                [
                    'member_id' => $member->id,
                    'bonus_year' => $year
                ],
                [
                    'period_start' => $startDate,
                    'period_end' => $endDate,
                    'total_lapsed_pv' => $lapsedPv,
                    'bonus_percentage' => $bonusPercentage,
                    'bonus_amount' => $bonusAmount,
                    'calculated_at' => now()
                ]
            );

            $processed++;
            $totalBonus += $bonusAmount;
        }

        return response()->json([
            "message" => "Diwali bonus calculated successfully",
            "data" => [
                "year" => $year,
                "processed_members" => $processed,
                "total_bonus" => $totalBonus
            ]
        ]);
    }

    private function resolveDiwaliPeriod(int $year): array
    {
        return [
            'start' => Carbon::create($year, 10, 1)->startOfDay(),
            'end' => Carbon::create($year, 10, 31)->endOfDay(),
        ];
    }
}