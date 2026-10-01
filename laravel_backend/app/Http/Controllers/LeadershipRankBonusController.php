<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class LeadershipRankBonusController extends Controller
{
    // Dynamic generation bonus based on leadership_rank_settings and active left/right rank criteria.
    private const INSERT_BATCH_SIZE = 2000;


    public function runCycle(string $cycleDate): array
    {
        $request = Request::create('/api/bonuses/leadership-rank/calculate', 'POST', [
            'cycle_date' => $cycleDate,
        ]);

        $response = $this->calculateCycle($request);
        $payload = $response->getData(true);
        $data = $payload['data'] ?? [];

        return [
            'cycle_date' => $data['cycle_date'] ?? $cycleDate,
            'income_source_column' => 'matching_income',
            'processed_matching_rows' => (int) ($data['processed_rows'] ?? 0),
            'generated_rows' => (int) ($data['generated_rows'] ?? 0),
            'inserted_rows' => (int) ($data['inserted_rows'] ?? 0),
            'total_matching_income' => (float) ($data['total_matching_income'] ?? 0),
            'total_bonus_amount' => (float) ($data['total_bonus_amount'] ?? 0),
        ];
    }

  
    public function index(Request $request)
    {
        $userId = $request->user_id;

        $query = DB::table('leadership_rank_bonuses')
            ->join('members', 'members.id', '=', 'leadership_rank_bonuses.upline_member_id')
            ->select(
                'leadership_rank_bonuses.id',
                'leadership_rank_bonuses.cycle_date',
                'leadership_rank_bonuses.rank_name',
                'leadership_rank_bonuses.rank_achieved_date',
                'leadership_rank_bonuses.matching_income',
                'leadership_rank_bonuses.bonus_amount',
                'leadership_rank_bonuses.status',
                'members.user_id',
                'members.fullname'
            )
            ->orderBy('leadership_rank_bonuses.id', 'desc');

        if ($userId) {
            $query->where('members.user_id', $userId);
        }

        $data = $query->get()->map(function ($row) {

         return [
    'id' => $row->id,
    'transaction_id' => 'LRB' . str_pad($row->id, 6, '0', STR_PAD_LEFT),
    'date' => Carbon::parse($row->cycle_date)->format('Y-m-d'),
    'rank_name' => $row->rank_name,
    'rank_achieved_date' => $row->rank_achieved_date,
    'business_volume' => $row->matching_income,
    'earned' => $row->bonus_amount,
    'status' => $row->status,
    'user_id' => $row->user_id,
    'name' => $row->fullname
];
        });

        return response()->json([
            'message' => 'Leadership bonus history fetched',
            'data' => $data
        ]);
    }

    public function calculateCycle(Request $request)
    {
        $validated = $request->validate([
            'cycle_date' => 'required|date_format:Y-m-d',
        ]);

        $result = $this->processCycle($validated['cycle_date']);

        return response()->json([
            'message' => 'Leadership bonus calculated',
            'data' => $result,
        ], 201);
    }

    private function processCycle(string $cycleDate): array
    {
        return DB::transaction(function () use ($cycleDate) {
            $now = now();

            $settings = $this->getActiveRankSettings();

            if ($settings->isEmpty()) {
                return [
                    'cycle_date' => $cycleDate,
                    'processed_rows' => 0,
                    'generated_rows' => 0,
                    'inserted_rows' => 0,
                    'total_matching_income' => 0.0,
                    'total_bonus_amount' => 0.0,
                    'rank_settings_count' => 0,
                ];
            }

            $hasStepLevel = Schema::hasColumn('members', 'step_level');
            $hasPackageStep = Schema::hasColumn('members', 'package_step');

            $members = $this->getMembersMap($hasStepLevel, $hasPackageStep);
            $childrenByParent = $this->buildChildrenMap($members);
            $rankByMemberId = $this->buildRankMap($members, $childrenByParent, $settings, $hasStepLevel, $hasPackageStep);

           $pvMatchings = DB::table('daily_closings')
    ->whereDate('closing_date', $cycleDate)
    ->where('income', '>', 0)
    ->orderBy('id')
    ->get([
        'id',
        'member_id',
        DB::raw('income as matching_income')
    ]);

            if ($pvMatchings->isEmpty()) {
                return [
                    'cycle_date' => $cycleDate,
                    'processed_rows' => 0,
                    'generated_rows' => 0,
                    'inserted_rows' => 0,
                    'total_matching_income' => 0.0,
                    'total_bonus_amount' => 0.0,
                    'rank_settings_count' => $settings->count(),
                ];
            }

            // Refresh pending rows for this cycle/source set so rank names and percentages
            // always reflect the latest leadership_rank_settings values.
            if (Schema::hasColumn('leadership_rank_bonuses', 'source_pv_matching_id')) {
                DB::table('leadership_rank_bonuses')
                    ->whereDate('cycle_date', $cycleDate)
                    ->whereIn('source_pv_matching_id', $pvMatchings->pluck('id')->all())
                    ->where('status', 'pending')
                    ->delete();
            }

            $existingKeys = $this->getExistingBonusKeys($cycleDate, $pvMatchings->pluck('id')->all());

            $processedRows = 0;
            $generatedRows = 0;
            $insertedRows = 0;
            $totalMatchingIncome = 0.0;
            $totalBonus = 0.0;
            $rowsForInsert = [];

            foreach ($pvMatchings as $match) {
                $processedRows++;

                $sourceMemberId = (int) $match->member_id;
                $matchingIncome = round((float) $match->matching_income, 2);

                if ($matchingIncome <= 0) {
                    continue;
                }

                $totalMatchingIncome += $matchingIncome;

                $uplineId = (int) ($members[$sourceMemberId]['sponsor_id'] ?? 0);
                $generationNo = 1;

                while ($uplineId > 0) {
                    if (!isset($members[$uplineId])) {
                        break;
                    }

                    $rank = $rankByMemberId[$uplineId] ?? null;

                    if ($rank !== null) {
                        $percentage = (float) ($rank['generation_percentages'][$generationNo] ?? 0);

                        if ($percentage > 0 && $generationNo <= (int) $rank['generation_count']) {
                            $key = $this->bonusKey((int) $match->id, $uplineId, $generationNo);

                            if (!isset($existingKeys[$key])) {
                                $bonusAmount = round(($matchingIncome * $percentage) / 100, 2);

                                if ($bonusAmount > 0) {
                                    $rowsForInsert[] = [
                                        'upline_member_id' => $uplineId,
                                        'source_member_id' => $sourceMemberId,
                                        'source_pv_matching_id' => (int) $match->id,
                                        'generation_no' => $generationNo,
                                        'cycle_date' => $cycleDate,
                                        'matching_income' => $matchingIncome,
                                        'bonus_percentage' => $percentage,
                                        'bonus_amount' => $bonusAmount,
                                        'rank_name' => $rank['rank_name'],
                                        'rank_achieved_date' => $cycleDate,
                                        'status' => 'pending',
                                        'calculated_at' => $now,
                                        'created_at' => $now,
                                        'updated_at' => $now,
                                    ];

                                    $generatedRows++;
                                    $totalBonus += $bonusAmount;
                                    $existingKeys[$key] = true;
                                }
                            }
                        }
                    }

                    $uplineId = (int) ($members[$uplineId]['sponsor_id'] ?? 0);
                    $generationNo++;
                }

                if (count($rowsForInsert) >= self::INSERT_BATCH_SIZE) {
                    $insertedRows += DB::table('leadership_rank_bonuses')->insertOrIgnore($rowsForInsert);
                    $rowsForInsert = [];
                }
            }

            if (!empty($rowsForInsert)) {
                $insertedRows += DB::table('leadership_rank_bonuses')->insertOrIgnore($rowsForInsert);
            }

            return [
                'cycle_date' => $cycleDate,
                'processed_rows' => $processedRows,
                'generated_rows' => $generatedRows,
                'inserted_rows' => $insertedRows,
                'total_matching_income' => round($totalMatchingIncome, 2),
                'total_bonus_amount' => round($totalBonus, 2),
                'rank_settings_count' => $settings->count(),
            ];
        });
    }

    private function getActiveRankSettings(): Collection
    {
        if (!Schema::hasTable('leadership_rank_settings')) {
            return collect();
        }

        return DB::table('leadership_rank_settings')
            ->where('is_active', 1)
            ->orderByDesc('min_step_level')
            ->get()
            ->map(function ($row) {
                $percentages = $this->normalizeGenerationPercentages($row->generation_percentages ?? []);

                return [
                    'id' => (int) $row->id,
                    'rank_name' => (string) $row->rank_name,
                    'min_step_level' => (int) ($row->min_step_level ?? 0),
                    'generation_count' => (int) ($row->generation_count ?? 0),
                    'generation_percentages' => $percentages,
                ];
            })
            ->values();
    }

    private function normalizeGenerationPercentages($raw): array
    {
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($raw)) {
            return [];
        }

        $normalized = [];

        foreach ($raw as $generation => $percentage) {
            $generationNo = (int) $generation;
            $percentageValue = (float) $percentage;

            if ($generationNo > 0 && $percentageValue > 0) {
                $normalized[$generationNo] = $percentageValue;
            }
        }

        ksort($normalized);

        return $normalized;
    }

    private function getMembersMap(bool $hasStepLevel, bool $hasPackageStep): array
    {
        $query = DB::table('members')
            ->select('id', 'sponsor_id', 'parent_id', 'position', 'status');

        if ($hasStepLevel) {
            $query->addSelect('step_level');
        }

        if ($hasPackageStep) {
            $query->addSelect('package_step');
        }

        return $query->get()
            ->mapWithKeys(function ($row) {
                return [
                    (int) $row->id => [
                        'id' => (int) $row->id,
                        'sponsor_id' => (int) ($row->sponsor_id ?? 0),
                        'parent_id' => (int) ($row->parent_id ?? 0),
                        'position' => $row->position,
                        'status' => (int) $row->status,
                        'step_level' => (int) ($row->step_level ?? 0),
                        'package_step' => (int) ($row->package_step ?? 0),
                    ],
                ];
            })
            ->all();
    }

    private function buildChildrenMap(array $members): array
    {
        $childrenByParent = [];

        foreach ($members as $member) {
            $parentId = (int) $member['parent_id'];

            if ($parentId > 0) {
                $childrenByParent[$parentId][] = (int) $member['id'];
            }
        }

        return $childrenByParent;
    }

    private function buildRankMap(
        array $members,
        array $childrenByParent,
        Collection $settings,
        bool $hasStepLevel,
        bool $hasPackageStep
    ): array {
        $activeCountsBySide = [];
        $rankByMemberId = [];

        foreach (array_keys($members) as $memberId) {
            $left = $this->countActiveSide($memberId, 'left', $members, $childrenByParent, $activeCountsBySide);
            $right = $this->countActiveSide($memberId, 'right', $members, $childrenByParent, $activeCountsBySide);

            $member = $members[$memberId];
            $memberStep = 0;

            if ($hasStepLevel && $hasPackageStep) {
                $memberStep = max((int) $member['step_level'], (int) $member['package_step']);
            } elseif ($hasStepLevel) {
                $memberStep = (int) $member['step_level'];
            } elseif ($hasPackageStep) {
                $memberStep = (int) $member['package_step'];
            }

            $achieved = $settings->first(function (array $setting) use ($left, $right, $memberStep) {
                $requiredPerSide = $this->requiredActivePerSide($setting);

                if ($requiredPerSide <= 0) {
                    return false;
                }

                if ((int) $setting['min_step_level'] > 0 && $memberStep > 0 && $memberStep < (int) $setting['min_step_level']) {
                    return false;
                }

                return $left >= $requiredPerSide && $right >= $requiredPerSide;
            });

            if ($achieved) {
                $rankByMemberId[$memberId] = $achieved;
            }
        }

        return $rankByMemberId;
    }

    private function countActiveSide(
        int $memberId,
        string $side,
        array $members,
        array $childrenByParent,
        array &$memo
    ): int {
        $key = $memberId . ':' . $side;

        if (isset($memo[$key])) {
            return $memo[$key];
        }

        $total = 0;

        foreach ($childrenByParent[$memberId] ?? [] as $childId) {
            $child = $members[$childId] ?? null;

            if ($child === null) {
                continue;
            }

            if (($child['position'] ?? null) !== $side) {
                continue;
            }

            $total += $this->countActiveSubtree($childId, $members, $childrenByParent, $memo);
        }

        $memo[$key] = $total;

        return $total;
    }

    private function countActiveSubtree(int $memberId, array $members, array $childrenByParent, array &$memo): int
    {
        $memoKey = 'subtree:' . $memberId;

        if (isset($memo[$memoKey])) {
            return $memo[$memoKey];
        }

        $current = $members[$memberId] ?? null;
        $total = ($current !== null && (int) $current['status'] === 1) ? 1 : 0;

        foreach ($childrenByParent[$memberId] ?? [] as $childId) {
            $total += $this->countActiveSubtree($childId, $members, $childrenByParent, $memo);
        }

        $memo[$memoKey] = $total;

        return $total;
    }

    private function requiredActivePerSide(array $setting): int
    {
        $rankName = strtolower((string) ($setting['rank_name'] ?? ''));

        if (str_contains($rankName, 'regional')) {
            return 12;
        }

        if (str_contains($rankName, 'zonal')) {
            return 7;
        }

        if (str_contains($rankName, 'area')) {
            return 3;
        }

        if (str_contains($rankName, 'manager')) {
            return 1;
        }

        return match ((int) ($setting['min_step_level'] ?? 0)) {
            3 => 1,
            4 => 3,
            5 => 7,
            6 => 12,
            default => 0,
        };
    }

    private function getExistingBonusKeys(string $cycleDate, array $sourcePvMatchingIds): array
    {
        if (
            empty($sourcePvMatchingIds)
            || !Schema::hasTable('leadership_rank_bonuses')
            || !Schema::hasColumn('leadership_rank_bonuses', 'source_pv_matching_id')
        ) {
            return [];
        }

        $keys = [];

        DB::table('leadership_rank_bonuses')
            ->whereDate('cycle_date', $cycleDate)
            ->whereIn('source_pv_matching_id', $sourcePvMatchingIds)
            ->get(['source_pv_matching_id', 'upline_member_id', 'generation_no'])
            ->each(function ($row) use (&$keys) {
                $keys[$this->bonusKey((int) $row->source_pv_matching_id, (int) $row->upline_member_id, (int) $row->generation_no)] = true;
            });

        return $keys;
    }

    private function bonusKey(int $pvMatchingId, int $uplineMemberId, int $generationNo): string
    {
        return $pvMatchingId . '|' . $uplineMemberId . '|' . $generationNo;
    }
    
