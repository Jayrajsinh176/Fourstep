<?php

namespace App\Http\Controllers\shoppee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\OrderTypeEcom;

class BranchSaleController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | FETCH ORDER TYPES
    |--------------------------------------------------------------------------
    */
    public function getOrderTypes()
    {

        $types = OrderTypeEcom::where(
            'status',
            1
        )->get();

        return response()->json([

            'success' => true,

            'types' => $types

        ]);

    }

    /*
    |--------------------------------------------------------------------------
    | FETCH PRODUCTS BY ORDER TYPE
    |--------------------------------------------------------------------------
    */
    public function getProductsByOrderType($id)
    {

        $orderType = OrderTypeEcom::with([
            'products.variants'
        ])->find($id);

        if (!$orderType) {

            return response()->json([

                'success' => false,

                'message' => 'Order type not found'

            ]);

        }

        $products = [];

        foreach ($orderType->products as $product) {

            foreach ($product->variants as $variant) {

                $products[] = [

                    'id' => $product->id,

                    'name' => $product->name,

                    'category_name' => '',

                    'variant_id' => $variant->id,

                    'packing_size' =>
                        $variant->packing_size,

                    'price' =>
                        $variant->price,

                    'offer_price' =>
                        $variant->offer_price,

                    'pv' =>
                        $variant->pv,

                    'bv' =>
                        $variant->bv,

                    'stock' =>
                        $variant->stock,

                    'minimum_quantity' =>
                        $variant->minimum_quantity,

                ];

            }

        }

        return response()->json([

            'success' => true,

            'products' => $products

        ]);

    }

    /*
    |--------------------------------------------------------------------------
    | SUBMIT TURNOVER ORDER
    |--------------------------------------------------------------------------
    */
    public function submitTurnoverOrder(Request $request)
    {

        DB::beginTransaction();

        try {

$member = DB::table('shoppee_members')
    ->where(
        'member_id',
        $request->member_id
    )
    ->first();

if (!$member) {

    return response()->json([
        'success' => false,
        'message' => 'Member not found'
    ], 404);
}

if (empty($member->transaction_password)) {

    return response()->json([
        'success' => false,
        'message' =>
        'Please set transaction password first'
    ], 400);
}

if (
    !Hash::check(
        $request->transaction_password,
        $member->transaction_password
    )
) {

    return response()->json([
        'success' => false,
        'message' =>
        'Invalid Transaction Password'
    ], 400);
}


$memberId = $member->id;

            $products = $request->products;

            $totalAmount = 0;
            
            $totalCommission = 0;

            /*
            |--------------------------------------------------------------------------
            | TOTAL CALCULATION
            |--------------------------------------------------------------------------
            */

            foreach ($products as $item) {

                $totalAmount +=
                    $item['offer_price']
                    * $item['quantity'];

            }

            /*
            |--------------------------------------------------------------------------
            | TURNOVER BALANCE CHECK
            |--------------------------------------------------------------------------
            */

            $totalCredit = DB::table(
                'shoppee_transactions'
            )

            ->where(
                'member_id',
                $memberId
            )

            ->where(
                'balance_type',
                'turnover'
            )

            ->where(
                'entry_type',
                'credit'
            )

            ->sum('amount');

            $totalDebit = DB::table(
                'shoppee_transactions'
            )

            ->where(
                'member_id',
                $memberId
            )

            ->where(
                'balance_type',
                'turnover'
            )

            ->where(
                'entry_type',
                'debit'
            )

            ->sum('amount');

            $turnoverBalance =
                $totalCredit - $totalDebit;

            if ($turnoverBalance < $totalAmount) {

                return response()->json([

                    'success' => false,

                    'message' =>
                        'Insufficient Turnover Balance'

                ], 400);

            }

            /*
            |--------------------------------------------------------------------------
            | STOCK VALIDATION
            |--------------------------------------------------------------------------
            */

            foreach ($products as $item) {

                // TOTAL RECEIVED
                $received = DB::table(
                    'shoppee_member_stocks'
                )

                ->where(
                    'member_id',
                    $memberId
                )

                ->where(
                    'variant_id',
                    $item['variant_id']
                )

                ->sum('quantity');

                // TOTAL USED
                $used = DB::table(
                    'shoppee_branch_sale_order_items'
                )

                ->where(
                    'variant_id',
                    $item['variant_id']
                )

                ->sum('quantity');

                // AVAILABLE STOCK
                $availableStock =
                    $received - $used;

                // CHECK STOCK
                if (
                    $availableStock <
                    $item['quantity']
                ) {

                    return response()->json([

                        'success' => false,

                        'message' =>
                            'Low stock for product'

                    ], 400);

                }

            }

            /*
            |--------------------------------------------------------------------------
            | CREATE ORDER
            |--------------------------------------------------------------------------
            */

            $orderId = DB::table(
                'shoppee_branch_sale_orders'
            )

            ->insertGetId([

                'member_id' =>
                    $memberId,
                    
    'customer_user_id' =>
        $request->customer_user_id,

                'order_type_id' =>
                    $request->order_type_id,

                'total_amount' =>
                    $totalAmount,

                'payment_method' =>
                    'Turnover Wallet',

                'status' =>
                    'Success',

                'created_at' =>
                    now(),

                'updated_at' =>
                    now(),

            ]);

            /*
            |--------------------------------------------------------------------------
            | SAVE ORDER ITEMS
            |--------------------------------------------------------------------------
            */

            foreach ($products as $item) {

    $variant = DB::table('ecom_product_variants')
        ->where('id', $item['variant_id'])
        ->first();

    $commissionPercentage = 0;

    if ($variant) {

        switch ($member->branch_type) {

            case 'Mega Branch':
                $commissionPercentage = $variant->mega_branch_commission ?? 0;
                break;

            case 'Mini Branch':
                $commissionPercentage = $variant->mini_branch_commission ?? 0;
                break;

            case 'Area Branch':
                $commissionPercentage = $variant->pincode_branch_commission ?? 0;
                break;

        }

    }

    $commissionAmount =
        (
            $item['offer_price']
            *
            $commissionPercentage
            / 100
        )
        *
        $item['quantity'];

    $totalCommission += $commissionAmount;

    DB::table(
        'shoppee_branch_sale_order_items'
    )

    ->insert([

        'order_id' =>
            $orderId,

        'product_id' =>
            $item['id'],

        'variant_id' =>
            $item['variant_id'],

        'quantity' =>
            $item['quantity'],

        'price' =>
            $item['offer_price'],

        'amount' =>
            $item['offer_price']
            * $item['quantity'],

        'commission_amount' =>
            $commissionAmount,
            
            'commission_percentage' =>
    $commissionPercentage,

        'created_at' =>
            now(),

        'updated_at' =>
            now(),

    ]);

}

            /*
            |--------------------------------------------------------------------------
            | DEBIT TURNOVER BALANCE
            |--------------------------------------------------------------------------
            */

            DB::table(
                'shoppee_transactions'
            )

            ->insert([

                'member_id' =>
                    $memberId,

                'ref_id' =>
                    $orderId,

                'ref_type' =>
                    'branch_sale',

                'balance_type' =>
                    'turnover',

                'entry_type' =>
                    'debit',

                'amount' =>
                    $totalAmount,

                'detail' =>
                    'Debited Against Branch Sale Order#' .
                    $orderId,

                'created_at' =>
                    now(),

                'updated_at' =>
                    now(),

            ]);
            
            if ($totalCommission > 0) {

    DB::table('shoppee_transactions')
        ->insert([

            'member_id' => $memberId,

            'ref_id' => $orderId,

            'ref_type' => 'branch_sale',

            'balance_type' => 'commission',

            'entry_type' => 'credit',

            'amount' => $totalCommission,

     'detail' => 'Commission Credited Against Branch Sale Order#' . $orderId,

            'created_at' => now(),

            'updated_at' => now(),

        ]);

}
            /*
|--------------------------------------------------------------------------
| MLM ACTIVATION FROM BRANCH SALE
|--------------------------------------------------------------------------
*/

$mlmMember = null;
$orderBv = 0;

if (
    $request->customer_user_id &&
    DB::table('members')
        ->where('user_id', $request->customer_user_id)
        ->exists()
) {

    $mlmMember = DB::table('members')
        ->where('user_id', $request->customer_user_id)
        ->first();

    foreach ($products as $item) {

        $variant = DB::table('ecom_product_variants')
            ->where('id', $item['variant_id'])
            ->first();

        if ($variant) {
    $orderBv += ($variant->bv ?? 0) * $item['quantity'];
}
    }

$currentStep = (int) ($mlmMember->package_step ?? 0);

$currentSelfBv = (float) ($mlmMember->self_bv ?? 0);
$totalMemberBv = $currentSelfBv + $orderBv;

$step = 0;

if ($totalMemberBv >= 1000) {
    $step = 4;
} elseif ($totalMemberBv >= 500) {
    $step = 3;
} elseif ($totalMemberBv >= 250) {
    $step = 2;
} elseif ($totalMemberBv >= 125) {
    $step = 1;
}

$updateData = [
    'self_bv' => $totalMemberBv,
    'updated_at' => now(),
];

if ($totalMemberBv >= 125) {

    $updateData['status'] = 1;

    if (!$mlmMember->activation_date) {
        $updateData['activation_date'] = now();
    }
}

if ($step > $currentStep) {
    $updateData['package_step'] = $step;
}

    DB::table('members')
        ->where('id', $mlmMember->id)
        ->update($updateData);
}

/*
|--------------------------------------------------------------------------
| DISTRIBUTE BV TO UPLINES
|--------------------------------------------------------------------------
*/

if ($orderBv > 0 && $mlmMember) {

    $currentMember = $mlmMember;

    while ($currentMember && $currentMember->parent_id) {

        $parent = DB::table('members')
            ->where('id', $currentMember->parent_id)
            ->first();

        if (!$parent) {
            break;
        }

        if ($currentMember->position === 'left') {

            DB::table('members')
                ->where('id', $parent->id)
                ->increment('builtup_left_bv', $orderBv);

        } else {

            DB::table('members')
                ->where('id', $parent->id)
                ->increment('builtup_right_bv', $orderBv);
        }

        $currentMember = $parent;
    }
}
            DB::commit();

            return response()->json([

                'success' => true,

                'message' =>
                    'Order placed successfully'

            ]);


        } catch (\Exception $e) {

            DB::rollback();

            return response()->json([

                'success' => false,

                'message' =>
                    $e->getMessage()

            ], 500);

        }

    }
    
