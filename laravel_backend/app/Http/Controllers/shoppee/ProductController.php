<?php

namespace App\Http\Controllers\Shoppee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Productecom;
use App\Models\ProductVariantecom;
use App\Models\Categoryecom;

use App\Models\Shoppee_ProductRequest;
use App\Models\Shoppee_Transaction;
use App\Models\Shoppee_MemberStock;
use Illuminate\Support\Facades\Hash;
use App\Models\Shoppee_Member;

class ProductController extends Controller
{

    // GET PRODUCTS
    public function index(Request $request)
    {

        $query = ProductVariantecom::with([
            'product.category'
        ]);

        // CATEGORY FILTER
        if ($request->category) {

            $query->whereHas(
                'product.category',
                function ($q) use ($request) {

                    $q->where(
                        'name',
                        $request->category
                    );
                }
            );
        }

$variants = $query
    ->whereHas('product')
    ->get();

        $data = $variants->map(function ($variant) {

            return [

               'id' => $variant->product?->id,

                'variant_id' => $variant->id,

               'productname' => $variant->product?->name,

                'category' => $variant->product?->category?->name,

                'packing_size' => $variant->packing_size,

                'mrp' => $variant->price,

                'offerprice' => $variant->offer_price,

                'pv' => $variant->pv,

                'minimum_quantity' =>
                    $variant->minimum_quantity,

                'stock' => $variant->stock,

                'product_status' =>
                    $variant->product_status,
            ];
        });

        return response()->json($data);
    }

    // GET CATEGORIES
    public function categories()
    {

        return Categoryecom::select(
            'id',
            'name as category',
            'image'
        )->get();
    }

    // SUBMIT PRODUCT REQUEST
    public function submitRequest(Request $request)
    {
$member = Shoppee_Member::find(
    $request->member_id
);

if (!$member) {

    return response()->json([
        'message' => 'Member not found'
    ], 404);
}

if (
    empty($member->transaction_password)
) {

    return response()->json([
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
        'message' =>
        'Invalid Transaction Password'
    ], 400);
}
        $products = $request->products;

        $totalAmount = 0;

        $totalPV = 0;
        $totalBV = 0;

        foreach ($products as $item) {

            $variant = ProductVariantecom::find(
                $item['variant_id']
            );

            if (!$variant) {

                return response()->json([
                    'message' => 'Variant not found'
                ], 404);
            }

            // STOCK CHECK
            if ($item['quantity'] > $variant->stock) {

                return response()->json([
                    'message' =>
                        $variant->product->name .
                        ' stock is low'
                ], 400);
            }

            $totalAmount +=
                $variant->offer_price *
                $item['quantity'];

            $totalPV +=
                $variant->pv *
                $item['quantity'];
                
                $totalBV +=
    $variant->bv *
    $item['quantity'];
        }

        // CHECK MEMBER BALANCE
        $totalCredit = Shoppee_Transaction::where(
    'member_id',
    $request->member_id
)
->where('balance_type', 'purchase')
->where('entry_type', 'credit')
->sum('amount');

$totalDebit = Shoppee_Transaction::where(
    'member_id',
    $request->member_id
)
->where('balance_type', 'purchase')
->where('entry_type', 'debit')
->sum('amount');

        $currentBalance =
            $totalCredit - $totalDebit;

        if ($totalAmount > $currentBalance) {

            return response()->json([
                'message' =>
                    'Your balance is low. Please add money to place order.'
            ], 400);
        }

        // CREATE REQUEST
        $productRequest =
            Shoppee_ProductRequest::create([

                'member_id' =>
                    $request->member_id,

                'total_products' =>
                    count($products),

                'total_amount' =>
                    $totalAmount,

                'total_pv' =>
                    $totalPV,
                    
                    'total_bv' =>
    $totalBV,

                'status' => 'Pending'
            ]);

        // STORE ITEMS
        foreach ($products as $item) {

            $variant = ProductVariantecom::find(
                $item['variant_id']
            );

            DB::table(
                'shoppee_product_request_items'
            )->insert([

                'request_id' =>
                    $productRequest->id,

                'product_id' =>
                    $variant->product_id,

                'variant_id' =>
                    $variant->id,

                'quantity' =>
                    $item['quantity'],

                'amount' =>
                    $variant->offer_price *
                    $item['quantity'],

                'pv' =>
                    $variant->pv *
                    $item['quantity'],
                    
                    'bv' =>
    $variant->bv *
    $item['quantity'],

                'created_at' => now(),

                'updated_at' => now(),
            ]);
        }

        return response()->json([
            'message' =>
                'Product request submitted successfully'
        ]);
    }

