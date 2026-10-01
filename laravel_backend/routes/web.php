<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminProductController;
use App\Http\Controllers\MemberController;
use Livewire\Livewire;
// use Illuminate\Support\Facades\Mail;

use App\Http\Controllers\Admin\BalanceRequestController;


// Route for reports in admin
use App\Http\Controllers\MemberReport\RepurchaseBonusReportController;
use App\Http\Controllers\MemberReport\CashbackReportController;
use App\Http\Controllers\MemberReport\LeadershipRankBonusReportController;
use App\Http\Controllers\MemberReport\GroupBuiltupBonusReportController;
use App\Http\Controllers\MemberReport\RoyaltyClubBonusReportController;
use App\Http\Controllers\MemberReport\ConsistencyBonusReportController;
// use App\Http\Controllers\MemberReport\DiwaliBonusReportController;
use App\Http\Controllers\MemberReport\RankRewardReportController;
use App\Http\Controllers\MemberReport\BusinessMonitoringBonusReportController;
use App\Http\Controllers\MemberReport\FamilySaverBonusReportController;
// use App\Http\Controllers\MemberReport\BranchTurnoverBonusReportController; // disabled
use App\Http\Controllers\MemberReport\PayoutReportController;
use App\Http\Controllers\MemberReport\TdsReportController;
use App\Http\Controllers\MemberReport\ExpenseReportController;
// use App\Filament\Pages\DailyClosingDetails;

use App\Exports\PayoutSheetExport;
use App\Exports\TdsReportExport;
use App\Models\PayoutBatch;
use Maatwebsite\Excel\Facades\Excel;

// Payout excel
Route::get('/admin/payout-cycle-sheet', function () {

    $today = now()->day;

    if ($today <= 10) {
        $start = now()->startOfMonth()->toDateString();
        $end = now()->startOfMonth()->addDays(9)->toDateString();
        $fileName = 'Payout_01-10.xlsx';
    } elseif ($today <= 20) {
        $start = now()->startOfMonth()->addDays(10)->toDateString();
        $end = now()->startOfMonth()->addDays(19)->toDateString();
        $fileName = 'Payout_11-20.xlsx';
    } else {
        $start = now()->startOfMonth()->addDays(20)->toDateString();
        $end = now()->endOfMonth()->toDateString();
        $fileName = 'Payout_21-' . now()->daysInMonth . '.xlsx';
    }

    $batchIds = PayoutBatch::whereBetween('payout_date', [$start, $end])
        ->pluck('id');

    return Excel::download(
        new PayoutSheetExport($batchIds),
        $fileName
    );

})->name('admin.payout-cycle-sheet');

// TDS Report excel
Route::get('/admin/tds-report/excel', function () {

    $from = request('from_date');
    $to   = request('to_date');

    $batchIds = \App\Models\PayoutBatch::whereBetween('payout_date', [
            $from,
            $to,
        ])
        ->pluck('id');

    return Excel::download(
        new TdsReportExport($batchIds),
        'TDS_Report_' . $from . '_to_' . $to . '.xlsx'
    );

})->name('admin.tds-report.excel');


Route::get('/test-route', function () {
    return "working";
});

Route::get('/admin-auto-logout', function () {
    Auth::guard()->logout();

    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/admin/login');
});

Route::get('/run-binary-test', function () {
    $controller = new \App\Http\Controllers\GroupBuiltupBonusController();

    $request = new \Illuminate\Http\Request([
        'cycle_date' => now()->toDateString()
    ]);

    return $controller->calculateCycle($request);
});

Route::get('/admin/balance-request/approve/{id}', [BalanceRequestController::class, 'approve']);
Route::get('/admin/balance-request/reject/{id}', [BalanceRequestController::class, 'reject']);

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin-api')->name('admin.')->group(function () {
    Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
    Route::post('/products/repurchase', [AdminProductController::class, 'storeRepurchase'])->name('products.repurchase.store');
    Route::post('/products/consistency', [AdminProductController::class, 'storeConsistency'])->name('products.consistency.store');
});

Route::get('/livewire/livewire.js', function () {
    return response()->file(base_path('vendor/livewire/livewire/dist/livewire.js'), [
        'Content-Type' => 'application/javascript',
    ]);
})->where('any', '.*');


Route::get('/admin-login-member/{id}', function ($id) {

    return redirect()->away(rtrim(config('app.frontend_url'), '/') . '/member/auto-login/' . urlencode($id));

})->name('admin.login.member');

// Route for reports in admin

Route::get(
    '/admin/repurchase-bonus-report/pdf',
    [RepurchaseBonusReportController::class, 'download']
)->name('admin.repurchase-bonus-report.pdf');
Route::get(
    '/admin/leadership-rank-bonus-report/pdf',
    [LeadershipRankBonusReportController::class, 'download']
)->name('admin.leadership-rank-bonus-report.pdf');
Route::get(
    '/admin/cashback-report/pdf',
    [CashbackReportController::class, 'download']
)->name('admin.cashback-report.pdf');
Route::get(
    '/admin/group-builtup-bonus-report/pdf',
    [GroupBuiltupBonusReportController::class, 'download']
)->name('admin.group-builtup-bonus-report.pdf');
Route::get(
    '/admin/royalty-club-bonus-report/pdf',
    [RoyaltyClubBonusReportController::class, 'download']
)->name('admin.royalty-club-bonus-report.pdf');
Route::get(
    '/admin/consistency-bonus-report/pdf',
    [ConsistencyBonusReportController::class, 'download']
)->name('admin.consistency-bonus-report.pdf');
// Route::get(
//     '/admin/diwali-bonus-report/pdf',
//     [DiwaliBonusReportController::class, 'download']
// )->name('admin.diwali-bonus-report.pdf');
Route::get(
    '/admin/rank-reward-report/pdf',
    [RankRewardReportController::class, 'download']
)->name('admin.rank-reward-report.pdf');
Route::get(
    '/admin/business-monitoring-bonus-report/pdf',
    [BusinessMonitoringBonusReportController::class, 'download']
)->name('admin.business-monitoring-bonus-report.pdf');
Route::get(
    '/admin/family-saver-bonus-report/pdf',
    [FamilySaverBonusReportController::class, 'download']
)->name('admin.family-saver-bonus-report.pdf');
// Branch Turnover Bonus report is disabled across the project.
// Route::get(
//     '/admin/branch-turnover-bonus-report/pdf',
//     [BranchTurnoverBonusReportController::class, 'download']
// )->name('admin.branch-turnover-bonus-report.pdf');



Route::get(
    '/admin/payout-report/{batch}/pdf',
    [PayoutReportController::class, 'download']
)->name('admin.payout-report.pdf');

Route::get(
    '/admin/tds-report/pdf',
    [TdsReportController::class, 'download']
)->name('admin.tds-report.pdf');

Route::get(
    '/admin/expense_report/pdf',
    [ExpenseReportController::class, 'download']
)->name('admin.expense_report.pdf');


// any web routes remain here; member API routes moved to routes/api.php to avoid CSRF errors