public function verifyEcomMember($id)
{

    /*
    |--------------------------------------------------------------------------
    | CHECK ECOM MEMBERS
    |--------------------------------------------------------------------------
    */

    $member = DB::table('ecom_members')

        ->where('member_id', $id)

        ->orWhere('mobile_no', $id)

        ->orWhere('email', $id)

        ->first();

    /*
    |--------------------------------------------------------------------------
    | CHECK MLM MEMBERS
    |--------------------------------------------------------------------------
    */

    if (!$member) {

        $member = DB::table('members')

            ->where('user_id', $id)

            ->orWhere('mobile_no', $id)

            ->orWhere('email', $id)

            ->first();

        // MLM FIELD MAP
        if ($member) {

            $member->member_id =
                $member->user_id;

        }

    }

    /*
    |--------------------------------------------------------------------------
    | MEMBER NOT FOUND
    |--------------------------------------------------------------------------
    */

    if (!$member) {

        return response()->json([

            'success' => false,

            'message' => 'Member not found'

        ]);

    }

    /*
    |--------------------------------------------------------------------------
    | SUCCESS RESPONSE
    |--------------------------------------------------------------------------
    */

    return response()->json([

        'success' => true,

        'member' => $member

    ]);

}

public function orderHistory(Request $request, $memberId)
{
    $member = DB::table('shoppee_members')
        ->where('member_id', $memberId)
        ->first();

    if (!$member) {

        return response()->json([
            'success' => false,
            'message' => 'Member not found'
        ]);

    }

    $query = DB::table('shoppee_branch_sale_orders')
        ->where('member_id', $member->id);

    if ($request->filled('from_date')) {

        $query->whereDate(
            'created_at',
            '>=',
            $request->from_date
        );

    }

    if ($request->filled('to_date')) {

        $query->whereDate(
            'created_at',
            '<=',
            $request->to_date
        );

    }

    $orders = $query
        ->orderByDesc('id')
        ->get();

    return response()->json([
        'success' => true,
        'data' => $orders
    ]);
}
public function orderDetails($orderId)
{
    $order = DB::table('shoppee_branch_sale_orders')
        ->where('id', $orderId)
        ->first();

    if (!$order) {
        return response()->json([
            'success' => false,
            'message' => 'Order not found'
        ]);
    }
    $member = DB::table('shoppee_members')
        ->where('id', $order->member_id)
        ->first();


    $products = DB::table(
            'shoppee_branch_sale_order_items as oi'
        )
        ->leftJoin(
            'ecom_products as p',
            'p.id',
            '=',
            'oi.product_id'
        )
        ->leftJoin(
            'ecom_product_variants as v',
            'v.id',
            '=',
            'oi.variant_id'
        )
        ->where('oi.order_id', $orderId)
        ->select(
            'p.name as product_name',
            'v.packing_size',
            'v.pv',
            'oi.quantity',
            'oi.price',
            'oi.amount'
        )
        ->get();

        
        return response()->json([

    'success' => true,

    'order' => $order,

    'products' => $products,

    'member' => [

        'fullname'    => $member->fullname,
        'branch_name' => $member->branch_name,
        'address'     => $member->address,
        'city'        => $member->city,
        'state'       => $member->state,
        'pin_code'    => $member->pin_code,
        'mobile_no'   => $member->mobile_no,
        'email'       => $member->email,
        'gst_no'      => $member->gst_no,

    ]

]);
}

}