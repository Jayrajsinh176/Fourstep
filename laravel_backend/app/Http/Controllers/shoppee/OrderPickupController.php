<?php

namespace App\Http\Controllers\shoppee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Orderecom;
use App\Models\Shoppee_Order;
use App\Models\Shoppee_Member;

class OrderPickupController extends Controller
{
    // ====================================
    // FETCH PICKUP ORDERS
    // ====================================
    public function getPickupOrders($userId)
    {
        $userId = trim($userId);

        // ====================================
        // MLM MEMBER CHECK
        // ====================================
        $mlmMember = DB::table('members')
            ->where('user_id', $userId)
            ->first();

        // MLM MEMBER FOUND
        if ($mlmMember) {

            $orders = Orderecom::with([
                    'items.product',
                    'items.variant'
                ])
                ->where('mlm_member_id', $userId)
                ->where('delivery_type', 'pickup')
                ->whereIn('status', [
                    'pending',
                    'processing',
                    'dispatched'
                ])
                ->latest()
                ->get();

            return response()->json([
                'success' => true,
                'orders' => $orders
            ]);
        }

        // ====================================
        // NORMAL / GUEST USER CHECK
        // ====================================
        $member = DB::table('ecom_members')
            ->where('member_id', $userId)
            ->first();

        // USER FOUND
        if ($member) {

            $orders = Orderecom::with([
                    'items.product',
                    'items.variant'
                ])
                ->where('member_id', $member->id)
                ->where('delivery_type', 'pickup')
                ->whereIn('status', [
                    'pending',
                    'processing',
                    'dispatched'
                ])
                ->latest()
                ->get();

            return response()->json([
                'success' => true,
                'orders' => $orders
            ]);
        }

        // ====================================
        // INVALID USER
        // ====================================
        return response()->json([
            'success' => false,
            'message' => 'Invalid User ID'
        ], 404);
    }

// ====================================
// DISPATCH ORDER
// ====================================
public function dispatchOrder(Request $request, $orderId)
{
    $order = Orderecom::with([
        'items.product',
        'items.variant'
    ])->findOrFail($orderId);


    // ====================================
    // GET SHOPPEE MEMBER
    // ====================================
    $shoppeeMember = Shoppee_Member::find(
        $request->shoppee_member_id
    );


    // ====================================
    // MEMBER NOT FOUND
    // ====================================
    if (!$shoppeeMember) {

        return response()->json([

            'success' => false,

            'message' => 'Shoppee member not found'
        ]);
    }


    // ====================================
    // STOCK CHECK
    // ====================================
    foreach ($order->items as $item) {

        // ====================================
        // GET VARIANT ID
        // ====================================
        // $variantId =
        //     $item->variant_id
        //     ?: optional($item->variant)->id;


        // ====================================
        // VARIANT NOT FOUND
        // ====================================
        // if (!$variantId) {

        //     return response()->json([

        //         'success' => false,

        //         'message' => 'Variant not found'
        //     ]);
        // }


        // ====================================
        // GET STOCK
        // ====================================
     $stock = DB::table('shoppee_member_stocks')

    ->join(
        'ecom_product_variants',
        'shoppee_member_stocks.variant_id',
        '=',
        'ecom_product_variants.id'
    )

    ->where(
        'shoppee_member_stocks.member_id',
        $shoppeeMember->id
    )

    ->where(
        'ecom_product_variants.id',
        $item->variant_id
    )

    ->sum(
        'shoppee_member_stocks.quantity'
    );


$availableStock = $stock ?? 0;


        // ====================================
        // LOW STOCK
        // ====================================
        if (
            $availableStock <
            $item->quantity
        ) {

            return response()->json([

                'success' => false,

                'message' =>
                    (
                        optional($item->product)
                            ->productname
                        ?? optional($item->product)
                            ->name
                        ?? 'Product'
                    ) . ' stock is low'
            ]);
        }
    }


    // ====================================
    // GENERATE OTP
    // ====================================
    $otp = rand(100000, 999999);


    // ====================================
    // UPDATE ORDER
    // ====================================
    $order->dispatch_otp = $otp;

    $order->status = 'dispatched';

    $order->dispatched_at = now();

    $order->save();


    // ====================================
    // SUCCESS RESPONSE
    // ====================================
    return response()->json([

        'success' => true,

        'message' =>
            'OTP Generated Successfully : ' . $otp,

        'otp' => $otp
    ]);
}

    // ====================================
// VERIFY OTP
// ====================================
public function verifyOtp(Request $request)
{
    $order = Orderecom::with('items.product')
        ->findOrFail($request->order_id);

    // ====================================
    // VERIFY OTP
    // ====================================
    if ($order->dispatch_otp != $request->otp) {

        return response()->json([
            'success' => false,
            'message' => 'Invalid OTP'
        ]);
    }

    DB::beginTransaction();

    try {

        // ====================================
        // REDUCE SHOPPEE STOCK
        // ====================================
        foreach ($order->items as $item) {

          DB::table('shoppee_member_stocks')

    ->join(
        'ecom_product_variants',
        'shoppee_member_stocks.variant_id',
        '=',
        'ecom_product_variants.id'
    )

    ->where(
        'shoppee_member_stocks.member_id',
        $request->shoppee_member_id
    )

    ->where(
        'ecom_product_variants.id',
        $item->variant_id
    )

    ->decrement(
        'shoppee_member_stocks.quantity',
        $item->quantity
    );
        }


        // ====================================
        // INSERT USED STOCK
        // ====================================
        foreach ($order->items as $item) {

          DB::table('shoppee_order_items')->insert([

    'member_id' => $request->shoppee_member_id,

    'order_id' => $order->id,

    'product_id' => $item->product_id,

    'variant_id' => $item->variant_id,

    'quantity' => $item->quantity,

    'created_at' => now(),

    'updated_at' => now(),
]);
        }


        // ====================================
        // CREDIT PURCHASE BALANCE
        // ====================================
        DB::table('shoppee_transactions')->insert([

            'member_id' =>
                $request->shoppee_member_id,

            'ref_id' =>
                $order->id,

            'ref_type' =>
                'pickup_order',

            'balance_type' =>
                'purchase',

            'entry_type' =>
                'credit',

            'amount' =>
                $order->total_amount,

            'detail' =>
                'Pickup Order Delivered #'
                . $order->invoice_id,

            'created_at' => now(),

            'updated_at' => now(),
        ]);


        // ====================================
        // UPDATE ORDER STATUS
        // ====================================
        $order->status = 'delivered';

        $order->delivered_at = now();

        $order->save();


        // ====================================
        // STORE ORDER HISTORY
        // ====================================
        Shoppee_Order::create([

            'order_no' =>
                'PK-' . time(),

            'pickup_order_id' =>
                $order->id,

            'user_id' =>
                $order->mlm_member_id
                ?: $order->guest_id
                ?: $order->member_id,

            'customer_name' =>
                $order->delivery_name
                ?: 'Customer',

            'order_date' =>
                now()->format('Y-m-d'),

            'order_type' =>
                'Pickup',

            'status' =>
                $order->status,

            'total_amount' =>
                $order->total_amount,
        ]);


        DB::commit();

        return response()->json([

            'success' => true,

            'message' =>
                'Order Delivered Successfully'
        ]);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([

            'success' => false,

            'message' => $e->getMessage()
        ]);
    }
}
}