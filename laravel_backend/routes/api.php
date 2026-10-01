<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// ─────────────────────────────────────────────
// MLM MEMBER PANEL CONTROLLERS
// ─────────────────────────────────────────────
use App\Http\Controllers\MemberController;
// use App\Http\Controllers\DiwaliBonusController;
use App\Http\Controllers\RoyaltyClubBonusController;
use App\Http\Controllers\BusinessMonitoringBonusController;
use App\Http\Controllers\LeadershipRankBonusController;
use App\Http\Controllers\FamilySaverBonusController;
// use App\Http\Controllers\BranchTurnoverBonusController;
use App\Http\Controllers\LoyaltyBonusController;
use App\Http\Controllers\GroupBuiltupBonusController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\MemberMessageController;
use App\Http\Controllers\RepurchaseWalletStatusController;
use App\Http\Controllers\IncomeSummaryController;
use App\Http\Controllers\EarningBalanceController;
use App\Http\Controllers\RankRewardController;
use App\Http\Controllers\CashbackWalletController;
use App\Http\Controllers\LeadershipGiftBonusController;

// use App\Http\Controllers\TravelClubBonusController;
use App\Http\Controllers\AdminProductController;
use App\Http\Controllers\IncomeReportController;
use App\Http\Controllers\HelpTicketController;
use App\Http\Controllers\RepurchaseBonusController;
use App\Http\Controllers\BalanceRequestController as MlmBalanceRequestController;
use App\Http\Controllers\TransactionPasswordController;
use App\Http\Controllers\MemberOrderController;




use App\Models\Member;

Route::post('/member-auto-login', function (Request $request) {

    // Accept either the member's user_id (e.g. FRL843FE4) or numeric id
    $member = Member::where('user_id', $request->id)->first()
        ?? (is_numeric($request->id) ? Member::find($request->id) : null);

    if (!$member) {
        return response()->json([
            'error' => 'User not found'
        ], 404);
    }

    return response()->json([
        'id' => $member->id,
        'user_id' => $member->user_id,
        'fullname' => $member->fullname
    ]);
});


// ─────────────────────────────────────────────
// ECOMMERCE MODULE CONTROLLERS (api/ folder)
// ─────────────────────────────────────────────
use App\Http\Controllers\api\MemberControllerecom;
use App\Http\Controllers\api\ProductController    as EcomProductController;
use App\Http\Controllers\api\OrderController      as EcomOrderController;
use App\Http\Controllers\api\CartController;
use App\Http\Controllers\api\NewsletterController;

// ─────────────────────────────────────────────
// SHOPPEE MODULE CONTROLLERS (shoppee/ folder)
// ─────────────────────────────────────────────
use App\Http\Controllers\shoppee\MemberController      as ShopeeMemberController;
use App\Http\Controllers\shoppee\BalanceRequestController as ShopeeBalanceRequestController;

use App\Http\Controllers\shoppee\ProductController     as ShopeeProductController;
use App\Http\Controllers\shoppee\DistributorController;
use App\Http\Controllers\shoppee\OrderController       as ShopeeOrderController;
use App\Http\Controllers\shoppee\HelpdeskController;
use App\Http\Controllers\shoppee\MemberKycController;
use App\Http\Controllers\shoppee\OrderPickupController;
use App\Http\Controllers\shoppee\BranchSaleController;
use App\Http\Controllers\shoppee\DashboardController;


Route::post('/send-otp', [MemberControllerecom::class, 'sendOtp']);
Route::post('/verify-otp', [MemberControllerecom::class, 'verifyOtp']);




Route::get('/run-binary-test', function () {
    $controller = new \App\Http\Controllers\GroupBuiltupBonusController();

    $request = new \Illuminate\Http\Request([
        'cycle_date' => now()->toDateString()
    ]);

    return $controller->calculateCycle($request);
});

Route::get('/bonuses/group-builtup', [\App\Http\Controllers\GroupBuiltupBonusController::class, 'getBonuses']);

Route::get('/member-info/{member_id}', [MemberController::class, 'getMemberInfo']);


// =============================================================
//  SHOPPEE ROUTES  (prefix: shoppee/)
// =============================================================





Route::get('/member/referral-downline', [MemberController::class, 'getReferralDownline']);



Route::post('/transaction-password/status', [TransactionPasswordController::class, 'status']);
Route::post('/transaction-password/create', [TransactionPasswordController::class, 'create']);
Route::post('/transaction-password/send-otp', [TransactionPasswordController::class, 'sendOtp']);
Route::post('/transaction-password/update', [TransactionPasswordController::class, 'update']);
Route::get(
    'shoppee/admin-auto-login/{id}',
    [ShopeeMemberController::class, 'adminAutoLogin']
);


