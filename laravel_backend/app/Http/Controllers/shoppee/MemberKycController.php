<?php

namespace App\Http\Controllers\shoppee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Shoppee_Kyc;
use App\Models\Shoppee_Member;

class MemberKycController extends Controller
{
    // Submit KYC
    public function store(Request $request)
    {
        $request->validate([
            'member_id' => 'required',

            'account_beneficiary_name' => 'required|string',
            'account_no' => 'required|numeric|digits_between:8,20',
               're_account_no' => 'required|numeric|digits_between:8,20',
            'ifs_code' => 'required|string',
            'bank_name' => 'required|string',
            'branch_name' => 'required|string',

            'aadhaar_number' => 'required|digits:12',
          'pan_number' => [
    'required',
    'regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/'
],

            'transaction_password' => 'required',

            'bank_passbook_image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'aadhaar_image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'pan_image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ],[
    'aadhaar_number.digits' => 'Aadhaar number must be exactly 12 digits.',
    'account_no.numeric' => 'Account number must contain only digits.',
    're_account_no.numeric' => 'Re-enter account number must contain only digits.',
]);

        if ($request->account_no !== $request->re_account_no) {
            return response()->json([
                'status' => false,
                'message' => 'Account numbers do not match'
            ], 422);
        }

        $member = Shoppee_Member::find($request->member_id);

        if (!$member) {
            return response()->json([
                'status' => false,
                'message' => 'Member not found'
            ], 404);
        }

        if (!$member->transaction_password) {
            return response()->json([
                'status' => false,
                'message' => 'Please set transaction password first'
            ], 400);
        }

        if (!Hash::check(
            $request->transaction_password,
            $member->transaction_password
        )) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid transaction password'
            ], 400);
        }

        // Make old KYC records non-latest
        Shoppee_Kyc::where('member_id', $member->member_id)
            ->update([
                'is_latest' => 0
            ]);

        $bankPassbook = null;
        $aadhaarImage = null;
        $panImage = null;

        if ($request->hasFile('bank_passbook_image')) {
            $bankPassbook = $request->file('bank_passbook_image')
                ->store('shoppee-kyc/bank', 'public');
        }

        if ($request->hasFile('aadhaar_image')) {
            $aadhaarImage = $request->file('aadhaar_image')
                ->store('shoppee-kyc/aadhaar', 'public');
        }

        if ($request->hasFile('pan_image')) {
            $panImage = $request->file('pan_image')
                ->store('shoppee-kyc/pan', 'public');
        }

        $kyc = Shoppee_Kyc::create([
            'member_id' => $member->member_id,
            'member_name' => $member->fullname,

            'account_beneficiary_name' => $request->account_beneficiary_name,
            'account_no' => $request->account_no,
            'ifs_code' => strtoupper($request->ifs_code),
            'bank_name' => $request->bank_name,
            'branch_name' => $request->branch_name,

            'bank_passbook_image' => $bankPassbook,

            'aadhaar_number' => $request->aadhaar_number,
            'aadhaar_image' => $aadhaarImage,

            'pan_number' => strtoupper($request->pan_number),
            'pan_image' => $panImage,

            'status' => 'process',
            'is_latest' => 1,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'KYC submitted successfully',
            'data' => $kyc
        ], 201);
    }

    // Get Member KYC History
    public function index($member_id)
    {
        $kyc = Shoppee_Kyc::where('member_id', $member_id)
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $kyc
        ]);
    }

    // Get Latest KYC
    public function latest($member_id)
    {
        $kyc = Shoppee_Kyc::where('member_id', $member_id)
            ->where('is_latest', 1)
            ->first();

        return response()->json([
            'status' => true,
            'data' => $kyc
        ]);
    }
}