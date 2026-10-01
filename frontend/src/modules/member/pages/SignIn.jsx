import React, { useState, useEffect } from "react";
import { FaUser, FaLock, FaEye, FaEyeSlash } from "react-icons/fa";
import { Link, useNavigate, useLocation } from "react-router-dom";

const API_BASE_URL =
  import.meta.env.VITE_API_BASE_URL || "https://fourstepretail.com/api";

function AutoRedirectModal({ onDone }) {
  const [progress, setProgress] = useState(0);

  React.useEffect(() => {
    const interval = setInterval(() => {
      setProgress((prev) => {
        if (prev >= 100) {
          clearInterval(interval);
          onDone();
          return 100;
        }
        return prev + 2;
      });
    }, 40); // 40ms × 50 steps = 2000ms

    return () => clearInterval(interval);
  }, []);

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center  backdrop-blur-sm">
      <div className="bg-white rounded-xl shadow-md w-full max-w-sm mx-4 overflow-hidden">

        {/* Header */}
        <div style={{ background: "#AE4329" }} className="px-6 py-6 text-center">
          <div
            className="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-3"
            style={{ background: "rgba(255,255,255,0.2)", border: "2px solid rgba(255,255,255,0.6)" }}
          >
            <svg className="w-7 h-7 text-white" fill="none" stroke="currentColor" strokeWidth={2.5} viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <p className="text-white text-lg font-semibold">Login Successful!</p>
        </div>

        {/* Body */}
        <div className="px-6 py-5 text-center border-b border-gray-100">
          <img src="/images/fourstep_logo.png" className="mx-auto h-10 mb-3" alt="logo" />
          <p className="text-gray-500 text-sm">Welcome back! You have signed in successfully.</p>
          <p className="text-gray-400 text-xs mt-1">Redirecting to dashboard...</p>
        </div>

        {/* Progress Bar Only */}
        <div className="px-6 py-4">
          <div className="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
            <div
              className="h-2 rounded-full bg-blue-600"
              style={{ width: `${progress}%`, transition: "width 40ms linear" }}
            />
          </div>
        </div>

      </div>
    </div>
  );
}

function SignIn() {
  const navigate = useNavigate();
  const location = useLocation();

  React.useEffect(() => {
    if (localStorage.getItem("memberSession")) {
      navigate("/member/dashboard");
    }
  }, [navigate]);

  useEffect(() => {
  if (location.state?.sessionExpired) {
    setApiError(
      "⚠ Session Expired. For your security, please login again."
    );

    window.history.replaceState({}, document.title);
  }
}, [location]);

  const [form, setForm] = useState({
    identifier: "",
    password: "",
    remember: false,
  });

  const [errors, setErrors] = useState({});
  const [apiError, setApiError] = useState("");
  const [showModal, setShowModal] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [showPassword, setShowPassword] = useState(false);

  const handleChange = (e) => {
    const { name, value, type, checked } = e.target;
    setForm({ ...form, [name]: type === "checkbox" ? checked : value });
    setErrors({ ...errors, [name]: "" });
  };

  const validate = () => {
    let newErrors = {};

    if (!form.identifier.trim()) {
      newErrors.identifier = "User ID / Mobile / Email is required";
    }

    if (!form.password.trim()) {
      newErrors.password = "Password is required";
    } else if (form.password.length < 4) {
      newErrors.password = "Password must be at least 4 characters";
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    setApiError("");
    if (!validate()) return;
    signinMember();
  };

  const signinMember = async () => {
    try {
      setIsSubmitting(true);

      const response = await fetch(`${API_BASE_URL}/member/signin`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify({
          identifier: form.identifier,
          password: form.password,
        }),
      });

      const data = await response.json();

      if (!response.ok) {
        setApiError(data?.message || "Sign in failed. Check credentials.");
        return;
      }

      localStorage.setItem("memberSession", "true");
      localStorage.setItem("memberData", JSON.stringify(data.member || {}));
      setShowModal(true);
    } catch {
      setApiError("Unable to connect to server. Please check backend is running.");
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleModalOk = () => {
    setShowModal(false);
    navigate("/member/dashboard");
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-200">

      {showModal && <AutoRedirectModal onDone={handleModalOk} />}

      <div className="w-full max-w-2xl px-4">
        {/* Logo */}
        <div className="text-center mb-4">
          <img src="/images/fourstep_logo.png" className="mx-auto h-20" alt="logo" />
          <h2 className="mt-5 text-2xl font-semibold">Sign In</h2>
        </div>

        {/* Sign In Section */}
        <div className="bg-white rounded-xl shadow-md p-6">
          <div className="flex flex-wrap items-center gap-3 mb-4 text-xl">
            <div className="font-bold text-[#AE4329]">Sign in with Password</div>
          </div>

          {/* Form section */}
          <form onSubmit={handleSubmit}>
            {apiError && (
              <p className="text-red-500 text-sm mb-3">{apiError}</p>
            )}

            <div className="mb-3">
              <div className="flex items-center border-b">
                <FaUser className="text-gray-400 mr-2" />
                <input
                  type="text"
                  name="identifier"
                  placeholder="User ID / Mobile / Email"
                  value={form.identifier}
                  onChange={handleChange}
                  className="w-full py-2 outline-none text-lg"
                />
              </div>
              {errors.identifier && (
                <p className="text-red-500 text-xs mt-1">{errors.identifier}</p>
              )}
            </div>

            <div className="mb-3">
              <div className="flex items-center border-b">
                <FaLock className="text-gray-400 mr-2" />
                <input
                  type={showPassword ? "text" : "password"}
                  name="password"
                  placeholder="Enter Password"
                  value={form.password}
                  onChange={handleChange}
                  className="w-full py-2 outline-none text-lg"
                />
                <button
                  type="button"
                  onClick={() => setShowPassword((p) => !p)}
                  className="text-gray-400 hover:text-gray-600 ml-2"
                  tabIndex={-1}
                  aria-label={showPassword ? "Hide password" : "Show password"}
                >
                  {showPassword ? <FaEyeSlash /> : <FaEye />}
                </button>
              </div>
              {errors.password && (
                <p className="text-red-500 text-xs mt-1">{errors.password}</p>
              )}
            </div>

            <div className="flex justify-center mt-6">
              <button
                type="submit"
                disabled={isSubmitting}
                className="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white text-lg px-10 py-2 rounded"
              >
                {isSubmitting ? "Logging in..." : "Login"}
              </button>
            </div>

            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mt-4 font-medium text-lg">
              <div className="space-x-2">
                <Link to="/member/forgot-password" className="text-blue-600 hover:underline">
                  Forgot Password?
                </Link>
              </div>
            </div>
          </form>

          <p className="text-center text-lg mt-4 text-gray-600">
            Don't have an account?
            <Link to="/member/signup" className="text-blue-600 ml-1 cursor-pointer hover:underline">
              Sign Up
            </Link>
          </p>
        </div>
      </div>
    </div>
  );
}

export default SignIn;