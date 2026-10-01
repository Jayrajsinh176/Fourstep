import React, { useState } from "react";
import { FaUser, FaLock, FaEye, FaEyeSlash } from "react-icons/fa";
import { Link, useNavigate } from "react-router-dom";

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

function SignIn() {
  const navigate = useNavigate();
  const [toast, setToast] = useState({ message: "", type: "" });

  const showToast = (message, type = "success") => {
    setToast({ message, type });
    setTimeout(() => setToast({ message: "", type: "" }), 3000);
  };

//   // ✅ UPDATED: check new auth key
// React.useEffect(() => {
//   if (localStorage.getItem("isAuth")) {
//     navigate("/");
//   }
// }, [navigate]);

  const [form, setForm] = useState({
    identifier: "",
    password: "",
    remember: false,
  });

  const [errors, setErrors] = useState({});
  const [apiError, setApiError] = useState("");
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [showPassword, setShowPassword] = useState(false);

  const handleChange = (e) => {
    const { name, value, type, checked } = e.target;

    setForm({
      ...form,
      [name]: type === "checkbox" ? checked : value,
    });

    setErrors({
      ...errors,
      [name]: "",
    });
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
    setApiError("");

    console.log("LOGIN REQUEST:", {
      identifier: form.identifier,
      password: form.password,
    });

    const response = await fetch(`${API_BASE_URL}/login`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({
        mobile_no: form.identifier,
        password: form.password,
      }),
    });

    console.log("LOGIN HTTP STATUS:", response.status);

    const data = await response.json();

    console.log("LOGIN RESPONSE:", data);

    if (!response.ok) {
      setApiError(data?.message || "Login failed. Check credentials.");
      return;
    }

    // Save login data
    localStorage.setItem("isAuth", "true");
    localStorage.setItem("user", JSON.stringify(data.data || data.user || data));
    localStorage.setItem("userType", data.type || "member");

    showToast("Login Successful", "success");

    setTimeout(() => navigate("/"), 1000);
  } catch (error) {
    console.error("LOGIN ERROR:", error);

    setApiError(
      "Unable to connect to server. Please check backend is running."
    );
  } finally {
    setIsSubmitting(false);
  }
};

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-200 px-3 py-8 sm:px-6">
      <Toast message={toast.message} type={toast.type} />
      <div className="w-full max-w-2xl px-2 sm:px-4">
        {/* Logo */}
        <div className="text-center mb-4">
          <img
            src="/images/fourstep_logo.png"
            className="mx-auto h-20"
            alt="logo"
          />
          <h2 className="mt-5 text-2xl font-semibold">Sign In</h2>
        </div>

        {/* Sign In Section */}
        <div className="bg-white rounded-xl shadow-md p-4 sm:p-6">
          <div className="text-center mb-4">
            <div className="font-bold text-[#AE4329] text-xl">
              Sign in with Password
            </div>
          </div>



          {/* Form */}
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
                <p className="text-red-500 text-xs mt-1">
                  {errors.identifier}
                </p>
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
    <p className="text-red-500 text-xs mt-1">
      {errors.password}
    </p>
  )}
</div>

        <div className="flex justify-center mt-6">
          <button
            type="submit"
            disabled={isSubmitting}
            className="bg-blue-600 hover:bg-blue-700 text-white text-lg px-10 py-2 rounded"
          >
            {isSubmitting ? "Logging in..." : "Login"}
          </button>
        </div>

        <div className="flex items-center justify-between mt-4 font-medium text-lg">
          <Link
            to="/forgotpassword"
            className="text-blue-600 hover:underline"
          >
            Forgot Password?
          </Link>
        </div>
      </form>

      <p className="text-center text-lg mt-4 text-gray-600">
        Don't have an account?
        <Link
          to="/member/signup"
          className="text-blue-600 ml-1 cursor-pointer hover:underline"
        >
          Sign Up
        </Link>
      </p>
    </div>
        </div >
      </div >
      );
}

export default SignIn;