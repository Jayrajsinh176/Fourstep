import React, { useState } from "react";
import { Link, useNavigate } from "react-router-dom";

const API_BASE_URL =
    import.meta.env.VITE_API_BASE_URL || "https://fourstepretail.com/api";

// ─── Brand tokens ─────────────────────────────────────────────────────────────
const B = "#B0422E";   // primary brand
const B_DARK = "#8c3422";   // hover / dark
const B_LITE = "#fdf0ed";   // tint background
const B_MID = "#e8866e";   // soft accent

// ─── Tiny helpers ─────────────────────────────────────────────────────────────
const Label = ({ children, htmlFor }) => (
    <label
        htmlFor={htmlFor}
        style={{ display: "block", fontSize: 12, fontWeight: 600, color: "#4b2318", marginBottom: 5 }}
    >
        {children}
    </label>
);

const FieldWrap = ({ children }) => (
    <div style={{
        display: "flex", alignItems: "center",
        borderBottom: `1.5px solid #e5d5d1`,
        paddingBottom: 4,
        transition: "border-color 0.15s",
    }}
        onFocusCapture={e => (e.currentTarget.style.borderColor = B)}
        onBlurCapture={e => (e.currentTarget.style.borderColor = "#e5d5d1")}
    >
        {children}
    </div>
);

const inputStyle = {
    width: "100%", background: "transparent",
    border: "none", outline: "none",
    fontSize: 13, color: "#1f2937", padding: "4px 0",
};

const ErrMsg = ({ msg }) =>
    msg ? <p style={{ color: "#dc2626", fontSize: 11, marginTop: 4 }}>{msg}</p> : null;

const SectionCard = ({ title, children }) => (
    <div style={{
        background: "#fff", borderRadius: 14,
        boxShadow: "0 1px 6px rgba(176,66,46,0.08)",
        padding: "22px 24px", marginBottom: 18,
        border: `1px solid ${B}18`,
    }}>
        <div style={{ display: "flex", alignItems: "center", gap: 8, marginBottom: 18 }}>
            <div style={{ width: 4, height: 18, borderRadius: 2, background: B }} />
            <h3 style={{ fontSize: 15, fontWeight: 700, color: B, margin: 0 }}>{title}</h3>
        </div>
        {children}
    </div>
);

const Grid2 = ({ children, style }) => (
    <div className="signup-grid" style={style}>
        {children}
    </div>
);

