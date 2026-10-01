<?php

namespace App\Http\Controllers\MemberReport;

use App\Http\Controllers\Controller;
use App\Models\LeadershipGiftBonus;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class LeadershipRankBonusReportController extends Controller
{
    public function download(Request $request)
    {
        $request->validate([
            'from_date' => ['required', 'date'],
            'to_date'   => ['required', 'date', 'after_or_equal:from_date'],
        ]);

        $from = $request->from_date;
        $to = $request->to_date;

        $reports = LeadershipGiftBonus::with('member')
            ->whereBetween('qualified_date', [
                $from,
                $to,
            ])
            ->orderBy('qualified_date')
            ->get();

        $pdf = Pdf::loadView('pdf.leadership_bonus', [
            'reports' => $reports,
            'from'    => $from,
            'to'      => $to,
        ]);

        return $pdf->download(
            'Leadership_Gift_Achievers_Report_' . $from . '_to_' . $to . '.pdf'
        );
    }
}