    // GET REQUESTS
    public function getRequests(Request $request)
    {

        $query =
            Shoppee_ProductRequest::query();

        if ($request->member_id) {

            $query->where(
                'member_id',
                $request->member_id
            );
        }

        if (
            $request->status &&
            $request->status !== 'all'
        ) {

            $query->where(
                'status',
                $request->status
            );
        }

        $requests =
            $query->latest()->get();

        $data = [];

        foreach ($requests as $req) {

            $items = DB::table(
                'shoppee_product_request_items'
            )
            ->join(
                'ecom_products',
                'shoppee_product_request_items.product_id',
                '=',
                'ecom_products.id'
            )
            ->join(
                'ecom_product_variants',
                'shoppee_product_request_items.variant_id',
                '=',
                'ecom_product_variants.id'
            )
            ->where(
                'shoppee_product_request_items.request_id',
                $req->id
            )
            ->select(
                'ecom_products.name as productname',
                'ecom_product_variants.packing_size',
                'shoppee_product_request_items.quantity'
            )
            ->get();

            $data[] = [

                'id' => $req->id,

                'date' => $req->created_at,

                'total_products' =>
                    $req->total_products,

                'total_amount' =>
                    $req->total_amount,

                'total_pv' =>
                    $req->total_pv,
                    
                    'total_bv' =>
    $req->total_bv,

                'status' =>
                    $req->status,

                'products' => $items,
            ];
        }

        return response()->json($data);
    }

    // STOCK REPORT
    public function stockReport(Request $request)
    {
$variants = ProductVariantecom::with([
    'product.category'
])
->whereHas('product')
->where('product_status', 'order_now')
->get();

        $data = [];

        foreach ($variants as $variant) {

            // RECEIVED
// RECEIVED
$received = DB::table(
    'shoppee_member_stocks'
)
->where(
    'member_id',
    $request->member_id
)
->where(
    'variant_id',
    $variant->id
)
->sum('quantity');

// NORMAL ORDER USED
$normalUsed = DB::table(
    'shoppee_order_items'
)
->where(
    'product_id',
    $variant->product_id
)
->where(
    'member_id',
    $request->member_id
)
->sum('quantity');

// BRANCH SALE USED
$branchSaleUsed = DB::table(
    'shoppee_branch_sale_order_items'
)
->join(
    'shoppee_branch_sale_orders',
    'shoppee_branch_sale_order_items.order_id',
    '=',
    'shoppee_branch_sale_orders.id'
)
->where(
    'shoppee_branch_sale_order_items.variant_id',
    $variant->id
)
->where(
    'shoppee_branch_sale_orders.member_id',
    $request->member_id
)
->sum(
    'shoppee_branch_sale_order_items.quantity'
);

// TOTAL USED
$used = $normalUsed + $branchSaleUsed;

            $balance =
                $received - $used;

            $data[] = [

                "id" =>
                    $variant->id,

                "product" =>
    ($variant->product?->name ?? 'Unknown Product')
    . ' (' .
    $variant->packing_size .
    ')',
"category" =>
    $variant->product?->category?->name ?? '',

                "mrp" =>
                    $variant->price,

                "offerPrice" =>
                    $variant->offer_price,

                "pv" =>
                    $variant->pv,
"bv" =>
    $variant->bv ?? 0,

                "productReceived" =>
                    $received,

                "productUsed" =>
                    $used,

                "productBalance" =>
                    $balance,

                "balanceAmount" =>
                    $balance *
                    $variant->offer_price
            ];
        }

        return response()->json($data);
    }