// ─── Main component ───────────────────────────────────────────────────────────
function SignUp() {
    const navigate = useNavigate();

    React.useEffect(() => {
        const params = new URLSearchParams(window.location.search);
        const hasQuery = params.has("sponsorId") || params.has("position");
        if (localStorage.getItem("memberSession") && !hasQuery) {
            navigate("/member/dashboard");
        }
    }, [navigate]);

    const [formData, setFormData] = useState({
        sponsorId: "", position: "", fullname: "", dob: "", gender: "",
        email: "", mobileNo: "", password: "", confirmPassword: "",
        address: "", pinCode: "", state: "", city: "", district: "",
        agreeTerms: false, ageConfirmed: false,
    });

    const [sponsorName, setSponsorName] = useState("");
    const [sponsorError, setSponsorError] = useState("");
    const [fixedSponsor, setFixedSponsor] = useState(false);
    const [fixedPosition, setFixedPosition] = useState(false);
    const [errors, setErrors] = useState({});
    const [apiError, setApiError] = useState("");
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [showPass, setShowPass] = useState(false);
    const [showConfirm, setShowConfirm] = useState(false);
    const [otpSent, setOtpSent] = useState(false);
    const [otp, setOtp] = useState("");
    const [isSendingOtp, setIsSendingOtp] = useState(false);

    const validateForm = () => {
        const newErrors = {};
        const emailRegex = /^\S+@\S+\.\S+$/;
        const mobileRegex = /^[0-9]{10}$/;
        const pinCodeRegex = /^[0-9]{6}$/;

        if (!formData.sponsorId.trim()) newErrors.sponsorId = "Sponsor ID is required";
        if (!formData.position) newErrors.position = "Placement is required";
        if (!formData.fullname.trim()) newErrors.fullname = "Full name is required";

        if (!formData.dob) {
            newErrors.dob = "Date of birth is required";
        } else {
            const birth = new Date(formData.dob);
            const today = new Date();
            let age = today.getFullYear() - birth.getFullYear();
            const m = today.getMonth() - birth.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) age--;
            if (age < 18) newErrors.dob = "You must be at least 18 years old";
        }

        if (!formData.gender) newErrors.gender = "Gender is required";
        if (!formData.email.trim()) newErrors.email = "Email is required for verification";
        else if (!emailRegex.test(formData.email.trim())) newErrors.email = "Enter a valid email";
        if (!formData.mobileNo.trim()) newErrors.mobileNo = "Mobile number is required";
        else if (!mobileRegex.test(formData.mobileNo.trim())) newErrors.mobileNo = "Enter a valid 10-digit mobile number";
        if (!formData.password.trim()) newErrors.password = "Password is required";
        else if (formData.password.length < 6) newErrors.password = "Minimum 6 characters";
        if (!formData.confirmPassword.trim()) newErrors.confirmPassword = "Confirm your password";
        else if (formData.password !== formData.confirmPassword) newErrors.confirmPassword = "Passwords do not match";
        if (formData.pinCode && !pinCodeRegex.test(formData.pinCode.trim())) newErrors.pinCode = "Pin code must be 6 digits";
        if (!formData.agreeTerms) newErrors.agreeTerms = "You must agree to the terms";
        if (!formData.ageConfirmed) newErrors.ageConfirmed = "Please confirm your age";

        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };

    React.useEffect(() => {
        try {
            const params = new URLSearchParams(window.location.search);
            const s = params.get("sponsorId");
            const p = params.get("position");
            if (s) { setFormData(prev => ({ ...prev, sponsorId: s })); checkSponsor(s); setFixedSponsor(true); }
            if (p) { setFormData(prev => ({ ...prev, position: p })); setFixedPosition(true); }
        } catch { }
    }, []);

    const handleChange = (e) => {
        const { name, value, type, checked } = e.target;
        let v = type === "checkbox" ? checked : value;
        if (name === "mobileNo" || name === "pinCode") v = v.replace(/\D/g, "");
        if (name === "email") { setOtpSent(false); setOtp(""); }
        setFormData({ ...formData, [name]: v });
    };

    const checkSponsor = async (id) => {
        setSponsorName(""); setSponsorError("");
        if (!id?.trim()) return;
        try {
            const res = await fetch(`${API_BASE_URL}/member/check-sponsor`, {
                method: "POST",
                headers: { "Content-Type": "application/json", Accept: "application/json" },
                body: JSON.stringify({ sponsorId: id }),
            });
            if (!res.ok) { setSponsorError("Sponsor not found or inactive"); return; }
            const data = await res.json();
            setSponsorName(data.sponsor.fullname || data.sponsor.user_id);
        } catch { setSponsorError("Unable to verify sponsor"); }
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        setApiError("");
        if (!validateForm()) return;
        if (!otpSent) { sendSignupOtp(); return; }
        if (otp.trim().length !== 6) {
            setErrors(prev => ({ ...prev, otp: "Enter the 6-digit code" }));
            return;
        }
        signupMember();
    };

    const sendSignupOtp = async () => {
        try {
            setIsSendingOtp(true);
            setApiError("");
            const response = await fetch(`${API_BASE_URL}/member/send-signup-otp`, {
                method: "POST",
                headers: { "Content-Type": "application/json", Accept: "application/json" },
                body: JSON.stringify({ email: formData.email, mobileNo: formData.mobileNo }),
            });
            const data = await response.json();
            if (!response.ok) {
                if (data?.errors) {
                    const mapped = {};
                    Object.entries(data.errors).forEach(([k, v]) => { mapped[k] = Array.isArray(v) ? v[0] : v; });
                    setErrors(prev => ({ ...prev, ...mapped }));
                }
                setApiError(data?.message || "Could not send verification code.");
                return;
            }
            setOtpSent(true);
            setErrors(prev => ({ ...prev, otp: undefined }));
        } catch { setApiError("Unable to connect to server."); }
        finally { setIsSendingOtp(false); }
    };

    const signupMember = async () => {
        try {
            setIsSubmitting(true);
            const response = await fetch(`${API_BASE_URL}/member/signup`, {
                method: "POST",
                headers: { "Content-Type": "application/json", Accept: "application/json" },
                body: JSON.stringify({ ...formData, otp }),
            });
            const data = await response.json();
            if (!response.ok) {
                if (data?.errors) {
                    const mappedErrors = {};
                    Object.entries(data.errors).forEach(([k, v]) => { mappedErrors[k] = Array.isArray(v) ? v[0] : v; });
                    setErrors(prev => ({ ...prev, ...mappedErrors }));
                }
                setApiError(data?.message || "Signup failed. Please try again.");
                return;
            }
            alert(`Signup Successful\nMember ID: ${data.member.user_id}`);
            localStorage.setItem("memberSession", "true");
            localStorage.setItem("memberData", JSON.stringify(data.member || {}));
            navigate("/member/dashboard");
        } catch { setApiError("Unable to connect to server."); }
        finally { setIsSubmitting(false); }
    };

    const canSubmit = formData.agreeTerms && formData.ageConfirmed && !isSubmitting && !isSendingOtp;

    // Eye icon
    const EyeIcon = ({ open }) => (
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"
            strokeLinecap="round" strokeLinejoin="round"
            style={{ width: 16, height: 16, color: "#9ca3af", cursor: "pointer", flexShrink: 0 }}>
            {open
                ? <><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" /><circle cx="12" cy="12" r="3" /></>
                : <><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24" /><line x1="1" y1="1" x2="23" y2="23" /></>
            }
        </svg>
    );

    return (
        <div style={{
            minHeight: "100vh",
            background: `linear-gradient(135deg, #f7ede9 0%, #f0e4df 40%, #e8d5cf 100%)`,
            display: "flex", justifyContent: "center", alignItems: "flex-start",
            padding: "32px 16px 48px",
        }}>
            <div style={{ width: "100%", maxWidth: 620 }}>

                {/* Logo + header */}
                <div style={{ textAlign: "center", marginBottom: 24 }}>
                    <img src="/images/fourstep_logo.png" alt="logo"
                        style={{ height: 72, margin: "0 auto 16px", display: "block" }} />

                    <div style={{
                        background: B, borderRadius: 12,
                        padding: "12px 24px", marginBottom: 4,
                        boxShadow: `0 4px 18px rgba(176,66,46,0.28)`,
                    }}>
                        <h1 style={{ color: "#fff", fontSize: 24, fontWeight: 700, margin: 0, letterSpacing: "-0.3px" }}>
                            Create Account
                        </h1>
                        <p style={{ color: "rgba(255,255,255,0.72)", fontSize: 13, margin: "4px 0 0" }}>
                            Join 4Step — one destination for success
                        </p>
                    </div>
                </div>

                <form onSubmit={handleSubmit}>

                    {/* ── Sponsor ── */}
                    <SectionCard title="Sponsor Info">
                        <Grid2>
                            <div>
                                <Label htmlFor="sponsorId">Sponsor ID</Label>
                                <FieldWrap>
                                    <input
                                        id="sponsorId" name="sponsorId"
                                        value={formData.sponsorId}
                                        readOnly={fixedSponsor}
                                        onChange={e => { if (!fixedSponsor) { handleChange(e); setSponsorName(""); setSponsorError(""); } }}
                                        onBlur={e => !fixedSponsor && checkSponsor(e.target.value)}
                                        placeholder="e.g. MAINDEV001"
                                        style={{ ...inputStyle, background: fixedSponsor ? B_LITE : "transparent" }}
                                    />
                                </FieldWrap>
                                {sponsorName && <p style={{ color: "#16a34a", fontSize: 11, marginTop: 4 }}>✓ {sponsorName}</p>}
                                {sponsorError && <ErrMsg msg={sponsorError} />}
                                <ErrMsg msg={errors.sponsorId} />
                            </div>

                            <div>
                                <Label htmlFor="position">Placement</Label>
                                <FieldWrap>
                                    <select
                                        id="position" name="position"
                                        value={formData.position}
                                        onChange={fixedPosition ? undefined : handleChange}
                                        disabled={fixedPosition}
                                        style={{ ...inputStyle, cursor: fixedPosition ? "not-allowed" : "pointer" }}
                                    >
                                        <option value="">Select position</option>
                                        <option value="left">Left</option>
                                        <option value="right">Right</option>
                                    </select>
                                </FieldWrap>
                                <ErrMsg msg={errors.position} />
                            </div>
                        </Grid2>
                    </SectionCard>

                    {/* ── Personal Details ── */}
                    <SectionCard title="Personal Details">
                        <div style={{ marginBottom: 16 }}>
                            <Label htmlFor="fullname">Full Name</Label>
                            <FieldWrap>
                                <input id="fullname" name="fullname" value={formData.fullname}
                                    onChange={handleChange} placeholder="Your full name" style={inputStyle} />
                            </FieldWrap>
                            <ErrMsg msg={errors.fullname} />
                        </div>

                        <Grid2>
                            <div>
                                <Label htmlFor="dob">Date of Birth</Label>
                                <FieldWrap>
                                    <input id="dob" type="date" name="dob" value={formData.dob}
                                        onChange={handleChange} style={inputStyle} />
                                </FieldWrap>
                                <ErrMsg msg={errors.dob} />
                            </div>
                            <div>
                                <Label htmlFor="gender">Gender</Label>
                                <FieldWrap>
                                    <select
                                        id="gender"
                                        name="gender"
                                        value={formData.gender}
                                        onChange={handleChange}
                                        style={{ ...inputStyle, cursor: "pointer" }}
                                    >
                                        <option value="">Select</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </FieldWrap>
                                <ErrMsg msg={errors.gender} />
                            </div>
                        </Grid2>

                        <Grid2 style={{ marginTop: 16 }}>
                            <div style={{ marginTop: 16 }}>
                                <Label htmlFor="email">Email Address</Label>
                                <FieldWrap>
                                    <input id="email" name="email" type="email" value={formData.email}
                                        onChange={handleChange} placeholder="you@example.com" style={inputStyle} />
                                </FieldWrap>
                                <ErrMsg msg={errors.email} />
                            </div>
                            <div style={{ marginTop: 16 }}>
                                <Label htmlFor="mobileNo">Mobile No.</Label>
                                <FieldWrap>
                                    <input id="mobileNo" name="mobileNo" type="tel" maxLength={10}
                                        value={formData.mobileNo} onChange={handleChange}
                                        placeholder="10-digit number" style={inputStyle} />
                                </FieldWrap>
                                <ErrMsg msg={errors.mobileNo} />
                            </div>
                        </Grid2>

                        <div style={{ marginTop: 16 }}>
                            <Label htmlFor="password">Password</Label>
                            <FieldWrap>
                                <input id="password" name="password"
                                    type={showPass ? "text" : "password"}
                                    value={formData.password} onChange={handleChange}
                                    placeholder="Min. 6 characters" style={inputStyle} />
                                <span onClick={() => setShowPass(p => !p)}><EyeIcon open={showPass} /></span>
                            </FieldWrap>
                            <ErrMsg msg={errors.password} />
                        </div>

                        <div style={{ marginTop: 16 }}>
                            <Label htmlFor="confirmPassword">Confirm Password</Label>
                            <FieldWrap>
                                <input id="confirmPassword" name="confirmPassword"
                                    type={showConfirm ? "text" : "password"}
                                    value={formData.confirmPassword} onChange={handleChange}
                                    placeholder="Re-enter password" style={inputStyle} />
                                <span onClick={() => setShowConfirm(p => !p)}><EyeIcon open={showConfirm} /></span>
                            </FieldWrap>
                            <ErrMsg msg={errors.confirmPassword} />
                        </div>
                    </SectionCard>

                    {/* ── Address ── */}
                    <SectionCard title="Address Details">
                        <div style={{ marginBottom: 16 }}>
                            <Label htmlFor="address">Address</Label>
                            <FieldWrap>
                                <input id="address" name="address" value={formData.address}
                                    onChange={handleChange} placeholder="Street / locality" style={inputStyle} />
                            </FieldWrap>
                            <ErrMsg msg={errors.address} />
                        </div>

                        <Grid2>
                            <div>
                                <Label htmlFor="pinCode">Pincode</Label>
                                <FieldWrap>
                                    <input id="pinCode" name="pinCode" type="text" maxLength={6}
                                        value={formData.pinCode} onChange={handleChange}
                                        placeholder="6-digit code" style={inputStyle} />
                                </FieldWrap>
                                <ErrMsg msg={errors.pinCode} />
                            </div>
                            <div>
                                <Label htmlFor="state">State</Label>
                                <FieldWrap>
                                    <input id="state" name="state" value={formData.state}
                                        onChange={handleChange} style={inputStyle} />
                                </FieldWrap>
                                <ErrMsg msg={errors.state} />
                            </div>
                        </Grid2>

                        <Grid2 style={{ marginTop: 16 }}>
                            <div style={{ marginTop: 16 }}>
                                <Label htmlFor="city">City</Label>
                                <FieldWrap>
                                    <input id="city" name="city" value={formData.city}
                                        onChange={handleChange} style={inputStyle} />
                                </FieldWrap>
                                <ErrMsg msg={errors.city} />
                            </div>
                            <div style={{ marginTop: 16 }}>
                                <Label htmlFor="district">District</Label>
                                <FieldWrap>
                                    <input id="district" name="district" value={formData.district}
                                        onChange={handleChange} style={inputStyle} />
                                </FieldWrap>
                                <ErrMsg msg={errors.district} />
                            </div>
                        </Grid2>

                        {/* Checkboxes */}
                        <div style={{ marginTop: 20, display: "flex", flexDirection: "column", gap: 10 }}>
                            {[
                                {
                                    name: "agreeTerms",
                                    checked: formData.agreeTerms,
                                    err: errors.agreeTerms,
                                    label: "I have read & agree to the Terms and Conditions",
                                },
                                {
                                    name: "ageConfirmed",
                                    checked: formData.ageConfirmed,
                                    err: errors.ageConfirmed,
                                    label: "I am at least 18 years old (21 in Maharashtra) and a citizen of India",
                                },
                            ].map(({ name, checked, err, label }) => (
                                <div key={name}>
                                    <label style={{
                                        display: "flex", alignItems: "flex-start", gap: 10,
                                        cursor: "pointer", fontSize: 12, color: "#374151", fontWeight: 500,
                                    }}>
                                        <div style={{ position: "relative", flexShrink: 0, marginTop: 1 }}>
                                            <input type="checkbox" name={name} checked={checked}
                                                onChange={handleChange}
                                                style={{ position: "absolute", opacity: 0, width: 16, height: 16, cursor: "pointer", margin: 0 }} />
                                            <div style={{
                                                width: 16, height: 16, borderRadius: 4,
                                                border: `2px solid ${checked ? B : "#d1d5db"}`,
                                                background: checked ? B : "#fff",
                                                display: "flex", alignItems: "center", justifyContent: "center",
                                                transition: "all 0.15s",
                                            }}>
                                                {checked && (
                                                    <svg viewBox="0 0 12 10" fill="none" style={{ width: 10, height: 8 }}>
                                                        <path d="M1 5l3.5 3.5L11 1" stroke="#fff" strokeWidth="2"
                                                            strokeLinecap="round" strokeLinejoin="round" />
                                                    </svg>
                                                )}
                                            </div>
                                        </div>
                                        {label}
                                    </label>
                                    <ErrMsg msg={err} />
                                </div>
                            ))}
                        </div>
                    </SectionCard>

                    {/* Email Verification */}
                    {otpSent && (
                        <SectionCard title="Email Verification">
                            <p style={{ fontSize: 12, color: "#4b2318", marginBottom: 12 }}>
                                We sent a 6-digit code to <b>{formData.email}</b>. Enter it below to create your account.
                            </p>
                            <div style={{ maxWidth: 220 }}>
                                <Label htmlFor="otp">Verification Code</Label>
                                <FieldWrap>
                                    <input
                                        id="otp" name="otp" inputMode="numeric" maxLength={6}
                                        value={otp}
                                        onChange={e => { setOtp(e.target.value.replace(/\D/g, "")); setErrors(prev => ({ ...prev, otp: undefined })); }}
                                        placeholder="Enter 6-digit code"
                                        style={inputStyle}
                                    />
                                </FieldWrap>
                                <ErrMsg msg={errors.otp} />
                            </div>
                            <button
                                type="button"
                                onClick={sendSignupOtp}
                                disabled={isSendingOtp}
                                style={{
                                    marginTop: 10, background: "transparent", border: "none",
                                    color: B, fontWeight: 600, fontSize: 12, padding: 0,
                                    cursor: isSendingOtp ? "not-allowed" : "pointer",
                                }}
                            >
                                {isSendingOtp ? "Sending…" : "Resend code"}
                            </button>
                        </SectionCard>
                    )}

                    {/* Submit */}
                    <div style={{ textAlign: "center", marginTop: 8 }}>
                        {apiError && (
                            <div style={{
                                background: "#fef2f2", border: "1px solid #fecaca",
                                borderRadius: 8, padding: "10px 16px", marginBottom: 14,
                                color: "#dc2626", fontSize: 13,
                            }}>
                                {apiError}
                            </div>
                        )}

                        <button
                            type="submit"
                            disabled={!canSubmit}
                            style={{
                                padding: "11px 52px",
                                borderRadius: 10,
                                border: "none",
                                fontSize: 15,
                                fontWeight: 700,
                                color: "#fff",
                                cursor: canSubmit ? "pointer" : "not-allowed",
                                background: canSubmit ? B : "#d1d5db",
                                boxShadow: canSubmit ? `0 4px 18px rgba(176,66,46,0.30)` : "none",
                                transition: "background 0.15s, box-shadow 0.15s",
                                letterSpacing: "0.2px",
                            }}
                            onMouseOver={e => { if (canSubmit) e.currentTarget.style.background = B_DARK; }}
                            onMouseOut={e => { if (canSubmit) e.currentTarget.style.background = B; }}
                        >
                            {isSendingOtp
                                ? "Sending code…"
                                : isSubmitting
                                    ? "Submitting…"
                                    : otpSent
                                        ? "Verify & Create Account"
                                        : "Create Account"}
                        </button>
                        <div style={{ marginTop: 16, textAlign: "center" }}>

                            <p style={{ fontSize: 13, color: "#6b7280", marginBottom: 6 }}>
                                Already have an account?{" "}
                                <Link to="/member/signin"
                                    style={{ color: B, fontWeight: 600, textDecoration: "none" }}
                                    onMouseOver={e => (e.currentTarget.style.textDecoration = "underline")}
                                    onMouseOut={e => (e.currentTarget.style.textDecoration = "none")}
                                >
                                    Sign In
                                </Link>
                            </p>

                            <p style={{ fontSize: 12, color: "#9ca3af" }}>
                                Contact Us:{" "}
                                <a href="tel:+919726286000"
                                    style={{ color: B, fontWeight: 600, textDecoration: "none" }}
                                >
                                    +91 97262 86000
                                </a>
                            </p>

                        </div>
                    </div>

                </form>
            </div>
        </div>
    );
}

export default SignUp;