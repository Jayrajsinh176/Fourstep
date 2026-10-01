<?php

namespace App\Http\Controllers\MemberReport;

use App\Http\Controllers\Controller;
use App\Models\DiwaliBonus;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class DiwaliBonusReportController extends Controller
{
    public function download(Request $request)
    {
        $request->validate([
            'from_date' => ['required', 'date'],
            'to_date'   => ['required', 'date', 'after_or_equal:from_date'],
        ]);

        $from = $request->from_date;
        $to   = $request->to_date;

        $reports = DiwaliBonus::with('member')
            ->whereBetween('calculated_at', [
                $from . ' 00:00:00',
                $to . ' 23:59:59',
            ])
            ->orderBy('calculated_at')
            ->get();

        $totalLapsedPv = $reports->sum('total_lapsed_pv');
        $totalBonus    = $reports->sum('bonus_amount');

        $pdf = Pdf::loadView(
            'pdf.diwali_bonus',
            [
                'reports'        => $reports,
                'from'           => $from,
                'to'             => $to,
                'totalLapsedPv'  => $totalLapsedPv,
                'totalBonus'     => $totalBonus,
            ]
        );

        return $pdf->download(
            'Diwali_Bonus_' .
            $from .
            '_to_' .
            $to .
            '.pdf'
        );
    }
}