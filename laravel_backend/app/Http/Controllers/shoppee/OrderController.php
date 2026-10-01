<?php

namespace App\Http\Controllers\Shoppee;

use App\Http\Controllers\Controller;
use App\Models\Shoppee_Order;
use Illuminate\Support\Facades\DB;
class OrderController extends Controller
{

    // ORDER HISTORY
public function history($userId)
    {

  $orderIds = \DB::table('shoppee_order_items')
    ->where('member_id', $userId)
    ->pluck('order_id');

$orders = Shoppee_Order::with([
    'pickupOrder.items.product'
])
->whereIn('pickup_order_id', $orderIds)
->latest()
->get();
        $data = $orders->map(function ($order) {

            $totalQuantity = 0;

            foreach (
                $order->pickupOrder->items
                as $item
            ) {

                $totalQuantity +=
                    $item->quantity;
            }

            return [

                'id' => $order->id,

                'order_no' =>
                    $order->pickupOrder
                        ->invoice_id,

                'order_date' =>
                    $order->pickupOrder
                        ->created_at
                        ->format('Y-m-d'),

                'order_type' =>
                    $order->order_type,

                'status' =>
                    $order->status,

                'customer_name' =>
                    $order->customer_name,

                'user_id' =>
                    $order->user_id,

                'total_products' =>
                    $order->pickupOrder
                        ->items
                        ->count(),

                'total_quantity' =>
                    $totalQuantity,

                'total_amount' =>
                    $order->total_amount,
            ];
        });

        return response()->json([

            'status' => true,

            'data' => $data

        ]);
    }


    // ORDER DETAILS
    public function show($id)
    {

        $order = Shoppee_Order::with([
            'pickupOrder.items.product'
        ])->find($id);

        if (!$order) {

            return response()->json([

                'message' =>
                    'Order not found'

            ], 404);
        }
$shoppeeOrderItem = DB::table('shoppee_order_items')
    ->where('order_id', $order->pickup_order_id)
    ->first();

$shoppeeMember = null;

if ($shoppeeOrderItem) {

    $shoppeeMember = DB::table('shoppee_members')
        ->where('id', $shoppeeOrderItem->member_id)
        ->first();
}
        $products = [];
        $totalQuantity = 0;
$totalPv = 0;

foreach (
    $order->pickupOrder->items
    as $item
) {

    $variant = DB::table('ecom_product_variants')
        ->where('id', $item->variant_id)
        ->first();
$pv = $item->product->pv ?? 0;

$totalQuantity += $item->quantity;

$totalPv += ($pv * $item->quantity);

    $products[] = [

        'product_id' =>
            $item->product_id,

        'product_name' =>
            $item->product->name
            ?? 'N/A',

        'package_size' =>
    $variant->packing_size
    ?? '-',

        'quantity' =>
            $item->quantity,

        'mrp' =>
            $item->price ?? 0,

       'pv' => $pv,

        'status' =>
            $order->status,

        'amount' =>
            $item->price *
            $item->quantity,
    ];
}

        return response()->json([

            'id' => $order->id,

            'order_no' =>
                $order->pickupOrder
                    ->invoice_id,

            'order_date' =>
                $order->pickupOrder
                    ->created_at
                    ->format('Y-m-d'),

            'order_type' =>
                $order->order_type,

            'status' =>
                $order->status,

            'customer_name' =>
                $order->customer_name,

            'user_id' =>
                $order->user_id,

            'total_amount' =>
                $order->total_amount,
'total_products' =>
    count($products),

'total_quantity' =>
    $totalQuantity,

'total_pv' =>
    $totalPv,
    
    'shoppee_name' =>
    $shoppeeMember->fullname ?? '',

'shoppee_mobile' =>
    $shoppeeMember->mobile_no ?? '',

'shoppee_email' =>
    $shoppeeMember->email ?? '',

'shoppee_address' =>
    $shoppeeMember->address ?? '',

'shoppee_city' =>
    $shoppeeMember->city ?? '',

'shoppee_state' =>
    $shoppeeMember->state ?? '',

'shoppee_pincode' =>
    $shoppeeMember->pin_code ?? '',
            'products' =>
                $products

        ]);
    }
}