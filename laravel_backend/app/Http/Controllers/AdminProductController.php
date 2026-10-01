<?php

namespace App\Http\Controllers;

use App\Models\ConsistencyWalletProduct;
use App\Models\Member;
use App\Models\RepurchaseWalletProduct;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class AdminProductController extends Controller
{
    private const CONSISTENCY_TX_PREFIX = 'CONSISTENCY_TX:';

    public function memberProducts(): JsonResponse
    {
        $userId = request()->header('X-Auth-Member') ?: request()->query('user_id');

        if (!$userId) {
            return response()->json(['message' => 'User id missing'], 401);
        }

        $member = Member::where('user_id', $userId)->first();

        if (!$member) {
            return response()->json(['message' => 'Member not found'], 404);
        }

        $repurchaseProducts = RepurchaseWalletProduct::query()
            ->where('status', '>', 0)
            ->latest()
            ->get(['id', 'product_name', 'amount', 'image_path']);

        $consistencyProducts = ConsistencyWalletProduct::query()
            ->where('status', '>', 0)
            ->latest()
            ->get(['id', 'product_name', 'amount', 'image_path']);

        $repurchaseProducts = $repurchaseProducts->map(function ($product) {
            $resolvedImageUrl = null;
            if (!empty($product->image_path)) {
                $resolvedImageUrl = Str::startsWith($product->image_path, ['http://', 'https://'])
                    ? $product->image_path
                    : asset('images/ecom/' . $product->image_path);
            }

            return [
                'id' => $product->id,
                'product_name' => $product->product_name,
                'amount' => $product->amount,
                'image_path' => $product->image_path,
                'image_url' => $resolvedImageUrl,
            ];
        });

        $consistencyProducts = $consistencyProducts->map(function ($product) {
            $resolvedImageUrl = null;
            if (!empty($product->image_path)) {
                $resolvedImageUrl = Str::startsWith($product->image_path, ['http://', 'https://'])
                    ? $product->image_path
                    : asset('images/ecom/' . $product->image_path);
            }

            return [
                'id' => $product->id,
                'product_name' => $product->product_name,
                'amount' => $product->amount,
                'image_path' => $product->image_path,
                'image_url' => $resolvedImageUrl,
            ];
        });

        return response()->json([
            'message' => 'Products fetched successfully',
            'data' => [
                'repurchase_products' => $repurchaseProducts,
                'consistency_products' => $consistencyProducts,
                'repurchase_wallet_balance' => $this->resolveRepurchaseWalletBalance($member->id, $member->user_id),
                'consistency_wallet_balance' => $this->resolveConsistencyWalletBalance($member->id, $member->user_id),
            ],
        ]);
    }

    public function purchaseFromWallet(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'wallet_type' => ['required', 'in:repurchase,consistency'],
            'product_id' => ['required', 'integer', 'min:1'],
        ]);

        $userId = $request->header('X-Auth-Member') ?: $request->input('user_id');

        if (!$userId) {
            return response()->json(['message' => 'User id missing'], 401);
        }

        $member = Member::where('user_id', $userId)->first();

        if (!$member) {
            return response()->json(['message' => 'Member not found'], 404);
        }

        $walletType = $validated['wallet_type'];
        $isConsistency = $walletType === 'consistency';

        $product = $isConsistency
            ? ConsistencyWalletProduct::query()->where('status', '>', 0)->find($validated['product_id'])
            : RepurchaseWalletProduct::query()->where('status', '>', 0)->find($validated['product_id']);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $productAmount = round((float) $product->amount, 2);

        $currentBalance = $isConsistency
            ? $this->resolveConsistencyWalletBalance($member->id, $member->user_id)
            : $this->resolveRepurchaseWalletBalance($member->id, $member->user_id);

        if ($currentBalance < $productAmount) {
            return response()->json([
                'message' => 'Insufficient wallet balance for this purchase.',
                'data' => [
                    'wallet_type' => $walletType,
                    'wallet_balance' => $currentBalance,
                    'product_amount' => $productAmount,
                ],
            ], 422);
        }

        $newBalance = round($currentBalance - $productAmount, 2);

        [$ownerColumn, $ownerValue] = $this->resolveWalletTransactionOwner('repurchase_wallet_transactions', $member->id, $member->user_id);

        DB::table('repurchase_wallet_transactions')->insert([
            $ownerColumn => $ownerValue,
            'type' => 'debit',
            'amount' => $productAmount,
            'description' => $isConsistency
                ? self::CONSISTENCY_TX_PREFIX . sprintf(' Product purchase: %s', $product->product_name)
                : sprintf('Product purchase: %s (Repurchase wallet)', $product->product_name),
            'balance_after' => $newBalance,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Product purchased successfully.',
            'data' => [
                'wallet_type' => $walletType,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->product_name,
                    'amount' => $productAmount,
                ],
                'repurchase_wallet_balance' => $this->resolveRepurchaseWalletBalance($member->id, $member->user_id),
                'consistency_wallet_balance' => $this->resolveConsistencyWalletBalance($member->id, $member->user_id),
            ],
        ]);
    }

    private function resolveRepurchaseWalletBalance(int $memberId, string $userId): float
    {
        if (!Schema::hasTable('repurchase_wallet_transactions')) {
            return 0.0;
        }

        [$ownerColumn, $ownerValue] = $this->resolveWalletTransactionOwner('repurchase_wallet_transactions', $memberId, $userId);

        $query = DB::table('repurchase_wallet_transactions')
            ->where($ownerColumn, $ownerValue)
            ->where(function ($innerQuery) {
                $innerQuery->whereNull('description')
                    ->orWhere('description', 'not like', self::CONSISTENCY_TX_PREFIX . '%');
            });

        $credits = (float) (clone $query)->where('type', 'credit')->sum('amount');
        $debits = (float) (clone $query)->where('type', 'debit')->sum('amount');

        return round($credits - $debits, 2);
    }

    private function resolveConsistencyWalletBalance(int $memberId, string $userId): float
    {
        $bonusCredits = 0.0;
        if (Schema::hasTable('loyalty_bonuses') && Schema::hasColumn('loyalty_bonuses', 'bonus_amount')) {
            $bonusCredits = (float) DB::table('loyalty_bonuses')
                ->where('member_id', $memberId)
                ->where('type', 'consistency')
                ->sum('bonus_amount');
        }

        if (!Schema::hasTable('repurchase_wallet_transactions')) {
            return round($bonusCredits, 2);
        }

        [$ownerColumn, $ownerValue] = $this->resolveWalletTransactionOwner('repurchase_wallet_transactions', $memberId, $userId);

        $txQuery = DB::table('repurchase_wallet_transactions')
            ->where($ownerColumn, $ownerValue)
            ->where('description', 'like', self::CONSISTENCY_TX_PREFIX . '%');

        $debits = (float) (clone $txQuery)->where('type', 'debit')->sum('amount');
        $credits = (float) (clone $txQuery)->where('type', 'credit')->sum('amount');

        return round($bonusCredits + $credits - $debits, 2);
    }

    private function resolveWalletTransactionOwner(string $table, int $memberId, string $memberUserId): array
    {
        if (Schema::hasColumn($table, 'user_id')) {
            $columnType = Schema::getColumnType($table, 'user_id');
            $numericTypes = ['bigint', 'integer', 'int', 'mediumint', 'smallint', 'tinyint', 'decimal', 'float'];

            return in_array(strtolower($columnType), $numericTypes, true)
                ? ['user_id', $memberId]
                : ['user_id', $memberUserId];
        }

        if (Schema::hasColumn($table, 'member_id')) {
            return ['member_id', $memberId];
        }

        return ['user_id', $memberUserId];
    }

    public function index(): View
    {
        $repurchaseProducts = RepurchaseWalletProduct::query()
            ->where('status', '>', 0)
            ->latest()
            ->get();

        $consistencyProducts = ConsistencyWalletProduct::query()
            ->where('status', '>', 0)
            ->latest()
            ->get();

        return view('admin.products.index', [
            'repurchaseProducts' => $repurchaseProducts,
            'consistencyProducts' => $consistencyProducts,
        ]);
    }

    public function storeRepurchase(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'product_image' => ['nullable', 'image', 'max:2048'],
            'product_image_url' => ['nullable', 'url', 'max:1000'],
        ]);

        $imagePath = $request->hasFile('product_image')
            ? $request->file('product_image')->store('products/repurchase', 'public')
            : ($validated['product_image_url'] ?? null);

        RepurchaseWalletProduct::create([
            'product_name' => $validated['product_name'],
            'amount' => $validated['amount'],
            'image_path' => $imagePath,
            'status' => true,
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Repurchase product added successfully.');
    }

    public function storeConsistency(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'product_image' => ['nullable', 'image', 'max:2048'],
            'product_image_url' => ['nullable', 'url', 'max:1000'],
        ]);

        $imagePath = $request->hasFile('product_image')
            ? $request->file('product_image')->store('products/consistency', 'public')
            : ($validated['product_image_url'] ?? null);

        ConsistencyWalletProduct::create([
            'product_name' => $validated['product_name'],
            'amount' => $validated['amount'],
            'image_path' => $imagePath,
            'status' => true,
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Consistency product added successfully.');
    }
}
