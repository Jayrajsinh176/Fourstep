import React, { useState } from "react";
import { Link } from "react-router-dom";
import { FaUser, FaLock } from "react-icons/fa";
import { MdOutlineSecurity } from "react-icons/md";

const API_BASE_URL =
  import.meta.env.VITE_API_BASE_URL || "https://fourstepretail.com/api";

function Toast({ message, type }) {
  if (!message) return null;
  return (
    <div
      className={`fixed top-5 right-5 z-50 flex items-center gap-3 px-4 py-3 rounded-lg shadow-lg text-white text-sm font-medium transition-all duration-300 ${
        type === "success" ? "bg-green-600" : "bg-red-500"
      }`}
    >
      {type === "success" ? (
        <svg className="w-5 h-5 shrink-0" fill="none" stroke="currentColor" strokeWidth={2.5} viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
        </svg>
      ) : (
        <svg className="w-5 h-5 shrink-0" fill="none" stroke="currentColor" strokeWidth={2.5} viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
      )}
      {message}
    </div>
  );
}

function ForgotPassword() {
  const [step, setStep] = useState(1);
  const [form, setForm] = useState({
    identifier: "",
    otp: "",
    password: "",
    confirmPassword: "",
  });
  const [loading, setLoading] = useState(false);
  const [toast, setToast] = useState({ message: "", type: "" });

  const showToast = (message, type = "success") => {
    setToast({ message, type });
    setTimeout(() => setToast({ message: "", type: "" }), 5000);
  };

  const handleChange = (e) => {
    setForm({ ...form, [e.target.name]: e.target.value });
  };

  // STEP 1 - SEND OTP
 const sendOtp = async () => {
    if (!form.identifier.trim()) {
      showToast("Please enter Member ID", "error");
      return;
    }

    try {
      setLoading(true);

      const response = await fetch(`${API_BASE_URL}/member/forgot-password`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify({ identifier: form.identifier }),
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || "Failed to send OTP");
      }

      showToast(
        data.email
          ? `Verification code sent to ${data.email}. Please check your inbox.`
          : (data.message || "Verification code sent to your registered email."),
        "success"
      );
      setTimeout(() => setStep(2), 1500);
    } catch (err) {
      showToast(err.message || "Failed to send OTP", "error");
    } finally {
      setLoading(false);
    }
  };

  // STEP 2 - VERIFY OTP
  const verifyOtp = async () => {
    if (!form.otp.trim()) {
      showToast("Please enter OTP", "error");
      return;
    }

    try {
      setLoading(true);

      const response = await fetch(`${API_BASE_URL}/member/verify-forgot-otp`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify({
          identifier: form.identifier,
          otp: form.otp,
        }),
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message);
      }

      showToast(data.message || "OTP verified successfully!", "success");
      setTimeout(() => setStep(3), 1000);
    } catch (err) {
      showToast(err.message || "Invalid OTP", "error");
    } finally {
      setLoading(false);
    }
  };

  // STEP 3 - RESET PASSWORD
  const resetPassword = async () => {
    if (!form.password.trim()) {
      showToast("Please enter new password", "error");
      return;
    }

    if (form.password.length < 6) {
      showToast("Password must be at least 6 characters", "error");
      return;
    }

    if (form.password !== form.confirmPassword) {
      showToast("Passwords do not match", "error");
      return;
    }

    try {
      setLoading(true);

      const response = await fetch(`${API_BASE_URL}/member/reset-password`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify({
          identifier: form.identifier,
          otp: form.otp,
          password: form.password,
        }),
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message);
      }

      showToast(data.message || "Password changed successfully!", "success");
      setTimeout(() => {
        window.location.href = "/member/signin";
      }, 1500);
    } catch (err) {
      showToast(err.message || "Failed to reset password", "error");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-gray-200 flex items-center justify-center px-4 py-8">

      <Toast message={toast.message} type={toast.type} />

      <div className="w-full max-w-md bg-white rounded-xl shadow-lg p-6 sm:p-8">

        {/* Logo */}
        <div className="text-center mb-6">
          <img src="/images/fourstep_logo.png" alt="Logo" className="h-20 mx-auto" />
          <h2 className="text-2xl font-bold mt-4 text-gray-800">Forgot Password</h2>
          <p className="text-gray-500 mt-2 text-sm">Reset your Member account password</p>
        </div>

        {/* STEP INDICATOR */}
        <div className="flex items-center justify-center mb-8">
          <div className={`w-8 h-8 rounded-full flex items-center justify-center text-white ${step >= 1 ? "bg-blue-600" : "bg-gray-300"}`}>1</div>
          <div className="w-10 h-1 bg-gray-300"></div>
          <div className={`w-8 h-8 rounded-full flex items-center justify-center text-white ${step >= 2 ? "bg-blue-600" : "bg-gray-300"}`}>2</div>
          <div className="w-10 h-1 bg-gray-300"></div>
          <div className={`w-8 h-8 rounded-full flex items-center justify-center text-white ${step >= 3 ? "bg-blue-600" : "bg-gray-300"}`}>3</div>
        </div>

        {/* STEP 1 */}
        {step === 1 && (
          <>
            <div className="mb-5">
              <div className="flex items-center border-b">
                <FaUser className="text-gray-400 mr-2" />
                <input
                  type="text"
                  placeholder="User ID / Mobile / Email"
                  name="identifier"
                  value={form.identifier}
                  onChange={handleChange}
                  className="w-full py-3 outline-none"
                />
              </div>
            </div>
            <button
              onClick={sendOtp}
              disabled={loading}
              className="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-semibold"
            >
              {loading ? "Sending OTP..." : "Send OTP"}
            </button>
          </>
        )}

        {/* STEP 2 */}
        {step === 2 && (
          <>
            <div className="mb-5">
              <div className="flex items-center border-b">
                <MdOutlineSecurity className="text-gray-400 mr-2 text-xl" />
                <input
                  type="text"
                  name="otp"
                  placeholder="Enter OTP"
                  value={form.otp}
                  onChange={handleChange}
                  className="w-full py-3 outline-none"
                />
              </div>
            </div>
            <button
              onClick={verifyOtp}
              disabled={loading}
              className="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-semibold"
            >
              {loading ? "Verifying..." : "Verify OTP"}
            </button>
          </>
        )}

        {/* STEP 3 */}
        {step === 3 && (
          <>
            <div className="mb-4">
              <div className="flex items-center border-b">
                <FaLock className="text-gray-400 mr-2" />
                <input
                  type="password"
                  name="password"
                  placeholder="New Password"
                  value={form.password}
                  onChange={handleChange}
                  className="w-full py-3 outline-none"
                />
              </div>
            </div>
            <div className="mb-5">
              <div className="flex items-center border-b">
                <FaLock className="text-gray-400 mr-2" />
                <input
                  type="password"
                  name="confirmPassword"
                  placeholder="Confirm Password"
                  value={form.confirmPassword}
                  onChange={handleChange}
                  className="w-full py-3 outline-none"
                />
              </div>
            </div>
            <button
              onClick={resetPassword}
              disabled={loading}
              className="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg font-semibold"
            >
              {loading ? "Updating..." : "Reset Password"}
            </button>
          </>
        )}

        <div className="text-center mt-6">
          <Link to="/member/signin" className="text-blue-600 hover:underline font-medium">
            Back to Login
          </Link>
        </div>

      </div>
    </div>
  );
}

export default ForgotPassword;