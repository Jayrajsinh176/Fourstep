<?php

namespace App\Http\Controllers\MemberReport;

use App\Http\Controllers\Controller;
use App\Models\CashbackWallet;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class CashbackReportController extends Controller
{
    public function download(Request $request)
    {
        $request->validate([
            'from_date' => ['required', 'date'],
            'to_date'   => ['required', 'date', 'after_or_equal:from_date'],
        ]);

        $from = $request->from_date;
        $to = $request->to_date;

        $reports = CashbackWallet::with('member')
            ->whereBetween('created_at', [
                $from . ' 00:00:00',
                $to . ' 23:59:59',
            ])
            ->orderBy('created_at')
            ->get();

        $totalCredit = $reports->sum('credit');
        $totalDebit  = $reports->sum('debit');

        $pdf = Pdf::loadView('pdf.cashback_report', [
            'reports'     => $reports,
            'from'        => $from,
            'to'          => $to,
            'totalCredit' => $totalCredit,
            'totalDebit'  => $totalDebit,
        ]);

        return $pdf->download(
            'Cashback_Report_' . $from . '_to_' . $to . '.pdf'
        );
    }
}