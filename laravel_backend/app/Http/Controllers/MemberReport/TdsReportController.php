<?php

namespace App\Http\Controllers\MemberReport;

use App\Http\Controllers\Controller;
use App\Models\PayoutDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class TdsReportController extends Controller
{
    public function download(Request $request)
    {
        $request->validate([
            'from_date' => ['required', 'date'],
            'to_date'   => ['required', 'date', 'after_or_equal:from_date'],
        ]);

        $from = $request->from_date;
        $to   = $request->to_date;

        $reports = PayoutDetail::with(['member', 'kyc'])
            ->where('payment_status', 'paid')
            ->whereBetween('paid_at', [
                $from . ' 00:00:00',
                $to . ' 23:59:59',
            ])
            ->orderBy('paid_at')
            ->get();

        $totalGross = $reports->sum('gross_amount');
        $totalTds = $reports->sum('tds');

        $pdf = Pdf::loadView(
            'pdf.tds_report',
            [
                'reports' => $reports,
                'from' => $from,
                'to' => $to,
                'totalGross' => $totalGross,
                'totalTds' => $totalTds,
            ]
        );

        return $pdf->download(
            'TDS_Report_' . $from . '_to_' . $to . '.pdf'
        );
    }
}