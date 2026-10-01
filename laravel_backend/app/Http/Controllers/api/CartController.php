<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cartitemecom;
use App\Models\Member;
use App\Models\Productecom as Product;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    // ✅ ADD TO CART
    public function addToCart(Request $request)
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

        if ($request->member_id) {

            $mlmMember = Member::where('id', $request->member_id)->first();

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
                        'message' => 'Weekly cycle is running. MLM member shopping is temporarily unavailable. Please try again after 12:10 AM.'
                    ], 503);
                }
            }
        }

        try {
           $request->validate([
    'member_id'  => 'nullable',
    'guest_id'   => 'nullable',
    'product_id' => 'required',
    'variant_id' => 'required',
    'quantity'   => 'required|integer|min:1',
]);
      \Log::info('ADD TO CART', $request->all());

            $memberId = $request->member_id;
            $guestId  = $request->guest_id;
            
            // ✅ BLOCK COMING SOON PRODUCTS

$product = Product::with('variants')
    ->find($request->product_id);

if (!$product) {

    return response()->json([
        'message' => 'Product not found'
    ], 404);
}

$variant = collect($product->variants)
    ->where('id', $request->variant_id)
    ->first();

if ($variant && $variant->product_status === 'coming_soon') {
    return response()->json([
        'message' => 'This product is coming soon'
    ], 403);
}

            // ✅ FIX: must have at least one
            if (!$memberId && !$guestId) {
                return response()->json(['error' => 'member_id or guest_id required'], 400);
            }

            // ✅ FIX: check existing properly
            if ($memberId) {

    $existing = Cartitemecom::where('member_id', $memberId)
        ->where('product_id', $request->product_id)
        ->where('variant_id', $request->variant_id)
        ->first();

} else {

    $existing = Cartitemecom::where('guest_id', $guestId)
        ->where('product_id', $request->product_id)
        ->where('variant_id', $request->variant_id)
        ->first();
}

            if ($existing) {
                $existing->quantity += $request->quantity;
                $existing->save();
            } else {
              Cartitemecom::create([
    'member_id'  => $memberId ?: null,
    'guest_id'   => $guestId ?: null,
    'product_id' => $request->product_id,
    'variant_id' => $request->variant_id,
    'quantity'   => $request->quantity,
]);
            }

            return response()->json(['message' => 'Added to cart successfully']);
        } catch (\Exception $e) {

    return response()->json([

        'error' => true,

        'message' => $e->getMessage(),

        'line' => $e->getLine(),

        'file' => $e->getFile(),

    ], 500);
}
    }

    // ✅ GET CART
    public function getCart(Request $request)
    {
        $memberId = $request->member_id;
        $guestId  = $request->guest_id;

        if (!$memberId && !$guestId) {
            return response()->json([]);
        }

        if ($memberId) {

    $cartItems = Cartitemecom::with([
            'product.orderTypes',
            'product.variants'
        ])
        ->where('member_id', $memberId)
        ->get();

} else {

    $cartItems = Cartitemecom::with([
            'product.orderTypes',
            'product.variants'
        ])
        ->where('guest_id', $guestId)
        ->get();
}

        $result = $cartItems->map(function ($item) {
            if (!$item->product) return null;

            $variant = $item->product->variants
    ->where('id', $item->variant_id)
    ->first();

return [
    'id'       => $item->id,
    'quantity' => $item->quantity,
    'product'  => [
        'id'            => $item->product->id,
        'name'          => $item->product->name,
        'packing_size'  => $variant?->packing_size ?? '',
        'price'         => ($variant && $variant->offer_price > 0)
            ? $variant->offer_price
            : ($variant?->price ?? 0),
        'mrp'           => $variant?->price ?? 0,
        'offer_price'   => $variant?->offer_price ?? 0,
        'brand'         => $item->product->brand ?? '',
        'image'         => $item->product->image,
        'order_types'   => $item->product->orderTypes->pluck('name')->toArray(),
    ],
];
        })->filter()->values();

        return response()->json($result);
    }

    
    public function updateQuantity(Request $request, $id)
{
        // WEEKLY MLM CYCLE LOCK
    if ($request->member_id) {

        $mlmMember = Member::where('id', $request->member_id)->first();

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
                    'message' => 'Weekly cycle is running. MLM member cart update is temporarily unavailable. Please try again after 12:10 AM.'
                ], 503);
            }
        }
    }

    $memberId = $request->member_id;
    $guestId  = $request->guest_id;

    if ($memberId) {

        $item = Cartitemecom::where('id', $id)
            ->where('member_id', $memberId)
            ->first();

    } else {

        $item = Cartitemecom::where('id', $id)
            ->where('guest_id', $guestId)
            ->first();
    }

    if (!$item) {
        return response()->json([
            'message' => 'Item not found'
        ], 404);
    }

    $item->quantity = $request->quantity;
    $item->save();

    return response()->json([
        'message' => 'Quantity updated successfully'
    ]);
}

   
    public function remove($id)
    {
            // WEEKLY MLM CYCLE LOCK
    $memberId = request()->member_id;

    if ($memberId) {

        $mlmMember = Member::where('id', $memberId)->first();

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
                    'message' => 'Weekly cycle is running. MLM member cart changes are temporarily unavailable. Please try again after 12:10 AM.'
                ], 503);
            }
        }
    }

        $item = Cartitemecom::find($id);

        if (!$item) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        $item->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }

    // ✅ CLEAR CART
    public function clearCart(Request $request)
    {

        // WEEKLY MLM CYCLE LOCK
    if ($request->member_id) {

        $mlmMember = Member::where('id', $request->member_id)->first();

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
                    'message' => 'Weekly cycle is running. MLM member cart changes are temporarily unavailable. Please try again after 12:10 AM.'
                ], 503);
            }
        }
    }

        $memberId = $request->member_id;
        $guestId  = $request->guest_id;

        if (!$memberId && !$guestId) {
            return response()->json([
                'status' => false,
                'message' => 'member_id or guest_id required'
            ], 400);
        }

        if ($memberId) {

    Cartitemecom::where('member_id', $memberId)->delete();

} else {

    Cartitemecom::where('guest_id', $guestId)->delete();
}

        return response()->json([
            'status' => true,
            'message' => 'Cart cleared successfully'
        ]);
    }

    // ✅ CART COUNT
    public function cartCount($member_id)
    {
        $count = Cartitemecom::where('member_id', $member_id)->sum('quantity');

        return response()->json(['count' => (int) $count]);
    }

    // ✅ MERGE CART (GUEST → MEMBER)
    public function mergeCart(Request $request)
    {
            // WEEKLY MLM CYCLE LOCK
    if ($request->member_id) {

        $mlmMember = Member::where('id', $request->member_id)->first();

        if ($mlmMember) {

            $now = \Carbon\Carbon::now();

            if (
                $now->dayOfWeek === \Carbon\Carbon::SUNDAY &&
                $now->format('H:i') >= '00:01' &&
                $now->format('H:i') <= '00:10'
            ) {
                return response()->json([
                    'status' => false,
                    'cycle_locked' => true,
                    'message' => 'Weekly cycle is running. MLM member cart merge is temporarily unavailable. Please try again after 12:10 AM.'
                ], 503);
            }
        }
    }
    
        if (!$request->guest_id || !$request->member_id) {
            return response()->json(['message' => 'guest_id and member_id required'], 422);
        }

        $guestItems = Cartitemecom::where('guest_id', $request->guest_id)->get();

        foreach ($guestItems as $guestItem) {
       $existing = Cartitemecom::where('member_id', $request->member_id)
    ->where('product_id', $guestItem->product_id)
    ->where('variant_id', $guestItem->variant_id)
    ->first();

            if ($existing) {
                $existing->quantity += $guestItem->quantity;
                $existing->save();
                $guestItem->delete();
            } else {
                $guestItem->member_id = $request->member_id;
                $guestItem->guest_id  = null;
                $guestItem->save();
            }
        }

        return response()->json(['message' => 'Cart merged']);
    }

    // ✅ WALLET CHECK