    // APPROVE REQUEST
    public function approveRequest($id)
    {

        $productRequest =
            Shoppee_ProductRequest::find($id);

        if (!$productRequest) {

            return response()->json([
                "message" =>
                    "Product request not found"
            ], 404);
        }

        if (
            $productRequest->status === 'Approved'
        ) {

            return response()->json([
                "message" =>
                    "Product request already approved"
            ], 400);
        }

        // CHECK BALANCE
    $totalCredit =
    Shoppee_Transaction::where(
        'member_id',
        $productRequest->member_id
    )
    ->where(
        'balance_type',
        'purchase'
    )
    ->where(
        'entry_type',
        'credit'
    )
    ->sum('amount');

$totalDebit =
    Shoppee_Transaction::where(
        'member_id',
        $productRequest->member_id
    )
    ->where(
        'balance_type',
        'purchase'
    )
    ->where(
        'entry_type',
        'debit'
    )
    ->sum('amount');

        $currentBalance =
            $totalCredit - $totalDebit;

        if (
            $productRequest->total_amount >
            $currentBalance
        ) {

            return response()->json([
                'message' =>
                    'Insufficient Purchase Balance'
            ], 400);
        }

        // APPROVE
        $productRequest->status =
            'Approved';

        $productRequest->save();

        // GET ITEMS
        $items = DB::table(
            'shoppee_product_request_items'
        )
        ->where(
            'request_id',
            $productRequest->id
        )
        ->get();

      // REDUCE MAIN STOCK
foreach ($items as $item) {
    
    // SKIP OLD NULL VARIANTS
    if (!$item->variant_id) {
        continue;
    }

    // REDUCE MAIN STOCK
    ProductVariantecom::where(
        'id',
        $item->variant_id
    )->decrement(
        'stock',
        $item->quantity
    );

    // MEMBER STOCK
   $memberStock =
    Shoppee_MemberStock::where(
        'member_id',
        $productRequest->member_id
    )
    ->where(
        'variant_id',
        $item->variant_id
    )
    ->first();

if ($memberStock) {

    $memberStock->increment(
        'quantity',
        $item->quantity
    );

} else {

    Shoppee_MemberStock::create([

        'member_id' =>
            $productRequest->member_id,

        'variant_id' =>
            $item->variant_id,

        'quantity' =>
            $item->quantity,
    ]);
}
}

        // PRODUCT NAMES
        $productNames = DB::table(
            'shoppee_product_request_items'
        )
        ->join(
            'ecom_products',
            'shoppee_product_request_items.product_id',
            '=',
            'ecom_products.id'
        )
        ->join(
            'ecom_product_variants',
            'shoppee_product_request_items.variant_id',
            '=',
            'ecom_product_variants.id'
        )
        ->where(
            'shoppee_product_request_items.request_id',
            $productRequest->id
        )
        ->select(
            DB::raw(
                "CONCAT(
                    ecom_products.name,
                    ' (',
                    ecom_product_variants.packing_size,
                    ')'
                ) as product"
            )
        )
        ->pluck('product')
        ->implode(', ');

        // CREATE TRANSACTION
        Shoppee_Transaction::create([

            'member_id' =>
                $productRequest->member_id,

            'ref_id' =>
                $productRequest->id,

            'ref_type' =>
                'product_request',

            'balance_type' =>
                'purchase',

            'entry_type' =>
                'debit',

            'amount' =>
                $productRequest->total_amount,

            'detail' =>
                'Debited Against Product Request#' .
                $productRequest->id .
                ' (' .
                $productNames .
                ')',
        ]);

        return response()->json([
            "message" =>
                "Product request approved successfully"
        ]);
    }
}