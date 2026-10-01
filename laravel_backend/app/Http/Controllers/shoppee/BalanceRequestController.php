<?php
namespace App\Http\Controllers\Shoppee;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shoppee_BalanceRequest;
use App\Models\Shoppee_Transaction;
use Illuminate\Support\Facades\DB;
class BalanceRequestController extends Controller
{
    // Submit a new balance request
   public function store(Request $request)
{
    
        // Check duplicate transaction number
$existingRequest = Shoppee_BalanceRequest::where(
    'transaction_no',
    $request->transaction_no
)->first();

if ($existingRequest) {
    return response()->json([
        'message' => 'This transaction number has already been submitted.',
        'status' => false,
    ], 422);
}

    $request->validate([
     'member_id' => 'nullable',
        'type' => 'required',
        'amount' => 'required|numeric|min:500',
        'mode_of_payment' => 'required',
 'transaction_no' => 'required',
'payment_slip' => 'required|mimes:jpg,jpeg,png,webp|max:2048',
    ]);
    
    $filePath = null;

    if ($request->hasFile('payment_slip')) {

        $file = $request->file('payment_slip');

        // CHECK VALID IMAGE
        if (!getimagesize($file)) {
            return response()->json([
                'message' => 'Invalid image file'
            ], 422);
        }

        $extension = $file->getClientOriginalExtension();

        $filename = time() . '.' . $extension;

        // Newly Added
         
        $destinationPath = config('app.public_html_path') . '/payment_slips';

        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0775, true);
        }

        $file->move($destinationPath, $filename);

        $filePath = 'payment_slips/' . $filename;
    }

    Shoppee_BalanceRequest::create([
          'member_id' => $request->member_id,// ✅ IMPORTANT
        'type' => $request->type,
        'amount' => $request->amount,
        'mode_of_payment' => $request->mode_of_payment,
        'transaction_no' => $request->transaction_no,
        'payment_slip' => $filePath,
        'status' => 'pending',
    ]);

    return response()->json([
        'message' => 'Request submitted successfully'
    ]);
}
   
  // Balance request history Data
public function history(Request $request)
{
    $query = Shoppee_BalanceRequest::query();

    // specific member history
    if ($request->member_id) {
        $query->where('member_id', $request->member_id);
    }

    // filter by type
    if ($request->type && $request->type != 'all') {
        $query->where('type', $request->type);
    }

    $data = $query->latest()->get();

    return response()->json($data);
}

    // Approve a balance request Data
    public function approveRequest($id)
    {
        $requestData = Shoppee_BalanceRequest::find($id);
        if (!$requestData) {
            return response()->json([
                'message' => 'Balance request not found'
            ], 404);
        }
        if ($requestData->status === 'Approved'){
            return response()->json([
                'message' => 'Balance request already approved'
            ], 400);
        }
       $requestData->status = 'Approved';
       
        $requestData->save();
     DB::table('shoppee_transactions')->insert([
    'member_id' => $requestData->member_id,
    'ref_id' => $requestData->id,
    'ref_type' => 'balance_request',
    'balance_type' => $requestData->type,
    'entry_type' => 'credit',
    'amount' => $requestData->amount,
    'detail' => 'Credited Against ' .
        ucfirst($requestData->type) .
        ' Request#' .
        $requestData->id,
    'created_at' => now(),
    'updated_at' => now(),
]);
        return response()->json([
            'message' => 'Balance request approved successfully'
        ]);
    }
    
// Reject balance request
public function rejectRequest(Request $request, $id)
{
    $request->validate([
        'reject_reason' => 'required|string',
    ]);

    $requestData = Shoppee_BalanceRequest::find($id);

    if (!$requestData) {
        return response()->json([
            'message' => 'Balance request not found'
        ], 404);
    }

    if ($requestData->status === 'Approved') {
        return response()->json([
            'message' => 'Approved request cannot be rejected'
        ], 400);
    }

    if ($requestData->status === 'Rejected') {
        return response()->json([
            'message' => 'Balance request already rejected'
        ], 400);
    }

    $requestData->update([
        'status' => 'Rejected',
        'reject_reason' => $request->reject_reason,
    ]);

    return response()->json([
        'message' => 'Balance request rejected successfully'
    ]);
}
  
