<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Member;
class IncomeSummaryController extends Controller
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
        $validated = $request->validate([
            'from_date' => 'nullable|date_format:Y-m-d',
            'to_date' => 'nullable|date_format:Y-m-d|after_or_equal:from_date',
        ]);

       if (!Schema::hasTable('income_reports')) {
    return response()->json([
        'message' => 'income_reports table not found.',
        'data' => [],
    ], 422);
}

        $query = DB::table('income_reports')
    ->where('member_id', $member->id);

if (!empty($validated['from_date'])) {
    $query->whereDate('entry_date', '>=', $validated['from_date']);
}

if (!empty($validated['to_date'])) {
    $query->whereDate('entry_date', '<=', $validated['to_date']);
}

$runningBalance = 0;

$rows = $query
    ->orderBy('entry_date')
    ->orderBy('id')
    ->get()
    ->map(function ($row, $index) use (&$runningBalance) {

  $credit = round((float) ($row->earned_bonus ?? 0), 2);

$runningBalance = round($runningBalance + $credit, 2);

        return [
            'sr_no' => $index + 1,
            'date' => !empty($row->entry_date)
                ? date('d-m-Y', strtotime((string) $row->entry_date))
                : '-',
            'description' => (string) ($row->bonus_name ?? ''),
'credit_amount' => number_format($credit, 2, '.', ''),
'balance_amount' => number_format($runningBalance, 2, '.', ''),
    
        ];
    })
    ->reverse()
    ->values();
    return response()->json([
    'message' => 'Income summary fetched successfully.',
    'data' => $rows,
]);
    }
    
}