Route::post('/balance-request', [MlmBalanceRequestController::class, 'store']);
Route::get('/balance-history', [MlmBalanceRequestController::class, 'history']);
Route::get('/balance-transactions', [MlmBalanceRequestController::class, 'transactions']);

// Auth
Route::post('shoppee/signup',              [ShopeeMemberController::class, 'store']);
Route::post('shoppee/login',               [ShopeeMemberController::class, 'login']);

Route::post('shoppee/transaction-password/status', [ShopeeMemberController::class, 'transactionPasswordStatus']);
Route::post('shoppee/transaction-password/create', [ShopeeMemberController::class, 'createTransactionPassword']);
Route::post('shoppee/transaction-password/send-otp', [ShopeeMemberController::class, 'sendTransactionPasswordOtp']);
Route::post('shoppee/transaction-password/update', [ShopeeMemberController::class, 'updateTransactionPassword']);

Route::post('shoppee/forgot-password', [ShopeeMemberController::class, 'forgotPassword']);
Route::post('shoppee/verify-forgot-otp', [ShopeeMemberController::class, 'verifyOtp']);
Route::post('shoppee/reset-password', [ShopeeMemberController::class, 'resetPassword']);
Route::put('shoppee/update-profile/{id}',  [ShopeeMemberController::class, 'updateProfile']);


Route::post('shoppee/member-kyc', [MemberKycController::class, 'store']);
Route::get('shoppee/member-kyc/{member_id}', [MemberKycController::class, 'index']);
Route::get('shoppee/member-kyc/latest/{member_id}', [MemberKycController::class, 'latest']);

// Helpdesk
Route::post('shoppee/helpdesk',              [HelpdeskController::class, 'store']);
Route::get('shoppee/helpdesk/{user_id}',     [HelpdeskController::class, 'userHelpdesk']);
Route::post('shoppee/helpdesk/reply/{id}',   [HelpdeskController::class, 'reply']);

// Orders
Route::post('shoppee/create-order',          [ShopeeOrderController::class, 'store']);
Route::get('shoppee/order-details/{id}',     [ShopeeOrderController::class, 'show']);
Route::get('shoppee/order-history/{userId}',          [ShopeeOrderController::class, 'history']);
Route::get('shoppee/branch-order/{id}',      [ShopeeOrderController::class, 'getOrder']);

// Distributor
// Route::get('shoppee/verify-distributor/{id}', [DistributorController::class, 'show']);

// Products
Route::get('shoppee/products',                      [ShopeeProductController::class, 'index']);
Route::get(
    'shoppee/categories',
    [ShopeeProductController::class, 'categories']
);
Route::get('shoppee/stock-report',                  [ShopeeProductController::class, 'stockReport']);
Route::post('shoppee/product-request',              [ShopeeProductController::class, 'submitRequest']);
Route::get('shoppee/product-requests',              [ShopeeProductController::class, 'getRequests']);
Route::post('shoppee/product-request/approve/{id}', [ShopeeProductController::class, 'approveRequest']);


// Balance / Wallet
Route::post('shoppee/balance-request',              [ShopeeBalanceRequestController::class, 'store']);
Route::get('shoppee/balance-history',               [ShopeeBalanceRequestController::class, 'history']);
Route::post('shoppee/balance-request/approve/{id}', [ShopeeBalanceRequestController::class, 'approveRequest']);
Route::get('shoppee/transactions',                  [ShopeeBalanceRequestController::class, 'transactions']);
Route::post('shoppee/balance-request-reject/{id}', [ShopeeBalanceRequestController::class, 'rejectRequest']);


Route::get('shoppee/pickup-orders/{userId}',[OrderPickupController::class, 'getPickupOrders']);
Route::post('shoppee/dispatch-order/{orderId}',[OrderPickupController::class, 'dispatchOrder']);
Route::post('shoppee/verify-otp',[OrderPickupController::class, 'verifyOtp']);

Route::get('shoppee/order-types',[BranchSaleController::class, 'getOrderTypes']);
Route::get('shoppee/products-by-order-type/{id}',[BranchSaleController::class, 'getProductsByOrderType']);
Route::post('shoppee/submit-turnover-order',[BranchSaleController::class, 'submitTurnoverOrder']);
Route::get('shoppee/verify-ecom-member/{id}',[BranchSaleController::class, 'verifyEcomMember']);
Route::get(
    'shoppee/branch-sale-history/{memberId}',
    [BranchSaleController::class, 'orderHistory']
);

Route::get(
    'shoppee/branch-sale-details/{id}',
    [BranchSaleController::class, 'orderDetails']
);

Route::get('shoppee/dashboard-summary/{memberCode}',[DashboardController::class, 'summary']);
// =============================================================
//  ECOMMERCE ROUTES
// =============================================================

