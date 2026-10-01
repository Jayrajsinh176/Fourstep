<x-filament::page>

<div style="max-width:1300px;width:95%;margin:auto;">

    {{-- ── Header ── --}}
    <div style="
       background:#1a2535;
        color:white;
        padding:15px;
        border-radius:10px;
        text-align:center;
        margin-bottom:20px;
    ">
        <h2 style="margin:0;font-size:28px;">
            Add Branch
        </h2>
    </div>

    {{-- ══════════════════════════════════════
         PERSONAL DETAILS CARD
    ══════════════════════════════════════ --}}
    <div style="
        background:white;
        padding:25px;
        border-radius:10px;
        margin-bottom:20px;
        width:100%;
    ">

        <h3 style="color:#AE4329;margin-bottom:20px;">
            Personal Details
        </h3>

     
{{-- Full Name & DOB --}}
<div style="display:flex;gap:20px;margin-bottom:20px;">

    <div style="flex:1;">
        <label>Full Name</label>
        <input
            type="text"
            wire:model="fullname"
                placeholder="Enter Full Name"
            style="width:100%;border:none;border-bottom:1px solid #000;padding:8px;outline:none;">
        @error('fullname')
            <div style="color:red;font-size:12px;">{{ $message }}</div>
        @enderror
    </div>

    <div style="flex:1;">
        <label>Date Of Birth</label>
        <input
            type="date"
            wire:model="dob"
            style="width:100%;border:none;border-bottom:1px solid #000;padding:8px;outline:none;">
        @error('dob')
            <div style="color:red;font-size:12px;">{{ $message }}</div>
        @enderror
    </div>

</div>

{{-- Email & Mobile --}}
<div style="display:flex;gap:20px;margin-bottom:20px;">

    <div style="flex:1;">
        <label>Email Address</label>
        <input
            type="email"
            wire:model="email"
            placeholder="example123@gmail.com"
            style="width:100%;border:none;border-bottom:1px solid #000;padding:8px;">
        @error('email')
            <div style="color:red;font-size:12px;">{{ $message }}</div>
        @enderror
    </div>

    <div style="flex:1;">
        <label>Mobile No</label>
      <input
    type="tel"
    wire:model="mobile_no"
    placeholder="Enter 10 Digit Mobile Number"
    maxlength="10"
    inputmode="numeric"
    oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10)"
    style="width:100%;border:none;border-bottom:1px solid #000;padding:8px;">
        @error('mobile_no')
            <div style="color:red;font-size:12px;">{{ $message }}</div>
        @enderror
    </div>

</div>

{{-- PAN & Aadhaar --}}
<div style="display:flex;gap:20px;margin-bottom:20px;">

    <div style="flex:1;">
        <label>User PAN</label>
        <input
    type="text"
    wire:model="user_pan"
    placeholder="ABCDE1234F"
    maxlength="10"
    oninput="this.value=this.value.toUpperCase()"
    style="text-transform:uppercase;width:100%;border:none;border-bottom:1px solid #000;padding:8px;">
        @error('user_pan')
            <div style="color:red;font-size:12px;">{{ $message }}</div>
        @enderror
    </div>

    <div style="flex:1;">
        <label>Aadhaar No</label>
      <input
    type="text"
    wire:model="aadhaar_no"
    placeholder="Enter 12 Digit Aadhaar Number"
    maxlength="12"
    inputmode="numeric"
    oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,12)"
    style="width:100%;border:none;border-bottom:1px solid #000;padding:8px;">
        @error('aadhaar_no')
            <div style="color:red;font-size:12px;">{{ $message }}</div>
        @enderror
    </div>

</div>

{{-- User Address --}}
<div style="margin-bottom:20px;">
    <label>User Address</label>
    <input
        type="text"
        wire:model="user_address"
        placeholder="Enter Complete Residential Address"
        style="width:100%;border:none;border-bottom:1px solid #000;padding:8px;outline:none;box-sizing:border-box;">
    @error('user_address')
        <div style="color:red;font-size:12px;margin-top:5px;">{{ $message }}</div>
    @enderror
</div>

    
    </div>
    {{-- ══════════════════════════════════════
         Branch DETAILS CARD
    ══════════════════════════════════════ --}}
  <div style="
        background:white;
        padding:25px;
        border-radius:10px;
        margin-bottom:20px;
        width:100%;
    ">

        <h3 style="color:#AE4329;margin-bottom:20px;">
            Branch Details
        </h3>

 

        {{-- Branch Name & Branch PAN --}}
        <div style="display:flex;gap:20px;margin-bottom:20px;">

            <div style="flex:1;">
                <label>Branch Name</label>
                <input
                    type="text"
                    wire:model="branch_name"
                    placeholder="Enter Branch Name"
                    style="width:100%;border:none;border-bottom:1px solid #000;padding:8px;outline:none;box-sizing:border-box;">
                @error('branch_name')
                    <div style="color:red;font-size:12px;margin-top:5px;">{{ $message }}</div>
                @enderror
            </div>

            <div style="flex:1;">
                <label>Branch PAN</label>
                <input
    type="text"
    wire:model="branch_pan"
    placeholder="ABCDE1234F"
    maxlength="10"
    oninput="this.value=this.value.toUpperCase()"
    style="text-transform:uppercase;width:100%;border:none;border-bottom:1px solid #000;padding:8px;">
                @error('branch_pan')
                    <div style="color:red;font-size:12px;margin-top:5px;">{{ $message }}</div>
                @enderror
            </div>

        </div>
        

       <div style="display:flex;gap:20px;margin-bottom:20px;">

    <div style="flex:1;">
        <label>Branch Type</label>
        <select
            wire:model="branch_type"
            style="width:100%;border:none;border-bottom:1px solid #000;padding:8px;outline:none;box-sizing:border-box;background:white;appearance:auto;">
            <option value="">Select Branch Type</option>
            <option value="Mega Branch">Mega Branch</option>
            <option value="Mini Branch">Mini Branch</option>
            <option value="Area Branch">Area Branch</option>
        </select>
        @error('branch_type')
            <div style="color:red;font-size:12px;margin-top:5px;">{{ $message }}</div>
        @enderror
    </div>

    <div style="flex:1;">
        <label>GST No</label>
     <input
    type="text"
    wire:model="gst_no"
    placeholder="Enter GST Number"
    maxlength="15"
    oninput="this.value=this.value.toUpperCase()"
    style="text-transform:uppercase;width:100%;border:none;border-bottom:1px solid #000;padding:8px;">
        @error('gst_no')
            <div style="color:red;font-size:12px;margin-top:5px;">{{ $message }}</div>
        @enderror
    </div>

