<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Member;
use App\Models\MyKyc;
use App\Models\IdCard;
use App\Models\CashbackWallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class MemberController extends Controller
{
    private const CONSISTENCY_TX_PREFIX = 'CONSISTENCY_TX:';

   
  private const PACKAGES = [
    ['id' => 'step-1', 'step' => 1, 'label' => '1 Step', 'bv' => 125,  'weekly_capping' => 5000],
    ['id' => 'step-2', 'step' => 2, 'label' => '2 Step', 'bv' => 250,  'weekly_capping' => 10000],
    ['id' => 'step-3', 'step' => 3, 'label' => '3 Step', 'bv' => 500,  'weekly_capping' => 20000],
    ['id' => 'step-4', 'step' => 4, 'label' => '4 Step', 'bv' => 1000, 'weekly_capping' => 50000],
];

    /**
     * Step 1 of signup: email the user a one-time verification code.
     * No member row / user_id is created here.
     */
    public function sendSignupOtp(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email|unique:members,email',
            'mobileNo' => ['required', 'digits:10', 'regex:/^[6-9][0-9]{9}$/', 'unique:members,mobile_no'],
        ], [
            'email.required'    => 'Email is required for verification.',
            'email.email'       => 'Enter a valid email address.',
            'email.unique'      => 'Email already registered.',
            'mobileNo.required' => 'Mobile number is required.',
            'mobileNo.digits'   => 'Mobile number must be 10 digits.',
            'mobileNo.regex'    => 'Enter a valid mobile number.',
            'mobileNo.unique'   => 'Mobile number already exists.',
        ]);

        $email = strtolower(trim($data['email']));
        $otp   = (string) rand(100000, 999999);

        Cache::put('signup_otp:' . $email, $otp, now()->addMinutes(10));

        try {
            Mail::raw(
                "Your Fourstep sign-up verification code is: {$otp}\n\nThis code expires in 10 minutes.",
                function ($message) use ($email) {
                    $message->to($email)->subject('Fourstep Sign-Up Verification Code');
                }
            );
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Could not send the verification email. Please try again.',
            ], 500);
        }

        return response()->json([
            'message' => 'Verification code sent to your email.',
        ] + (config('app.debug') ? ['otp' => $otp] : []));
    }

    public function signup(Request $request)
    {
$data = $request->validate([
    'sponsorId' => 'required|string',
    'position'  => 'required|in:left,right',
    'otp'       => 'required',

    'fullname' => [
        'required',
        'string',
        'min:3',
        'max:255',
        'regex:/^[A-Za-z ]+$/',
    ],

    'dob' => 'required|date|before:' . now()->subYears(18)->format('Y-m-d'),

    'gender' => 'required|in:Male,Female,Other',

    'email' => 'required|email|unique:members,email',

    'mobileNo' => [
        'required',
        'digits:10',
        'regex:/^[6-9][0-9]{9}$/',
        'unique:members,mobile_no',
    ],

    'password' => 'required|string|min:6',

    'address' => 'required|string|min:10|max:255',

    'pinCode' => 'required|digits:6',

    'state' => 'required|string|max:100',

    'city' => 'required|string|max:100',

    'district' => 'required|string|max:100',

], [

    'fullname.required' => 'Full name is required.',
    'fullname.regex' => 'Full name should contain only letters.',

    'mobileNo.required' => 'Mobile number is required.',
    'mobileNo.digits' => 'Mobile number must be 10 digits.',
    'mobileNo.regex' => 'Enter a valid mobile number.',
    'mobileNo.unique' => 'Mobile number already exists.',

    'email.required' => 'Email is required.',
    'email.email' => 'Enter a valid email address.',
    'email.unique' => 'Email already registered.',

    'otp.required' => 'Verification code is required.',

    'password.required' => 'Password is required.',
    'password.min' => 'Password must be at least 6 characters.',

    'address.required' => 'Address is required.',

    'pinCode.required' => 'PIN code is required.',
    'pinCode.digits' => 'PIN code must be 6 digits.',

    'state.required' => 'State is required.',
    'city.required' => 'City is required.',
    'district.required' => 'District is required.',

    'dob.required' => 'Date of birth is required.',
    'dob.before' => 'You must be at least 18 years old.',

    'position.required' => 'Please select a position.',
    'position.in' => 'Invalid position selected.',

    'gender.required' => 'Please select a gender.',
    'gender.in' => 'Please select a valid gender.',
]);

        // Verify the email OTP issued by sendSignupOtp() before any ID is generated.
        $otpKey    = 'signup_otp:' . strtolower(trim($data['email']));
        $cachedOtp = Cache::get($otpKey);

        if (!$cachedOtp || (string) $cachedOtp !== (string) $data['otp']) {
            return response()->json([
                'message' => 'Invalid or expired verification code.',
                'errors'  => ['otp' => ['Invalid or expired verification code.']],
            ], 422);
        }

        $sponsor = Member::where('user_id', $data['sponsorId'])->first();

if (!$sponsor) {
    return response()->json([
        'message' => 'Sponsor not found'
    ], 422);
}

        $slot = $this->findSpotInLeg($sponsor->id, $data['position']);

        if (!$slot) {
            return response()->json(['message' => 'No available position found'], 422);
        }

        $member = Member::create([
            'sponsor_id' => $sponsor->id,
            'parent_id'  => $slot['parent_id'],
            'position'   => $slot['position'],
            'fullname'   => $data['fullname'],
            'dob'        => $data['dob'],
            'gender'     => $data['gender'],
            'email'      => $data['email'] ?? null,
            'mobile_no'  => $data['mobileNo'],
            'password'   => Hash::make($data['password']),
            'address'    => $data['address'] ?? null,
            'pin_code'   => $data['pinCode'] ?? null,
            'state'      => $data['state'] ?? null,
            'city'       => $data['city'] ?? null,
            'district'   => $data['district'] ?? null,
            'status'     => 0,
        ]);

        Cache::forget($otpKey);

        return response()->json(['message' => 'Member created successfully', 'member' => $member], 201);
    }
    public function internalAdd(Request $request)
    {
        $data = $request->validate([
            'sponsorId' => 'required|string',
            'position'  => 'required|in:left,right',
            'fullname'  => 'required|string|max:255',
            'dob'       => 'nullable|date',
            'gender'    => 'nullable|string',
            'email'     => 'nullable|email|unique:members,email',
            'mobileNo'  => 'required|string|max:15|unique:members,mobile_no',
            'address'   => 'nullable|string',
            'pinCode'   => 'nullable|string|max:10',
            'state'     => 'nullable|string',
            'city'      => 'nullable|string',
            'district'  => 'nullable|string',
        ]);

        $sponsor = Member::where('user_id', $data['sponsorId'])->first();

        if (!$sponsor) {
            return response()->json(['message' => 'Sponsor not found'], 422);
        }

        $slot = $this->findSpotInLeg($sponsor->id, $data['position']);

        if (!$slot) {
            return response()->json(['message' => 'No available position found'], 422);
        }

        $member = Member::create([
            'sponsor_id' => $sponsor->id,
            'parent_id'  => $slot['parent_id'],
            'position'   => $slot['position'],
            'fullname'   => $data['fullname'],
            'dob'        => $data['dob'] ?? null,
            'gender'     => $data['gender'] ?? null,
            'email'      => $data['email'] ?? null,
            'mobile_no'  => $data['mobileNo'],
            'password'   => Hash::make($data['mobileNo']), 
            'address'    => $data['address'] ?? null,
            'pin_code'   => $data['pinCode'] ?? null,
            'state'      => $data['state'] ?? null,
            'city'       => $data['city'] ?? null,
            'district'   => $data['district'] ?? null,
            'status'     => 0,
        ]);

        return response()->json(['message' => 'Member added successfully', 'member' => $member], 201);
    }

   public function signin(Request $request)
{
    $request->validate([
        'identifier' => 'required|string',
        'password'   => 'required|string',
    ]);

    $id = $request->identifier;

    $member = Member::where('user_id', $id)
        ->orWhere('mobile_no', $id)
        ->orWhere('email', $id)
        ->first();

    if (!$member || !Hash::check($request->password, $member->password)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    // ✅ BLOCK LOGIN IF MEMBER IS BLOCKED
    if ($member->account_status === 'blocked') {
        return response()->json([
            'message' => 'Access denied. Your account is currently blocked. Please contact the administrator.'
        ], 403);
    }
    
    // Allow inactive members to login only within 30 days
if ((int) $member->status === 0) {

    $activationDeadline = Carbon::parse($member->created_at)->addDays(30);

    if (now()->greaterThan($activationDeadline)) {

        return response()->json([
            'message' => 'Your ID has expired because it was not activated within 30 days.'
        ], 403);

    }
}

    return response()->json([
        'message' => 'Login successful',
        'member' => $member
    ]);
}
    public function profile(Request $request)
    {
        $request->validate(['user_id' => 'required|string']);

        $member = $this->findMemberByUserId($request->user_id);

        if (!$member) {
            return response()->json(['message' => 'Member not found'], 404);
        }

        return response()->json($member);
    }
    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'user_id'   => 'required|string|exists:members,user_id',
            'fullname'  => 'nullable|string|max:255',
            'dob'       => 'nullable|date',
            'gender'    => 'nullable|string',
            'email'     => 'nullable|email',
           'mobile_no' => 'nullable|numeric|digits:10',
            'address'   => 'nullable|string',
            'pin_code'  => 'nullable|string',
            'state'     => 'nullable|string',
            'city'      => 'nullable|string',
            'district'  => 'nullable|string',
            'shipping_address'  => 'nullable|string',
            'shipping_pin_code' => 'nullable|string',
            'shipping_state'    => 'nullable|string',
            'shipping_city'     => 'nullable|string',
            'shipping_district' => 'nullable|string',
            'nominee_name'      => 'nullable|string|max:255',
            'nominee_relation'  => 'nullable|string|max:120',
            'nominee_mobile_no' => 'nullable|string|max:10',
            'nominee_address'   => 'nullable|string',
            'nominee_state'     => 'nullable|string|max:120',
            'nominee_city'      => 'nullable|string|max:120',
            'nominee_district'  => 'nullable|string|max:120',
            'nominee_pin_code'  => 'nullable|string|max:20',
        ]);

        $member = $this->findMemberByUserId($data['user_id']);
        
        // Check duplicate mobile number