// Auth
Route::post('/login',        [MemberControllerecom::class, 'login']);
Route::post('/signup',       [MemberControllerecom::class, 'store']);
Route::post('/forgot-password', [MemberControllerecom::class, 'forgotPassword']);
Route::post('/verify-forgot-otp', [MemberControllerecom::class, 'verifyForgotOtp']);
Route::post('/reset-password', [MemberControllerecom::class, 'resetPassword']);

Route::put('/members/{member_id}', [MemberControllerecom::class, 'update']);
Route::get('/members/{member_id}', [MemberControllerecom::class, 'show']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe']);

// Help Ticket
Route::post('/help-ticket',              [HelpTicketController::class, 'store']);
Route::get('/help-ticket/{member_id}',   [HelpTicketController::class, 'getTickets']);


Route::get('/cart-count/{member_id}', [CartController::class, 'cartCount']);
Route::post('/add-to-cart', [CartController::class, 'addToCart']);
Route::get('/cart', [CartController::class, 'getCart']);
Route::put('/cart/{id}', [CartController::class, 'updateQuantity']);
Route::delete('/cart/{id}', [CartController::class, 'remove']);

// ✅ CLEAN UNIVERSAL CLEAR CART
Route::post('/clear-cart', [CartController::class, 'clearCart']);

// Merge cart
Route::post('/merge-cart', [CartController::class, 'mergeCart']);
Route::get('/wallet/{user_id}', [CartController::class, 'getWalletByUserId']);

// Orders
Route::get('/orders/{member_id}',        [EcomOrderController::class, 'userOrders']);
Route::get('/orders',                    [EcomOrderController::class, 'index']);
Route::post('/place-order',              [EcomOrderController::class, 'store']);
Route::get('/latest-order/{member_id}',  [EcomOrderController::class, 'latestOrder']);

// Products
Route::get('/products/category/{id}',   [EcomProductController::class, 'productsByCategory']);
Route::get('/product/{id}',             [EcomProductController::class, 'show']);
Route::get('/viral-products',           [EcomProductController::class, 'viralProducts']);
Route::get('/products',                 [EcomProductController::class, 'allProducts']);
Route::get('/categories',               [EcomProductController::class, 'categories']);
Route::get('/coming-soon-products', [EcomProductController::class, 'comingSoonProducts']);
Route::get(
    '/coming-soon-product/{id}',
    [EcomProductController::class, 'comingSoonProduct']
);

// =============================================================
//  MLM MEMBER PANEL ROUTES  (prefix: member/)
// =============================================================

Route::middleware('weekly.cycle.lock')->group(function () {

    // Auth
    Route::post('member/send-signup-otp', [MemberController::class, 'sendSignupOtp']);
    Route::post('member/signup',        [MemberController::class, 'signup']);
    Route::post('member/signin',        [MemberController::class, 'signin']);
    Route::match(['get','post'], 'member/forgot-password', [MemberController::class, 'forgotPassword']);
    Route::post('member/verify-forgot-otp', [MemberController::class, 'verifyForgotOtp']);
    Route::post('member/reset-password', [MemberController::class, 'resetPassword']);

    Route::post('member/check-sponsor', [MemberController::class, 'checkSponsor']);
    Route::post('member/internal-add',  [MemberController::class, 'internalAdd']);

    // Member Order History
    Route::get(
        'member/order-history',
        [MemberOrderController::class, 'orderHistory']
    );

    Route::get(
        'member/order-invoice/{id}',
        [MemberOrderController::class, 'invoice']
    );

    // Dashboard
    Route::get('member/dashboard',       [MemberController::class, 'dashboard']);
    Route::get('member/dashboard-stats', [MemberController::class, 'dashboardStats']);

    // Package & Products
    Route::post('member/activate-package',  [MemberController::class, 'activatePackage']);
    Route::get('member/products',            [AdminProductController::class, 'memberProducts']);
    Route::post('member/products/purchase',  [AdminProductController::class, 'purchaseFromWallet']);

    // Tree & Downline
    Route::get('member/tree',      [MemberController::class, 'tree']);
    Route::get('/downline',        [MemberController::class, 'getDownline']);
    Route::get('/matching-status', [MemberController::class, 'matchingStatus']);

    // Profile & KYC
    Route::get('member/profile',  [MemberController::class, 'profile']);
    Route::put('member/profile',  [MemberController::class, 'updateProfile']);
    Route::post('/member/profile-photo', [MemberController::class, 'updateProfilePhoto']); // added 
    Route::get('member/kyc',      [MemberController::class, 'getKyc']);
    Route::put('member/kyc',      [MemberController::class, 'upsertKyc']);
    Route::get('member/id-card',  [MemberController::class, 'getIdCard']);
    Route::post('member/id-card', [MemberController::class, 'uploadIdCard']);
    Route::post('member/kyc',     [MemberController::class, 'storeKyc']);

    // Wallet Status
    Route::get('member/consistency-status', [RepurchaseWalletStatusController::class, 'index']);

    Route::get(
        '/consistency-wallet/transactions',
        [LoyaltyBonusController::class, 'consistencyWallet']
    );

    Route::get(
        'member/repurchase-status',
        [LoyaltyBonusController::class, 'repurchaseStatus']
    );

    // Income & Earnings
    Route::get(
        'member/income-summary',
        [IncomeSummaryController::class, 'index']
    );

    Route::get(
        'member/income-report',
        [IncomeReportController::class, 'index']
    );

    Route::get(
        'member/earning-balance-withdrawal',
        [EarningBalanceController::class, 'withdrawal']
    );

    Route::get(
        'member/earning-balance-history',
        [EarningBalanceController::class, 'history']
    );

    // Bonus Status
    Route::get(
        'member/royalty-status',
        [RoyaltyClubBonusController::class, 'status']
    );

    Route::get(
        'member/business-monitoring-status',
        [BusinessMonitoringBonusController::class, 'status']
    );

});
Route::get('/admin/tree', [MemberController::class, 'adminTree']);
Route::post('/admin/auto-login/{userId}', [MemberController::class, 'autoLogin']);



// =============================================================
//  MLM BONUSES  (prefix: bonuses/)
// =============================================================
 
Route::get('bonuses/repurchase/calculate', [RepurchaseBonusController::class, 'calculateRepurchaseBonus']);

// Route::get('bonuses/diwali',                         [DiwaliBonusController::class, 'index']);
// Route::post('bonuses/diwali/calculate',              [DiwaliBonusController::class, 'calculateYearly']);

Route::get('bonuses/royalty-club',                   [RoyaltyClubBonusController::class, 'index']);
Route::post('bonuses/royalty-club/calculate',        [RoyaltyClubBonusController::class, 'calculateMonthly']);

Route::get('bonuses/business-monitoring',            [BusinessMonitoringBonusController::class, 'index']);
Route::post('bonuses/business-monitoring/calculate', [BusinessMonitoringBonusController::class, 'calculateCycle']);

Route::get('bonuses/leadership-rank',                [LeadershipRankBonusController::class, 'index']);
Route::post('bonuses/leadership-rank/calculate',     [LeadershipRankBonusController::class, 'calculateCycle']);

// New Added routes for Leadership Gift Bonus

Route::get(
    'bonuses/leadership-gift',
    [LeadershipGiftBonusController::class, 'status']
);

Route::get(
    'bonuses/leadership-gift/history',
    [LeadershipGiftBonusController::class, 'index']
);

Route::post(
    'bonuses/leadership-gift/calculate',
    [LeadershipGiftBonusController::class, 'calculate']
);

Route::get('bonuses/family-saver',                   [FamilySaverBonusController::class, 'index']);
Route::post('bonuses/family-saver/calculate',        [FamilySaverBonusController::class, 'calculateMonthly']);

// Route::get('bonuses/branch-turnover',                [BranchTurnoverBonusController::class, 'index']);
// Route::post('bonuses/branch-turnover/calculate',     [BranchTurnoverBonusController::class, 'calculateMonthly']);

Route::get('bonuses/loyalty',                        [LoyaltyBonusController::class, 'index']);
Route::post('bonuses/loyalty/calculate-monthly',     [LoyaltyBonusController::class, 'calculateMonthly']);
Route::post('bonuses/loyalty/calculate-consistency', [LoyaltyBonusController::class, 'calculateConsistencyBonus']);


Route::post('bonuses/group-builtup/calculate',       [GroupBuiltupBonusController::class, 'calculateCycle']);

Route::get(
    'bonuses/group-builtup',
    [GroupBuiltupBonusController::class, 'getBonuses']
);

Route::get(
    '/bonuses/leadership-rank/status',
    [LeadershipRankBonusController::class, 'status']
);

// Route::get('bonuses/travel-club',                    [TravelClubBonusController::class, 'index']);


// =============================================================
//  MLM BRANCHES, MESSAGES, REWARDS
// =============================================================

Route::get('branches',          [BranchController::class, 'index']);
Route::get('branches/referral', [BranchController::class, 'referralBranch']);

Route::post('messages/compose', [MemberMessageController::class, 'compose']);
Route::get('messages/inbox',    [MemberMessageController::class, 'inbox']);
Route::get('messages/outbox',   [MemberMessageController::class, 'outbox']);

Route::get('/rank-rewards',     [RankRewardController::class, 'rankRewards']);

Route::get('/cashback-wallet/balance', [CashbackWalletController::class, 'balance']);

Route::get('/cashback-wallet/transactions', [CashbackWalletController::class, 'transactions']);