public function status(Request $request)
{
    $userId = $request->user_id;

    $member = DB::table('members')
        ->where('user_id', $userId)
        ->first();

    if (!$member) {
        return response()->json([
            'message' => 'Member not found'
        ], 404);
    }

    $hasStepLevel = Schema::hasColumn('members', 'step_level');
    $hasPackageStep = Schema::hasColumn('members', 'package_step');

    $members = $this->getMembersMap($hasStepLevel, $hasPackageStep);
    $childrenByParent = $this->buildChildrenMap($members);

    $memo = [];

    $leftCount = $this->countActiveSide(
        (int) $member->id,
        'left',
        $members,
        $childrenByParent,
        $memo
    );

    $rightCount = $this->countActiveSide(
        (int) $member->id,
        'right',
        $members,
        $childrenByParent,
        $memo
    );

    $currentRank = 'None';
    $nextRank = 'Manager';

    if ($leftCount >= 12 && $rightCount >= 12) {
        $currentRank = 'Regional Manager';
        $nextRank = '-';
    } elseif ($leftCount >= 7 && $rightCount >= 7) {
        $currentRank = 'Zonal Manager';
        $nextRank = 'Regional Manager';
    } elseif ($leftCount >= 3 && $rightCount >= 3) {
        $currentRank = 'Area Manager';
        $nextRank = 'Zonal Manager';
    } elseif ($leftCount >= 1 && $rightCount >= 1) {
        $currentRank = 'Manager';
        $nextRank = 'Area Manager';
    }

    return response()->json([
        'current_rank' => $currentRank,
        'left_active' => $leftCount,
        'right_active' => $rightCount,
        'next_rank' => $nextRank,
    ]);
}
}