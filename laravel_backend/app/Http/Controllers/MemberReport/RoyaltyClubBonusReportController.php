<?php

namespace App\Http\Controllers\MemberReport;

use App\Http\Controllers\Controller;
use App\Models\RoyaltyClubBonus;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class RoyaltyClubBonusReportController extends Controller
{
    public function download(Request $request)
    {
        $request->validate([
            'from_date' => ['required', 'date'],
            'to_date'   => ['required', 'date', 'after_or_equal:from_date'],
        ]);

        $from = $request->from_date;
        $to   = $request->to_date;

        $reports = RoyaltyClubBonus::with('member')
            ->whereBetween('calculated_at', [
                $from . ' 00:00:00',
                $to . ' 23:59:59',
            ])
            ->orderBy('calculated_at')
            ->get();

        $totalTurnover = $reports->sum('monthly_turnover');
        $totalPool     = $reports->sum('royalty_pool_amount');
        $totalBonus    = $reports->sum('bonus_amount');

        $pdf = Pdf::loadView(
            'pdf.royalty_club_bonus',
            [
                'reports'        => $reports,
                'from'           => $from,
                'to'             => $to,
                'totalTurnover'  => $totalTurnover,
                'totalPool'      => $totalPool,
                'totalBonus'     => $totalBonus,
            ]
        );

        return $pdf->download(
            'Royalty_Club_Bonus_' .
            $from .
            '_to_' .
            $to .
            '.pdf'
        );
    }
}