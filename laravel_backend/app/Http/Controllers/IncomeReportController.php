<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class IncomeReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = trim((string) ($request->header('X-Auth-Member') ?: $request->query('user_id', '')));

        if ($userId === '') {
            return response()->json([
                'message' => 'Missing member identifier.',
            ], 401);
        }

        $member = Member::where('user_id', $userId)->first();

        if (!$member) {
            return response()->json([
                'message' => 'Member not found.',
            ], 404);
        }

        if (!Schema::hasTable('income_reports')) {
            return response()->json([
                'message' => 'income_reports table not found. Run migrations first.',
                'data' => [
                    'member' => null,
                    'rows' => [],
                    'totals' => [
                        'total_earned' => 0,
                    ],
                ],
            ], 422);
        }

        $this->syncRepurchaseBonusRows($member);

        $photoUrl = $this->resolveMemberPhotoUrl((int) $member->id);

        $rows = DB::table('income_reports')
            ->where('member_id', $member->id)
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->get();

        $mappedRows = $rows->values()->map(function ($row, $index) {
            return [
                'id' => (int) $row->id,
                'sr_no' => $index + 1,
                'transaction_id' => (string) ($row->transaction_id ?? ''),
                'bonus_name' => (string) ($row->bonus_name ?? 'Repurchase Bonus'),
                'bonus_month' => (string) ($row->bonus_month ?? ''),
                'earned_bonus' => round((float) ($row->earned_bonus ?? 0), 2),
                'status' => (string) ($row->status ?? 'pending'),
                'entry_date' => !empty($row->entry_date) ? date('Y-m-d', strtotime((string) $row->entry_date)) : null,
                'activation_date' => !empty($row->activation_date) ? date('Y-m-d', strtotime((string) $row->activation_date)) : null,
            ];
        });

        return response()->json([
            'message' => 'Income report fetched successfully.',
            'data' => [
                'member' => [
                    'user_id' => (string) $member->user_id,
                    'name' => (string) ($member->fullname ?? ''),
                    'activation_date' => $this->normalizeDate($member->activation_date),
                    'account_status' => ((int) ($member->status ?? 0)) === 1 ? 'Active' : 'Inactive',
                    'photo_url' => $photoUrl,
                ],
                'rows' => $mappedRows,
                'totals' => [
                    'total_earned' => round((float) $rows->sum('earned_bonus'), 2),
                ],
            ],
        ]);
    }

    private function syncRepurchaseBonusRows(Member $member): void
    {
        if (!Schema::hasTable('loyalty_bonuses')) {
            return;
        }

        $bonusRows = DB::table('loyalty_bonuses')
            ->where('member_id', $member->id)
            ->orderByDesc('id')
            ->get();

        foreach ($bonusRows as $bonusRow) {
            $transactionId = 'LB' . str_pad((string) $bonusRow->id, 6, '0', STR_PAD_LEFT);
            $bonusName = ($bonusRow->type ?? 'monthly') === 'consistency'
                ? 'Consistency Bonus'
                : 'Repurchase Bonus';

            $entryDate = null;
            if (!empty($bonusRow->calculated_at)) {
                $entryDate = date('Y-m-d', strtotime((string) $bonusRow->calculated_at));
            } elseif (!empty($bonusRow->created_at)) {
                $entryDate = date('Y-m-d', strtotime((string) $bonusRow->created_at));
            }

            DB::table('income_reports')->updateOrInsert(
                [
                    'member_id' => $member->id,
                    'transaction_id' => $transactionId,
                ],
                [
                    'bonus_name' => $bonusName,
                    'bonus_month' => $bonusRow->month_key,
                    'earned_bonus' => round((float) ($bonusRow->bonus_amount ?? 0), 2),
                    'status' => (string) ($bonusRow->status ?? 'pending'),
                    'entry_date' => $entryDate,
                    'activation_date' => $this->normalizeDate($member->activation_date),
                    'source_type' => 'loyalty_bonus',
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    private function resolveMemberPhotoUrl(int $memberId): ?string
{
    if (!Schema::hasTable('id_cards')) {
        return null;
    }

    $idCard = DB::table('id_cards')
        ->where('member_id', $memberId)
        ->first();

    if (!$idCard || empty($idCard->file_path)) {
        return null;
    }

    return asset($idCard->file_path);
}
    private function normalizeDate(mixed $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value->format('Y-m-d');
        }

        try {
            return Carbon::parse((string) $value)->format('Y-m-d');
        } catch (\Throwable $exception) {
            return null;
        }
    }
}
