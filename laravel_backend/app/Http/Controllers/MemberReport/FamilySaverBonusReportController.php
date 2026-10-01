<?php

namespace App\Http\Controllers\MemberReport;

use App\Http\Controllers\Controller;
use App\Models\FamilySaverBonus;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class FamilySaverBonusReportController extends Controller
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

        $reports = FamilySaverBonus::with(['nominee', 'deceased'])
            ->whereBetween('calculated_at', [
                $from . ' 00:00:00',
                $to . ' 23:59:59',
            ])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderBy('calculated_at')
            ->get();


        $totalCompanyBv = $reports->sum('monthly_company_bv');

        $totalBonus = $reports->sum('bonus_amount');


        $pdf = Pdf::loadView(
            'pdf.family_saver_bonus',
            [
                'reports' => $reports,
                'from' => $from,
                'to' => $to,
                'totalCompanyBv' => $totalCompanyBv,
                'totalBonus' => $totalBonus,
            ]
        );


        return $pdf->download(
            'Family_Saver_Bonus_Report_' .
            $from .
            '_to_' .
            $to .
            '.pdf'
        );
    }
}