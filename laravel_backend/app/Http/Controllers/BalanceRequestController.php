<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BalanceRequestController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'member_id'      => 'required',
            'type'           => 'required',
            'amount'         => 'required|numeric|min:500',
            'transaction_no' => 'nullable|string',
            'payment_slip'   => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);
        
// Transaction number duplicate check
if (!empty($request->transaction_no)) {

    $exists = DB::table('balance_requests')
        ->where('transaction_no', $request->transaction_no)
        ->exists();

    if ($exists) {
        return response()->json([
            'success' => false,
            'message' => 'Transaction number already exists. Please enter a different transaction number.'
        ], 422);
    }
}

// ADD THIS BLOCK
$pendingRequest = DB::table('balance_requests')
    ->where('member_id', $request->member_id)
    ->where('status', 'pending')
    ->exists();

if ($pendingRequest) {
    return response()->json([
        'success' => false,
        'message' => 'You already have a pending balance request. Please wait until it is approved or rejected.'
    ], 422);
}

$slipPath = null;

        if ($request->hasFile('payment_slip')) {
            $slipPath = $request->file('payment_slip')
                ->store('payment_slips', 'public');
        }

        DB::table('balance_requests')->insert([
            'member_id'       => $request->member_id,
            'type'            => $request->type,
            'amount'          => $request->amount,
            'mode_of_payment' => $request->mode_of_payment ?? 'By Online Payment',
            'transaction_no'  => $request->transaction_no,
            'payment_slip'    => $slipPath,
            'status'          => 'pending',
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        return response()->json([
            'message' => 'Request submitted successfully',
        ]);
    }

    public function history(Request $request)
    {
        $member_id = $request->member_id;

        $data = DB::table('balance_requests')
    ->where('member_id', $member_id)
    ->where('entry_type', 'credit')
    ->latest()
    ->get();

        return response()->json($data);
    }

   public function transactions(Request $request)
{
    $member_id = $request->member_id;

    $records = DB::table('balance_requests')
        ->where('member_id', $member_id)
        ->where('status', 'approved')
        ->orderBy('id', 'asc')
        ->get();

    $transactions = [];

    $balance = 0;

    $total_credit = 0;

    $total_debit = 0;

    foreach ($records as $item) {

        $credit = null;

        $debit = null;

        if ($item->entry_type === 'credit') {

            $credit = $item->amount;

            $balance += $credit;

            $total_credit += $credit;

            $detail = 'Balance Added';
        }

       
        elseif ($item->entry_type === 'debit') {

            $debit = $item->amount;

            $balance -= $debit;

            $total_debit += $debit;

            $detail = 'Ecommerce Order';
        }

        else {

            continue;
        }

        $transactions[] = [

            'id' => $item->id,

            'date' => $item->created_at,

            'detail' => $detail,

            'credit_amount' => $credit,

            'debit_amount' => $debit,

            'balance' => $balance,
        ];
    }

    return response()->json([

        'balance' => $balance,

        'total_credit' => $total_credit,

        'total_debit' => $total_debit,

        'transactions' => array_reverse($transactions),
    ]);
}
}