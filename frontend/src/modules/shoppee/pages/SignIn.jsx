import React, { useState, useEffect } from "react";
import { shoppeeApi as api } from "../api/axios";
import { Link, useNavigate, useLocation } from "react-router-dom";
import { FaUser, FaLock, FaEye, FaEyeSlash } from "react-icons/fa";

// ── Toast ────────────────────────────────────────────────────
function Toast({ toasts, removeToast }) {
  const config = {
    success: { bar: "bg-green-500", icon: "✓", title: "text-green-800" },
    error:   { bar: "bg-red-500",   icon: "✕", title: "text-red-800"   },
    warning: { bar: "bg-yellow-500",icon: "⚠", title: "text-yellow-800"},
  };
  return (
    <div className="fixed top-4 right-4 z-50 flex flex-col gap-2 w-72 pointer-events-none">
      {toasts.map((t) => {
        const c = config[t.type];
        return (
          <div key={t.id} className="bg-white rounded-xl shadow-lg flex items-start gap-3 p-3 pointer-events-auto animate-slide-in">
            <div className={`w-1 self-stretch rounded-full flex-shrink-0 ${c.bar}`} />
            <span className={`text-base mt-0.5 flex-shrink-0 ${c.bar.replace("bg-","text-")}`}>{c.icon}</span>
            <div className="flex-1 min-w-0">
              <p className={`text-sm font-semibold ${c.title}`}>{t.title}</p>
              {t.message && <p className="text-xs text-gray-500 mt-0.5">{t.message}</p>}
            </div>
            <button onClick={() => removeToast(t.id)} className="text-gray-300 hover:text-gray-500 text-lg leading-none">×</button>
          </div>
        );
      })}
    </div>
  );
}

function useToast() {
  const [toasts, setToasts] = useState([]);
  const removeToast = (id) => setToasts((p) => p.filter((t) => t.id !== id));
  const add = (type, title, message = "", ms = 4500) => {
    const id = Date.now();
    setToasts((p) => [...p, { id, type, title, message }]);
    setTimeout(() => removeToast(id), ms);
  };
  return {
    toasts, removeToast,
    success: (title, msg) => add("success", title, msg, 3500),
    error:   (title, msg) => add("error",   title, msg),
    warning: (title, msg) => add("warning", title, msg),
  };
}

