import React, { useState, useEffect } from "react";
import { useNavigate, Link } from "react-router-dom";
import { shoppeeApi as api } from "../api/axios";
import { ToastContainer, useToast } from "../components/Toast";



function Signup() {

    const navigate = useNavigate();

    useEffect(() => {
        const user = localStorage.getItem("user");

        if (user) {
            navigate("/shoppee/dashboard");
        }
    }, [navigate]);

    const [formData, setFormData] = useState({
        fullname: "",
        branchName: "",
        branchType: "",
        branchPan: "",
        dob: "",
        gstNo: "",
        email: "",
        mobileNo: "",
        password: "",
        confirmPassword: "",
        address: "",
        pinCode: "",
        state: "",
        city: "",
        district: "",
        agreeTerms: false,
        ageConfirmed: false,
    });

    const [errors, setErrors] = useState({});
    const { toasts, showToast, removeToast } = useToast();

    const generateMemberId = () => {
        const random = Math.floor(100000 + Math.random() * 900000);
        return "4STEP" + random;
    };

    const validateForm = () => {

        let newErrors = {};

        // regular expressions for common formats
        const emailRegex = /^\S+@\S+\.\S+$/;
        const mobileRegex = /^[0-9]{10}$/;
        const pinCodeRegex = /^[0-9]{6}$/;
        // const panRegex = /^[A-Z]{5}[0-9]{4}[A-Z]{1}$/; // simple PAN format
        const gstRegex = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/i; // basic GSTIN

        if (!formData.fullname.trim()) {
            newErrors.fullname = "Full name is required";
        }

        if (!formData.branchName.trim()) {
            newErrors.branchName = "Branch name is required";
        }

        if (!formData.branchType.trim()) {
            newErrors.branchType = "Branch type is required";
        }

        // if (!formData.branchPan.trim()) {
        //     newErrors.branchPan = "Branch PAN is required";
        // } else if (!panRegex.test(formData.branchPan.trim().toUpperCase())) {
        //     newErrors.branchPan = "Invalid PAN format";
        // }

        if (!formData.dob) {
            newErrors.dob = "Date of birth is required";
        } else {
            // verify age >= 18
            const birth = new Date(formData.dob);
            const today = new Date();
            let age = today.getFullYear() - birth.getFullYear();
            const m = today.getMonth() - birth.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) {
                age--;
            }
            if (age < 18) {
                newErrors.dob = "You must be at least 18 years old";
            }
        }

        if (!formData.gstNo.trim()) {
            newErrors.gstNo = "GST No. is required";
        } else if (!gstRegex.test(formData.gstNo.trim())) {
            newErrors.gstNo = "Invalid GSTIN format";
        }

        if (!formData.email.trim()) {
            newErrors.email = "Email is required";
        } else if (!emailRegex.test(formData.email.trim())) {
            newErrors.email = "Please enter a valid email address";
        }

        if (!formData.mobileNo.trim()) {
            newErrors.mobileNo = "Mobile number is required";
        } else if (!mobileRegex.test(formData.mobileNo.trim())) {
            newErrors.mobileNo = "Enter a valid 10‑digit mobile number";
        }

        if (!formData.password) {
            newErrors.password = "Password is required";
        } else if (formData.password.length < 6) {
            newErrors.password = "Password must be at least 6 characters";
        }

        if (!formData.confirmPassword) {
            newErrors.confirmPassword = "Confirm your password";
        } else if (formData.password !== formData.confirmPassword) {
            newErrors.confirmPassword = "Passwords do not match";
        }
        if (!formData.address.trim()) {
            newErrors.address = "Address is required";
        }

        if (!formData.pinCode.trim()) {
            newErrors.pinCode = "Pin code is required";
        } else if (!pinCodeRegex.test(formData.pinCode.trim())) {
            newErrors.pinCode = "Pin code must be 6 digits";
        }

        if (!formData.state.trim()) {
            newErrors.state = "State is required";
        }

        if (!formData.city.trim()) {
            newErrors.city = "City is required";
        }

        if (!formData.district.trim()) {
            newErrors.district = "District is required";
        }

        if (!formData.agreeTerms) {
            newErrors.agreeTerms = "You must agree to the terms";
        }

        if (!formData.ageConfirmed) {
            newErrors.ageConfirmed = "Please confirm your age";
        }

        setErrors(newErrors);

        return Object.keys(newErrors).length === 0;
    };


    const handleChange = (e) => {

        const { name, value, type, checked } = e.target;
        let newValue = type === "checkbox" ? checked : value;

        // automatically uppercase certain identifiers
        if (name === "branchPan" || name === "gstNo") {
            newValue = String(newValue).toUpperCase();
        }

        // allow only digits for mobile and pincode
        if (name === "mobileNo" || name === "pinCode") {
            // strip non-digits
            newValue = newValue.replace(/\D/g, "");
        }

        setFormData({
            ...formData,
            [name]: newValue
        });

    };

    const [loading, setLoading] = useState(false);
    const handleSubmit = async (e) => {
        e.preventDefault();

        if (validateForm()) {

            const memberId = generateMemberId();

            try {

                setLoading(true);
                await api.post("/signup", {
                    member_id: memberId,
                    fullname: formData.fullname,
                    branch_name: formData.branchName,
                    branch_type: formData.branchType,
                    branch_pan: formData.branchPan,
                    dob: formData.dob,
                    gst_no: formData.gstNo,
                    email: formData.email,
                    mobile_no: formData.mobileNo,
                    password: formData.password,
                    address: formData.address,
                    pin_code: formData.pinCode,
                    state: formData.state,
                    city: formData.city,
                    district: formData.district,
                });

                showToast("success", "Signup Successful\nMember ID: " + memberId);
                setTimeout(() => {
                  navigate("/shoppee/signin");
                }, 1200);

            } catch (error) {

                if (error.response?.status === 422) {
                    const errors = error.response.data.errors;
                    Object.values(errors).forEach(err => showToast("error", err[0]));
                }
            } finally {
                setLoading(false);
            }
        }
    };


    return (

        <div className="min-h-screen bg-gray-200 flex justify-center items-center">

            <div className="w-full max-w-3xl px-4 sm:px-6 lg:px-8">

                {/* logo */}
                <div className="text-center mb-6 mt-6">
                    <img
                        src="/images/fourstep_logo.png"
                        className="mx-auto h-20"
                        alt="logo"
                    />

                    <div className=" p-3 overflow-hidden rounded-xl text-center mb-4 mt-4 bg-[#8b5e3c]">
                        <h1 className='text-3xl font-semibold text-white '>Sign up</h1>
                    </div>
                </div>


                <form onSubmit={handleSubmit}>

                    {/* Personal Details */}
                    <div className="bg-white rounded-xl p-6 mb-6">

                        <h3 className="text-[#AE4329] text-lg font-bold mb-3">
                            Personal Details
                        </h3>
                        {/* Full Name  */}
                        <div>
                            <div className="flex flex-col mb-4">
                                <label htmlFor="fullname" className="text-black text-sm font-medium mb-2">
                                    Full Name
                                </label>
                                <div className="flex items-center border-b">
                                    <input
                                        id="fullname"
                                        name="fullname"
                                        value={formData.fullname}
                                        onChange={handleChange}
                                        className="w-full bg-transparent outline-none text-sm "
                                    />
                                </div>
                                {errors.fullname &&
                                    <p className="text-red-500 text-xs">
                                        {errors.fullname}
                                    </p>}
                            </div>
                        </div>
                        {/* Brach Name & Brach PAN */}
                        <div className="grid grid-cols-1 gap-6 md:grid-cols-2 mb-4">
                            <div>
                                <div className="flex flex-col">
                                    <label htmlFor="branchName" className="text-black text-sm font-medium mb-2">
                                        Branch Name
                                    </label>
                                    <div className="flex items-center border-b">
                                        <input
                                            id="branchName"
                                            name="branchName"
                                            value={formData.branchName}
                                            onChange={handleChange}
                                            className="w-full  bg-transparent outline-none text-sm text-black  "
                                        />
                                    </div>
                                </div>
                                {errors.branchName &&
                                    <p className="text-red-500 text-xs">
                                        {errors.branchName}
                                    </p>}
                            </div>
                            <div>
                                <div className="flex flex-col">
                                    <label htmlFor="branchPan" className="text-black text-sm font-medium mb-2">
                                        Branch PAN
                                    </label>
                                    <div className="flex items-center border-b">
                                        <input
                                            id="branchPan"
                                            name="branchPan"
                                            type="text"
                                            maxLength={10}
                                            value={formData.branchPan}
                                            onChange={handleChange}
                                            className="w-full bg-transparent outline-none  text-sm "
                                        />
                                    </div>
                                </div>

                            </div>
                        </div>
                        {/* Branch Type */}
                        <div className="mb-4">
                            <div className="flex flex-col">
                                <label
                                    htmlFor="branchType"
                                    className="text-black text-sm font-medium mb-2"
                                >
                                    Branch Type
                                </label>

                                <div className="flex items-center border-b">
                                    <select
                                        id="branchType"
                                        name="branchType"
                                        value={formData.branchType}
                                        onChange={handleChange}
                                        className="w-full bg-transparent outline-none text-sm"
                                    >
                                        <option value="">Select Branch Type</option>
                                        <option value="Mega Branch">Mega Branch</option>
                                        <option value="Mini Branch">Mini Branch</option>
                                        <option value="Pin-Code Branch">Pin-Code Branch</option>
                                    </select>
                                </div>
                            </div>

                            {errors.branchType && (
                                <p className="text-red-500 text-xs">
                                    {errors.branchType}
                                </p>
                            )}
                        </div>
                        {/* DOB & GST NO */}
                        <div className="grid grid-cols-1 gap-6 md:grid-cols-2 mb-4">
                            <div className="flex flex-col">
                                <label htmlFor="dob" className="text-black text-sm font-medium mb-2">
                                    Date of Birth
                                </label>
                                <div className="flex items-center border-b">
                                    <input
                                        id="dob"
                                        type="date"
                                        name="dob"
                                        value={formData.dob}
                                        onChange={handleChange}
                                        className="w-full bg-transparent outline-none text-sm"
                                    />
                                </div>
                                {errors.dob &&
                                    <p className="text-red-500 text-xs">
                                        {errors.dob}
                                    </p>}
                            </div>
                            <div>
                                <div className="flex flex-col">
                                    <label htmlFor="gstNo" className="text-black text-sm font-medium mb-2">
                                        GST NO.
                                    </label>
                                    <div className="flex itemscenter border-b">
                                        <input
                                            id="gstNo"
                                            name="gstNo"
                                            type="text"
                                            maxLength={15}
                                            value={formData.gstNo}
                                            onChange={handleChange}
                                            className="w-full bg-transparent outline-none  text-sm "
                                        />

                                    </div>
                                </div>

                                {errors.gstNo &&
                                    <p className="text-red-500 text-xs">
                                        {errors.gstNo}
                                    </p>}
                            </div>

                        </div>
                        {/* Email & Phone No */}
                        <div className="grid grid-cols-1 gap-6 md:grid-cols-2 mb-4">
                            <div>
                                <div className="flex flex-col">
                                    <label htmlFor="email" className="text-black text-sm font-medium mb-2">
                                        Email Address
                                    </label>
                                    <div className="flex items-center border-b ">
                                        <input
                                            id="email"
                                            name="email"
                                            type="email"
                                            value={formData.email}
                                            onChange={handleChange}
                                            className="w-full bg-transparent outline-none  text-sm "
                                        />
                                    </div>
                                </div>
                                {errors.email &&
                                    <p className="text-red-500 text-xs">
                                        {errors.email}
                                    </p>}
                            </div>

                            <div>
                                <div className="flex flex-col">
                                    <label htmlFor="mobileNo" className="text-black text-sm font-medium mb-2">
                                        Mobile No.
                                    </label>
                                    <div className="flex items-center border-b">
                                        <input
                                            id="mobileNo"
                                            name="mobileNo"
                                            type="tel"
                                            pattern="[0-9]{10}"
                                            maxLength={10}
                                            value={formData.mobileNo}
                                            onChange={handleChange}
                                            className="w-full bg-transparent outline-none  text-sm "
                                        />
                                    </div>
                                </div>
                                {errors.mobileNo &&
                                    <p className="text-red-500 text-xs">
                                        {errors.mobileNo}
                                    </p>}
                            </div>

                        </div>
                        {/* Password & Confirm Password */}
                        <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <div className="flex flex-col">
                                    <label htmlFor="password" className="text-black text-sm font-medium mb-2">
                                        Password
                                    </label>
                                    <div className="flex items-center border-b">
                                        <input
                                            id="password"
                                            name="password"
                                            type="password"
                                            value={formData.password}
                                            onChange={handleChange}
                                            className="w-full bg-transparent outline-none text-sm"
                                        />
                                    </div>
                                </div>
                                {errors.password && (
                                    <p className="text-red-500 text-xs">
                                        {errors.password}
                                    </p>
                                )}
                            </div>
                            <div>
                                <div className="flex flex-col">
                                    <label htmlFor="confirmPassword" className="text-black text-sm font-medium mb-2">
                                        Confirm Password
                                    </label>
                                    <div className="flex items-center border-b">
                                        <input
                                            id="confirmPassword"
                                            name="confirmPassword"
                                            type="password"
                                            value={formData.confirmPassword}
                                            onChange={handleChange}
                                            className="w-full bg-transparent outline-none text-sm"
                                        />
                                    </div>
                                </div>
                                {errors.confirmPassword && (
                                    <p className="text-red-500 text-xs">
                                        {errors.confirmPassword}
                                    </p>
                                )}
                            </div>

                        </div>
                    </div>
                    {/* Address Details */}
                    <div className="bg-white rounded-xl p-6 mb-6">
                        <h3 className="text-[#AE4329] text-lg font-bold mb-3">
                            Address Details
                        </h3>
                        {/* Address */}
                        <div>
                            <div className="flex flex-col mb-4">

                                <label htmlFor="address" className="text-black text-sm font-medium mb-2">
                                    Address
                                </label>
                                <div className="flex items-center border-b">
                                    <input
                                        id="address"
                                        name="address"
                                        value={formData.address}
                                        onChange={handleChange}
                                        className="w-full bg-transparent outline-none text-sm "
                                    />
                                </div>
                                {errors.address &&
                                    <p className="text-red-500 text-xs mb-4">
                                        {errors.address}
                                    </p>}
                            </div>
                        </div>
                        {/* Pin Code & State */}
                        <div className="grid grid-cols-1 gap-6 md:grid-cols-2 mb-4">
                            <div>
                                <div className="flex flex-col">
                                    <label htmlFor="pinCode" className="text-black text-sm font-medium mb-2">
                                        Pincode
                                    </label>
                                    <div className="flex items-center border-b ">
                                        <input
                                            id="pinCode"
                                            name="pinCode"
                                            type="text"
                                            pattern="[0-9]{6}"
                                            maxLength={6}
                                            value={formData.pinCode}
                                            onChange={handleChange}
                                            className=" bg-transparent outline-none  text-sm "
                                        />
                                    </div>
                                </div>
                                {errors.pinCode &&
                                    <p className="text-red-500 text-xs">
                                        {errors.pinCode}
                                    </p>}
                            </div>
                            <div>
                                <div className="flex flex-col">
                                    <label htmlFor="state" className="text-black text-sm font-medium mb-2">
                                        State
                                    </label>
                                    <div className="flex items-center border-b ">
                                        <input
                                            id="state"
                                            name="state"
                                            value={formData.state}
                                            onChange={handleChange}
                                            className=" bg-transparent outline-none  text-sm "
                                        />
                                    </div>
                                </div>
                                {errors.state &&
                                    <p className="text-red-500 text-xs">
                                        {errors.state}
                                    </p>}
                            </div>
                        </div>
                        {/* City & District */}
                        <div className="grid grid-cols-1 gap-6 md:grid-cols-2 mb-4">
                            <div>
                                <div className="flex flex-col">
                                    <label htmlFor="city" className="text-black text-sm font-medium mb-2">
                                        City
                                    </label>
                                    <div className="flex items-center border-b ">
                                        <input
                                            id="city"
                                            name="city"
                                            value={formData.city}
                                            onChange={handleChange}
                                            className=" bg-transparent outline-none  text-sm "
                                        />
                                    </div>
                                </div>
                                {errors.city &&
                                    <p className="text-red-500 text-xs">
                                        {errors.city}
                                    </p>}
                            </div>
                            <div>
                                <div className="flex flex-col">
                                    <label htmlFor="district" className="text-black text-sm font-medium mb-2">
                                        District
                                    </label>
                                    <div className="flex items-center border-b ">
                                        <input
                                            id="district"
                                            name="district"
                                            value={formData.district}
                                            onChange={handleChange}
                                            className=" bg-transparent outline-none  text-sm "
                                        />
                                    </div>
                                </div>
                                {errors.district &&
                                    <p className="text-red-500 text-xs">
                                        {errors.district}
                                    </p>}
                            </div>

                        </div>
                        {/* checkboxes */}
                        <div className="text-xs text-red-600 space-y-2">

                            <label className="flex items-center font-semibold">
                                <input
                                    type="checkbox"
                                    name="agreeTerms"
                                    checked={formData.agreeTerms}
                                    onChange={handleChange}
                                    className="mr-2"
                                />
                                I have read & agree to Terms and Conditions*
                            </label>
                            {errors.agreeTerms && (
                                <p className="ml-6 text-red-500 text-xs ">
                                    {errors.agreeTerms}
                                </p>
                            )}

                            <label className="flex items-center font-semibold">
                                <input
                                    type="checkbox"
                                    name="ageConfirmed"
                                    checked={formData.ageConfirmed}
                                    onChange={handleChange}
                                    className="mr-2"
                                />
                                I am atleast 18 years old (21 years in case of domicile being Maharashtra) and citizen of India
                            </label>
                            {errors.ageConfirmed && (
                                <p className="ml-6 text-red-500 text-xs ">
                                    {errors.ageConfirmed}
                                </p>
                            )}

                        </div>

                    </div>
                    {/* submit */}
                    <div className="flex justify-center">
                        <button
                            type="submit"
                            disabled={loading || !(formData.agreeTerms && formData.ageConfirmed)}
                            className={`w-full sm:w-auto px-10 sm:px-14 py-2 rounded text-white mb-4 flex items-center justify-center gap-2 
                                 ${loading
                                    ? "bg-blue-400 cursor-not-allowed"
                                    : formData.agreeTerms && formData.ageConfirmed
                                        ? "bg-blue-600 hover:bg-blue-700"
                                        : "bg-gray-400 cursor-not-allowed"
                                }`}
                        >
                            {loading ? "Submitting..." : "Submit"}
                        </button>

                    </div>

                </form>

            </div>
            <ToastContainer toasts={toasts} removeToast={removeToast} />

        </div>
    );
}

export default Signup;