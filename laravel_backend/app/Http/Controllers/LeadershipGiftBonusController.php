<?php

namespace App\Http\Controllers;

use App\Models\LeadershipGiftBonus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LeadershipGiftBonusController extends Controller
{
    // Gift achievers history (mirrors the LeadershipGiftAchievers admin page).
    public function index(Request $request)
    {
        $userId = $request->query('user_id');

        $query = LeadershipGiftBonus::query()
            ->join('members', 'members.id', '=', 'leadership_gift_bonuses.member_id')
            ->select(
                'leadership_gift_bonuses.id',
                'leadership_gift_bonuses.rank_name',
                'leadership_gift_bonuses.gift_name',
                'leadership_gift_bonuses.qualified_date',
                'leadership_gift_bonuses.status',
                'members.user_id',
                'members.fullname'
            )
            ->orderByDesc('leadership_gift_bonuses.qualified_date')
            ->orderByDesc('leadership_gift_bonuses.id');

        if ($userId) {
            $query->where('members.user_id', $userId);
        }

        $data = $query->get()->map(function ($row) {
            return [
                'id' => $row->id,
                'member_id' => $row->user_id,
                'member_name' => $row->fullname,
                'rank_name' => $row->rank_name,
                'gift_name' => $row->gift_name,
                'qualified_date' => $row->qualified_date
                    ? \Carbon\Carbon::parse($row->qualified_date)->format('Y-m-d')
                    : null,
                'status' => $row->status,
            ];
        });

        return response()->json([
            'message' => 'Leadership gift achievers fetched',
            'data' => $data,
        ]);
    }

public function status(Request $request)
{
    $userId = $request->query('user_id');

    if (!$userId) {
        return response()->json([
            'message' => 'User ID required',
        ], 422);
    }

    $member = DB::table('members')
        ->where('user_id', $userId)
        ->first();

    if (!$member) {
        return response()->json([
            'message' => 'Member not found',
        ], 404);
    }

    $members = $this->getMembersMap();

    $childrenByParent = $this->buildChildrenMap($members);

    $currentRank = $this->resolveHighestQualifiedRank(
    (int) $member->id,
    $members,
    $childrenByParent
);

$currentGift = $currentRank
    ? $this->resolveGiftForRank($currentRank)
    : null;

    // Manager qualification
    $leftCount = $this->countQualifyingSide(
        (int) $member->id,
        'left',
        $members,
        $childrenByParent
    );

    $rightCount = $this->countQualifyingSide(
        (int) $member->id,
        'right',
        $members,
        $childrenByParent
    );

    $managerQualified = $this->qualifiesForManager(
        (int) $member->id,
        $members,
        $childrenByParent
    );

    // Area Manager qualification
    $leftManagers = $this->countManagerSide(
        (int) $member->id,
        'left',
        $members,
        $childrenByParent
    );

    $rightManagers = $this->countManagerSide(
        (int) $member->id,
        'right',
        $members,
        $childrenByParent
    );

    $areaManagerQualified = $this->qualifiesForAreaManager(
        (int) $member->id,
        $members,
        $childrenByParent
    );

    $zonalManagerQualified = $this->qualifiesForZonalManager(
    (int) $member->id,
    $members,
    $childrenByParent
);

$regionalManagerQualified = $this->qualifiesForRegionalManager(
    (int) $member->id,
    $members,
    $childrenByParent
);


    return response()->json([
        'message' => 'Leadership qualification checked',

        'data' => [
            'member_id' => (int) $member->id,
            'user_id' => $member->user_id,

        'current_rank' => $currentRank,
        'current_gift' => $currentGift,

            'manager' => [
                'left_4step_count' => $leftCount,
                'right_4step_count' => $rightCount,
                'required_left' => 1,
                'required_right' => 1,
                'qualified' => $managerQualified,
            ],

            'area_manager' => [
                'left_manager_count' => $leftManagers,
                'right_manager_count' => $rightManagers,
                'required_left' => 3,
                'required_right' => 3,
                'qualified' => $areaManagerQualified,
            ],

            'zonal_manager' => [
    'left_manager_count' => $leftManagers,
    'right_manager_count' => $rightManagers,
    'required_left' => 7,
    'required_right' => 7,
    'qualified' => $zonalManagerQualified,
],
'regional_manager' => [
    'left_manager_count' => $leftManagers,
    'right_manager_count' => $rightManagers,
    'required_left' => 12,
    'required_right' => 12,
    'qualified' => $regionalManagerQualified,
],

        ],
    ]);
}

// QUlification logic is based on the following criteria:
// - Manager: Active member with at least 1 qualifying 4-Step+ member on both left and right sides.
    
private function qualifiesForManager(
    int $memberId,
    array $members,
    array $childrenByParent
): bool {
    $member = $members[$memberId] ?? null;

    if (!$member || (int) $member['status'] !== 1) {
        return false;
    }

    $leftCount = $this->countQualifyingSide(
        $memberId,
        'left',
        $members,
        $childrenByParent
    );

    $rightCount = $this->countQualifyingSide(
        $memberId,
        'right',
        $members,
        $childrenByParent
    );

    return $leftCount >= 1 && $rightCount >= 1;
}

private function countQualifyingSide(
    int $memberId,
    string $side,
    array $members,
    array $childrenByParent
): int {
    $total = 0;

    foreach ($childrenByParent[$memberId] ?? [] as $childId) {
        $child = $members[$childId] ?? null;

        if (!$child) {
            continue;
        }

        if (($child['position'] ?? null) !== $side) {
            continue;
        }

        $total += $this->countQualifyingSubtree(
            $childId,
            $members,
            $childrenByParent
        );
    }

    return $total;
}

private function countQualifyingSubtree(
    int $memberId,
    array $members,
    array $childrenByParent
): int {
    $member = $members[$memberId] ?? null;

    if (!$member) {
        return 0;
    }

    $step = max(
        (int) ($member['package_step'] ?? 0),
        (int) ($member['step_level'] ?? 0)
    );

    $total = 0;

    if (
        (int) $member['status'] === 1 &&
        $step >= 4
    ) {
        $total = 1;
    }

    foreach ($childrenByParent[$memberId] ?? [] as $childId) {
        $total += $this->countQualifyingSubtree(
            $childId,
            $members,
            $childrenByParent
        );
    }

    return $total;
}

private function getMembersMap(): array
{
    $query = DB::table('members')
        ->select(
            'id',
            'parent_id',
            'position',
            'status',
            'package_step'
        );

    if (Schema::hasColumn('members', 'step_level')) {
        $query->addSelect('step_level');
    }

    return $query->get()
        ->mapWithKeys(function ($row) {
            return [
                (int) $row->id => [
                    'id' => (int) $row->id,
                    'parent_id' => (int) ($row->parent_id ?? 0),
                    'position' => $row->position,
                    'status' => (int) $row->status,
                    'package_step' => (int) ($row->package_step ?? 0),
                    'step_level' => (int) ($row->step_level ?? 0),
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

// Qualification logic is based on the following criteria:
// - Area Manager: Must first qualify as Manager, then have at least 3 qualifying Managers on both left and right sides.

private function qualifiesForAreaManager(
    int $memberId,
    array $members,
    array $childrenByParent
): bool {
    // Member must first qualify as Manager
    if (!$this->qualifiesForManager(
        $memberId,
        $members,
        $childrenByParent
    )) {
        return false;
    }

    $leftManagers = $this->countManagerSide(
        $memberId,
        'left',
        $members,
        $childrenByParent
    );

    $rightManagers = $this->countManagerSide(
        $memberId,
        'right',
        $members,
        $childrenByParent
    );

    return $leftManagers >= 3 && $rightManagers >= 3;
}

private function countManagerSide(
    int $memberId,
    string $side,
    array $members,
    array $childrenByParent
): int {
    $total = 0;

    foreach ($childrenByParent[$memberId] ?? [] as $childId) {
        $child = $members[$childId] ?? null;

        if (!$child) {
            continue;
        }

        if (($child['position'] ?? null) !== $side) {
            continue;
        }

        $total += $this->countManagerSubtree(
            $childId,
            $members,
            $childrenByParent
        );
    }

    return $total;
}

private function countManagerSubtree(
    int $memberId,
    array $members,
    array $childrenByParent
): int {
    $total = 0;

    /*
     * For now, a Manager means:
     * active member
     * + 1 qualifying 4-Step+ member on left
     * + 1 qualifying 4-Step+ member on right
     */
    if ($this->qualifiesForManager(
        $memberId,
        $members,
        $childrenByParent
    )) {
        $total = 1;
    }

    foreach ($childrenByParent[$memberId] ?? [] as $childId) {
        $total += $this->countManagerSubtree(
            $childId,
            $members,
            $childrenByParent
        );
    }

    return $total;
}

// Qualification logic is based on the following criteria:
//  - Zonal Manager: Must first qualify as Area Manager, then have at least 7 qualifying Managers on both left and right sides.

private function qualifiesForZonalManager(
    int $memberId,
    array $members,
    array $childrenByParent
): bool {
    // Member must first qualify as Area Manager
    if (!$this->qualifiesForAreaManager(
        $memberId,
        $members,
        $childrenByParent
    )) {
        return false;
    }

    $leftManagers = $this->countManagerSide(
        $memberId,
        'left',
        $members,
        $childrenByParent
    );

    $rightManagers = $this->countManagerSide(
        $memberId,
        'right',
        $members,
        $childrenByParent
    );

    return $leftManagers >= 7 && $rightManagers >= 7;
}

// Qualification logic is based on the following criteria:
//  - Regional Manager: Must first qualify as Zonal Manager, then have at least 12 qualifying Managers on both left and right sides.

private function qualifiesForRegionalManager(
    int $memberId,
    array $members,
    array $childrenByParent
): bool {
    // Member must first qualify as Zonal Manager
    if (!$this->qualifiesForZonalManager(
        $memberId,
        $members,
        $childrenByParent
    )) {
        return false;
    }

    $leftManagers = $this->countManagerSide(
        $memberId,
        'left',
        $members,
        $childrenByParent
    );

    $rightManagers = $this->countManagerSide(
        $memberId,
        'right',
        $members,
        $childrenByParent
    );

    return $leftManagers >= 12 && $rightManagers >= 12;
}

private function resolveGiftForRank(string $rank): ?string
{
    return match ($rank) {
        'Manager' => '4Step Diary',
        'Area Manager' => '4Step Tie',
        'Zonal Manager' => '4Step Backpack Bag',
        'Regional Manager' => '4Step LDP + Domestic Tour',
        default => null,
    };
}

private function resolveHighestQualifiedRank(
    int $memberId,
    array $members,
    array $childrenByParent
): ?string {
    if ($this->qualifiesForRegionalManager(
        $memberId,
        $members,
        $childrenByParent
    )) {
        return 'Regional Manager';
    }

    if ($this->qualifiesForZonalManager(
        $memberId,
        $members,
        $childrenByParent
    )) {
        return 'Zonal Manager';
    }

    if ($this->qualifiesForAreaManager(
        $memberId,
        $members,
        $childrenByParent
    )) {
        return 'Area Manager';
    }

    if ($this->qualifiesForManager(
        $memberId,
        $members,
        $childrenByParent
    )) {
        return 'Manager';
    }

    return null;
}

// Calulation of Leadership Gift Bonus is based on the following criteria:
// - Manager: Active member with at least 1 qualifying 4-Step+ member on both left and right sides.
// - Area Manager: Must first qualify as Manager, then have at least 3 qualifying Managers on both left and right sides.
// - Zonal Manager: Must first qualify as Area Manager, then have at least 7 qualifying Managers on both left and right sides.
// - Regional Manager: Must first qualify as Zonal Manager, then have at least 12 qualifying Managers on both left and right sides.

public function calculate(Request $request)
{
    $members = $this->getMembersMap();

    $childrenByParent = $this->buildChildrenMap($members);

    $qualified = 0;
    $inserted = 0;

    foreach ($members as $memberId => $member) {

        if ((int) $member['status'] !== 1) {
            continue;
        }

        $rank = $this->resolveHighestQualifiedRank(
            (int) $memberId,
            $members,
            $childrenByParent
        );

        if (!$rank) {
            continue;
        }

        $gift = $this->resolveGiftForRank($rank);

        if (!$gift) {
            continue;
        }

        $qualified++;

        $giftRecord = LeadershipGiftBonus::firstOrCreate(
            [
                'member_id' => $memberId,
                'rank_name' => $rank,
            ],
            [
                'gift_name' => $gift,
                'qualified_date' => now()->toDateString(),
                'status' => 'pending',
            ]
        );

        if ($giftRecord->wasRecentlyCreated) {
            $inserted++;
        }
    }

    return response()->json([
        'message' => 'Leadership Gift Bonus calculated successfully.',
        'data' => [
            'qualified_members' => $qualified,
            'inserted_gifts' => $inserted,
        ],
    ]);
}

}