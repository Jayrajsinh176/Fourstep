<?php

namespace App\Http\Controllers\MemberReport;

use App\Http\Controllers\Controller;
use App\Models\PurchaseBonusTransaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class GroupBuiltupBonusReportController extends Controller
{
    public function download(Request $request)
    {
        $request->validate([
            'from_date' => ['required', 'date'],
            'to_date'   => ['required', 'date', 'after_or_equal:from_date'],
        ]);

        $from = $request->from_date;
        $to   = $request->to_date;

        $reports = PurchaseBonusTransaction::with('member')
            ->whereBetween('calculated_at', [
                $from . ' 00:00:00',
                $to . ' 23:59:59',
            ])
            ->orderBy('calculated_at')
            ->get();

        $totalMatchingBV = $reports->sum('matching_bv');
        $totalPayableIncome = $reports->sum('payable_income');

        $pdf = Pdf::loadView('pdf.group_builtup_bonus', [
            'reports'            => $reports,
            'from'               => $from,
            'to'                 => $to,
            'totalMatchingBV'    => $totalMatchingBV,
            'totalPayableIncome' => $totalPayableIncome,
        ]);

        return $pdf->download(
            'Purchase_Bonus_Report_' . $from . '_to_' . $to . '.pdf'
        );
    }
}