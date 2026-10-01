import { useEffect, useState } from "react";
import Sidebar from "../components/sidebar";
import Navbar from "../components/navbar";
import { shoppeeApi as api } from "../api/axios";
import { ToastContainer, useToast } from "../components/Toast";

export default function TransactionPassword() {
  const [hasPassword, setHasPassword] = useState(false);
  const [otpSent, setOtpSent] = useState(false);
  const [otp, setOtp] = useState("");

  const member = JSON.parse(localStorage.getItem("user"));

  const [form, setForm] = useState({
    password: "",
    password_confirmation: "",
  });

  const { toasts, showToast, removeToast } = useToast();

  const handleChange = (e) => {
    setForm({
      ...form,
      [e.target.name]: e.target.value,
    });
  };

  useEffect(() => {
    const fetchStatus = async () => {
      try {
        const res = await api.post(
          "/transaction-password/status",
          {
            id: member?.id,
          }
        );

        if (res.data.success) {
          setHasPassword(res.data.hasPassword);
        }
      } catch (err) {
        console.error(err);
        showToast("error", "Failed to fetch status");
      }
    };

    fetchStatus();
  }, [member?.id, showToast]);

  const handleSubmit = async () => {
    if (!form.password || !form.password_confirmation) {
      showToast("warning", "All fields are required");
      return;
    }

    if (form.password !== form.password_confirmation) {
      showToast("warning", "Passwords do not match");
      return;
    }

    try {
      // FIRST TIME PASSWORD
      if (!hasPassword) {
        const res = await api.post(
          "/transaction-password/create",
          {
            id: member?.id,
            password: form.password,
            password_confirmation:
              form.password_confirmation,
          }
        );

        if (!res.data.success) {
          showToast("error", res.data.message);
          return;
        }

        showToast("success", res.data.message);

        setHasPassword(true);

        setForm({
          password: "",
          password_confirmation: "",
        });

        return;
      }

      // EXISTING PASSWORD → SEND OTP

      const res = await api.post(
        "/transaction-password/send-otp",
        {
          id: member?.id,
        }
      );

      if (!res.data.success) {
        showToast("error", res.data.message);
        return;
      }

      setOtpSent(true);

      showToast("info", `Your OTP is: ${res.data.otp}`);
    } catch (err) {
      console.error(err);
      showToast("error", "Something went wrong");
    }
  };

  const verifyOtp = async () => {
    try {
      const res = await api.post(
        "/transaction-password/update",
        {
          id: member?.id,
          otp,
          password: form.password,
          password_confirmation:
            form.password_confirmation,
        }
      );

      if (!res.data.success) {
        showToast("error", res.data.message || "Invalid OTP");
        return;
      }

      showToast("success", res.data.message);

      setOtp("");
      setOtpSent(false);

      setForm({
        password: "",
        password_confirmation: "",
      });
    } catch (err) {
      console.error(err);
      showToast("error", "Something went wrong");
    }
  };

  return (
    <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">
      {/* <Sidebar /> */}

      <div className="flex-1 flex flex-col">
        {/* <Navbar /> */}

        <div className="p-6">
          <div className="text-center mb-6">
            <h1 className="text-3xl font-bold text-[#B0422E]">
              Transaction Password
            </h1>
          </div>

          <div className="bg-white rounded-2xl shadow-sm p-6 max-w-2xl mx-auto">

            <h2 className="text-lg font-bold text-[#B0422E] mb-6">
              {hasPassword
                ? "Change Transaction Password"
                : "Set Transaction Password"}
            </h2>

            <div className="space-y-6">

              <div className="grid grid-cols-3 items-center">
                <label className="text-sm font-medium">
                  New Password *
                </label>

                <div className="col-span-2">
                  <input
                    type="password"
                    name="password"
                    value={form.password}
                    onChange={handleChange}
                    placeholder="Enter new password"
                    className="w-full border rounded px-3 py-2"
                  />
                </div>
              </div>

              <div className="grid grid-cols-3 items-center">
                <label className="text-sm font-medium">
                  Confirm Password *
                </label>

                <div className="col-span-2">
                  <input
                    type="password"
                    name="password_confirmation"
                    value={form.password_confirmation}
                    onChange={handleChange}
                    placeholder="Confirm password"
                    className="w-full border rounded px-3 py-2"
                  />
                </div>
              </div>

              {otpSent && (
                <div className="grid grid-cols-3 items-center">
                  <label className="text-sm font-medium">
                    Enter OTP
                  </label>

                  <div className="col-span-2">
                    <input
                      type="text"
                      value={otp}
                      onChange={(e) =>
                        setOtp(e.target.value)
                      }
                      placeholder="Enter OTP"
                      className="w-full border rounded px-3 py-2"
                    />
                  </div>
                </div>
              )}

              <div className="flex justify-center">
                <button
                  onClick={
                    otpSent
                      ? verifyOtp
                      : handleSubmit
                  }
                  className="bg-[#B0422E] text-white px-8 py-2 rounded-md hover:opacity-90"
                >
                  {otpSent
                    ? "Verify OTP"
                    : hasPassword
                    ? "Update Password"
                    : "Set Password"}
                </button>
              </div>

            </div>
          </div>
        </div>
      </div>
      <ToastContainer toasts={toasts} removeToast={removeToast} />
    </div>
  );
}