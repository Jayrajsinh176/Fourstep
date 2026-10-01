<?php

namespace App\Http\Controllers\MemberReport;

use App\Http\Controllers\Controller;
use App\Models\BusinessMonitoringBonus;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class BusinessMonitoringBonusReportController extends Controller
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

        $reports = BusinessMonitoringBonus::with(['sponsor', 'downline'])
            ->whereBetween('cycle_date', [$from, $to])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderBy('cycle_date')
            ->get();

        $totalMatchingIncome = $reports->sum('matching_income');
        $totalBonus = $reports->sum('bonus_amount');

        $pdf = Pdf::loadView(
            'pdf.business_monitoring_bonus',
            [
                'reports' => $reports,
                'from' => $from,
                'to' => $to,
                'totalMatchingIncome' => $totalMatchingIncome,
                'totalBonus' => $totalBonus,
            ]
        );

        return $pdf->download(
            'Business_Monitoring_Bonus_Report_' .
            $from .
            '_to_' .
            $to .
            '.pdf'
        );
    }
}