<?php

namespace App\Http\Controllers\MemberReport;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyBonus;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class RepurchaseBonusReportController extends Controller
{
    public function download(Request $request)
    {
        $request->validate([
            'from_date' => ['required', 'date'],
            'to_date'   => ['required', 'date', 'after_or_equal:from_date'],
        ]);

        $from = $request->from_date;
        $to   = $request->to_date;

        /*
        |--------------------------------------------------------------------------
        | REPURCHASE BONUS REPORT
        |--------------------------------------------------------------------------
        | Repurchase Bonus is calculated weekly.
        | Only records generated with type = repurchase are included.
        |--------------------------------------------------------------------------
        */

        $reports = LoyaltyBonus::with('member')
            ->where('type', 'repurchase')
            ->whereBetween('calculated_at', [
                $from . ' 00:00:00',
                $to . ' 23:59:59',
            ])
            ->orderBy('calculated_at')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | TOTALS
        |--------------------------------------------------------------------------
        */

        $totalPurchase = $reports->sum('purchase_amount');
        $totalBonus    = $reports->sum('bonus_amount');

        /*
        |--------------------------------------------------------------------------
        | GENERATE PDF
        |--------------------------------------------------------------------------
        */

        $pdf = Pdf::loadView(
            'pdf.repurchase_bonus',
            [
                'reports'       => $reports,
                'from'          => $from,
                'to'            => $to,
                'totalPurchase' => $totalPurchase,
                'totalBonus'    => $totalBonus,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | DOWNLOAD
        |--------------------------------------------------------------------------
        */

        return $pdf->download(
            'Repurchase_Bonus_' .
            $from .
            '_to_' .
            $to .
            '.pdf'
        );
    }
}

