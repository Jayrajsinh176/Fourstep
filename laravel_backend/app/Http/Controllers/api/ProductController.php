<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Productecom as Product;
use App\Models\Categoryecom as Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // ✅ COMMON FORMAT FUNCTION
private function formatProduct($item)
{
    // ✅ HANDLE IMAGES
    if ($item->image) {

        $images = is_array($item->image)
            ? $item->image
            : json_decode($item->image, true);

        if (!is_array($images)) {
            $images = [$item->image];
        }

        $item->image = collect($images)->map(function ($img) {

            if (str_starts_with($img, 'http')) {
                return $img;
            }

            return asset('storage/' . ltrim($img, '/'));

        })->toArray();

    } else {

        $item->image = [asset('default-product.png')];
    }

    // ✅ SAFE VARIANTS
    $variants = collect($item->variants ?? []);

    // ✅ FORMAT VARIANTS
    $variants->transform(function ($variant) {

        $variant->price =
            (float) ($variant->price ?? 0);

        $variant->offer_price =
            (float) ($variant->offer_price ?? 0);

        $variant->gst_price =
            (float) ($variant->gst_price ?? 0);

        $variant->discount_percentage =
            (float) ($variant->discount_percentage ?? 0);

        // $variant->pv =
        //     (float) ($variant->pv ?? 0);

        $variant->bv =
            (float) ($variant->bv ?? 0);

        $variant->stock =
            (int) ($variant->stock ?? 0);

        $variant->minimum_quantity =
            (int) ($variant->minimum_quantity ?? 1);

        $variant->cashback =
            (float) ($variant->cashback ?? 0);
            
$variant->mega_branch_commission =
    (float) ($variant->mega_branch_commission ?? 0);

$variant->mini_branch_commission =
    (float) ($variant->mini_branch_commission ?? 0);

$variant->pincode_branch_commission =
    (float) ($variant->pincode_branch_commission ?? 0);

        $variant->product_status =
            $variant->product_status ?? 'order_now';

        return $variant;
    });

    // ✅ SET BACK
    $item->variants = $variants;

    // ✅ AUTO SELECT VARIANT
    if (!$item->selected_variant) {

        $item->selected_variant =
            $variants->first()?->id;
    }

    // ✅ SELECTED VARIANT
    $selectedVariant = $variants
        ->where('id', $item->selected_variant)
        ->first()

        ?? $variants->first();

    // ✅ PRODUCT DATA
    if ($selectedVariant) {

        $item->price =
            $selectedVariant->price;

        $item->offer_price =
            $selectedVariant->offer_price;

        $item->discount_percentage =
            $selectedVariant->discount_percentage;

        // $item->pv =
        //     $selectedVariant->pv;

        $item->bv =
            $selectedVariant->bv;

        $item->stock =
            $selectedVariant->stock;

        $item->product_status =
            $selectedVariant->product_status;
    }

    // ✅ ORDER TYPES
    $item->order_types =
        collect($item->orderTypes ?? [])
            ->map(function ($type) {

                return [
                    'id' => $type->id,
                    'name' => $type->name,
                    'description' => $type->description,
                    'status' => $type->status,
                ];
            });

    return $item;
}

    // ✅ ALL PRODUCTS
    public function allProducts()
    {
        $products = Product::with('variants', 'orderTypes')
           ->select(
    'id',
    'selected_variant',
    'category_id',
    'brand',
    'name',
    'short_description',
    'description',
    'image',
    'is_viral'
)
       ->whereDoesntHave('variants', function ($q) {

    $q->where('product_status', 'coming_soon');

})

->get();

        $products->transform(fn ($item) => $this->formatProduct($item));

        return response()->json($products);
    }

    // ✅ VIRAL PRODUCTS
    public function viralProducts()
    {
        $products = Product::with('variants', 'orderTypes')
            ->where('is_viral', 1)
            ->whereDoesntHave('variants', function ($q) {

    $q->where('product_status', 'coming_soon');

})
            ->select(
    'id',
    'selected_variant',
    'brand',
    'name',
    'short_description',
    'description',
    'image',
    'is_viral'
)
            ->get();

        $products->transform(fn ($item) => $this->formatProduct($item));

        return response()->json($products);
    }

    // ✅ SINGLE PRODUCT
    public function show($id)
{
    $product = Product::with('variants', 'orderTypes')
        ->findOrFail($id);

    // ✅ BLOCK COMING SOON PRODUCT PAGE
    $comingSoon = collect($product->variants)
        ->contains('product_status', 'coming_soon');

    if ($comingSoon) {

        return response()->json([
            'message' => 'Product coming soon'
        ], 403);
    }

    return $this->formatProduct($product);
}

    // ✅ PRODUCTS BY CATEGORY
    public function productsByCategory($id)
    {
        $products = Product::with('category', 'variants','orderTypes')
            ->where('category_id', $id)
            ->whereDoesntHave('variants', function ($q) {

    $q->where('product_status', 'coming_soon');

})
            ->get();

        $products->transform(function ($item) {

            $item = $this->formatProduct($item);

            $item->category_name = $item->category->name ?? null;

            return $item;
        });

        return response()->json($products);
    }
    
    // ✅ COMING SOON PRODUCTS
public function comingSoonProducts()
{
    try {

        $products = Product::with('variants')

            ->whereHas('variants', function ($q) {

                $q->where('product_status', 'coming_soon');

            })

            ->get();

        return response()->json($products);

    } catch (\Exception $e) {

        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
}

// ✅ SINGLE COMING SOON PRODUCT
public function comingSoonProduct($id)
{
    $product = Product::with('variants', 'orderTypes')
        ->findOrFail($id);

    $comingSoon = collect($product->variants)
        ->contains('product_status', 'coming_soon');

    if (!$comingSoon) {
        return response()->json([
            'message' => 'Product is not a Coming Soon product'
        ], 404);
    }

    return response()->json(
        $this->formatProduct($product)
    );
}


    // ✅ CATEGORIES
      public function categories()
    {
        $categories = Category::select('id', 'name', 'image')->get();

        $categories->transform(function ($item) {
            $item->image = $item->image
                ? asset($item->image)
                : asset('default-product.png');
            return $item;
        });

        return response()->json($categories);
    }
}