<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CashbackWallet;
use App\Models\Member;
class CashbackWalletController extends Controller
{
  public function balance(Request $request)
{
    $request->validate([
        'member_id' => 'required'
    ]);

    $member = Member::where('user_id', $request->member_id)->first();

    if (!$member) {
        return response()->json([
            'balance' => 0
        ]);
    }

    $balance = CashbackWallet::where('user_id', $member->id)
        ->selectRaw('COALESCE(SUM(credit) - SUM(debit),0) as balance')
        ->value('balance');

    return response()->json([
        'balance' => (float) $balance
    ]);
}
    
    public function transactions(Request $request)
{
    $request->validate([
        'member_id' => 'required'
    ]);

    $member = Member::where('user_id', $request->member_id)->first();

if (!$member) {

    return response()->json([
        'balance' => 0,
        'total_credit' => 0,
        'total_debit' => 0,
        'transactions' => [],
    ]);
}

$records = CashbackWallet::where('user_id', $member->id)
    ->orderBy('id', 'asc')
    ->get();
    $transactions = [];

    $balance = 0;

    $totalCredit = 0;

    $totalDebit = 0;

    foreach ($records as $item) {

        $credit = null;
        $debit = null;

        if ($item->credit > 0) {

            $credit = (float) $item->credit;

            $balance += $credit;

            $totalCredit += $credit;

        } elseif ($item->debit > 0) {

            $debit = (float) $item->debit;

            $balance -= $debit;

            $totalDebit += $debit;
        }

        $transactions[] = [

            'id' => $item->id,

            'date' => $item->created_at,

            'detail' => $item->detail,

            'credit_amount' => $credit,

            'debit_amount' => $debit,

            'balance' => $balance,
        ];
    }

    return response()->json([

        'balance' => $balance,

        'total_credit' => $totalCredit,

        'total_debit' => $totalDebit,

        'transactions' => array_reverse($transactions),
    ]);
}
}