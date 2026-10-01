<?php

namespace App\Http\Controllers\MemberReport;

use App\Http\Controllers\Controller;
use App\Models\RewardAchiever;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class RankRewardReportController extends Controller
{
    public function download(Request $request)
    {
        $from = $request->from_date;
        $to = $request->to_date;
        $status = $request->status;

        $reports = RewardAchiever::with(['member', 'reward'])
            ->when($from, fn ($q) => $q->whereDate('achieved_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('achieved_at', '<=', $to))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest('achieved_at')
            ->get();

        $pdf = Pdf::loadView('pdf.rank_reward', [
    'reports' => $reports,
    'from' => $from,
    'to' => $to,
    'totalRecords' => $reports->count(),
])->setPaper('a4', 'landscape');

return $pdf->download(
    'Rank_Reward_Report_' .
    $from .
    '_to_' .
    $to .
    '.pdf'
);

    }
}