public function getWalletByUserId($user_id, Request $request)
{
    $member = Member::where('user_id', $user_id)->first();

    if (!$member) {

        return response()->json([
            'is_mlm'  => false,
            'message' => 'Invalid Member ID',
        ]);
    }

    $repurchase = DB::table('repurchase_wallet_transactions')
        ->where('user_id', $member->id)
        ->orderByDesc('id')
        ->value('balance_after') ?? 0;

    $consistency = DB::table('consistency_wallet')
        ->where('user_id', $member->id)
        ->selectRaw('SUM(credit - debit) as balance')
        ->value('balance') ?? 0;

    $purchaseWallet = DB::table('balance_requests')
        ->where('member_id', $member->user_id)
        ->where('status', 'approved')
        ->selectRaw("
            COALESCE(SUM(
                CASE
                    WHEN entry_type = 'credit' THEN amount
                    WHEN entry_type = 'debit' THEN -amount
                    ELSE 0
                END
            ),0) as balance
        ")
        ->value('balance') ?? 0;
        
        $cashbackWallet = DB::table('cashback_wallet')
    ->where('user_id', $member->id)
    ->selectRaw('COALESCE(SUM(credit - debit),0) as balance')
    ->value('balance') ?? 0;
    

    return response()->json([

    'is_mlm'             => true,

    'member_id'          => $member->user_id,

    'repurchase_wallet'  => round($repurchase, 2),

    'consistency_wallet' => round($consistency, 2),

    'purchase_wallet'    => round($purchaseWallet, 2),

    'cashback_wallet'    => round($cashbackWallet, 2),
]);

}
}