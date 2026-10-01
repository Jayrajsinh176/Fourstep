import React, { useState } from "react";
import { Link } from "react-router-dom";
import { shoppeeApi as api } from "../api/axios";
import { FaUser, FaLock } from "react-icons/fa";
import { MdOutlineSecurity } from "react-icons/md";
import { ToastContainer, useToast } from "../components/Toast";

function ForgotPassword() {
  const [step, setStep] = useState(1);

  const [form, setForm] = useState({
    member_id: "",
    otp: "",
    password: "",
    confirmPassword: "",
  });

  const [loading, setLoading] = useState(false);
  const { toasts, showToast, removeToast } = useToast();

  const handleChange = (e) => {
    setForm({
      ...form,
      [e.target.name]: e.target.value,
    });
  };

  // STEP 1 - SEND OTP
  const sendOtp = async () => {
    if (!form.member_id.trim()) {
      showToast("warning", "Please enter Member ID");
      return;
    }

    try {
      setLoading(true);

      const response = await api.post("/forgot-password", {
        member_id: form.member_id,
      });

      showToast(
        "info",
        `OTP: ${response.data.otp}\n\nUse this OTP to reset your password.`
      );
      setStep(2);
    } catch (error) {
      showToast(
        "error",
        error?.response?.data?.message ||
          "Failed to send OTP"
      );
    } finally {
      setLoading(false);
    }
  };

  // STEP 2 - VERIFY OTP
  const verifyOtp = async () => {
    if (!form.otp.trim()) {
      showToast("warning", "Please enter OTP");
      return;
    }

    try {
      setLoading(true);

      const response = await api.post("/verify-forgot-otp", {
        member_id: form.member_id,
        otp: form.otp,
      });

      showToast("success", response.data.message || "OTP verified");
      setStep(3);
    } catch (error) {
      showToast(
        "error",
        error?.response?.data?.message ||
          "Invalid OTP"
      );
    } finally {
      setLoading(false);
    }
  };

  // STEP 3 - RESET PASSWORD
  const resetPassword = async () => {
    if (!form.password.trim()) {
      showToast("warning", "Please enter new password");
      return;
    }

    if (form.password.length < 6) {
      showToast("warning", "Password must be at least 6 characters");
      return;
    }

    if (form.password !== form.confirmPassword) {
      showToast("warning", "Passwords do not match");
      return;
    }

    try {
      setLoading(true);

      const response = await api.post("/reset-password", {
        member_id: form.member_id,
        otp: form.otp,
        password: form.password,
      });

      showToast("success", response.data.message || "Password changed successfully");
      setTimeout(() => {
        window.location.href = "/shoppee/signin";
      }, 1200);
    } catch (error) {
      showToast(
        "error",
        error?.response?.data?.message ||
          "Failed to reset password"
      );
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-gray-200 flex items-center justify-center px-4 py-8">
      <div className="w-full max-w-md bg-white rounded-xl shadow-lg p-6 sm:p-8">
        {/* Logo */}
        <div className="text-center mb-6">
          <img
            src="/images/fourstep_logo.png"
            alt="Logo"
            className="h-20 mx-auto"
          />

          <h2 className="text-2xl font-bold mt-4 text-gray-800">
            Forgot Password
          </h2>

          <p className="text-gray-500 mt-2 text-sm">
            Reset your Shoppee account password
          </p>
        </div>

        {/* STEP INDICATOR */}
        <div className="flex items-center justify-center mb-8">
          <div
            className={`w-8 h-8 rounded-full flex items-center justify-center text-white ${
              step >= 1 ? "bg-blue-600" : "bg-gray-300"
            }`}
          >
            1
          </div>

          <div className="w-10 h-1 bg-gray-300"></div>

          <div
            className={`w-8 h-8 rounded-full flex items-center justify-center text-white ${
              step >= 2 ? "bg-blue-600" : "bg-gray-300"
            }`}
          >
            2
          </div>

          <div className="w-10 h-1 bg-gray-300"></div>

          <div
            className={`w-8 h-8 rounded-full flex items-center justify-center text-white ${
              step >= 3 ? "bg-blue-600" : "bg-gray-300"
            }`}
          >
            3
          </div>
        </div>

        {/* STEP 1 */}
        {step === 1 && (
          <>
            <div className="mb-5">
              <div className="flex items-center border-b">
                <FaUser className="text-gray-400 mr-2" />

                <input
                  type="text"
                  name="member_id"
                  placeholder="Enter Member ID"
                  value={form.member_id}
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
          <Link
            to="/shoppee/signin"
            className="text-blue-600 hover:underline font-medium"
          >
            Back to Login
          </Link>
        </div>
      </div>
      <ToastContainer toasts={toasts} removeToast={removeToast} />
    </div>
  );
}

export default ForgotPassword;