// Get member transactions
public function transactions(Request $request)
{
    $query = Shoppee_Transaction::query();

    // filter by member
    if ($request->member_id) {
        $query->where('member_id', $request->member_id);
    }

    $transactionsRaw = $query
        ->orderBy('created_at', 'asc')
        ->get();

    /*
    |--------------------------------------------------------------------------
    | PURCHASE TOTALS
    |--------------------------------------------------------------------------
    */
    $purchaseCredit = $transactionsRaw
        ->where('balance_type', 'purchase')
        ->where('entry_type', 'credit')
        ->sum('amount');

    $purchaseDebit = $transactionsRaw
        ->where('balance_type', 'purchase')
        ->where('entry_type', 'debit')
        ->sum('amount');

    $purchaseBalance = $purchaseCredit - $purchaseDebit;

    /*
    |--------------------------------------------------------------------------
    | TURNOVER TOTALS
    |--------------------------------------------------------------------------
    */
    $turnoverCredit = $transactionsRaw
        ->where('balance_type', 'turnover')
        ->where('entry_type', 'credit')
        ->sum('amount');

    $turnoverDebit = $transactionsRaw
        ->where('balance_type', 'turnover')
        ->where('entry_type', 'debit')
        ->sum('amount');

    $turnoverBalance = $turnoverCredit - $turnoverDebit;

/*
|--------------------------------------------------------------------------
| COMMISSION TOTALS
|--------------------------------------------------------------------------
*/

$commissionCredit = $transactionsRaw
    ->where('balance_type', 'commission')
    ->where('entry_type', 'credit')
    ->sum('amount');

$commissionDebit = $transactionsRaw
    ->where('balance_type', 'commission')
    ->where('entry_type', 'debit')
    ->sum('amount');

$commissionBalance = $commissionCredit - $commissionDebit;



    /*
    |--------------------------------------------------------------------------
    | RUNNING BALANCE
    |--------------------------------------------------------------------------
    */
    $purchaseRunning = 0;
    $turnoverRunning = 0;
    $commissionRunning = 0;

   $transactions = $transactionsRaw->map(function ($item)
    use (
        &$purchaseRunning,
        &$turnoverRunning,
        &$commissionRunning
    ) {

        // PURCHASE BALANCE
        if ($item->balance_type === 'purchase') {

            if ($item->entry_type === 'credit') {
                $purchaseRunning += $item->amount;
            } else {
                $purchaseRunning -= $item->amount;
            }

            $currentBalance = $purchaseRunning;
        }

      // TURNOVER BALANCE
else if ($item->balance_type === 'turnover') {

    if ($item->entry_type === 'credit') {
        $turnoverRunning += $item->amount;
    } else {
        $turnoverRunning -= $item->amount;
    }

    $currentBalance = $turnoverRunning;
}

// COMMISSION BALANCE
else if ($item->balance_type === 'commission') {

    if ($item->entry_type === 'credit') {
        $commissionRunning += $item->amount;
    } else {
        $commissionRunning -= $item->amount;
    }

    $currentBalance = $commissionRunning;
}

// OTHER BALANCES
else {

    $currentBalance = 0;

}

        return [
            'id' => $item->id,
            'date' => $item->created_at->format('d-m-Y'),
            'detail' => $item->detail,

            'balance_type' => $item->balance_type,

            'credit_amount' => $item->entry_type === 'credit'
                ? $item->amount
                : null,

            'debit_amount' => $item->entry_type === 'debit'
                ? $item->amount
                : null,

            'balance' => $currentBalance,
        ];
    })->reverse()->values();

   return response()->json([
    'purchase_balance' => round($purchaseBalance, 2),
    'purchase_credit' => round($purchaseCredit, 2),
    'purchase_debit' => round($purchaseDebit, 2),

    'turnover_balance' => round($turnoverBalance, 2),
    'turnover_credit' => round($turnoverCredit, 2),
    'turnover_debit' => round($turnoverDebit, 2),
    
    'commission_balance' => round($commissionBalance, 2),
'commission_credit' => round($commissionCredit, 2),
'commission_debit' => round($commissionDebit, 2),


    'transactions' => $transactions->map(function ($item) {
        $item['credit_amount'] = $item['credit_amount']
            ? round($item['credit_amount'], 2)
            : null;

        $item['debit_amount'] = $item['debit_amount']
            ? round($item['debit_amount'], 2)
            : null;

        $item['balance'] = round($item['balance'], 2);

        return $item;
    }),
]);
}


}