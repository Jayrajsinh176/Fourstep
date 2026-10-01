<?php
namespace App\Http\Controllers\Shoppee;
use App\Http\Controllers\Controller;
use App\Models\Shoppee_Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
class MemberController extends Controller
{
    // SIGNUP
    public function store(Request $request)
    {
        $validated = $request->validate([
            
        'fullname' => ['required','min:3','max:100','regex:/^[A-Za-z ]+$/'],

        'user_pan' => ['required','regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/'],
        
        'aadhaar_no' => ['required','regex:/^[2-9][0-9]{11}$/'],

        'user_address' => 'required|min:10|max:500',

'branch_name' => 'required|min:3|max:150',

'branch_type' => 'required|in:Mega Branch,Mini Branch,Area Branch',

'branch_pan' => [
    'nullable',
    'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/'
],

'dob' => 'required|date|before:today',

'gst_no' => [
    'required',
    'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[A-Z0-9]{3}$/'
],

'email' => 'required|email:rfc,dns|unique:shoppee_members,email',

'mobile_no' => [
    'required',
    'regex:/^[6-9][0-9]{9}$/',
    'unique:shoppee_members,mobile_no',
],

'password' => [
    'required',
    'min:6',
    'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/'
],

'address' => 'required|min:10|max:500',

'pin_code' => [
    'required',
    'regex:/^[1-9][0-9]{5}$/'
],

'state' => 'required|string|max:100',
'city' => 'required|string|max:100',
'district' => 'required|string|max:100',
        ]);
        $validated['password'] = Hash::make($validated['password']);
        $member = Shoppee_Member::create($validated);
        return response()->json([
            'message' => 'Signup successful',
            'data' => $member
        ], 201);
    }
    // LOGIN
   public function login(Request $request)
{
    $request->validate([
        'login_id' => 'required',
        'password' => 'required'
    ]);

    $member = Shoppee_Member::where('member_id', $request->login_id)
        ->orWhere('email', $request->login_id)
        ->orWhere('mobile_no', $request->login_id)
        ->first();

    if (!$member) {
        return response()->json([
            'message' => 'Invalid login details'
        ], 401);
    }

    if (!Hash::check($request->password, $member->password)) {
        return response()->json([
            'message' => 'Invalid login details'
        ], 401);
    }

    return response()->json([
        'message' => 'Login successful',
        'data' => $member
    ], 200);
}
    
    // FORGOT PASSWORD - SEND OTP
public function forgotPassword(Request $request)
{
    $request->validate([
        'member_id' => 'required'
    ]);

    $member = Shoppee_Member::where('member_id', $request->member_id)->first();

    if (!$member) {
        return response()->json([
            'success' => false,
            'message' => 'Member not found'
        ], 404);
    }

    $otp = rand(100000, 999999);

    $member->update([
        'forgot_password_otp' => $otp,
        'otp_expiry' => now()->addMinutes(10),
    ]);

    return response()->json([
        'success' => true,
        'message' => 'OTP sent successfully',
        'otp' => $otp
    ]);
}

// VERIFY OTP
public function verifyOtp(Request $request)
{
    $request->validate([
        'member_id' => 'required',
        'otp' => 'required'
    ]);

    $member = Shoppee_Member::where('member_id', $request->member_id)
        ->where('forgot_password_otp', $request->otp)
        ->first();

    if (!$member) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid OTP'
        ], 400);
    }

    if (now()->gt($member->otp_expiry)) {
        return response()->json([
            'success' => false,
            'message' => 'OTP expired'
        ], 400);
    }

    return response()->json([
        'success' => true,
        'message' => 'OTP verified'
    ]);
}