</div>
   
        {{-- Password & Confirm Password --}}
        <div style="display:flex;gap:20px;">

            <div style="flex:1;">
                <label>Password</label>
                <input
                    type="password"
                    wire:model="password"
                    placeholder="Minimum 6 Characters"
                    style="width:100%;border:none;border-bottom:1px solid #000;padding:8px;outline:none;box-sizing:border-box;">
                @error('password')
                    <div style="color:red;font-size:12px;margin-top:5px;">{{ $message }}</div>
                @enderror
            </div>

            <div style="flex:1;">
                <label>Confirm Password</label>
                <input
                    type="password"
                    wire:model="confirm_password"
                    placeholder="Re-enter Password"
                    style="width:100%;border:none;border-bottom:1px solid #000;padding:8px;outline:none;box-sizing:border-box;">
                @error('confirm_password')
                    <div style="color:red;font-size:12px;margin-top:5px;">{{ $message }}</div>
                @enderror
            </div>

        </div>

    </div>
    {{-- ══════════════════════════════════════
         ADDRESS DETAILS CARD
    ══════════════════════════════════════ --}}
    <div style="
        background:white;
        padding:25px;
        border-radius:10px;
        margin-bottom:20px;
        width:100%;
    ">

        <h3 style="color:#AE4329;margin-bottom:20px;">
           Branch Address Details
        </h3>

        {{-- Address --}}
        <div style="margin-bottom:20px;">
            <label>Address</label>
            <input
                type="text"
                wire:model="address"
                placeholder="Enter Complete Branch Address"
                style="width:100%;border:none;border-bottom:1px solid #000;padding:8px;outline:none;box-sizing:border-box;">
            @error('address')
                <div style="color:red;font-size:12px;margin-top:5px;">{{ $message }}</div>
            @enderror
        </div>

        {{-- Pincode & State --}}
        <div style="display:flex;gap:20px;margin-bottom:20px;">

            <div style="flex:1;">
                <label>Pincode</label>
               <input
    type="text"
    wire:model="pin_code"
    placeholder="Enter 6 Digit Pincode"
    maxlength="6"
    inputmode="numeric"
    oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,6)"
    style="width:100%;border:none;border-bottom:1px solid #000;padding:8px;">
                @error('pin_code')
                    <div style="color:red;font-size:12px;margin-top:5px;">{{ $message }}</div>
                @enderror
            </div>

            <div style="flex:1;">
                <label>State</label>
                <input
                    type="text"
                    wire:model="state"
                    placeholder="Enter State"
                    style="width:100%;border:none;border-bottom:1px solid #000;padding:8px;outline:none;box-sizing:border-box;">
                @error('state')
                    <div style="color:red;font-size:12px;margin-top:5px;">{{ $message }}</div>
                @enderror
            </div>

        </div>

        {{-- City & District --}}
        <div style="display:flex;gap:20px;">

            <div style="flex:1;">
                <label>City</label>
                <input
                    type="text"
                    wire:model="city"
                    placeholder="Enter City"
                    style="width:100%;border:none;border-bottom:1px solid #000;padding:8px;outline:none;box-sizing:border-box;">
                @error('city')
                    <div style="color:red;font-size:12px;margin-top:5px;">{{ $message }}</div>
                @enderror
            </div>

            <div style="flex:1;">
                <label>District</label>
                <input
                    type="text"
                    wire:model="district"
                    placeholder="Enter District"
                    style="width:100%;border:none;border-bottom:1px solid #000;padding:8px;outline:none;box-sizing:border-box;">
                @error('district')
                    <div style="color:red;font-size:12px;margin-top:5px;">{{ $message }}</div>
                @enderror
            </div>

        </div>

    </div>

    {{-- ── Submit Button ── --}}
    <div style="text-align:center;margin-top:20px;margin-bottom:30px;">

        <button
            type="button"
            wire:click="createMember"
            wire:loading.attr="disabled"
            wire:target="createMember"
            style="
              background:#1a2535;
                color:white;
                border:none;
                padding:10px 40px;
                border-radius:5px;
                cursor:pointer;
                font-size:14px;
            ">

            <span wire:loading.remove wire:target="createMember">
                Add Member
            </span>

            <span wire:loading wire:target="createMember">
                Creating Member...
            </span>

        </button>

    </div>

</div>

</x-filament::page>