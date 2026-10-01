<?php

namespace App\Http\Controllers\MemberReport;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ExpenseReportController extends Controller
{
    public function download(Request $request)
    {
        $request->validate([
            'from_date' => ['required', 'date'],
            'to_date'   => ['required', 'date', 'after_or_equal:from_date'],
        ]);

        $from = $request->from_date;
        $to   = $request->to_date;

        $expenses = Expense::whereBetween('expense_date', [
                $from,
                $to,
            ])
            ->orderBy('expense_date')
            ->get();

        $totalExpense = $expenses->sum('amount');

$pdf = Pdf::loadView(
    'pdf.expense_report',
    [
        'expenses' => $expenses,
        'from' => $from,
        'to' => $to,
        'totalExpense' => $totalExpense,
    ]
);

return $pdf->download(
    'Expense_Report_' . $from . '_to_' . $to . '.pdf'
);
    }
}