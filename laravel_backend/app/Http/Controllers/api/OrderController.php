<?php

namespace App\Http\Controllers\Api;

use App\Models\Orderecom;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Productecom;
use App\Models\CashbackWallet;

class OrderController extends Controller
{

    public function store(Request $request)
    {
         /*
    |--------------------------------------------------------------------------
    | WEEKLY MLM CYCLE LOCK
    |--------------------------------------------------------------------------
    | Sunday 12:01 AM to 12:10 AM = LOCKED
    | 12:11 AM onwards = UNLOCKED
    |
    | Only MLM members are blocked.
    | Normal E-commerce users and guests are not affected.
    |--------------------------------------------------------------------------
    */

    if ($request->mlm_member_id) {

        $mlmMember = DB::table('members')
            ->where('user_id', $request->mlm_member_id)
            ->first();

        if ($mlmMember) {

            $now = \Carbon\Carbon::now();

            if (
                $now->dayOfWeek === \Carbon\Carbon::SUNDAY &&
                $now->format('H:i') >= '00:00' &&
                $now->format('H:i') <= '00:11'
            ) {
                return response()->json([
                    'status' => false,
                    'cycle_locked' => true,
                    'message' => 'Weekly cycle is running. MLM member purchase is temporarily unavailable. Please try again after 12:10 AM.'
                ], 503);
            }
        }
    }

    
        DB::beginTransaction();

        try {

            $memberId = $request->member_id;
            $guestId  = $request->guest_id;

            if (!$memberId && !$guestId) {
                return response()->json(['message' => 'User required'], 400);
            }

            /*
            |------------------------------------------------------------------
            | GET CART ITEMS
            |------------------------------------------------------------------
            */

            if ($memberId) {
                $cartItems = DB::table('ecom_cart_items')
                    ->where(function ($q) use ($memberId, $guestId) {
                        $q->where('member_id', $memberId);
                        if ($guestId) $q->orWhere('guest_id', $guestId);
                    })
                    ->get();
            } elseif ($guestId) {
                $cartItems = DB::table('ecom_cart_items')
                    ->where('guest_id', $guestId)
                    ->get();
            } else {
                return response()->json(['message' => 'User required'], 400);
            }

            if ($cartItems->isEmpty()) {
                return response()->json(['message' => 'Cart is empty'], 400);
            }

            /*
            |------------------------------------------------------------------
            | CALCULATE TOTAL
            |------------------------------------------------------------------
            */

            $totalAmount = 0;
$totalQty    = 0;
$totalPv     = 0;
$totalBv     = 0;
$totalCashback = 0;

            foreach ($cartItems as $item) {

                $product = Productecom::with('variants')->find($item->product_id);

                if (!$product) {
                    return response()->json(['message' => 'Product not found'], 404);
                }

              $variant = $product->variants
    ->where('id', $item->variant_id)
    ->first();

                if (!$variant) {
                    return response()->json(['message' => $product->name . ' variant not found'], 400);
                }

                if (($variant->stock ?? 0) < $item->quantity) {
                    return response()->json(['message' => $product->name . ' out of stock'], 400);
                }

               $price = ($variant->offer_price > 0)
    ? $variant->offer_price
    : $variant->price;

// Calculate cashback for this item
$cashbackPercent = (float) ($variant->cashback ?? 0);

$totalCashback += (
    ($price * $item->quantity)
    * $cashbackPercent
) / 100;

$totalAmount += $price * $item->quantity;
$totalQty    += $item->quantity;
$totalPv     += ($variant->pv ?? 0) * $item->quantity;
$totalBv += ($variant->bv ?? 0) * $item->quantity;
            }

            /*
            |------------------------------------------------------------------
            | RESOLVE WALLET BREAKDOWN
            | Supports both new multi-wallet (wallet_breakdown object)
            | and old single-wallet (wallet_type + wallet_amount) for safety
            |------------------------------------------------------------------
            */

            // New format: { "consistency": 100, "purchase": 50 }
            $walletBreakdown = $request->wallet_breakdown ?? [];

            // Fallback: old single-wallet format still works
            if (empty($walletBreakdown) && $request->wallet_type && $request->wallet_amount > 0) {
                $walletBreakdown = [$request->wallet_type => (float) $request->wallet_amount];
            }

            // Total wallet deduction across all wallets
            $totalWalletDeduction = collect($walletBreakdown)->sum();

        
/*
    |------------------------------------------------------------------
    | UPDATE ECOM MEMBER-GUEST DETAILS ONLY
    |------------------------------------------------------------------
*/

if ($memberId) {

    $ecomMember = DB::table('ecom_members')
        ->where('id', $memberId)
        ->first();

    if ($ecomMember) {

        DB::table('ecom_members')
            ->where('id', $memberId)
            ->update([

                // Only update if empty
                'fullname' => $ecomMember->fullname
                    ?: ($request->delivery_name ?? $request->guest_name),

                'mobile_no' => $ecomMember->mobile_no
                    ?: ($request->guest_phone ?? $request->mobile_no),

                'address' => $ecomMember->address
                    ?: $request->delivery_address,

                'updated_at' => now(),
            ]);
    }
}

            /*
            |------------------------------------------------------------------
            | CREATE ORDER
            |------------------------------------------------------------------
            */

            $order = Orderecom::create([
                'member_id'          => $memberId ?: null,
                'guest_id'           => $guestId ?: null,
                'mlm_member_id'      => $request->mlm_member_id,
                'order_type'         => $request->order_type,
                'delivery_address'   => $request->delivery_address,
                'delivery_name'      => $request->delivery_name,
                'delivery_member_id' => $request->delivery_member_id,
                'payment_method'     => $request->payment_method ?? 'online',
                'delivery_type'      => $request->delivery_type ?? 'courier',
                'wallet_used'        => $totalWalletDeduction > 0 ? 1 : 0,
                'wallet_type'        => $request->wallet_type ?? implode(',', array_keys((array) $walletBreakdown)),
                'wallet_amount'      => $totalWalletDeduction,
                'coupon_code'        => $request->coupon_code,
                'coupon_discount'    => $request->coupon_discount ?? 0,
                'quantity'           => $totalQty,
                'total_amount'       => $totalAmount,
                'amount_paid'        => $request->amount_paid ?? $totalAmount,
                'status'             => 'pending',
                'invoice_id'         => 'INV-' . time(),
            ]);
            
if ($request->mlm_member_id) {

    $member = DB::table('members')
        ->where('user_id', $request->mlm_member_id)
        ->first();

    if ($member) {

        DB::table('purchases')->insert([
            'member_id'       => $member->id,
            'invoice_no'      => $order->invoice_id,
            'purchase_date'   => now()->toDateString(),
            'amount'          => $totalAmount,
            'total_bv'        => $totalBv,
            'cashback_amount' => 0,
            'status'          => 'approved',
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | UPDATE COMPANY TURNOVER
        |--------------------------------------------------------------------------
        */

       $monthKey = now()->format('Y-m');
$amount = (float) $totalAmount;

DB::table('company_turnovers')->insertOrIgnore([
    'month_key' => $monthKey,
    'total_turnover' => 0,
    'member_turnover' => 0,
    'source' => 'purchases',
    'created_at' => now(),
    'updated_at' => now(),
]);

DB::table('company_turnovers')
    ->where('month_key', $monthKey)
    ->increment('total_turnover', $amount);

DB::table('company_turnovers')
    ->where('month_key', $monthKey)
    ->increment('member_turnover', $amount);

DB::table('company_turnovers')
    ->where('month_key', $monthKey)
    ->update([
        'source' => 'purchases',
        'updated_at' => now(),
    ]);
    }
}

/* 👇 ADD GUEST TURNOVER CODE HERE 👇 */

if (!$request->mlm_member_id) {

   $monthKey = now()->format('Y-m');
$amount = (float) $totalAmount;

DB::table('company_turnovers')->insertOrIgnore([
    'month_key' => $monthKey,
    'total_turnover' => 0,
    'member_turnover' => 0,
    'source' => 'purchases',
    'created_at' => now(),
    'updated_at' => now(),
]);

DB::table('company_turnovers')
    ->where('month_key', $monthKey)
    ->increment('total_turnover', $amount);

DB::table('company_turnovers')
    ->where('month_key', $monthKey)
    ->update([
        'source' => 'purchases',
        'updated_at' => now(),
    ]);
}

            /*
            |------------------------------------------------------------------
            | WALLET DEDUCTION (multi-wallet)
            |------------------------------------------------------------------
            */

            if ($totalWalletDeduction > 0 && $request->mlm_member_id && !empty($walletBreakdown)) {

                $member = DB::table('members')
                    ->where('user_id', $request->mlm_member_id)
                    ->first();

                if (!$member) {
                    DB::rollBack();
                    return response()->json(['message' => 'MLM Member not found'], 404);
                }

                foreach ($walletBreakdown as $walletType => $amount) {

                    $amount = (float) $amount;
                    if ($amount <= 0) continue;

                    // ── CONSISTENCY WALLET ──
                    if ($walletType === 'consistency') {

                        DB::table('consistency_wallet')->insert([
                            'user_id'    => $member->id,
                            'detail'     => 'Ecommerce Order #' . $order->id . ' Deduction',
                            'credit'     => 0,
                            'debit'      => $amount,
                            'created_at' => now(),
                        ]);
                    }

                    // ── REPURCHASE WALLET ──
                    elseif ($walletType === 'repurchase') {

                        $lastBalance = DB::table('repurchase_wallet_transactions')
                            ->where('user_id', $member->id)
                            ->latest('id')
                            ->value('balance_after') ?? 0;

                        $newBalance = $lastBalance - $amount;

                        DB::table('repurchase_wallet_transactions')->insert([
                            'user_id'       => $member->id,
                            'detail'        => 'Ecommerce Order #' . $order->id . ' Deduction',
                            'credit'        => 0,
                            'debit'         => $amount,
                            'balance_after' => $newBalance,
                            'created_at'    => now(),
                        ]);
                    }

                    // ── PURCHASE WALLET ──
                    elseif ($walletType === 'purchase') {

                        $currentBalance = DB::table('balance_requests')
                            ->where('member_id', $request->mlm_member_id)
                            ->where('status', 'approved')
                            ->selectRaw("COALESCE(SUM(
                                CASE
                                    WHEN entry_type = 'credit' THEN amount
                                    WHEN entry_type = 'debit'  THEN -amount
                                    ELSE 0
                                END
                            ), 0) as balance")
                            ->value('balance') ?? 0;

                        if ($currentBalance < $amount) {
                            DB::rollBack();
                            return response()->json(['message' => 'Insufficient Purchase Wallet Balance'], 400);
                        }

                        DB::table('balance_requests')->insert([
                            'member_id'       => $request->mlm_member_id,
                            'type'            => 'purchase',
                            'amount'          => $amount,
                            'mode_of_payment' => 'Purchase Wallet',
                            'transaction_no'  => 'ORDER-' . $order->id,
                            'payment_slip'    => '',
                            'status'          => 'approved',
                            'entry_type'      => 'debit',
                            'created_at'      => now(),
                            'updated_at'      => now(),
                        ]);
                    }

                   // ── CASHBACK WALLET ──
elseif ($walletType === 'cashback') {

    $currentBalance = DB::table('cashback_wallet')
        ->where('user_id', $member->id)
        ->selectRaw('COALESCE(SUM(credit - debit),0) as balance')
        ->value('balance') ?? 0;

    if ($currentBalance < $amount) {

        DB::rollBack();

        return response()->json([
            'message' => 'Insufficient Cashback Wallet Balance'
        ], 400);
    }

    DB::table('cashback_wallet')->insert([
        'user_id'    => $member->id,
        'order_id'   => $order->id,
        'detail'     => 'Ecommerce Order #' . $order->invoice_id . ' Deduction',
        'credit'     => 0,
        'debit'      => $amount,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}
                }
            }

            /*
            |------------------------------------------------------------------
            | ORDER ITEMS + STOCK DEDUCTION
            |------------------------------------------------------------------
            */

            foreach ($cartItems as $item) {

                $product = Productecom::with('variants')->find($item->product_id);
               $variant = $product->variants
    ->where('id', $item->variant_id)
    ->first();
                $price   = ($variant->offer_price > 0) ? $variant->offer_price : $variant->price;

               DB::table('order_items')->insert([
    'order_id'   => $order->id,
    'product_id' => $product->id,
    'variant_id' => $item->variant_id,
    'price'      => $price,
    'quantity'   => $item->quantity,
    'created_at' => now(),
]);

             if ($request->delivery_type !== 'pickup') {
    $variant->decrement('stock', $item->quantity);
}
            }
            
           // ========================================================
// CREDIT CASHBACK FOR SELF ORDER
// (Only if Cashback Wallet is NOT used)
// ========================================================

if (
    $request->mlm_member_id &&
    empty($request->delivery_member_id) &&
    $totalCashback > 0 &&
    empty($walletBreakdown['cashback'])
) {

    $member = DB::table('members')
        ->where('user_id', $request->mlm_member_id)
        ->first();

    if ($member) {

        CashbackWallet::create([

            'user_id'  => $member->id,

            'order_id' => $order->id,

            'detail'   => 'Cashback for Order #' . $order->invoice_id,

            'credit'   => round($totalCashback, 2),

            'debit'    => 0,

        ]);
    }
}


            /*
            |------------------------------------------------------------------
            | AUTO MLM ACTIVATION BASED ON BV
            |------------------------------------------------------------------
            */

         if (
    $request->mlm_member_id &&
    !str_starts_with($request->mlm_member_id, 'GUEST')
) {

                $member = DB::table('members')
                    ->where('user_id', $request->mlm_member_id)
                    ->first();

                if ($member) {

                 $currentStep = (int) ($member->package_step ?? 0);

$currentSelfBv = (float) ($member->self_bv ?? 0);
$totalMemberBv = $currentSelfBv + $totalBv;

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

    if (!$member->activation_date) {
        $updateData['activation_date'] = now();
    }
}

                    if ($step > $currentStep) {
                        $updateData['package_step'] = $step;
                    }

                    DB::table('members')->where('id', $member->id)->update($updateData);
                }
            }


          /*
|------------------------------------------------------------------
| DISTRIBUTE BV TO UPLINES
|------------------------------------------------------------------
*/

if ($request->mlm_member_id && $totalBv > 0) {

    $currentMember = DB::table('members')
        ->where('user_id', $request->mlm_member_id)
        ->first();

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
                ->increment('builtup_left_bv', $totalBv);

        } else {

            DB::table('members')
                ->where('id', $parent->id)
                ->increment('builtup_right_bv', $totalBv);
        }

        $currentMember = $parent;
    }
}

            /*
            |------------------------------------------------------------------
            | CLEAR CART
            |------------------------------------------------------------------
            */

            if ($memberId) {
                DB::table('ecom_cart_items')
                    ->where(function ($q) use ($memberId, $guestId) {
                        $q->where('member_id', $memberId);
                        if ($guestId) $q->orWhere('guest_id', $guestId);
                    })
                    ->delete();
            } elseif ($guestId) {
                DB::table('ecom_cart_items')->where('guest_id', $guestId)->delete();
            }

            DB::commit();

            return response()->json([
                'message' => 'Order placed successfully',
                'order'   => $order,
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'Order failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /* ─────────────────────────────────────────────
       ADMIN ORDERS LIST
    ───────────────────────────────────────────── */
    public function index()
    {
       $orders = Orderecom::with([
    'ecomMember',
    'mlmMember',
    'items.product',
    'items.variant'
])
            ->latest()
            ->get()
            ->map(function ($order) {
                return [
                    'id'             => $order->id,
                    'invoice_id'     => $order->invoice_id,
                    'total_amount'   => $order->total_amount,
                    'payment_method' => $order->payment_method,
                    'status'         => $order->status,
                    'delivery_type'  => $order->delivery_type ?? 'courier',
                   'member_name' =>
    $order->mlmMember->fullname
    ?? $order->ecomMember->fullname
    ?? $order->delivery_name
    ?? 'Guest',
                    'products'       => $order->items->map(function ($item) {
                        return [
                            'name' => $item->product->name ?? '',
                              'packing_size' => $item->variant->packing_size ?? '',
                            'qty'  => $item->quantity,
                        ];
                    }),
                ];
            });

        return response()->json($orders);
    }

    /* ─────────────────────────────────────────────
       USER ORDERS
    ───────────────────────────────────────────── */
    public function userOrders($member_id)
    {
        $orders = Orderecom::with([
    'ecomMember',
    'mlmMember',
    'items.product',
    'items.variant'
])
            ->where('member_id', $member_id)
            ->latest()
            ->get()
            ->map(function ($order) {
                return [
                    'id'             => $order->id,
                    'invoice_id'     => $order->invoice_id,
                    'quantity'       => $order->quantity,
                    'total_amount'   => $order->total_amount,
                    'payment_method' => $order->payment_method,
                    'status'         => $order->status,
                    'delivery_type'  => $order->delivery_type ?? 'courier',
                  'member_name' =>
    $order->mlmMember->fullname
    ?? $order->ecomMember->fullname
    ?? $order->delivery_name
    ?? 'Guest',
                 'address' =>
    $order->mlmMember->address
    ?? $order->ecomMember->address
    ?? $order->delivery_address
    ?? '',
                    'mobile' =>
    $order->mlmMember->mobile_no
    ?? $order->ecomMember->mobile_no
    ?? '',
                    'created_at'     => $order->created_at,
                    'dispatched_at'  => $order->dispatched_at,
                    'delivered_at'   => $order->delivered_at,
                'product_name' =>
    ($order->items->first()->product->name ?? '') .
    ' - ' .
    ($order->items->first()->variant->packing_size ?? ''),
                   'image' => !empty($order->items->first()?->product?->image)
    ? (
        collect(
            is_array($order->items->first()->product->image)
                ? $order->items->first()->product->image
                : json_decode($order->items->first()->product->image, true)
        )
        ->map(function ($img) {
            return str_starts_with($img, 'http')
                ? $img
                : asset('storage/' . ltrim($img, '/'));
        })
        ->first()
    )
    : asset('default-product.png'),
                    'products'       => $order->items->map(function ($item) {
                        return [
                            'name'  => $item->product->name ?? '',
                             'packing_size' => $item->variant->packing_size ?? '',
                            'qty'   => $item->quantity,
                            'price' => $item->price,
                        ];
                    }),
                ];
            });

        return response()->json($orders);
    }

    /* ─────────────────────────────────────────────
       LATEST ORDER
    ───────────────────────────────────────────── */
    public function latestOrder($member_id)
    {
        $order = Orderecom::with(['items.product', 'items.variant'])
            ->where('member_id', $member_id)
            ->latest()
            ->first();

        return response()->json($order);
    }
}