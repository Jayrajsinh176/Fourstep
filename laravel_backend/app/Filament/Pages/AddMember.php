<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use App\Models\Shoppee_Member;

class AddMember extends Page
{
    protected static string | \UnitEnum | null $navigationGroup = 'Shoppee Panel';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $navigationLabel = 'Add Branch';

    protected static ?string $title = '';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.add-member';

    public $fullname;
    public $user_pan;
public $aadhaar_no;
public $user_address;
    public $branch_name;
    public $branch_type;
    public $branch_pan;
    public $dob;
    public $gst_no;
    public $email;
    public $mobile_no;
    public $password;
    public $confirm_password;

    public $address;
    public $pin_code;
    public $state;
    public $city;
    public $district;

    public function createMember()
    {
        $this->validate(
[
    'fullname' => 'required|min:3|max:100',

    'user_pan' => [
        'required',
        'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/'
    ],

    'aadhaar_no' => [
        'required',
        'digits:12'
    ],

    'user_address' => 'required|min:10|max:500',

    'branch_name' => 'required|min:3|max:150',

    'branch_type' => 'required',

    'branch_pan' => [
        'nullable',
        'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/'
    ],

    'dob' => 'required|date|before:today',

 'gst_no' => [
    'required',
    'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[A-Z0-9]{3}$/'
],

    'email' => 'required|email|unique:shoppee_members,email',

   'mobile_no' => 'required|digits:10|unique:shoppee_members,mobile_no',

    'password' => [
        'required',
        'min:6',
        'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/'
    ],

    'confirm_password' => 'required|same:password',

    'address' => 'required|min:10|max:500',

    'pin_code' => 'required|digits:6',

    'state' => 'required',
    'city' => 'required',
    'district' => 'required',
],
[
    'fullname.required' => 'Full Name is required',
    'user_pan.required' => 'User PAN is required',
'aadhaar_no.required' => 'Aadhaar No is required',
'user_address.required' => 'User Address is required',
    'branch_name.required' => 'Branch Name is required',
    'branch_type.required' => 'Please select Branch Type',
    'dob.required' => 'Date of Birth is required',
    'gst_no.required' => 'GST No is required',
    'email.required' => 'Email is required',
    'email.email' => 'Enter valid Email',
    'mobile_no.required' => 'Mobile No is required',
    'mobile_no.digits' => 'Mobile No must be 10 digits',
    'password.required' => 'Password is required',
    'password.min' => 'Password must be at least 6 characters',
    'confirm_password.same' => 'Passwords do not match',
    'address.required' => 'Address is required',
    'pin_code.required' => 'Pincode is required',
    'pin_code.digits' => 'Pincode must be 6 digits',
    'state.required' => 'State is required',
    'city.required' => 'City is required',
    'district.required' => 'District is required',
]
);

       do {
    $memberId = 'FRLB' . rand(100000, 999999);
} while (
    Shoppee_Member::where('member_id', $memberId)->exists()
);
$plainPassword = $this->password;
        Shoppee_Member::create([
            'member_id'   => $memberId,
            'fullname'    => $this->fullname,
            'user_pan' => $this->user_pan,
'aadhaar_no' => $this->aadhaar_no,
'user_address' => $this->user_address,
            'branch_name' => $this->branch_name,
            'branch_type' => $this->branch_type,
            'branch_pan'  => $this->branch_pan,
            'dob'         => $this->dob,
            'gst_no'      => $this->gst_no,
            'email'       => $this->email,
            'mobile_no'   => $this->mobile_no,
     'password'    => Hash::make($plainPassword),
            'address'     => $this->address,
            'pin_code'    => $this->pin_code,
            'state'       => $this->state,
            'city'        => $this->city,
            'district'    => $this->district,
        ]);



Notification::make()
    ->title('Member Created Successfully')
    ->body(
        'Member ID: ' . $memberId .
        ' | Password: ' . $plainPassword
    )
    ->success()
    ->duration(10000)
    ->send();

        $this->reset();
    }
    
    public static function canAccess(): bool
{
    $user = auth()->user();

    if (! $user) {
        return false;
    }

    if ($user->isOwner()) {
        return true;
    }

    return $user->hasPermission('add_branch');
}

public static function shouldRegisterNavigation(): bool
{
    return static::canAccess();
}
    
}