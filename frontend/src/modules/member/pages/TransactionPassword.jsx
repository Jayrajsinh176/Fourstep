import { useEffect, useState } from "react";
import Sidebar from "../components/Sidebar";
import Navbar from "../components/Navbar";
import { requestMemberApi } from "../utils/apiClient";

export default function TransactionPassword() {
  const [hasPassword, setHasPassword] = useState(false);
  const [otpSent, setOtpSent] = useState(false);
  const [otp, setOtp] = useState("");
  const member = JSON.parse(localStorage.getItem("memberData"));

  const [form, setForm] = useState({
    password: "",
    password_confirmation: "",
  });

  const handleChange = (e) => {
    setForm({ ...form, [e.target.name]: e.target.value });
  };

  useEffect(() => {
    const fetchStatus = async () => {
      try {
        const res = await requestMemberApi(
          "/transaction-password/status",
          "POST",
          { user_id: member?.user_id }
        );

        if (!res.ok) {
          alert(res.data?.message || "Failed to fetch status");
          return;
        }

        setHasPassword(res.data.hasPassword);
      } catch (err) {
        console.error(err);
      }
    };
    fetchStatus();
  }, []);

  const handleSubmit = async () => {
    if (!form.password || !form.password_confirmation) {
      alert("All fields are required");
      return;
    }

    if (form.password !== form.password_confirmation) {
      alert("Passwords do not match");
      return;
    }

    try {

      // FIRST TIME PASSWORD
      if (!hasPassword) {

        const res = await requestMemberApi(
          "/transaction-password/create",
          "POST",
          {
            ...form,
            user_id: member?.user_id,
          }
        );

        if (!res.ok) {
          alert(res.data?.message || "Failed");
          return;
        }

        alert(res.data.message);

        setHasPassword(true);

        setForm({
          password: "",
          password_confirmation: "",
        });

        return;
      }

      // EXISTING PASSWORD → SEND OTP
      const res = await requestMemberApi(
        "/transaction-password/send-otp",
        "POST",
        {
          user_id: member?.user_id,
        }
      );

      if (!res.ok) {
        alert(res.data?.message || "Failed to send OTP");
        return;
      }

      setOtpSent(true);

      alert(
        res.data.message ||
          `An OTP has been sent to your registered email${
            res.data.email ? ` (${res.data.email})` : ""
          }.`
      );

    } catch (err) {
      alert("Something went wrong");
    }
  };

  const verifyOtp = async () => {
    try {

      const res = await requestMemberApi(
        "/transaction-password/update",
        "POST",
        {
          user_id: member?.user_id,
          otp,
          password: form.password,
          password_confirmation: form.password_confirmation,
        }
      );

      if (!res.ok) {
        alert(res.data?.message || "Invalid OTP");
        return;
      }

      alert(res.data.message);

      setOtp("");
      setOtpSent(false);

      setForm({
        password: "",
        password_confirmation: "",
      });

    } catch (err) {
      alert("Something went wrong");
    }
  };

  return (
    <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">
      <Sidebar />

      <div className="flex-1 min-w-0 flex flex-col">
        <Navbar />

        <div className="p-6">

          <h1 className="text-xl font-semibold text-gray-800 mb-6">
            Transaction Password
          </h1>

          <div className="bg-white rounded-xl shadow-sm p-6 max-w-2xl">

            <h2 className="text-sm font-semibold mb-6" style={{ color: "#bd422e" }}>
              {hasPassword ? "Change Transaction Password" : "Set Transaction Password"}
            </h2>

            <div className="space-y-6">

              {/* New Password */}
              <div className="grid grid-cols-3 items-center pb-2">
                <label className="text-sm text-gray-600 font-medium">
                  New Password <span style={{ color: "#bd422e" }}>*</span>
                </label>
                <div className="col-span-2">
                  <input
                    type="password"
                    name="password"
                    value={form.password}
                    onChange={handleChange}
                    placeholder="Enter new password"
                    className="w-full border-b border-gray-300 focus:border-[#bd422e] outline-none py-1.5 text-sm text-gray-700 placeholder-gray-300 bg-transparent transition-colors"
                  />
                </div>
              </div>

              {/* Confirm Password */}
              <div className="grid grid-cols-3 items-center pb-2">
                <label className="text-sm text-gray-600 font-medium">
                  Confirm Password <span style={{ color: "#bd422e" }}>*</span>
                </label>
                <div className="col-span-2">
                  <input
                    type="password"
                    name="password_confirmation"
                    value={form.password_confirmation}
                    onChange={handleChange}
                    placeholder="Confirm new password"
                    className="w-full border-b border-gray-300 focus:border-[#bd422e] outline-none py-1.5 text-sm text-gray-700 placeholder-gray-300 bg-transparent transition-colors"
                  />
                </div>
              </div>

              {otpSent && (
                <div className="grid grid-cols-3 items-center pb-2">

                  <label className="text-sm text-gray-600 font-medium">
                    Enter OTP
                  </label>

                  <div className="col-span-2">
                    <input
                      type="text"
                      value={otp}
                      onChange={(e) => setOtp(e.target.value)}
                      placeholder="Enter OTP"
                      className="w-full border-b border-gray-300 focus:border-[#bd422e] outline-none py-1.5 text-sm"
                    />
                  </div>

                </div>
              )}

              {/* Button */}
              <div className="flex justify-center pt-2">
                <button
                  onClick={otpSent ? verifyOtp : handleSubmit}
                  className="text-white px-8 py-2 rounded-md text-sm font-medium hover:opacity-90 transition-opacity"
                  style={{ backgroundColor: "#bd422e" }}
                >
                  {
                    otpSent
                      ? "Verify OTP"
                      : hasPassword
                        ? "Update Password"
                        : "Set Password"
                  }
                </button>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>
  );
}