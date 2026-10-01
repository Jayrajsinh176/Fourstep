<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Member;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class MainMemberSeeder extends Seeder
{
    
    public function run(): void
    {
        $userId = env('MAIN_MEMBER_USER_ID', 'MAINDEV001');
        $mobile = env('MAIN_MEMBER_MOBILE', '9999999999');
        $email = env('MAIN_MEMBER_EMAIL', 'main@example.com');
        $password = env('MAIN_MEMBER_PASSWORD', 'ChangeMe123');

        $attributes = [
            'user_id' => $userId,
            'fullname' => env('MAIN_MEMBER_FULLNAME', 'Main Sponsor'),
            'dob' => env('MAIN_MEMBER_DOB', '1980-01-01'),
            'gender' => env('MAIN_MEMBER_GENDER', 'other'),
            'mobile_no' => $mobile,
            'email' => $email,
            'password' => Hash::make($password),
            'status' => 1,
            'activation_date' => Carbon::now(),
        ];

        Member::updateOrCreate(
            ['user_id' => $userId],
            $attributes
        );
    }
}