// RESET PASSWORD
public function resetPassword(Request $request)
{
    $request->validate([
        'member_id' => 'required',
        'otp' => 'required',
        'password' => 'required|min:6'
    ]);

    $member = Shoppee_Member::where('member_id', $request->member_id)
        ->where('forgot_password_otp', $request->otp)
        ->first();

    if (!$member) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid OTP'
        ], 400);
    }

    if (now()->gt($member->otp_expiry)) {
        return response()->json([
            'success' => false,
            'message' => 'OTP expired'
        ], 400);
    }
    
      // SAME PASSWORD CHECK
    if (Hash::check($request->password, $member->password)) {
        return response()->json([
            'success' => false,
            'message' => 'New password cannot be the same as current password'
        ], 400);
    }

    $member->update([
        'password' => Hash::make($request->password),
        'forgot_password_otp' => null,
        'otp_expiry' => null,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Password changed successfully'
    ]);
}
    // UPDATE PROFILE
   public function updateProfile(Request $request, $id)
{
    $member = Shoppee_Member::find($id);

    if (!$member) {
        return response()->json([
            'message' => 'User not found'
        ], 404);
    }

    $request->validate([
        'mobile_no' => 'required|digits:10|unique:shoppee_members,mobile_no,' . $member->id,
        'address' => 'required|min:10|max:500',
        'state' => 'required',
        'city' => 'required',
        'district' => 'required',
        'pin_code' => 'required|digits:6',
    ]);

    $member->update([
        'mobile_no' => $request->mobile_no,
        'address' => $request->address,
        'state' => $request->state,
        'city' => $request->city,
        'district' => $request->district,
        'pin_code' => $request->pin_code,
    ]);

    return response()->json([
        'message' => 'Profile updated successfully',
        'data' => $member
    ]);
}

public function adminAutoLogin($id)
{
    $member = Shoppee_Member::find($id);

    if (!$member) {
        return response()->json([
            'status' => false,
            'message' => 'Member not found'
        ], 404);
    }

    return response()->json([
        'status' => true,
        'data' => $member
    ]);
}

// Transaction Password Method

public function transactionPasswordStatus(Request $request)
{
    $member = Shoppee_Member::find($request->id);

    if (!$member) {
        return response()->json([
            'success' => false,
            'message' => 'Member not found'
        ]);
    }

    return response()->json([
        'success' => true,
        'hasPassword' => !empty($member->transaction_password)
    ]);
}

public function createTransactionPassword(Request $request)
{
    $request->validate([
        'id' => 'required',
        'password' => 'required|min:6|confirmed'
    ]);

    $member = Shoppee_Member::find($request->id);

    if (!$member) {
        return response()->json([
            'success' => false,
            'message' => 'Member not found'
        ]);
    }

    $member->transaction_password = Hash::make($request->password);
    $member->save();

    return response()->json([
        'success' => true,
        'message' => 'Transaction password created successfully'
    ]);
}

public function sendTransactionPasswordOtp(Request $request)
{
    $member = Shoppee_Member::find($request->id);

    if (!$member) {
        return response()->json([
            'success' => false,
            'message' => 'Member not found'
        ]);
    }

    $otp = rand(100000, 999999);

    $member->update([
        'transaction_password_otp' => $otp,
        'transaction_password_otp_expiry' => now()->addMinutes(10)
    ]);

    return response()->json([
        'success' => true,
        'message' => 'OTP sent successfully',
        'otp' => $otp
    ]);
}

public function updateTransactionPassword(Request $request)
{
    $request->validate([
        'id' => 'required',
        'otp' => 'required',
        'password' => 'required|min:6|confirmed'
    ]);

    $member = Shoppee_Member::find($request->id);

    if (!$member) {
        return response()->json([
            'success' => false,
            'message' => 'Member not found'
        ]);
    }

    if ($member->transaction_password_otp != $request->otp) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid OTP'
        ]);
    }

    if (now()->gt($member->transaction_password_otp_expiry)) {
        return response()->json([
            'success' => false,
            'message' => 'OTP expired'
        ]);
    }

    if (
        !empty($member->transaction_password) &&
        Hash::check(
            $request->password,
            $member->transaction_password
        )
    ) {
        return response()->json([
            'success' => false,
            'message' => 'New transaction password cannot be same as current password'
        ]);
    }

    $member->update([
        'transaction_password' => Hash::make($request->password),
        'transaction_password_otp' => null,
        'transaction_password_otp_expiry' => null
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Transaction password updated successfully'
    ]);
}
}