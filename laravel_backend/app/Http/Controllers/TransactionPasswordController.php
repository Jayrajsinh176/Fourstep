<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Member;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class TransactionPasswordController extends Controller
{
    private function getMember($request)
    {
        $member = Member::where('user_id', $request->user_id)->first();

        if (!$member) {
            return response()->json([
                'status' => false,
                'message' => 'Member not found'
            ], 404);
        }

        return $member;
    }

    public function status(Request $request)
    {
        $member = $this->getMember($request);

        if ($member instanceof \Illuminate\Http\JsonResponse) return $member;

        return response()->json([
            'status' => true,
            'hasPassword' => !empty($member->transaction_password),
        ]);
    }

    public function create(Request $request)
    {
        $member = $this->getMember($request);
        if ($member instanceof \Illuminate\Http\JsonResponse) return $member;

        if ($member->transaction_password) {
            return response()->json([
                'status' => false,
                'message' => 'Transaction password already set'
            ], 400);
        }

        $request->validate([
            'password' => 'required|min:4|confirmed',
        ]);

        $member->transaction_password = Hash::make($request->password);
        $member->save();

        return response()->json([
            'status' => true,
            'message' => 'Transaction password created successfully'
        ]);
    }
    
    public function sendOtp(Request $request)
{
    $member = $this->getMember($request);

    if ($member instanceof \Illuminate\Http\JsonResponse) {
        return $member;
    }

    if (!$member->transaction_password) {
        return response()->json([
            'status' => false,
            'message' => 'Please set transaction password first'
        ], 400);
    }

    if (!$member->email) {
        return response()->json([
            'status' => false,
            'message' => 'No email is registered on this account. Please contact support.'
        ], 422);
    }

    $otp = rand(100000, 999999);

    // store otp in database
    $member->transaction_otp = $otp;
    $member->transaction_otp_expiry = now()->addMinutes(5);
    $member->save();

    try {
        Mail::raw(
            "Your Fourstep transaction password OTP is: {$otp}\n\nThis code expires in 5 minutes. If you did not request this, please ignore this email.",
            function ($message) use ($member) {
                $message->to($member->email)->subject('Fourstep Transaction Password OTP');
            }
        );
    } catch (\Throwable $e) {
        return response()->json([
            'status' => false,
            'message' => 'Could not send the OTP email. Please try again.'
        ], 500);
    }

    return response()->json([
        'status' => true,
        'message' => 'An OTP has been sent to your registered email.',
        'email' => $this->maskEmail($member->email),
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

  public function update(Request $request)
{
    $member = $this->getMember($request);

    if ($member instanceof \Illuminate\Http\JsonResponse) {
        return $member;
    }

    if (!$member->transaction_password) {
        return response()->json([
            'status' => false,
            'message' => 'Please set transaction password first'
        ], 400);
    }

    $request->validate([
        'otp' => 'required',
        'password' => 'required|min:4|confirmed',
    ]);

    if (!$member->transaction_otp) {
        return response()->json([
            'status' => false,
            'message' => 'OTP not generated'
        ], 400);
    }

    if ($member->transaction_otp != $request->otp) {
        return response()->json([
            'status' => false,
            'message' => 'Invalid OTP'
        ], 400);
    }

    if (
        !$member->transaction_otp_expiry ||
        now()->greaterThan($member->transaction_otp_expiry)
    ) {
        return response()->json([
            'status' => false,
            'message' => 'OTP expired'
        ], 400);
    }

    $member->transaction_password = Hash::make($request->password);

    $member->transaction_otp = null;
    $member->transaction_otp_expiry = null;

    $member->save();

    return response()->json([
        'status' => true,
        'message' => 'Transaction password updated successfully'
        
    ]);
}
};