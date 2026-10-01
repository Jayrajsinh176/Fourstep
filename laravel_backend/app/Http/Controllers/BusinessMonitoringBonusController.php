<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\RoyaltySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class BusinessMonitoringBonusController extends Controller
{
    // Dynamic cycle bonus for sponsors from direct team income with step-based eligibility.
    private const DEFAULT_PERCENTAGE = 10.0;
    private const REQUIRED_STEP = 4;
    private const INSERT_BATCH_SIZE = 2000;

    public function index(Request $request)
    {
        $validated = $request->validate([
            'sponsor_user_id' => 'nullable|string|exists:members,user_id',
            'user_id' => 'nullable|string|exists:members,user_id',
            'cycle_date' => 'nullable|date_format:Y-m-d',
            'limit' => 'nullable|integer|min:1|max:500',
        ]);

        $limit = (int) ($validated['limit'] ?? 100);
        $sponsorUserId = $validated['sponsor_user_id'] ?? $validated['user_id'] ?? null;

        $hasPackageStep = Schema::hasColumn('members', 'package_step');
        $hasStepLevel = Schema::hasColumn('members', 'step_level');

        $query = DB::table('business_monitoring_bonuses as bmb')
            ->leftJoin('members as s', 's.id', '=', 'bmb.sponsor_member_id')
            ->leftJoin('members as d', 'd.id', '=', 'bmb.downline_member_id')
            ->select(
                'bmb.id',
                'bmb.sponsor_member_id',
                'bmb.downline_member_id',
                'bmb.cycle_date',
                'bmb.matching_income',
                'bmb.bonus_percentage',
                'bmb.bonus_amount',
                'bmb.status',
                'bmb.calculated_at',
                's.user_id as sponsor_user_id',
                's.fullname as sponsor_name',
                's.status as sponsor_status',
                'd.user_id as downline_user_id',
                'd.fullname as downline_name'
            )
            ->orderByDesc('bmb.cycle_date')
            ->orderByDesc('bmb.id');

        if ($hasPackageStep) {
            $query->addSelect('s.package_step as sponsor_package_step');
        } else {
            $query->selectRaw('0 as sponsor_package_step');
        }

        if ($hasStepLevel) {
            $query->addSelect('s.step_level as sponsor_step_level');
        } else {
            $query->selectRaw('0 as sponsor_step_level');
        }

        if (!empty($validated['cycle_date'])) {
            $query->whereDate('bmb.cycle_date', $validated['cycle_date']);
        }

        if (!empty($sponsorUserId)) {
            $sponsorId = Member::query()
                ->where('user_id', $sponsorUserId)
                ->value('id');

            if ($sponsorId) {
                $query->where('bmb.sponsor_member_id', $sponsorId);
            }
        }

        $rows = $query->limit($limit)->get();

        $sponsorIds = $rows
            ->pluck('sponsor_member_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $directActiveMap = [];
        $teamActiveMap = [];

        if (!empty($sponsorIds)) {
            $directActiveMap = Member::query()
                ->whereIn('sponsor_id', $sponsorIds)
                ->where('status', 1)
                ->selectRaw('sponsor_id, COUNT(*) as total')
                ->groupBy('sponsor_id')
                ->pluck('total', 'sponsor_id')
                ->map(fn ($count) => (int) $count)
                ->all();

            try {
                $placeholders = implode(',', array_fill(0, count($sponsorIds), '?'));

                $teamSql = "
                    WITH RECURSIVE team_tree AS (
                        SELECT
                            m.id,
                            m.parent_id,
                            m.status,
                            m.parent_id AS root_sponsor_id
                        FROM members m
                        WHERE m.parent_id IN ({$placeholders})

                        UNION ALL

                        SELECT
                            child.id,
                            child.parent_id,
                            child.status,
                            tree.root_sponsor_id
                        FROM members child
                        INNER JOIN team_tree tree ON child.parent_id = tree.id
                    )
                    SELECT
                        root_sponsor_id AS sponsor_id,
                        SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) AS active_team_count
                    FROM team_tree
                    GROUP BY root_sponsor_id
                ";

                $teamRows = DB::select($teamSql, $sponsorIds);

                foreach ($teamRows as $teamRow) {
                    $teamActiveMap[(int) $teamRow->sponsor_id] = (int) $teamRow->active_team_count;
                }
            } catch (\Throwable $exception) {
                foreach ($directActiveMap as $sponsorId => $count) {
                    $teamActiveMap[(int) $sponsorId] = (int) $count;
                }
            }
        }

        $rows = $rows->map(function ($row) use ($directActiveMap, $teamActiveMap) {
            $sponsorId = (int) ($row->sponsor_member_id ?? 0);
            $sponsorStep = max((int) ($row->sponsor_package_step ?? 0), (int) ($row->sponsor_step_level ?? 0));
            $isIdActive4Step = (int) ($row->sponsor_status ?? 0) === 1 && $sponsorStep === self::REQUIRED_STEP;

            return [
                'id' => $row->id,
                'transaction_id' => 'BMB' . str_pad((string) $row->id, 6, '0', STR_PAD_LEFT),
                'cycle_date' => $row->cycle_date,
                'direct_team_income' => (float) $row->matching_income,
                'matching_income' => (float) $row->matching_income,
                'bonus_percentage' => (float) $row->bonus_percentage,
                'bonus_amount' => (float) $row->bonus_amount,
                'direct_active' => $directActiveMap[$sponsorId] ?? 0,
                'team_active' => $teamActiveMap[$sponsorId] ?? 0,
                'id_active_4_step' => $isIdActive4Step,
                'requirement_met' => $isIdActive4Step && (($directActiveMap[$sponsorId] ?? 0) > 0),
                'status' => $row->status,
                'calculated_at' => $row->calculated_at,
                'sponsor_user_id' => $row->sponsor_user_id,
                'sponsor_name' => $row->sponsor_name,
                'downline_user_id' => $row->downline_user_id,
                'downline_name' => $row->downline_name,
            ];
        });

        return response()->json([
            'message' => 'Business Monitoring bonus history fetched successfully.',
            'data' => $rows,
        ]);
    }

    public function status(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|string|exists:members,user_id',
            'cycle_date' => 'nullable|date_format:Y-m-d',
        ]);

        $cycleDate = $validated['cycle_date'] ?? now()->toDateString();

        $member = Member::query()->where('user_id', $validated['user_id'])->first();

        if (!$member) {
            return response()->json([
                'message' => 'Member not found',
                'data' => null,
            ], 404);
        }

        $bonusPercentage = RoyaltySetting::getDecimalValue(
            'business_monitoring_bonus_percentage',
            self::DEFAULT_PERCENTAGE
        );

        $memberStep = max((int) ($member->package_step ?? 0), (int) ($member->step_level ?? 0));
        $idActive4Step = (int) $member->status === 1 && $memberStep === self::REQUIRED_STEP;

   $directMembers = Member::query()
    ->where('sponsor_id', $member->id)
    ->where('status', 1)
    ->where('package_step', self::REQUIRED_STEP)
    ->select('id', 'user_id', 'fullname')
    ->orderBy('id')
    ->get();
    
        $directMemberIds = $directMembers->pluck('id')->all();

   $cycle = Carbon::parse($cycleDate);

