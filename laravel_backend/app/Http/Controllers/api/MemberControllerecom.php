<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Memberecom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\Member;

class MemberControllerecom extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'firstName' => 'required',
            'lastName'  => 'required',
            'dob'       => 'required|date',
            'gender'    => 'required',
            'email'     => 'required|email|unique:ecom_members,email',
            'mobileNo' => [
    'required',
    'digits:10',
    'regex:/^[6-9][0-9]{9}$/',
    'unique:ecom_members,mobile_no',
],
            'address'   => 'required',
            'pinCode'   => 'required|digits:6',
            'state'     => 'required',
            'city'      => 'required',
            'password'  => 'required|min:6|confirmed',
        ]);

        $member = Memberecom::create([
            'member_id'  => 'FRL' . rand(100000, 999999),
            'fullname'   => $request->firstName . ' ' . $request->lastName,
            'dob'        => $request->dob,
            'gender'     => $request->gender,
            'email'      => $request->email,
            'mobile_no'  => $request->mobileNo,
            'address'    => $request->address,
            'pin_code'   => $request->pinCode,
            'state'      => $request->state,
            'city'       => $request->city,
            'password'   => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'Signup successful',
            'data'    => $member,
        ], 201);
    }

   

public function login(Request $request)
{
    // ✅ Flexible validation
    $request->validate([
        'password' => 'required',
    ]);

    // ✅ Accept user_id / mobile_no / email
    $input = $request->user_id 
        ?? $request->mobile_no 
        ?? $request->email;

    // =========================
    // 🔹 1. CHECK MLM MEMBERS
    // =========================
    $member = Member::where('mobile_no', $input)
        ->orWhere('user_id', $input)
        ->orWhere('email', $input)
        ->first();

    if ($member && Hash::check($request->password, $member->password)) {

    /*
    |--------------------------------------------------------------------------
    | WEEKLY MLM CYCLE LOCK
    |--------------------------------------------------------------------------
    | Sunday 12:01 AM to 12:10 AM = MLM LOGIN BLOCKED
    | 12:11 AM onwards = LOGIN AVAILABLE
    |
    | This applies ONLY to MLM members.
    | Normal E-commerce members are not affected.
    | Shoppee login is not affected.
    |--------------------------------------------------------------------------
    */

    $now = \Carbon\Carbon::now();

    if (
        $now->dayOfWeek === \Carbon\Carbon::SUNDAY &&
        $now->format('H:i') >= '00:00' &&
        $now->format('H:i') <= '00:11'
    ) {
        return response()->json([
            'status' => false,
            'cycle_locked' => true,
            'message' => 'Weekly cycle is running. MLM member login is temporarily unavailable. Please try again after 12:10 AM.'
        ], 503);
    }
        if ($member->account_status === 'blocked') {
            return response()->json([
                'status' => false,
                'message' => 'Access denied. Your account is blocked.'
            ], 403);
        }

        return response()->json([
            'status' => true,
            'type' => 'member',
            'data' => [
                'id' => $member->id,
                'member_id' => $member->user_id,
                'fullname' => $member->fullname,
                'mobile_no' => $member->mobile_no,
                'email' => $member->email,
                    'address' => $member->address,
        'shipping_address' => $member->shipping_address,
            ]
        ]);
    }

    // =========================
    // 🔹 2. CHECK ECOM MEMBERS
    // =========================
    $ecomUser = Memberecom::where('mobile_no', $input)
        ->orWhere('member_id', $input)
        ->orWhere('email', $input)
        ->first();

    if ($ecomUser && Hash::check($request->password, $ecomUser->password)) {

        $type = str_starts_with($ecomUser->member_id, 'GUEST') ? 'guest' : 'member';

        return response()->json([
            'status' => true,
            'type' => $type,
            'data' => $ecomUser
        ]);
    }

    // =========================
    // ❌ INVALID LOGIN
    // =========================
    return response()->json([
        'status' => false,
        'message' => 'Invalid Mobile Number / Member ID / Email or Password'
    ], 401);
}
  
    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $otp = rand(100000, 999999);

        \DB::table('otp_verifications')->updateOrInsert(
            ['email' => $request->email],
            [
                'otp'        => $otp,
                'expires_at' => now()->addMinutes(10),
                'created_at' => now(),
            ]
        );

        \Mail::raw("Your 4Step OTP is: $otp\n\nThis code expires in 10 minutes.", function ($message) use ($request) {
            $message->to($request->email)->subject('Your 4Step OTP Code');
        });

            return response()->json([
        'message' => 'OTP sent successfully',
        'otp' => $otp
         ]);
    }

    /**
     * Verify OTP and return / create a guest ecom_members record.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required',
        ]);

        $record = \DB::table('otp_verifications')
            ->where('email', $request->email)
            ->first();

        if (!$record || $record->otp != $request->otp) {
            return response()->json(['message' => 'Invalid OTP'], 400);
        }

        if (now()->gt($record->expires_at)) {
            return response()->json(['message' => 'OTP expired'], 400);
        }

        // Get or create ecom member record for this email
        $user = Memberecom::where('email', $request->email)->first();

        if (!$user) {
            $user = Memberecom::create([
                'member_id' => 'GUEST' . rand(100000, 999999),
                'email'     => $request->email,
                'fullname'  => '',
                'password'  => Hash::make(\Str::random(16)),
            ]);
        }

        // Clean up OTP
        \DB::table('otp_verifications')->where('email', $request->email)->delete();

        return response()->json([
            'message' => 'OTP verified',
            'user'    => $user,
        ]);
    }

    /**
     * Get member info by member_id (user_id in MLM table).
     * Used for "order for another member" feature.
     */
    public function memberInfo($memberId)
    {
        $member = Member::where('user_id', $memberId)->first();

        if (!$member) {
            return response()->json(['message' => 'Member not found'], 404);
        }

        return response()->json([
            'fullname'         => $member->fullname,
            'address'          => $member->address,
            'shipping_address' => $member->shipping_address ?? null,
            'mobile_no'        => $member->mobile_no,
        ]);
    }

   public function update(Request $request, $member_id)
{
    $member = Memberecom::where('member_id', $member_id)->first();

    // if ecommerce member not found
   if (!$member) {

    // try using same mobile number
    $mlmMember = Member::where('user_id', $member_id)->first();

    $member = Memberecom::where('mobile_no', optional($mlmMember)->mobile_no)
        ->orWhere('email', optional($mlmMember)->email)
        ->first();

    if (!$member) {

        return response()->json([
            'status' => false,
            'message' => 'Ecommerce profile not found'
        ], 404);
    }
}

    $request->validate([
        'fullname'  => 'required|string|max:255',
        'dob'       => 'nullable|date',
        'gender'    => 'nullable|in:Male,Female,Other',
        'email'     => [
            'required',
            'email',
            Rule::unique('ecom_members', 'email')->ignore($member->id)
        ],
        'mobile_no' => [
            'required',
            'digits:10',
            Rule::unique('ecom_members', 'mobile_no')->ignore($member->id)
        ],
        'address'   => 'nullable|string',
        'pin_code'  => 'nullable|digits:6',
        'state'     => 'nullable|string',
        'city'      => 'nullable|string',
//         'password' => 'nullable|min:6',
// 'confirm_password' => 'nullable|min:6',
    
    ]);
    
 /*
|--------------------------------------------------------------------------
| PASSWORD LOGIC TEMPORARILY DISABLED
|--------------------------------------------------------------------------
*/
// if ($request->password) {

//     if ($request->password !== $request->confirm_password) {

//         return response()->json([
//             'status' => false,
//             'message' => 'Passwords do not match'
//         ], 400);
//     }

//     $member->password = \Hash::make($request->password);

//     $member->save();
// }

$member->update([
    'fullname' => $request->fullname,
    'dob' => $request->dob,
    'gender' => $request->gender,
    'email' => $request->email,
    'mobile_no' => $request->mobile_no,
    'address' => $request->address,
    'pin_code' => $request->pin_code,
    'state' => $request->state,
    'city' => $request->city,
]);

// ALSO UPDATE MLM MEMBERS TABLE
Member::where('user_id', $member_id)->update([
    'fullname' => $request->fullname,
    'dob' => $request->dob,
    'gender' => $request->gender,
    'email' => $request->email,
    'mobile_no' => $request->mobile_no,
    'address' => $request->address,
    'pin_code' => $request->pin_code,
    'state' => $request->state,
    'city' => $request->city,
]);

    return response()->json([
        'status' => true,
        'message' => 'Profile updated successfully',
        'data' => $member
    ]);
}
public function show($member_id)
{
    try {

        // MLM MEMBER
        $mlmMember = Member::where('user_id', $member_id)->first();

        // ECOM MEMBER
        $ecomMember = Memberecom::where('member_id', $member_id)->first();

        // IF NOT FOUND
        if (!$mlmMember && !$ecomMember) {

            return response()->json([
                'status' => false,
                'message' => 'Member not found'
            ], 404);
        }

        return response()->json([
            'status' => true,

            'data' => [

                'member_id' => $ecomMember->member_id
                    ?? $mlmMember->user_id
                    ?? '',

                'fullname' => $ecomMember && $ecomMember->fullname
                    ? $ecomMember->fullname
                    : ($mlmMember->fullname ?? ''),

                'dob' => $ecomMember && $ecomMember->dob
                    ? $ecomMember->dob
                    : ($mlmMember->dob ?? ''),

                'gender' => $ecomMember && $ecomMember->gender
                    ? $ecomMember->gender
                    : ($mlmMember->gender ?? ''),

                'email' => $ecomMember && $ecomMember->email
                    ? $ecomMember->email
                    : ($mlmMember->email ?? ''),

                'mobile_no' => $ecomMember && $ecomMember->mobile_no
                    ? $ecomMember->mobile_no
                    : ($mlmMember->mobile_no ?? ''),

                'address' => $ecomMember && $ecomMember->address
                    ? $ecomMember->address
                    : ($mlmMember->address ?? ''),

                'pin_code' => $ecomMember && $ecomMember->pin_code
                    ? $ecomMember->pin_code
                    : ($mlmMember->pin_code ?? ''),

                'state' => $ecomMember && $ecomMember->state
                    ? $ecomMember->state
                    : ($mlmMember->state ?? ''),

                'city' => $ecomMember && $ecomMember->city
                    ? $ecomMember->city
                    : ($mlmMember->city ?? ''),
            ]
        ]);

    } catch (\Exception $e) {

        return response()->json([
            'status' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}

public function forgotPassword(Request $request)
{
    $request->validate([
        'identifier' => 'required|string',
    ]);

    // Check MLM Member
    $user = Member::where('user_id', $request->identifier)
        ->orWhere('mobile_no', $request->identifier)
        ->orWhere('email', $request->identifier)
        ->first();

    // Check Ecommerce Member
    if (!$user) {
        $user = Memberecom::where('member_id', $request->identifier)
            ->orWhere('mobile_no', $request->identifier)
            ->orWhere('email', $request->identifier)
            ->first();
    }

    if (!$user) {
        return response()->json([
            'message' => 'User not found'
        ], 404);
    }

    if (!$user->email) {
        return response()->json([
            'message' => 'No email is registered on this account. Please contact support.'
        ], 422);
    }

    $otp = rand(100000, 999999);

    $user->forgot_otp = $otp;
    $user->forgot_otp_expiry = now()->addMinutes(10);
    $user->save();

    try {
        \Mail::raw(
            "Your Fourstep password reset code is: {$otp}\n\nThis code expires in 10 minutes. If you did not request this, please ignore this email.",
            function ($message) use ($user) {
                $message->to($user->email)->subject('Fourstep Password Reset Code');
            }
        );
    } catch (\Throwable $e) {
        return response()->json([
            'message' => 'Could not send the verification email. Please try again.'
        ], 500);
    }

    return response()->json([
        'message' => 'A verification code has been sent to your registered email.',
        'email'   => $this->maskEmail($user->email),
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
        'otp' => 'required'
    ]);

    // Check MLM Member
    $user = Member::where('user_id', $request->identifier)
        ->orWhere('mobile_no', $request->identifier)
        ->orWhere('email', $request->identifier)
        ->first();

    // Check Ecommerce Member
    if (!$user) {
        $user = Memberecom::where('member_id', $request->identifier)
            ->orWhere('mobile_no', $request->identifier)
            ->orWhere('email', $request->identifier)
            ->first();
    }

    if (!$user) {
        return response()->json([
            'message' => 'User not found'
        ], 404);
    }

    if (
        !$user->forgot_otp ||
        $user->forgot_otp != $request->otp
    ) {
        return response()->json([
            'message' => 'Invalid OTP'
        ], 422);
    }

    if (
        $user->forgot_otp_expiry &&
        now()->gt($user->forgot_otp_expiry)
    ) {
        return response()->json([
            'message' => 'OTP expired'
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

    // Check MLM Member
    $user = Member::where('user_id', $request->identifier)
        ->orWhere('mobile_no', $request->identifier)
        ->orWhere('email', $request->identifier)
        ->first();

    // Check Ecommerce Member
    if (!$user) {
        $user = Memberecom::where('member_id', $request->identifier)
            ->orWhere('mobile_no', $request->identifier)
            ->orWhere('email', $request->identifier)
            ->first();
    }

    if (!$user) {
        return response()->json([
            'message' => 'User not found'
        ], 404);
    }

    if (
        !$user->forgot_otp ||
        $user->forgot_otp != $request->otp
    ) {
        return response()->json([
            'message' => 'Invalid OTP'
        ], 422);
    }

    if (
        $user->forgot_otp_expiry &&
        now()->gt($user->forgot_otp_expiry)
    ) {
        return response()->json([
            'message' => 'OTP expired'
        ], 422);
    }

    $user->password = Hash::make($request->password);
    $user->forgot_otp = null;
    $user->forgot_otp_expiry = null;
    $user->save();

    return response()->json([
        'message' => 'Password reset successfully'
    ]);
}

}