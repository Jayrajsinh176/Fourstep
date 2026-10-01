<?php

namespace App\Http\Controllers\MemberReport;

use App\Http\Controllers\Controller;
use App\Models\PayoutBatch;
use Barryvdh\DomPDF\Facade\Pdf;

class PayoutReportController extends Controller
{
    public function download(PayoutBatch $batch)
    {
        $details = $batch->details()
            ->with(['member', 'kyc'])
            ->get();

        $totalGross = $details->sum('gross_amount');
        $totalTds = $details->sum('tds');
        $totalAdmin = $details->sum('admin_charge');
        $totalNet = $details->sum('net_amount');

        $pdf = Pdf::loadView('pdf.payout_report', [
            'batch' => $batch,
            'details' => $details,
            'totalGross' => $totalGross,
            'totalTds' => $totalTds,
            'totalAdmin' => $totalAdmin,
            'totalNet' => $totalNet,
        ]);

        return $pdf->download(
            'Payout_Report_' . $batch->batch_no . '.pdf'
        );
    }
}