if (!empty($data['mobile_no'])) {

    $mobileExists = Member::where('mobile_no', $data['mobile_no'])
        ->where('id', '!=', $member->id)
        ->exists();

    if ($mobileExists) {
        return response()->json([
            'message' => 'Mobile number already exists.'
        ], 422);
    }
}

        $updateData = $data;
        unset($updateData['user_id']);

        $shippingFields = [
            'shipping_address',
            'shipping_pin_code',
            'shipping_state',
            'shipping_city',
            'shipping_district',
            'nominee_name',
            'nominee_relation',
            'nominee_mobile_no',
            'nominee_address',
            'nominee_state',
            'nominee_city',
            'nominee_district',
            'nominee_pin_code',
        ];

        foreach ($shippingFields as $field) {
            if (!Schema::hasColumn('members', $field)) {
                unset($updateData[$field]);
            }
        }

        $member->update($updateData);

        return response()->json(['message' => 'Profile updated successfully', 'member' => $member]);
    }

    public function updateProfilePhoto(Request $request)
{
    $data = $request->validate([
        'user_id' => 'required|string|exists:members,user_id',
        'profile_photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
    ]);

    $member = $this->findMemberByUserId($data['user_id']);

    if (!$member) {
        return response()->json([
            'message' => 'Member not found'
        ], 404);
    }

    // Delete old profile photo
    if ($member->profile_photo) {
        $oldPath = $member->profile_photo;

        if (str_starts_with($oldPath, '/storage/')) {
            $oldPath = substr($oldPath, strlen('/storage/'));
        }

        if (Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }
    }

// Save new profile photo directly inside public/profile_photos
$uploadDirectory = public_path('profile_photos');

if (!file_exists($uploadDirectory)) {
    mkdir($uploadDirectory, 0755, true);
}

$file = $request->file('profile_photo');

$fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

$file->move($uploadDirectory, $fileName);

// Save public URL path in database
$member->profile_photo = '/profile_photos/' . $fileName;
$member->save();

    return response()->json([
        'message' => 'Profile photo updated successfully',
        'member' => $member,
    ]);
}

    public function dashboard(Request $request)
    {
        $member = $this->getMemberFromHeader($request);

        if (!$member) {
            return response()->json(['message' => 'Member not found or missing header'], 401);
        }
        $leftChild  = Member::where('parent_id', $member->id)->where('position', 'left')->first();
        $rightChild = Member::where('parent_id', $member->id)->where('position', 'right')->first();

        $leftCount  = $leftChild  ? 1 + $this->countDownline($leftChild->id)  : 0;
        $rightCount = $rightChild ? 1 + $this->countDownline($rightChild->id) : 0;

        return response()->json([
            'left_members'          => $leftCount,
            'right_members'         => $rightCount,
            'total_team'            => $leftCount + $rightCount,
            'packages'              => self::PACKAGES,
            'selected_package_id'   => $this->resolveMemberPackageId($member),
            'selected_package_step' => $this->resolveMemberPackageStep($member),
            'package_step'          => (int) ($member->package_step ?? 0),
            'step_level'            => (int) ($member->step_level ?? 0),
            'status'                => (int) ($member->status ?? 0),
        ]);
    }
    public function dashboardStats(Request $request)
{
    $member = $this->getMemberFromHeader($request);
 
    if (!$member) {
        return response()->json(['message' => 'Member not found or missing header'], 401);
    }
 
    $memberId   = (int) $member->id;
    $userId     = (string) $request->header('X-Auth-Member');
    $monthStart = now()->startOfMonth()->toDateString();
    $monthEnd   = now()->endOfMonth()->toDateString();
    $monthKey   = now()->format('Y-m');
 
    $leftChild  = Member::where('parent_id', $memberId)->where('position', 'left')->first();
    $rightChild = Member::where('parent_id', $memberId)->where('position', 'right')->first();
 
    $leftTeamCount  = $leftChild ? 1 + $this->countDownline($leftChild->id) : 0;
    $rightTeamCount = $rightChild ? 1 + $this->countDownline($rightChild->id) : 0;
    $totalTeam      = $leftTeamCount + $rightTeamCount;
    
    $leftManagerCount = 0;

if ($leftChild) {
    $leftStep = (int) ($leftChild->package_step ?? $leftChild->step_level ?? 0);

    if ($leftStep >= 4) {
        $leftManagerCount++;
    }

    $leftManagerCount += $this->countStep4PlusMembers($leftChild->id);
}

$rightManagerCount = 0;

if ($rightChild) {
    $rightStep = (int) ($rightChild->package_step ?? $rightChild->step_level ?? 0);

    if ($rightStep >= 4) {
        $rightManagerCount++;
    }

    $rightManagerCount += $this->countStep4PlusMembers($rightChild->id);
}
 
    // ✅ FIX: count direct children themselves if active, then recurse
    $leftActiveCount = 0;
    if ($leftChild) {
        if ((int) $leftChild->status === 1) $leftActiveCount++;
        $leftActiveCount += $this->countActiveDownline($leftChild->id);
    }
 
    $rightActiveCount = 0;
    if ($rightChild) {
        if ((int) $rightChild->status === 1) $rightActiveCount++;
        $rightActiveCount += $this->countActiveDownline($rightChild->id);
    }
 
    $totalActiveTeam = $leftActiveCount + $rightActiveCount;
 
    $salesData      = $this->getSalesData($memberId, $userId, $monthStart, $monthEnd);
    $leadershipRank = $this->getLeadershipRank($memberId, $userId);
 
    return response()->json([
        'data' => [
            // ✅ Core team stats
            'total_team'            => $totalTeam,
            'total_register_team'   => $totalTeam,   // all downline regardless of status
            'total_active_team'     => $totalActiveTeam,
           'total_manager_left'    => $leftManagerCount,
'total_manager_right'   => $rightManagerCount,

            // ✅ Step / rank
            'id_position_step'      => $this->resolveMemberPackageStep($member),
            'leadership_rank'       => $leadershipRank,
            'rank_with_reward'      => $this->getRankWithReward($memberId, $userId),
 
            // ✅ Balances
            'repurchase_balance'    => $this->getRepurchaseBalance($memberId, $userId),
            'consistency_balance'   => $this->getConsistencyBalance($memberId, $userId),
            'earning_balance'       => $this->getEarningBalance($memberId, $userId),
            'cashback_balance'      => $this->getCashbackBalance($memberId),
            'total_left_right_earn' => $this->getLeftRightTotalEarn($memberId),
            'daily_earn'            => $this->getDailyEarn($memberId),
 
            // ✅ Purchase balance
            'purchase_balance'      => $this->getPurchaseBalance($memberId, $userId),
            'purchase_credit'       => DB::table('balance_requests')
                ->where(function ($q) use ($memberId, $userId) {
                    $q->where('member_id', $memberId)
                      ->orWhere('member_id', $userId);
                })
                ->where('type', 'purchase')
                ->where('status', 'approved')
                ->where('entry_type', 'credit')
                ->sum('amount'),
            'purchase_debit'        => DB::table('balance_requests')
                ->where(function ($q) use ($memberId, $userId) {
                    $q->where('member_id', $memberId)
                      ->orWhere('member_id', $userId);
                })
                ->where('type', 'purchase')
                ->where('status', 'approved')
                ->where('entry_type', 'debit')
                ->sum('amount'),
 
            // ✅ Direct
            'direct_id'             => $this->getDirectIdCount($memberId),
            'direct_branch'         => $this->getDirectBranchCount($memberId),
 
            // Legacy fields kept for compatibility
            'turnover_balance'      => $this->getTurnoverBalance($memberId, $userId),
            'purchase_orders'       => $this->getPurchaseOrders($memberId, $userId, $monthStart, $monthEnd),
            'sales_orders'          => $salesData['count'],
            'sales_turnover'        => $salesData['turnover'],
            'commission_amount'     => $this->getCommissionAmount($memberId, $userId, $monthStart, $monthEnd, $monthKey),
        ],
    ]);
}
 
    public function activatePackage(Request $request)
    {
        $data = $request->validate([
            'package_id' => 'required|string',
            'user_id'    => 'nullable|string',
        ]);

        $userId = $request->header('X-Auth-Member') ?: ($data['user_id'] ?? null);

        if (!$userId) {
            return response()->json(['message' => 'Missing member identifier'], 401);
        }

        $member = Member::where('user_id', $userId)->first();

        if (!$member) {
            return response()->json(['message' => 'Member not found'], 404);
        }

        $package = $this->findPackageById(trim($data['package_id']));

        if (!$package) {
            return response()->json(['message' => 'Invalid package selected'], 422);
        }

        $now = now();
        $isCurrentlyActive = (int) ($member->status ?? 0) === 1;
        $currentStep = $this->resolveMemberPackageStep($member);
        $targetStep = (int) ($package['step'] ?? 0);
        $requiredBv = (float) ($package['bv'] ?? 0);
$currentBv = (float) ($member->self_bv ?? 0);

if ($currentBv < $requiredBv) {
    return response()->json([
        'message' => 'Insufficient BV for the selected step.',
        'required_bv' => $requiredBv,
        'current_bv' => $currentBv,
        'shortfall_bv' => round($requiredBv - $currentBv, 2),
    ], 422);
}


        if (!$isCurrentlyActive) {
            $registrationDate = Carbon::parse($member->created_at);
            $activationDeadline = $registrationDate->copy()->addDays(30);

            if ($now->greaterThan($activationDeadline)) {
                return response()->json([
                    'message' => 'Activation deadline exceeded (30 days from registration)',
                    'activation_deadline' => $activationDeadline->toDateString(),
                ], 422);
            }
        }

        if ($isCurrentlyActive && $targetStep > $currentStep) {
            if (empty($member->activation_date)) {
                return response()->json([
                    'message' => 'Activation date missing; cannot validate upgrade window',
                ], 422);
            }

            $upgradeDeadline = Carbon::parse($member->activation_date)->addDays(60);
            if ($now->greaterThan($upgradeDeadline)) {
                return response()->json([
                 'message' => 'Upgrade window expired (60 days from activation)',
                    'upgrade_deadline' => $upgradeDeadline->toDateString(),
                ], 422);
            }
        }

     $updateData = [
    'status' => 1,
];

if (!$isCurrentlyActive) {
    $updateData['activation_date'] = $now;
}

if (Schema::hasColumn('members', 'package_step')) {

    $updateData['package_step'] = (int) $package['step'];

    /*
    |--------------------------------------------------------------------------
    | Save Package Upgrade Date
    |--------------------------------------------------------------------------
    */

    if (
        $isCurrentlyActive &&
        $targetStep > $currentStep &&
        Schema::hasColumn('members', 'package_upgraded_at')
    ) {
        $updateData['package_upgraded_at'] = now();
    }
}

if (Schema::hasColumn('members', 'step_level')) {
    $updateData['step_level'] = (int) $package['step'];
}


        Member::where('id', $member->id)->update($updateData);
        $member->refresh();

        return response()->json([
            'message' => 'Package activated successfully',
            'member'  => [
                'user_id'             => $member->user_id,
                'status'              => (int) $member->status,
                'activation_date'     => $member->activation_date,
                'selected_package_id' => $data['package_id'],
            ],
        ]);
    }
    public function checkSponsor(Request $request)
    {
        $request->validate(['sponsorId' => 'required|string']);

        $sponsor = Member::where('user_id', $request->sponsorId)->first();

        if (!$sponsor) {
            return response()->json(['message' => 'Sponsor not found'], 404);
        }

        return response()->json([
            'sponsor' => [
                'id'       => $sponsor->id,
                'user_id'  => $sponsor->user_id,
                'fullname' => $sponsor->fullname,
                'status'   => $sponsor->status,
            ],
        ]);
    }
    