$weekStart = $cycle->copy()
    ->startOfWeek(Carbon::MONDAY)
    ->toDateString();

$weekEnd = $cycle->copy()
    ->endOfWeek(Carbon::SUNDAY)
    ->toDateString();

$incomeByDownline = DB::table('purchase_bonus_transactions')
    ->whereIn(
        'member_id',
        !empty($directMemberIds) ? $directMemberIds : [0]
    )
    ->whereDate('week_start', $weekStart)
    ->whereDate('week_end', $weekEnd)
    ->where('payable_income', '>', 0)
    ->selectRaw('member_id, SUM(payable_income) as direct_income')
    ->groupBy('member_id')
    ->pluck('direct_income', 'member_id');

        $rows = $directMembers->map(function ($downline, $index) use ($incomeByDownline, $bonusPercentage) {
            $directIncome = round((float) ($incomeByDownline[$downline->id] ?? 0), 2);
            $sponsorBonus = round(($directIncome * $bonusPercentage) / 100, 2);

            return [
                'sr_no' => $index + 1,
                'downline_user_id' => $downline->user_id,
                'downline_name' => $downline->fullname,
                'direct_team_income' => $directIncome,
                'bonus_percentage' => $bonusPercentage,
                'monitoring_bonus' => $sponsorBonus,
            ];
        })->values();

        $totalDirectTeamIncome = round((float) $rows->sum('direct_team_income'), 2);
        $estimatedIncome = round(($totalDirectTeamIncome * $bonusPercentage) / 100, 2);

        $hasWorkingDirectMembers = $directMembers->count() > 0;
        $hasDirectTeamIncome = $totalDirectTeamIncome > 0;
        $eligible = $idActive4Step && $hasWorkingDirectMembers && $hasDirectTeamIncome;

        return response()->json([
            'message' => 'Business monitoring status',
            'data' => [
             'cycle_date' => $cycleDate,
'week_start' => $weekStart,
'week_end' => $weekEnd,
                'required_step' => self::REQUIRED_STEP,
                'bonus_percentage' => $bonusPercentage,
                'id_active_4_step' => $idActive4Step,
                'working_direct_members' => $hasWorkingDirectMembers,
                'direct_referrals_count' => $directMembers->count(),
                'total_direct_team_income' => $totalDirectTeamIncome,
                'estimated_income' => $estimatedIncome,
                'eligible' => $eligible,
                'rows' => $rows,
            ],
        ]);
    }

    public function calculateCycle(Request $request)
    {
        $validated = $request->validate([
            'cycle_date' => 'required|date_format:Y-m-d',
            'bonus_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $result = $this->processCycle(
            $validated['cycle_date'],
            isset($validated['bonus_percentage']) ? (float) $validated['bonus_percentage'] : null
        );

        return response()->json([
            'message' => 'Business Monitoring bonus calculated successfully.',
            'data' => $result,
        ], 201);
    }

    public function runCycle(string $cycleDate, ?float $overridePercentage = null): array
    {
        return $this->processCycle($cycleDate, $overridePercentage);
    }

   private function processCycle(string $cycleDate, ?float $overridePercentage = null): array
{
    $bonusPercentage = $overridePercentage ?? RoyaltySetting::getDecimalValue(
        'business_monitoring_bonus_percentage',
        self::DEFAULT_PERCENTAGE
    );

    $hasPackageStep = Schema::hasColumn('members', 'package_step');
    $hasStepLevel = Schema::hasColumn('members', 'step_level');

    $cycle = Carbon::parse($cycleDate);

    $weekStart = $cycle->copy()->startOfWeek(Carbon::MONDAY);
    $weekEnd = $cycle->copy()->endOfWeek(Carbon::SUNDAY);

    return DB::transaction(function () use (
        $weekStart,
        $weekEnd,
        $bonusPercentage,
        $hasPackageStep,
        $hasStepLevel
    ) {

        $now = now();

        $rowsForInsert = [];
        $generatedRows = 0;
        $insertedRows = 0;
        $totalDirectTeamIncome = 0.0;
        $totalBonus = 0.0;

        /*
        |--------------------------------------------------------------------------
        | PURCHASE BONUS → DIRECT 4-STEP MEMBER → SPONSOR
        |--------------------------------------------------------------------------
        */

        $query = DB::table('purchase_bonus_transactions as pbt')

            ->join('members as d', 'd.id', '=', 'pbt.member_id')

            ->join('members as s', function ($join) {

                $join->on('s.id', '=', 'd.sponsor_id')
                    ->where('s.status', '=', 1);
            })

            ->leftJoin('business_monitoring_bonuses as bmb', function ($join) use (
                $weekStart,
                $weekEnd
            ) {

                $join->on(
                    'bmb.sponsor_member_id',
                    '=',
                    'd.sponsor_id'
                )

                ->on(
                    'bmb.downline_member_id',
                    '=',
                    'pbt.member_id'
                )

                ->whereDate(
                    'bmb.cycle_date',
                    '=',
                    $weekStart->toDateString()
                );
            })

            /*
            |--------------------------------------------------------------------------
            | ONLY ACTIVE DIRECT MEMBERS
            |--------------------------------------------------------------------------
            */

            ->where('d.status', 1)

            /*
            |--------------------------------------------------------------------------
            | ONLY THIS WEEK'S PURCHASE BONUS
            |--------------------------------------------------------------------------
            */

            ->whereDate(
                'pbt.week_start',
                '=',
                $weekStart->toDateString()
            )

            ->whereDate(
                'pbt.week_end',
                '=',
                $weekEnd->toDateString()
            )

            /*
            |--------------------------------------------------------------------------
            | ONLY POSITIVE PURCHASE BONUS
            |--------------------------------------------------------------------------
            */

            ->where('pbt.payable_income', '>', 0)

            ->whereNull('bmb.id')

            ->groupBy(
                'd.sponsor_id',
                'pbt.member_id'
            )

            ->selectRaw(
                'd.sponsor_id as sponsor_member_id'
            )

            ->selectRaw(
                'pbt.member_id as downline_member_id'
            )

            ->selectRaw(
                'SUM(pbt.payable_income) as total_direct_income'
            )

            ->havingRaw(
                'SUM(pbt.payable_income) > 0'
            )

            ->orderBy('d.sponsor_id');


        /*
        |--------------------------------------------------------------------------
        | SPONSOR + DIRECT MEMBER MUST BOTH BE STEP 4
        |--------------------------------------------------------------------------
        */

        if ($hasPackageStep && $hasStepLevel) {

            // Sponsor must be Step 4
       $query->whereRaw(
    'GREATEST(
        COALESCE(s.package_step, 0),
        COALESCE(s.step_level, 0)
    ) = ?',
    [self::REQUIRED_STEP]
);

            // Direct member must also be Step 4
      $query->whereRaw(
    'GREATEST(
        COALESCE(d.package_step, 0),
        COALESCE(d.step_level, 0)
    ) = ?',
    [self::REQUIRED_STEP]
);

        } elseif ($hasPackageStep) {

            // Sponsor must be Step 4
            $query->where(
                's.package_step',
                '=',
                self::REQUIRED_STEP
            );

            // Direct member must be Step 4
            $query->where(
                'd.package_step',
                '=',
                self::REQUIRED_STEP
            );

        } elseif ($hasStepLevel) {

            // Sponsor must be Step 4
            $query->where(
                's.step_level',
                '=',
                self::REQUIRED_STEP
            );

            // Direct member must be Step 4
            $query->where(
                'd.step_level',
                '=',
                self::REQUIRED_STEP
            );
        }


        /*
        |--------------------------------------------------------------------------
        | CALCULATE 10% MONITORING BONUS
        |--------------------------------------------------------------------------
        */

        foreach ($query->cursor() as $item) {

            $directIncome = round(
                (float) $item->total_direct_income,
                2
            );

            $bonusAmount = round(
                ($directIncome * $bonusPercentage) / 100,
                2
            );

            if ($bonusAmount <= 0) {
                continue;
            }

            $rowsForInsert[] = [

                'sponsor_member_id' =>
                    (int) $item->sponsor_member_id,

                'downline_member_id' =>
                    (int) $item->downline_member_id,

                /*
                 * Store Monday as the weekly cycle identifier.
                 */
                'cycle_date' =>
                    $weekStart->toDateString(),

                'matching_income' =>
                    $directIncome,

                'bonus_percentage' =>
                    $bonusPercentage,

                'bonus_amount' =>
                    $bonusAmount,

                'status' =>
                    'pending',

                'calculated_at' =>
                    $now,

                'created_at' =>
                    $now,

                'updated_at' =>
                    $now,
            ];

            $generatedRows++;

            $totalDirectTeamIncome += $directIncome;

            $totalBonus += $bonusAmount;


            if (count($rowsForInsert) >= self::INSERT_BATCH_SIZE) {

                $insertedRows +=
                    DB::table('business_monitoring_bonuses')
                        ->insertOrIgnore($rowsForInsert);

                $rowsForInsert = [];
            }
        }


        /*
        |--------------------------------------------------------------------------
        | INSERT REMAINING ROWS
        |--------------------------------------------------------------------------
        */

        if (!empty($rowsForInsert)) {

            $insertedRows +=
                DB::table('business_monitoring_bonuses')
                    ->insertOrIgnore($rowsForInsert);
        }


        /*
        |--------------------------------------------------------------------------
        | RESULT
        |--------------------------------------------------------------------------
        */

        return [

            'week_start' =>
                $weekStart->toDateString(),

            'week_end' =>
                $weekEnd->toDateString(),

            'bonus_percentage' =>
                $bonusPercentage,

            'income_source' =>
                'purchase_bonus_transactions.payable_income',

            'generated_rows' =>
                $generatedRows,

            'inserted_rows' =>
                $insertedRows,

            'total_direct_team_income' =>
                round($totalDirectTeamIncome, 2),

            'total_monitoring_bonus' =>
                round($totalBonus, 2),

            'total_bonus_amount' =>
                round($totalBonus, 2),
        ];
    });
}
}
