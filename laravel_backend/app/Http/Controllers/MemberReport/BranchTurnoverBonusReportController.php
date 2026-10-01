<?php

namespace App\Http\Controllers\MemberReport;

use App\Http\Controllers\Controller;
use App\Models\BranchTurnoverBonus;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class BranchTurnoverBonusReportController extends Controller
{
    public function download(Request $request)
    {
        $request->validate([
            'from_date' => ['required', 'date'],
            'to_date'   => ['required', 'date', 'after_or_equal:from_date'],
        ]);

        $from = $request->from_date;
        $to   = $request->to_date;
        $status = $request->status;


        $reports = BranchTurnoverBonus::with('branch')
            ->whereBetween('calculated_at', [
                $from . ' 00:00:00',
                $to . ' 23:59:59',
            ])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderBy('calculated_at')
            ->get();


        $totalTurnover = $reports->sum('total_turnover');

        $totalBonus = $reports->sum('bonus_amount');


        $pdf = Pdf::loadView(
            'pdf.branch_turnover_bonus',
            [
                'reports' => $reports,
                'from' => $from,
                'to' => $to,
                'totalTurnover' => $totalTurnover,
                'totalBonus' => $totalBonus,
            ]
        );


        return $pdf->download(
            'Branch_Turnover_Bonus_Report_' .
            $from .
            '_to_' .
            $to .
            '.pdf'
        );
    }
}