public function tree(Request $request)
{
    if ($request->filled('user_id')) {
        $member = Member::where('user_id', $request->user_id)->first();
    } else {
        $member = $this->getMemberFromHeader($request);
    }

    if (!$member) {
        return response()->json(['message' => 'Member not found'], 404);
    }

    $loggedInMember = $this->getMemberFromHeader($request);

    if (
        $loggedInMember &&
        $request->filled('user_id') &&
        $member->id != $loggedInMember->id &&
        !$this->isDownline($loggedInMember->id, $member->id)
    ) {
        return response()->json([
            'message' => 'You can only view your downline members.'
        ], 403);
    }

    return response()->json([
        'tree' => $this->buildTree($member->id, 4)
    ]);
}

    public function getDownline(Request $request)
    {
        $userId = $request->header('X-Auth-Member') ?: $request->query('user_id');

        if (!$userId) {
            return response()->json(['message' => 'Missing member identifier'], 401);
        }

        $member = Member::where('user_id', $userId)->first();

        if (!$member) {
            return response()->json(['message' => 'Member not found'], 404);
        }

        return response()->json([
            'left'  => $this->getAllDownline($member->id, 'left',  []),
            'right' => $this->getAllDownline($member->id, 'right', []),
        ]);
    }
    
    
public function getKyc(Request $request)
{
    $request->validate(['user_id' => 'required|string']);

    $member = $this->findMemberByUserId($request->user_id);

    if (!$member) {
        return response()->json(['message' => 'Member not found'], 404);
    }

    // ✅ Always get latest KYC only
    $kyc = MyKyc::where('member_id', $member->id)
        ->where('is_latest', 1)
        ->latest()
        ->first();
return response()->json([
    'kyc' => $kyc,
    'status' => $kyc ? $kyc->status : 'process'
]);
}


       public function upsertKyc(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|string',
            'account_beneficiary_name' => 'required|string',
            'account_no' => 'required|string',
            're_account_no' => 'required|string',
            'ifs_code' => 'required|string',
            'bank_name' => 'required|string',
            'branch_name' => 'required|string',
            'aadhaar_number' => 'required|string',
            'pan_number' => 'required|string',
        ]);

        if ($data['account_no'] !== $data['re_account_no']) {
            return response()->json(['message' => 'Account numbers do not match'], 422);
        }

        $member = $this->findMemberByUserId($data['user_id']);

        if (!$member) {
            return response()->json(['message' => 'Member not found'], 404);
        }

        $existing = MyKyc::where('member_id', $member->id)->first();

        $kyc = MyKyc::updateOrCreate(
            ['member_id' => $member->id],
            [
                'user_id' => $member->user_id,
                'account_beneficiary_name' => $existing ? $existing->account_beneficiary_name : $data['account_beneficiary_name'],
                'account_no' => $existing ? $existing->account_no : $data['account_no'],
                'ifs_code' => $existing ? $existing->ifs_code : strtoupper($data['ifs_code']),
                'bank_name' => $existing ? $existing->bank_name : $data['bank_name'],
                'branch_name' => $existing ? $existing->branch_name : $data['branch_name'],
                'aadhaar_number' => $existing ? $existing->aadhaar_number : $data['aadhaar_number'],
                'pan_number' => $existing ? $existing->pan_number : strtoupper($data['pan_number']),
                'otp_verified' => true,
               'status' => $existing ? $existing->status : 'pending',
            ]
        );

        return response()->json(['message' => 'KYC updated successfully', 'kyc' => $kyc]);
    }
    
    public function storeKyc(Request $request)
{
    try {

       
      $request->validate([
  
    'account_beneficiary_name' => 'required|string',

    // Account Number
    'account_no' => 'required|numeric|digits_between:8,20',
    're_account_no' => 'required|numeric|digits_between:8,20',

    'ifs_code' => 'required|string',
    'bank_name' => 'required|string',
    'branch_name' => 'required|string',

    // Aadhaar
    'aadhaar_number' => 'required|numeric|digits:12',

    // PAN
    'pan_number' => [
        'required',
        'regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/'
    ],

    'transaction_password' => 'required',

    'bank_passbook_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
'aadhaar_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
'pan_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
], [
    'account_no.numeric' => 'Account number must contain only digits.',
    'account_no.digits_between' => 'Account number must be between 8 and 20 digits.',

    're_account_no.numeric' => 'Re-enter account number must contain only digits.',
    're_account_no.digits_between' => 'Re-enter account number must be between 8 and 20 digits.',

    'aadhaar_number.numeric' => 'Aadhaar number must contain only digits.',
    'aadhaar_number.digits' => 'Aadhaar number must be exactly 12 digits.',

    'pan_number.regex' => 'PAN number format is invalid. Example: ABCDE1234F',
]);
        
        if ($request->account_no !== $request->re_account_no) {
            return response()->json([
                'error' => 'Account numbers do not match'
            ], 422);
        }

       
        $userId = $request->header('X-Auth-Member') ?: $request->user_id;

$member = Member::where('user_id', $userId)->first();
        

if (!$member) {
    return response()->json([
        'error' => 'Member not found'
    ], 404);
}


if (!$member->transaction_password) {
    return response()->json([
        'status' => false,
        'message' => 'Please set transaction password first'
    ], 400);
}

if (!$request->filled('transaction_password')) {
    return response()->json([
        'status' => false,
        'message' => 'Transaction password required'
    ], 400);
}


if (!Hash::check($request->transaction_password, $member->transaction_password)) {
    return response()->json([
        'status' => false,
        'message' => 'Invalid transaction password'
    ], 400);
}

       $kyc = MyKyc::firstOrNew([
    'member_id' => $member->id
]);

$kyc->user_id = $member->user_id;
$kyc->account_beneficiary_name = $request->account_beneficiary_name;
$kyc->account_no = $request->account_no;
$kyc->ifs_code = strtoupper($request->ifs_code);
$kyc->bank_name = $request->bank_name;
$kyc->branch_name = $request->branch_name;
$kyc->aadhaar_number = $request->aadhaar_number;
$kyc->pan_number = strtoupper($request->pan_number);
$kyc->status = 'process';
$kyc->is_latest = 1;

        
       if ($request->hasFile('bank_passbook_image')) {
    $kyc->bank_passbook_image = $request->file('bank_passbook_image')->store('kyc/bank', 'public');
}

if ($request->hasFile('aadhaar_image')) {
    $kyc->aadhaar_image = $request->file('aadhaar_image')->store('kyc/aadhaar', 'public');
}

if ($request->hasFile('pan_image')) {
    $kyc->pan_image = $request->file('pan_image')->store('kyc/pan', 'public');
}

$kyc->save();

        
        

    

        return response()->json([
            'status' => true,
            'message' => 'KYC submitted successfully',
            'kyc' => $kyc
        ]);

    } catch (\Exception $e) {

        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
}

    public function verifyKycPassword(Request $request)
{
    $kyc = MyKyc::where('user_id', $request->user_id)->first();

    if (!$kyc) {
        return response()->json(['error' => 'KYC not found'], 404);
    }

    if (!Hash::check($request->transaction_password, $kyc->transaction_password_hash)) {
        return response()->json(['error' => 'Invalid password'], 401);
    }

    return response()->json([
        'status' => $kyc->status,
        'kyc' => $kyc
    ]);
}
    
    public function getIdCard(Request $request)
    {
        $request->validate(['user_id' => 'required|string']);

        $member = $this->findMemberByUserId($request->user_id);

        if (!$member) {
            return response()->json(['message' => 'Member not found'], 404);
        }

        $idCard = IdCard::where('member_id', $member->id)->first();

        if (!$idCard) {
            return response()->json(['message' => 'ID card photo not found'], 404);
        }

        return response()->json([
            'id_card'   => $idCard,
            'photo_url' => Storage::url($idCard->file_path),
        ]);
    }
    
 public function uploadIdCard(Request $request)
{
    $data = $request->validate([
        'user_id' => 'required|string',
        'photo'   => 'required|image|mimes:jpg,jpeg,png,gif|max:2048',
    ]);

    $member = $this->findMemberByUserId($data['user_id']);

    if (!$member) {
        return response()->json([
            'message' => 'Member not found'
        ], 404);
    }

    $existing = IdCard::where('member_id', $member->id)->first();
 

    // Web root path, set via PUBLIC_HTML_PATH in .env (see config/app.php) NEW ADDED
    $basePath = config('app.public_html_path') . '/';

    // Delete old image
    if (
        $existing &&
        $existing->file_path &&
        file_exists($basePath . $existing->file_path)
    ) {
        unlink($basePath . $existing->file_path);
    }

    $photo = $request->file('photo');

    // Save details before move
    $originalName = $photo->getClientOriginalName();
    $mimeType = $photo->getClientMimeType();
    $fileSize = $photo->getSize();

    // Unique file name
    $fileName = time() . '_' . $originalName;

    // Destination folder
    $destinationPath = $basePath . 'id_cards';

    // Create folder if not exists
    if (!file_exists($destinationPath)) {
        mkdir($destinationPath, 0775, true);
    }

    // Move file
    $photo->move($destinationPath, $fileName);

    // Save path in DB
    $storedPath = 'id_cards/' . $fileName;

    // Save/update DB
    $idCard = IdCard::updateOrCreate(
        ['member_id' => $member->id],
        [
            'user_id'       => $member->user_id,
            'file_path'     => $storedPath,
            'original_name' => $originalName,
            'mime_type'     => $mimeType,
            'file_size'     => $fileSize,
        ]
    );

    return response()->json([
        'message'   => 'ID card photo uploaded successfully',
        'id_card'   => $idCard,
        'photo_url' => asset($storedPath),
    ]);
}
public function matchingStatus(Request $request)
{
    $userId = $request->header('X-Auth-Member');

    $member = Member::where('user_id', $userId)->first();

    if (!$member) {
        return response()->json([
            'message' => 'Member not found'
        ], 404);
    }

    $history = DB::table('weekly_closings')
        ->where('member_id', $member->id)
        ->select([
            'week_start',
            'week_end',
            'carry_left',
            'carry_right',
            'left_bv',
            'right_bv',
            'matched_bv',
            'income',
            'status'
        ])
        ->orderByDesc('id')
        ->get()
        ->map(function ($row) {
            return [
                'match_date' => $row->week_start . ' to ' . $row->week_end,

                'carry_forward_left' => (float) $row->carry_left,
                'carry_forward_right' => (float) $row->carry_right,

                'left_bv' => (float) $row->left_bv,
                'right_bv' => (float) $row->right_bv,

                'matched_pv' => (float) $row->matched_bv,

                'income_generated' => (float) $row->income,

                'status' => $row->status,
            ];
        });

    return response()->json($history);
}

    private function findMemberByUserId(string $userId): ?Member
    {
        return Member::where('user_id', $userId)->first();
    }
    private function getMemberFromHeader(Request $request): ?Member
    {
        $userId = $request->header('X-Auth-Member');

        if (!$userId) {
            return null;
        }

        return Member::where('user_id', $userId)->first();
    }

    private function resolveMemberPackageStep(Member $member): int
    {
        $step = (int) ($member->package_step ?? $member->step_level ?? 0);

        return max(0, min(4, $step));
    }
    private function resolveMemberPackageId(Member $member): ?string
    {
        $step = $this->resolveMemberPackageStep($member);

        return $step > 0 ? 'step-' . $step : null;
    }
    private function findPackageById(string $packageId): ?array
    {
        foreach (self::PACKAGES as $package) {
            if ($package['id'] === $packageId) {
                return $package;
            }
        }

        return null;
    }
    private function findSpotInLeg(int $rootId, string $leg): ?array
    {
        // Walk strictly down the chosen side (left stays left, right stays right)
        // at every level, so a LEFT selection can never land on a RIGHT slot
        // (or vice versa) anywhere in the sponsor's leg chain.
        $currentParentId = $rootId;

        while (true) {
            $child = Member::where('parent_id', $currentParentId)->where('position', $leg)->first();

            if (!$child) {
                return ['parent_id' => $currentParentId, 'position' => $leg];
            }

            $currentParentId = $child->id;
        }
    }
    
    private function countDownline(int $memberId): int
    {
        $count = 0;

        foreach (Member::where('parent_id', $memberId)->get() as $child) {
            $count++;
            $count += $this->countDownline($child->id);
        }

        return $count;
    }
    
    private function isDownline(int $rootId, int $targetId): bool
{
    $children = Member::where('parent_id', $rootId)->get();

    foreach ($children as $child) {

        if ($child->id == $targetId) {
            return true;
        }

        if ($this->isDownline($child->id, $targetId)) {
            return true;
        }
    }

    return false;
}
    
    private function countStep4PlusMembers(int $memberId): int
{
    $count = 0;

    $children = Member::where('parent_id', $memberId)->get();

    foreach ($children as $child) {

        $step = (int) ($child->package_step ?? $child->step_level ?? 0);

        if ($step >= 4) {
            $count++;
        }

        $count += $this->countStep4PlusMembers($child->id);
    }

    return $count;
}

  private function countLegMembers(int $memberId, string $position): int
{
    $child = Member::where('parent_id', $memberId)
        ->where('position', $position)
        ->first();

    if (!$child) {
        return 0;
    }

    return 1 + $this->countDownline($child->id);
}

private function buildTree(int $memberId, int $levels): ?array
{
    if ($levels === 0) return null;

    $member = Member::find($memberId);
    if (!$member) return null;

    // ✅ SAFE KYC
    $kycStatus = 'pending';

    try {
        $kyc = MyKyc::where('member_id', $member->id)
            ->where('is_latest', 1)
            ->latest()
            ->first();

        if ($kyc && $kyc->status) {
            $kycStatus = $kyc->status;
        }
    } catch (\Exception $e) {
        $kycStatus = 'pending';
    }

    $left  = Member::where('parent_id', $memberId)->where('position', 'left')->first();
    $right = Member::where('parent_id', $memberId)->where('position', 'right')->first();
    $leftCount  = $left ? 1 + $this->countDownline($left->id) : 0;
$rightCount = $right ? 1 + $this->countDownline($right->id) : 0;

 return [
    'id'       => $member->id,
    'user_id'  => $member->user_id,
    'fullname' => $member->fullname,
    'status'   => $member->status,
       'left_count'  => $leftCount,
    'right_count' => $rightCount,
'created_at' => $member->created_at,
    'step' => (int) ($member->package_step ?? $member->step_level ?? 0),
    'kyc_status' => $kycStatus,

    'city'        => $member->city,
    'designation' => 'Associate',

    'team_a' => $this->countLegMembers($member->id, 'left'),
'team_b' => $this->countLegMembers($member->id, 'right'),

'self_bv' => (float) ($member->self_bv ?? 0),

'left_bv' => (float) ($member->builtup_left_bv ?? 0),

'right_bv' => (float) ($member->builtup_right_bv ?? 0),

'total_bv' => (float) (
    ($member->self_bv ?? 0) +
    ($member->builtup_left_bv ?? 0) +
    ($member->builtup_right_bv ?? 0)
),

'left'  => $left ? $this->buildTree($left->id, $levels - 1) : null,
'right' => $right ? $this->buildTree($right->id, $levels - 1) : null,

];
}
    private function getAllDownline(int $parentId, string $position, array $visited): \Illuminate\Support\Collection
    {
        if (in_array($parentId, $visited)) {
            return collect();
        }

        $visited[] = $parentId;
        $all       = collect();

        foreach (Member::where('parent_id', $parentId)->where('position', $position)->get() as $member) {
            $all->push($member);
            $all = $all->merge($this->getAllDownline($member->id, 'left',  $visited));
            $all = $all->merge($this->getAllDownline($member->id, 'right', $visited));
        }

        return $all;
    }
    
    
private function getDailyEarn(int $memberId): float
{
    if (!Schema::hasTable('ewallet_logs')) {
        return 0.0;
    }
 
    return (float) DB::table('ewallet_logs')
        ->where('member_id', $memberId)
        ->where('type', 'binary_income')
        ->whereDate('created_at', now()->toDateString())
        ->sum('amount');
}
    
    
private function getRepurchaseBalance(int $memberId, string $userId): float
{
    if (!Schema::hasTable('repurchase_wallet_transactions')) {
        return 0.0;
    }

    return (float) (
        DB::table('repurchase_wallet_transactions')
            ->where('user_id', $memberId)
            ->orderByDesc('id')
            ->value('balance_after') ?? 0
    );
}
    
private function getPurchaseBalance(int $memberId, string $userId): float
{
    if (!Schema::hasTable('balance_requests')) {
        return 0.0;
    }

    $credit = DB::table('balance_requests')
        ->where(function ($q) use ($memberId, $userId) {
            $q->where('member_id', $memberId)
              ->orWhere('member_id', $userId);
        })
        ->where('type', 'purchase')
        ->where('status', 'approved')
        ->where('entry_type', 'credit')
        ->sum('amount');

    $debit = DB::table('balance_requests')
        ->where(function ($q) use ($memberId, $userId) {
            $q->where('member_id', $memberId)
              ->orWhere('member_id', $userId);
        })
        ->where('type', 'purchase')
        ->where('status', 'approved')
        ->where('entry_type', 'debit')
        ->sum('amount');

    return round($credit - $debit, 2);
}

    private function getTurnoverBalance(int $memberId, string $userId): float
    {
        if (!Schema::hasTable('wallets')) {
            return 0.0;
        }

        $query = DB::table('wallets');

        if (Schema::hasColumn('wallets', 'user_id')) {
            $query->where(function ($q) use ($memberId, $userId) {
                $q->where('user_id', $memberId)
                  ->orWhere('user_id', (string) $memberId)
                  ->orWhere('user_id', $userId);
            });
        } elseif (Schema::hasColumn('wallets', 'member_id')) {
            $query->where('member_id', $memberId);
        }

        $wallet = $query->first();

        if (!$wallet) {
            return 0.0;
        }

        $totalIncome    = property_exists($wallet, 'total_income')    ? (float) $wallet->total_income    : 0.0;
        $matchingIncome = property_exists($wallet, 'matching_income') ? (float) $wallet->matching_income : 0.0;
        $royaltyIncome  = property_exists($wallet, 'royalty_income')  ? (float) $wallet->royalty_income  : 0.0;

        return $totalIncome > 0
            ? round($totalIncome, 2)
            : round($matchingIncome + $royaltyIncome, 2);
    }
    private function getPurchaseOrders(int $memberId, string $userId, string $monthStart, string $monthEnd): int
    {
        $table = 'repurchase_wallet_transactions';

        if (!Schema::hasTable($table)) {
            return 0;
        }

        [$idCol, $identifier] = $this->rwTableIdColumn($table, $memberId, $userId);

        $query = DB::table($table)->where($idCol, $identifier);

        if (Schema::hasColumn($table, 'created_at')) {
            $query->whereDate('created_at', '>=', $monthStart)
                  ->whereDate('created_at', '<=', $monthEnd);
        }

        return (int) $query->count();
    }

    private function getSalesData(int $memberId, string $userId, string $monthStart, string $monthEnd): array
    {
        if (!Schema::hasTable('branch_sales')) {
            return ['count' => 0, 'turnover' => 0.0];
        }

        $query = DB::table('branch_sales');

        if (Schema::hasColumn('branch_sales', 'sale_date')) {
            $query->whereDate('sale_date', '>=', $monthStart)
                  ->whereDate('sale_date', '<=', $monthEnd);
        }

        if (Schema::hasColumn('branch_sales', 'member_id')) {
            $query->where('member_id', $memberId);
        } elseif (Schema::hasColumn('branch_sales', 'user_id')) {
            $query->where('user_id', $userId);
        }

        $turnover = Schema::hasColumn('branch_sales', 'sale_amount')
            ? round((float) (clone $query)->sum('sale_amount'), 2)
            : 0.0;

        return ['count' => (int) $query->count(), 'turnover' => $turnover];
    }

    private function getCommissionAmount(int $memberId, string $userId, string $monthStart, string $monthEnd, string $monthKey): float
    {
        $total = 0.0;
        if (Schema::hasTable('loyalty_bonuses') && Schema::hasColumn('loyalty_bonuses', 'bonus_amount')) {
            $q = DB::table('loyalty_bonuses');
            $this->applyMemberFilter($q, 'loyalty_bonuses', $memberId, $userId);

            if (Schema::hasColumn('loyalty_bonuses', 'month_key')) {
                $q->where('month_key', $monthKey);
            }

            $total += (float) $q->sum('bonus_amount');
        }
        if (Schema::hasTable('business_monitoring_bonuses') && Schema::hasColumn('business_monitoring_bonuses', 'bonus_amount')) {
            $q = DB::table('business_monitoring_bonuses');
            $this->applyMemberFilter($q, 'business_monitoring_bonuses', $memberId, $userId);

            if (Schema::hasColumn('business_monitoring_bonuses', 'cycle_date')) {
                $q->whereDate('cycle_date', '>=', $monthStart)
                  ->whereDate('cycle_date', '<=', $monthEnd);
            }

            $total += (float) $q->sum('bonus_amount');
        }

        return round($total, 2);
    }
   private function countActiveDownline(int $memberId): int
{
    $count    = 0;
    $children = Member::where('parent_id', $memberId)->get();
 
    foreach ($children as $child) {
        if ((int) $child->status === 1) {
            $count++;
        }
        $count += $this->countActiveDownline($child->id);
    }
 
    return $count;
}
   private function getLeadershipRank(int $memberId, string $userId): string
{
    $leftChild = Member::where('parent_id', $memberId)
        ->where('position', 'left')
        ->first();

    $rightChild = Member::where('parent_id', $memberId)
        ->where('position', 'right')
        ->first();

    $leftActiveCount = 0;

    if ($leftChild) {
        if ((int) $leftChild->status === 1) {
            $leftActiveCount++;
        }

        $leftActiveCount += $this->countActiveDownline($leftChild->id);
    }

    $rightActiveCount = 0;

    if ($rightChild) {
        if ((int) $rightChild->status === 1) {
            $rightActiveCount++;
        }

        $rightActiveCount += $this->countActiveDownline($rightChild->id);
    }

    if ($leftActiveCount >= 12 && $rightActiveCount >= 12) {
        return 'Regional Manager';
    }

    if ($leftActiveCount >= 7 && $rightActiveCount >= 7) {
        return 'Zonal Manager';
    }

    if ($leftActiveCount >= 3 && $rightActiveCount >= 3) {
        return 'Area Manager';
    }

    if ($leftActiveCount >= 1 && $rightActiveCount >= 1) {
        return 'Manager';
    }

    return 'None';
}
    private function getRankWithReward(int $memberId, string $userId): string
    {
        $total = $this->getEarningBalance($memberId, $userId);

        $tiers = [
            ['rank' => 'Rising Star', 'target' => 5000],
            ['rank' => 'Bronze', 'target' => 10000],
            ['rank' => 'Silver', 'target' => 20000],
            ['rank' => 'Gold', 'target' => 45000],
            ['rank' => 'Platinum', 'target' => 100000],
            ['rank' => 'Ruby', 'target' => 500000],
            ['rank' => 'Sapphire', 'target' => 1100000],
            ['rank' => 'Emerald', 'target' => 2500000],
            ['rank' => 'Diamond', 'target' => 5100000],
        ];

        $achieved = 'N/A';

        foreach ($tiers as $tier) {
            if ($total >= $tier['target']) {
                $achieved = $tier['rank'];
            }
        }

        return $achieved;
    }
    
    


public function getMemberInfo($member_id)
{
    $member = DB::table('members')
        ->where('user_id', $member_id)
        ->first();

    if (!$member) {
        return response()->json([
            'status' => false,
            'message' => 'Member not found'
        ], 404);
    }

    
    $shippingAddress = trim(implode(', ', array_filter([
        $member->shipping_address,
        $member->shipping_district,
        $member->shipping_city,
        $member->shipping_state,
        $member->shipping_pin_code
    ])));

   
    $normalAddress = trim(implode(', ', array_filter([
        $member->address,
        $member->district,
        $member->city,
        $member->state,
        $member->pin_code
    ])));

   
    $finalAddress = $shippingAddress ?: $normalAddress;

    return response()->json([
        'status' => true,
        'member_id' => $member->user_id,
        'fullname' => $member->fullname,
        'mobile' => $member->mobile_no,

        
        'address' => $finalAddress
    ]);
}
    
    
private function getConsistencyBalance(int $memberId, string $userId): float
{
    if (!Schema::hasTable('consistency_wallet')) {
        return 0.0;
    }

    $query = DB::table('consistency_wallet')
        ->where('user_id', $memberId); 

    $totalCredit = (float) (clone $query)->sum('credit');
    $totalDebit  = (float) (clone $query)->sum('debit');

    return round($totalCredit - $totalDebit, 2);
}

private function getCashbackBalance(int $memberId): float
{
    if (!Schema::hasTable('cashback_wallet')) {
        return 0.0;
    }

    $query = DB::table('cashback_wallet')
        ->where('user_id', $memberId);

    $credit = (float) (clone $query)->sum('credit');
    $debit  = (float) (clone $query)->sum('debit');

    return round($credit - $debit, 2);
}

   private function getEarningBalance(int $memberId, string $userId): float
{
    if (!Schema::hasTable('ewallet_logs')) {
        return 0.0;
    }
 
    // ✅ Currently: Group Builtup (binary_income) only
    // Later: add more ->orWhere('type', 'other_type') as needed
    $total = (float) DB::table('ewallet_logs')
        ->where('member_id', $memberId)
        ->where('type', 'binary_income')
        ->sum('amount');
 
    return round($total, 2);
}
    
    private function getLeftRightTotalEarn(int $memberId): float
{
    $leftChild = Member::where('parent_id', $memberId)
        ->where('position', 'left')
        ->first();

    $rightChild = Member::where('parent_id', $memberId)
        ->where('position', 'right')
        ->first();

    $leftEarn = 0;
    $rightEarn = 0;

    // LEFT TEAM
    if ($leftChild) {

        $leftIds = $this->getDownlineIds($leftChild->id);

        $leftEarn = DB::table('wallets')
            ->whereIn('user_id', $leftIds)
            ->sum('total_income');
    }

    // RIGHT TEAM
    if ($rightChild) {

        $rightIds = $this->getDownlineIds($rightChild->id);

        $rightEarn = DB::table('wallets')
            ->whereIn('user_id', $rightIds)
            ->sum('total_income');
    }

    return round($leftEarn + $rightEarn, 2);
}

private function getDownlineIds($parentId): array
{
    $ids = [$parentId];

    $children = Member::where('parent_id', $parentId)->get();

    foreach ($children as $child) {

        $ids = array_merge(
            $ids,
            $this->getDownlineIds($child->id)
        );
    }

    return $ids;
}
    
    private function getDirectIdCount(int $memberId): int
    {
        return (int) Member::where('sponsor_id', $memberId)->count();
    }
    
    private function getDirectBranchCount(int $memberId): int
    {
        return (int) Member::where('sponsor_id', $memberId)
            ->whereNotNull('position')
            ->distinct('position')
            ->count('position');
    }
    private function rwTableIdColumn(string $table, int $memberId, string $userId): array
    {
        if (Schema::hasColumn($table, 'user_id')) {
            $columnType = Schema::getColumnType($table, 'user_id');
            $numericTypes = ['bigint', 'integer', 'int', 'mediumint', 'smallint', 'tinyint', 'decimal', 'float'];

            return in_array(strtolower($columnType), $numericTypes, true)
                ? ['user_id', $memberId]
                : ['user_id', $userId];
        }

        return ['member_id', $memberId];
    }
    private function applyMemberFilter(\Illuminate\Database\Query\Builder $query, string $table, int $memberId, string $userId): void
    {
        if (Schema::hasColumn($table, 'member_id')) {
            $query->where('member_id', $memberId);
        } elseif (Schema::hasColumn($table, 'user_id')) {
            $query->where('user_id', $userId);
        }
    }
    
     public function adminTree(Request $request)
{
    // ✅ If user_id given → use it
    if ($request->filled('user_id')) {
        $member = Member::where('user_id', $request->user_id)->first();
    } else {
        // ✅ No user_id → take ADMIN ROOT
        $member = Member::whereNull('parent_id')->first();
    }

    if (!$member) {
        return response()->json(['message' => 'Member not found'], 404);
    }

    return response()->json([
        'tree' => $this->buildTree($member->id, 10) // 🔥 increase depth
    ]);
}
    
    public function getReferralDownline(Request $request)
{
    $userId = $request->header('X-Auth-Member') ?: $request->query('user_id');
 
    if (!$userId) {
        return response()->json(['message' => 'Missing member identifier'], 401);
    }
 
    $member = Member::where('user_id', $userId)->first();
 
    if (!$member) {
        return response()->json(['message' => 'Member not found'], 404);
    }
 
    $allDownline = $this->getAllReferralDownline($member->id);
 
    $result = $allDownline->map(function ($m) {
        // Map package_step to package label
      $packageLabels = [
    1 => '1 Step (125 BV)',
    2 => '2 Step (250 BV)',
    3 => '3 Step (500 BV)',
    4 => '4 Step (1000 BV)',
];
 
        $step = (int) ($m->package_step ?? 0);
 
        return [
            'id'               => $m->id,
            'user_id'          => $m->user_id,
            'fullname'         => $m->fullname,
            'sponsor_id'       => $m->sponsor_id,
            'parent_id'        => $m->parent_id,
            'position'         => $m->position,
            'package_step'     => $step,
            'package_label'    => $packageLabels[$step] ?? '--',
            'state'            => $m->state,
            'city'             => $m->city,
            'sponsored_side'   => $m->position,
            'activation_date'  => $m->activation_date,
            'created_at'       => $m->created_at,
            'is_active'        => (int) ($m->status ?? 0),
        ];
    });
 
    return response()->json(['rows' => $result]);
}
 
// ============================================================
// STEP 2: Add this PRIVATE HELPER METHOD too (same place)
// ============================================================
 
private function getAllReferralDownline(int $rootId): \Illuminate\Support\Collection
{
    $all     = collect();
    $queue   = [$rootId];
    $visited = [];
 
    while (!empty($queue)) {
        $currentId = array_shift($queue);
 
        if (in_array($currentId, $visited)) continue;
        $visited[] = $currentId;
 
        $directReferrals = Member::where('sponsor_id', $currentId)
            ->where('id', '!=', $currentId)
            ->get();
 
        foreach ($directReferrals as $m) {
            $all->push($m);
            $queue[] = $m->id;
        }
    }
 
    return $all;
}

// Tree auto login
public function autoLogin($userId)
{
    $member = Member::where('user_id', $userId)->first();

    if (!$member) {
        return response()->json([
            'status' => false,
            'message' => 'Member not found'
        ]);
    }

    return response()->json([
        'status' => true,
        'member' => [
            'id' => $member->id,
            'user_id' => $member->user_id,
            'fullname' => $member->fullname,
            'status' => $member->status,
            'package_step' => $member->package_step,
            'step_level' => $member->step_level,
            'created_at' => $member->created_at,
            'activation_date' => $member->activation_date,
        ],
        'redirect_url' => url('/member/dashboard')
    ]);
}
// Forogot PAssword logic 

public function forgotPassword(Request $request)
{
    $request->validate([
        'identifier' => 'required|string',
    ]);

    $member = Member::where('user_id', $request->identifier)
        ->orWhere('mobile_no', $request->identifier)
        ->orWhere('email', $request->identifier)
        ->first();

    if (!$member) {
        return response()->json([
            'message' => 'Member not found'
        ], 404);
    }

    if (!$member->email) {
        return response()->json([
            'message' => 'No email is registered on this account. Please contact support.'
        ], 422);
    }

    $otp = rand(100000, 999999);

    $member->update([
        'forgot_otp' => $otp,
        'forgot_otp_expiry' => now()->addMinutes(10),
    ]);

    try {
        Mail::raw(
            "Your Fourstep password reset code is: {$otp}\n\nThis code expires in 10 minutes. If you did not request this, please ignore this email.",
            function ($message) use ($member) {
                $message->to($member->email)->subject('Fourstep Password Reset Code');
            }
        );
    } catch (\Throwable $e) {
        return response()->json([
            'message' => 'Could not send the verification email. Please try again.'
        ], 500);
    }

    return response()->json([
        'message' => 'A verification code has been sent to your registered email.',
        'email'   => $this->maskEmail($member->email),
    ] + (config('app.debug') ? ['otp' => $otp] : []));
}

private function maskEmail(string $email): string
{
    [$name, $domain] = array_pad(explode('@', $email, 2), 2, '');
    $len = strlen($name);
    $masked = $len <= 2
        ? str_repeat('*', $len)
        : substr($name, 0, 2) . str_repeat('*', max($len - 3, 1)) . substr($name, -1);

    return $masked . '@' . $domain;
}

public function verifyForgotOtp(Request $request)
{
    $request->validate([
        'identifier' => 'required|string',
        'otp' => 'required',
    ]);

    $member = Member::where('user_id', $request->identifier)
        ->orWhere('mobile_no', $request->identifier)
        ->orWhere('email', $request->identifier)
        ->first();

    if (!$member) {
        return response()->json([
            'message' => 'Member not found'
        ], 404);
    }

    if (
        !$member->forgot_otp ||
        $member->forgot_otp != $request->otp ||
        now()->gt($member->forgot_otp_expiry)
    ) {
        return response()->json([
            'message' => 'Invalid or expired OTP'
        ], 422);
    }

    return response()->json([
        'message' => 'OTP verified successfully'
    ]);
}

public function resetPassword(Request $request)
{
    $request->validate([
        'identifier' => 'required|string',
        'otp' => 'required',
        'password' => 'required|min:6',
    ]);

    $member = Member::where('user_id', $request->identifier)
        ->orWhere('mobile_no', $request->identifier)
        ->orWhere('email', $request->identifier)
        ->first();

    if (!$member) {
        return response()->json([
            'message' => 'Member not found'
        ], 404);
    }

    if (
        !$member->forgot_otp ||
        $member->forgot_otp != $request->otp ||
        now()->gt($member->forgot_otp_expiry)
    ) {
        return response()->json([
            'message' => 'Invalid or expired OTP'
        ], 422);
    }
    if (Hash::check($request->password, $member->password)) {
    return response()->json([
        'message' => 'New password cannot be the same as current password'
    ], 422);
}

    $member->update([
        'password' => Hash::make($request->password),
        'forgot_otp' => null,
        'forgot_otp_expiry' => null,
    ]);

    return response()->json([
        'message' => 'Password reset successfully'
    ]);
}

}
