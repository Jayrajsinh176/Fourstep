<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EarningBalanceController extends Controller
{
    private function resolveMemberUserId(Request $request): string
    {
        return trim((string) ($request->header('X-Auth-Member') ?: $request->query('user_id', '')));
    }

   
    public function withdrawal(Request $request)
    {
        $user_id = $this->resolveMemberUserId($request);

        if (!$user_id) {
            return response()->json([
                "message" => "User ID missing"
            ],401);
        }

        $query = DB::table('earning_balance_withdrawals as ebw')
            ->leftJoin('members as m', 'm.user_id', '=', 'ebw.user_id')
            ->select('ebw.*', 'm.fullname as member_name')
            ->where('ebw.user_id',$user_id)
            ->orderBy('ebw.id','desc');

        $rows = $query->get();

        $data = [];
        $sr = 1;
        $totalWithdrawal = 0;

        foreach ($rows as $row) {
            $amount = (float) ($row->payment_amount ?? 0);
            $totalWithdrawal += $amount;

            $data[] = [
                "sr_no" => $sr++,
                "user_id" => $row->user_id ?? "",
                "member_name" => $row->member_name ?? "--",
                "payment_date" => $row->payment_date 
                    ? date('d-m-Y',strtotime($row->payment_date))
                    : "-",
                "payment_amount" => $amount,
                "reference_no" => $row->reference_no ?? "",
                "status" => $row->status ?? "Pending"
            ];
        }

        return response()->json([
            "message" => "Withdrawal history",
            "data" => $data,
            "summary" => [
                "total_records" => count($data),
                "total_withdrawal" => round($totalWithdrawal, 2)
            ]
        ]);
    }


 
    public function history(Request $request)
    {
        $user_id = $this->resolveMemberUserId($request);

        if (!$user_id) {
            return response()->json([
                "message" => "User ID missing"
            ],401);
        }

        $query = DB::table('earning_balance_histories as ebh')
            ->leftJoin('members as m', 'm.user_id', '=', 'ebh.user_id')
            ->select('ebh.*', 'm.fullname as member_name')
            ->where('ebh.user_id',$user_id)
            ->orderBy('ebh.id','desc');

        $rows = $query->get();

        $data = [];
        $sr = 1;
        $totalCredit = 0;
        $totalDebit = 0;
        $latestBalance = 0;

        foreach ($rows as $row) {
            $creditAmount = (float) ($row->credit_amount ?? 0);
            $debitAmount = (float) ($row->debit_amount ?? 0);
            $balanceAmount = (float) ($row->balance_amount ?? 0);

            if ($sr === 1) {
                $latestBalance = $balanceAmount;
            }

            $totalCredit += $creditAmount;
            $totalDebit += $debitAmount;

            $data[] = [
                "sr_no" => $sr++,
                "user_id" => $row->user_id ?? "",
                "member_name" => $row->member_name ?? "--",
                "date" => $row->entry_date
                    ? date('d-m-Y',strtotime($row->entry_date))
                    : "-",
                "description" => $row->description ?? "",
                "credit_amount" => $creditAmount,
                "debit_amount" => $debitAmount,
                "balance_amount" => $balanceAmount,
                "status" => $row->status ?? "Pending"
            ];
        }

        return response()->json([
            "message" => "Earning balance history",
            "data" => $data,
            "summary" => [
                "total_records" => count($data),
                "total_credit" => round($totalCredit, 2),
                "total_debit" => round($totalDebit, 2),
                "net_amount" => round($totalCredit - $totalDebit, 2),
                "latest_balance" => round($latestBalance, 2)
            ]
        ]);
    }

}