// ── SignIn ───────────────────────────────────────────────────
function SignIn() {
  const navigate = useNavigate();
  const location = useLocation();
  const toast = useToast();
  const [loading, setLoading] = useState(false);
  const [showPass, setShowPass] = useState(false);
  const [form, setForm] = useState({ memberId: "", password: "" });
  const [errors, setErrors] = useState({});

  useEffect(() => {
    const stored = localStorage.getItem("user");
    if (!stored) return;
    try {
      const user = JSON.parse(stored);
      if (user?.member_id || user?.id || user?.access_token)
        navigate("/shoppee/dashboard");
    } catch { localStorage.removeItem("user"); }
  }, [navigate]);

  useEffect(() => {
  if (location.state?.sessionExpired) {
    toast.warning(
      "Session Expired",
      "For your security, please login again."
    );

    window.history.replaceState({}, document.title);
  }
}, [location]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setForm({ ...form, [name]: value });
    setErrors({ ...errors, [name]: "" });
  };

  const validate = () => {
    const e = {};
    if (!form.memberId.trim()) e.memberId = "Member ID is required";
    if (!form.password.trim()) e.password = "Password is required";
    else if (form.password.length < 4) e.password = "Password must be at least 4 characters";
    setErrors(e);
    return !Object.keys(e).length;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!validate()) return;
    try {
      setLoading(true);
      const response = await api.post("/login", {
        login_id: form.memberId,
        password: form.password,
      });
      const userData = response.data.data || response.data.user || response.data;
      if (userData) {
        localStorage.setItem("user", JSON.stringify(userData));
        toast.success("Login successful!", "Redirecting to your dashboard…");
        setTimeout(() => navigate("/shoppee/dashboard"), 1200);
      } else {
        toast.error("Login failed", "User data not found. Please try again.");
      }
    } catch (error) {
      if (error.response?.status === 401)
        toast.error("Invalid credentials", error.response.data?.message || "Please check your Member ID and password.");
      else if (error.response?.status === 429)
        toast.warning("Too many attempts", "Please wait a moment and try again.");
      else
        toast.error("Server error", "Something went wrong. Please try again later.");
    } finally {
      setLoading(false);
    }
  };

  return (
    <>
      <Toast toasts={toast.toasts} removeToast={toast.removeToast} />

      <div className="min-h-screen flex items-center justify-center bg-gray-200 py-8">
        <div className="w-full max-w-2xl px-4 sm:px-6 lg:px-8">

          {/* Logo — unchanged */}
          <div className="text-center mb-4">
            <img src="/images/fourstep_logo.png" className="mx-auto h-20" alt="logo" />
            <h2 className="mt-5 text-2xl font-semibold">Sign In</h2>
          </div>

          {/* Card */}
          <div className="bg-white rounded-xl shadow-md p-6">
            <div className="text-center mb-4">
              <h3 className="text-xl font-bold text-[#AE4329]">Shoppee Login</h3>
            </div>

            <form onSubmit={handleSubmit} noValidate>

              {/* Member ID */}
              <div className="mb-3">
                <div className={`flex items-center border-b-2 transition-colors ${errors.memberId ? "border-red-400" : "border-gray-200 focus-within:border-[#AE4329]"}`}>
                  <FaUser className="text-gray-400 mr-2" />
                  <input
                    type="text"
                    name="memberId"
                    placeholder="Enter Member ID / Email / Mobile Number"
                    value={form.memberId}
                    onChange={handleChange}
                    className="w-full py-2 outline-none text-lg"
                  />
                </div>
                {errors.memberId && <p className="text-red-500 text-xs mt-1">{errors.memberId}</p>}
              </div>

              {/* Password */}
              <div className="mb-3">
                <div className={`flex items-center border-b-2 transition-colors ${errors.password ? "border-red-400" : "border-gray-200 focus-within:border-[#AE4329]"}`}>
                  <FaLock className="text-gray-400 mr-2" />
                  <input
                    type={showPass ? "text" : "password"}
                    name="password"
                    placeholder="Enter Password"
                    value={form.password}
                    onChange={handleChange}
                    className="w-full py-2 outline-none text-lg"
                  />
                  <button type="button" onClick={() => setShowPass(!showPass)} className="text-gray-400 hover:text-gray-600 ml-2">
                    {showPass ? <FaEyeSlash /> : <FaEye />}
                  </button>
                </div>
                {errors.password && <p className="text-red-500 text-xs mt-1">{errors.password}</p>}
              </div>

              <div className="flex justify-center mt-6">
                <button
                  type="submit"
                  disabled={loading}
                  className={`text-white text-lg px-8 sm:px-10 py-2 rounded w-full sm:w-auto flex items-center justify-center gap-2
                    ${loading ? "bg-blue-400 cursor-not-allowed" : "bg-blue-600 hover:bg-blue-700"}`}
                >
                  {loading && (
                    <svg className="animate-spin w-5 h-5" viewBox="0 0 24 24" fill="none">
                      <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"/>
                      <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                  )}
                  {loading ? "Logging in..." : "Login"}
                </button>
              </div>

              <div className="flex items-center mt-4 font-medium text-lg">
                <Link to="/shoppee/forgot-password" className="text-blue-600 hover:underline">
                  Forget Password?
                </Link>
              </div>
            </form>

            <div className="text-center mt-4">
              <p className="text-lg text-gray-600">Contact Admin for Shoppee Branch Registration</p>
              <p className="text-xs text-gray-400 mt-1">
                Contact Us:{" "}
                <a href="tel:+919726286000" className="text-blue-600 font-semibold hover:underline">
                  +91 97262 86000
                </a>
              </p>
            </div>
          </div>

        </div>
      </div>
    </>
  );
}

export default SignIn;