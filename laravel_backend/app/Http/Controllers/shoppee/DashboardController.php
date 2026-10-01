<?php

namespace App\Http\Controllers\shoppee;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function summary($memberCode)
    {

        /*
        |--------------------------------------------------------------------------
        | MEMBER DETAILS
        |--------------------------------------------------------------------------
        */

        $member = DB::table('shoppee_members')
            ->where('member_id', $memberCode)
            ->first();

        if (!$member) {

            return response()->json([
                'message' => 'Member not found'
            ], 404);
        }

        $memberId = $member->id;

        /*
        |--------------------------------------------------------------------------
        | PURCHASE BALANCE
        |--------------------------------------------------------------------------
        */

        $purchaseCredit = DB::table('shoppee_transactions')
            ->where('member_id', $memberId)
            ->where('balance_type', 'purchase')
            ->where('entry_type', 'credit')
            ->sum('amount');

        $purchaseDebit = DB::table('shoppee_transactions')
            ->where('member_id', $memberId)
            ->where('balance_type', 'purchase')
            ->where('entry_type', 'debit')
            ->sum('amount');

        $purchaseBalance = $purchaseCredit - $purchaseDebit;

        /*
        |--------------------------------------------------------------------------
        | TURNOVER BALANCE
        |--------------------------------------------------------------------------
        */

        $turnoverCredit = DB::table('shoppee_transactions')
            ->where('member_id', $memberId)
            ->where('balance_type', 'turnover')
            ->where('entry_type', 'credit')
            ->sum('amount');

        $turnoverDebit = DB::table('shoppee_transactions')
            ->where('member_id', $memberId)
            ->where('balance_type', 'turnover')
            ->where('entry_type', 'debit')
            ->sum('amount');

        $turnoverBalance = $turnoverCredit - $turnoverDebit;
        
        /*
|--------------------------------------------------------------------------
| COMMISSION BALANCE
|--------------------------------------------------------------------------
*/

$commissionCredit = DB::table('shoppee_transactions')
    ->where('member_id', $memberId)
    ->where('balance_type', 'commission')
    ->where('entry_type', 'credit')
    ->sum('amount');

$commissionDebit = DB::table('shoppee_transactions')
    ->where('member_id', $memberId)
    ->where('balance_type', 'commission')
    ->where('entry_type', 'debit')
    ->sum('amount');

$commissionBalance = $commissionCredit - $commissionDebit;

        /*
        |--------------------------------------------------------------------------
        | PURCHASE ORDERS
        |--------------------------------------------------------------------------
        */

        $purchaseOrders = DB::table('shoppee_product_requests')
            ->where('member_id', $memberId)
            ->where('status', 'Approved')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | SALES ORDERS
        |--------------------------------------------------------------------------
        */

     $salesOrders = DB::table('shoppee_orders')
    ->where('shoppee_member_id', $memberCode)
    ->whereIn('status', ['Delivered', 'delivered'])
    ->count();

        /*
        |--------------------------------------------------------------------------
        | SALES TURNOVER
        |--------------------------------------------------------------------------
        */

    $salesTurnover = DB::table('shoppee_orders')
    ->where('shoppee_member_id', $memberCode)
    ->whereIn('status', ['Delivered', 'delivered'])
    ->sum('total_amount');

        return response()->json([

            /*
            |--------------------------------------------------------------------------
            | MEMBER DETAILS
            |--------------------------------------------------------------------------
            */

            'member_name' => $member->fullname,
            'member_id' => $member->member_id,
            'branch_name' => $member->branch_name,
            'mobile_no' => $member->mobile_no,
            'email' => $member->email,

            /*
            |--------------------------------------------------------------------------
            | DASHBOARD DETAILS
            |--------------------------------------------------------------------------
            */

            'purchase_balance' => number_format($purchaseBalance, 2),

            'turnover_balance' => number_format($turnoverBalance, 2),

            'purchase_orders' => $purchaseOrders,

'commission_amount' => number_format($commissionBalance, 2),

            'sales_orders' => $salesOrders,

            'sales_turnover' => number_format($salesTurnover, 2),

        ]);
    }
}