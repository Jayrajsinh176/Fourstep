import React, { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import api from "../api/axios";
import { FaTrash } from "react-icons/fa";
import { BiSolidCoupon } from "react-icons/bi";

const ORDER_TYPES = [
  {
    value: "purchase",
    label: "Purchase",
    wallets: ["purchase", "cashback"]
  },
  {
    value: "consistency",
    label: "Consistency",
    wallets: ["consistency", "purchase", "cashback"]
  },
  {
    value: "purchase_cashback",
    label: "Purchase & Cashback",
    wallets: ["purchase", "cashback"]
  },
];

const WALLET_META = {
  repurchase: {
    label: "Repurchase Wallet",
    bg: "bg-blue-50", border: "border-blue-100", text: "text-blue-700",
    toggleOn: "bg-blue-600", hex: "#1d4ed8",
    balKey: "repurchase_wallet"
  },
  consistency: {
    label: "Consistency Wallet",
    bg: "bg-purple-50", border: "border-purple-100", text: "text-purple-700",
    toggleOn: "bg-purple-600", hex: "#7c3aed",
    balKey: "consistency_wallet"
  },
  purchase: {
    label: "Purchase Wallet",
    bg: "bg-teal-50", border: "border-teal-100", text: "text-teal-700",
    toggleOn: "bg-teal-600", hex: "#0f766e",
    balKey: "purchase_wallet"
  },
  cashback: {
    label: "Cashback Wallet",
    bg: "bg-amber-50", border: "border-amber-100", text: "text-amber-700",
    toggleOn: "bg-amber-600", hex: "#b45309",
    balKey: "cashback_wallet"
  },
};

const isEligible = (productTypes, selectedType) => {
  if (!selectedType) return false;
  const normalize = (str) => String(str).toLowerCase().trim().replace(/\s+/g, "_");
  const selected = normalize(selectedType);
  const normalized = (productTypes || []).map(t => normalize(t));
  return normalized.includes(selected);
};

function Toast({ toast, onDismiss }) {
  if (!toast) return null;

  const isSuccess = toast.type === "success";
  const isError = toast.type === "error";

  const bg = isSuccess
    ? "bg-green-600"
    : isError
    ? "bg-red-500"
    : "bg-gray-800";

  return (
    <div
      className="fixed top-5 left-1/2 z-[9999] toast-enter"
      style={{ transform: "translateX(-50%)" }}
    >
      <div className={`${bg} text-white flex items-center gap-3 px-4 py-3 rounded-xl shadow-xl`}>
        <p className="text-sm font-medium flex-1">{toast.message}</p>
        <button onClick={onDismiss}>✕</button>
      </div>
    </div>
  );
}

function StepIndicator({ step }) {
  const steps = ["Identify", "Details", "Review"];
  return (
    <div className="flex items-center justify-center mb-8">
      {steps.map((label, i) => {
        const idx = i + 1;
        const active = idx === step;
        const done = idx < step;
        return (
          <React.Fragment key={label}>
            <div className="flex flex-col items-center">
              <div className={`w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold border-2 transition-all
                ${done ? "bg-gray-900 border-gray-900 text-white"
                  : active ? "bg-white border-gray-900 text-gray-900"
                    : "bg-white border-gray-200 text-gray-300"}`}>
                {done ? (
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="white" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round">
                    <polyline points="20 6 9 17 4 12" />
                  </svg>
                ) : idx}
              </div>
              <span className={`text-[10px] mt-1 font-medium ${active ? "text-gray-900" : "text-gray-400"}`}>{label}</span>
            </div>
            {i < steps.length - 1 && (
              <div className={`flex-1 h-px mx-2 mb-4 ${done ? "bg-gray-900" : "bg-gray-200"}`} />
            )}
          </React.Fragment>
        );
      })}
    </div>
  );
}

function WalletRow({ walletKey, wallet, enabled, onToggle, manualAmount, onManualChange, eligibleTotal }) {
  const meta = WALLET_META[walletKey];
  if (!meta) return null;

  const balance = wallet ? (wallet[meta.balKey] || 0) : 0;
  const maxUsable = Math.min(balance, eligibleTotal);

  const handleInputChange = (e) => {
    const raw = e.target.value.replace(/[^0-9.]/g, "").replace(/(\..*)\./g, "$1");
    if (raw === "" || raw === ".") { onManualChange(raw); return; }
    const num = parseFloat(raw);
    if (!isNaN(num)) {
      onManualChange(num > maxUsable ? String(maxUsable) : raw);
    }
  };

  const usable = enabled
    ? (manualAmount !== "" && !isNaN(parseFloat(manualAmount))
      ? Math.min(parseFloat(manualAmount), maxUsable)
      : maxUsable)
    : 0;

  return (
    <div className={`rounded-xl border ${meta.border} ${meta.bg} p-3 mb-2 last:mb-0`}>
      <div className="flex items-center justify-between mb-1.5">
        <div className="flex items-center gap-2">
          <div className="w-6 h-6 rounded-full bg-white/70 flex items-center justify-center">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke={meta.hex} strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
              <rect x="2" y="7" width="20" height="14" rx="2" /><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2" />
            </svg>
          </div>
          <span className={`text-xs font-semibold ${meta.text}`}>{meta.label}</span>
        </div>
        <span className={`text-sm font-bold ${meta.text}`}>₹{balance}</span>
      </div>

      <div className="flex items-center justify-between">
        <span className="text-xs text-gray-600 font-medium">Use this wallet</span>
        <div
          className={`relative w-10 h-[22px] rounded-full transition-colors duration-200 ${balance === 0 || eligibleTotal === 0
            ? "bg-gray-200 opacity-40 cursor-not-allowed"
            : enabled ? meta.toggleOn : "bg-gray-200 cursor-pointer"}`}
          onClick={() => { if (balance === 0 || eligibleTotal === 0) return; onToggle(); }}
        >
          <div className={`absolute top-[3px] w-4 h-4 bg-white rounded-full shadow-sm transition-all duration-200 ${enabled ? "left-[22px]" : "left-[3px]"}`} />
        </div>
      </div>

      {enabled && balance > 0 && (
        <div className="mt-2.5 slide-down">
          <label className="text-[10px] text-gray-500 font-medium block mb-1">
            Enter amount to use (max ₹{maxUsable})
          </label>
          <div className="flex gap-2 items-center">
            <input
              type="text"
              inputMode="numeric"
              value={manualAmount}
              onChange={handleInputChange}
              placeholder={`Up to ₹${maxUsable}`}
              className={`flex-1 border rounded-lg px-3 py-1.5 text-sm outline-none focus:border-gray-400 transition ${meta.border} bg-white`}
            />
            <button
              onClick={() => onManualChange(String(maxUsable))}
              className={`text-xs px-2.5 py-1.5 rounded-lg font-semibold border ${meta.border} ${meta.text} bg-white/80 hover:bg-white transition`}
            >
              Max
            </button>
          </div>
          {usable > 0 && (
            <div className="mt-1.5 flex items-center gap-1.5 bg-white/70 border border-white rounded-lg px-2.5 py-1.5">
              <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#16a34a" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><polyline points="20 6 9 17 4 12" /></svg>
              <span className="text-xs text-green-700 font-medium">₹{usable} will be deducted</span>
            </div>
          )}
        </div>
      )}
    </div>
  );
}

function Checkout() {
  const navigate = useNavigate();

  const [products, setProducts] = useState([]);
  const [flowStep, setFlowStep] = useState("choose");
  const [showModal, setShowModal] = useState(false);
  const [uiStep, setUiStep] = useState(1);

  const [memberUserId, setMemberUserId] = useState("");
  const [memberPass, setMemberPass] = useState("");
  const [memberChecked, setMemberChecked] = useState(false);
  const [memberError, setMemberError] = useState("");
  const [wallet, setWallet] = useState(null);
  const [memberInput, setMemberInput] = useState("");

  const [forOther, setForOther] = useState(false);
  const [otherIdInput, setOtherIdInput] = useState("");
  const [otherMember, setOtherMember] = useState(null);
  const [otherError, setOtherError] = useState("");
  const [selectedAddress, setSelectedAddress] = useState("");

  const [orderType, setOrderType] = useState("");

  const [walletToggles, setWalletToggles] = useState({});
  const [walletAmounts, setWalletAmounts] = useState({});

  const [deliveryType, setDeliveryType] = useState("courier");

  const [guestEmail, setGuestEmail] = useState("");
  const [guestOtp, setGuestOtp] = useState("");
  const [otpSent, setOtpSent] = useState(false);
  const [otpVerified, setOtpVerified] = useState(false);
  const [guestName, setGuestName] = useState("");
  const [guestPhone, setGuestPhone] = useState("");
  const [guestAddress, setGuestAddress] = useState("");
  const [guestRefId, setGuestRefId] = useState("");
  const [guestEmailError, setGuestEmailError] = useState("");
  const [guestOtpError, setGuestOtpError] = useState("");
  const [guestFormError, setGuestFormError] = useState("");
  const [guestSession, setGuestSession] = useState(null);

  const [couponInput, setCouponInput] = useState("");
  const [couponApplied, setCouponApplied] = useState(false);
  const [couponDiscount, setCouponDiscount] = useState(0);
  const [couponError, setCouponError] = useState("");
  const [showCouponBox, setShowCouponBox] = useState(false);

  const [loading, setLoading] = useState(false);
const [toast, setToast] = useState(null);

const showToast = (message, type = "info") => {
  setToast({ message, type });
  setTimeout(() => setToast(null), 4000);
};
  const isRealMember = memberChecked && wallet?.is_mlm;

  const [user, setUser] = useState(() => JSON.parse(localStorage.getItem("user")));
  const [guestId, setGuestId] = useState(localStorage.getItem("guest_id"));

  const dynamicOrderTypes = [
    ...new Set(
      products.flatMap(item =>
        (item.product?.order_types || []).map(t => t.toLowerCase().trim())
      )
    )
  ];

  useEffect(() => {
    if (!orderType && dynamicOrderTypes.length > 0) {
      setOrderType(dynamicOrderTypes[0]);
    }
  }, [dynamicOrderTypes]);

  useEffect(() => {
    setWalletToggles({});
    setWalletAmounts({});
  }, [orderType]);

  const fetchWallet = async (userData) => {
    try {
      const res = await api.get(`/wallet/${userData.member_id}?user_id=${userData.id}`);
      setWallet(res.data);
    } catch (err) {
      console.log("Wallet fetch failed after refresh", err);
    }
  };

  useEffect(() => {
    const fetchCart = () => {
      const currentUser = JSON.parse(localStorage.getItem("user"));
      const currentGuestId = localStorage.getItem("guest_id");
      if (currentGuestId !== guestId) setGuestId(currentGuestId);
      if (currentUser?.id) {
        api.get(`/cart?member_id=${currentUser.id}&guest_id=${currentGuestId || ""}`)
          .then(res => setProducts(res.data.filter(i => i && i.product)))
          .catch(err => console.log(err));
      } else if (currentGuestId) {
        api.get(`/cart?guest_id=${currentGuestId}`)
          .then(res => setProducts(res.data.filter(i => i && i.product)))
          .catch(err => console.log(err));
      }
    };
    fetchCart();
    window.addEventListener("cartUpdated", fetchCart);
    return () => window.removeEventListener("cartUpdated", fetchCart);
  }, [user, guestId]);

  useEffect(() => {
    const storedUser = JSON.parse(localStorage.getItem("user"));
    if (storedUser?.id && storedUser?.member_id) {
      setUser(storedUser);
      setMemberChecked(true);
      setMemberInput(storedUser.member_id);
      setUiStep(2);
      fetchWallet(storedUser);
    }
  }, []);

  useEffect(() => {
    window.dispatchEvent(new Event("cartUpdated"));
  }, []);

  const handleDelete = (id) => {
    api.delete(`/cart/${id}?guest_id=${guestId || ""}&member_id=${user?.id || ""}`)
      .then(() => {
        setProducts(products.filter(item => item.id !== id));
        window.dispatchEvent(new Event("cartUpdated"));
      }).catch(err => console.log(err));
  };

  const updateQty = (id, newQty) => {
    if (newQty < 1) return;
    api.put(`/cart/${id}`, { quantity: newQty, guest_id: guestId, member_id: user?.id })
      .then(() => {
        setProducts(products.map(item => item.id === id ? { ...item, quantity: newQty } : item));
        window.dispatchEvent(new Event("cartUpdated"));
      }).catch(err => console.log(err));
  };

  const handleMemberLogin = async () => {
    setMemberError("");
    if (!memberUserId || !memberPass) { setMemberError("Enter User ID and password"); return; }
    setLoading(true);
    try {
      const res = await api.post("/login", { user_id: memberUserId, password: memberPass });
      if (!res.data.status) { setMemberError(res.data.message || "Invalid credentials"); setLoading(false); return; }
      const loggedUser = res.data.data;
      localStorage.setItem("user", JSON.stringify(loggedUser));
      setUser(loggedUser);
      const gId = localStorage.getItem("guest_id");
      if (gId && loggedUser?.id) {
        try {
          await api.post("/merge-cart", { guest_id: gId, member_id: loggedUser.id });
          window.dispatchEvent(new Event("cartUpdated"));
        } catch (err) { console.log("Cart merge failed", err); }
      }
      setMemberInput(loggedUser.member_id || "");
      setMemberChecked(true);
      try {
        const walletRes = await api.get(`/wallet/${loggedUser.member_id}?user_id=${loggedUser.id}`);
        setWallet(walletRes.data);
      } catch (err) { console.log("Wallet fetch failed", err); }
      setShowModal(false);
      setUiStep(2);
    } catch { setMemberError("Login failed. Check credentials."); }
    setLoading(false);
  };

  const handleSendOtp = async () => {
    setGuestEmailError("");
    if (!guestEmail || !/\S+@\S+\.\S+/.test(guestEmail)) { setGuestEmailError("Enter a valid email"); return; }
    setLoading(true);
    try {
      await api.post("/send-otp", { email: guestEmail });
     showToast(`OTP sent to ${guestEmail}`, "success");
      setOtpSent(true);
    } catch { setGuestEmailError("Failed to send OTP. Try again."); }
    setLoading(false);
  };

  const handleVerifyOtp = async () => {
  setGuestOtpError("");

  if (!guestOtp) {
    setGuestOtpError("Enter the OTP");
    return;
  }

  setLoading(true);

  try {
    const res = await api.post("/verify-otp", {
      email: guestEmail,
      otp: guestOtp,
    });

    const guestUser = res.data.user;

    // Save user session
    setGuestSession(guestUser);
    setUser(guestUser);

    localStorage.setItem(
      "user",
      JSON.stringify(guestUser)
    );

    // Load saved guest details
    setGuestName(guestUser.fullname || "");
    setGuestPhone(guestUser.mobile_no || "");
    setGuestAddress(guestUser.address || "");

    // Merge guest cart
    if (guestId && guestUser?.id) {
      await api.post("/merge-cart", {
        guest_id: guestId,
        member_id: guestUser.id,
      });

      const cartRes = await api.get(
        `/cart?member_id=${guestUser.id}`
      );

      setProducts(
        cartRes.data.filter(i => i && i.product)
      );
    }

    setOtpVerified(true);
    setShowModal(false);

    // Existing guest => skip details form
    if (
      guestUser.fullname &&
      guestUser.mobile_no &&
      guestUser.address
    ) {
      setFlowStep("completed");
    } else {
      // New guest => show details form
      setFlowStep("guest-form");
    }

    setUiStep(2);

  } catch (err) {
    setGuestOtpError("Invalid OTP. Try again.");
  }

  setLoading(false);
};

  const handleCheckOther = async () => {
    setOtherError("");
    if (!otherIdInput.trim()) { setOtherError("Enter recipient's Member ID"); return; }
    try {
      const res = await api.get(`/member-info/${otherIdInput}`);
      setOtherMember(res.data);
      const addrs = buildAddresses(res.data);
      if (addrs.length > 0) setSelectedAddress(addrs[0]);
    } catch { setOtherError("Member not found"); }
  };

  const buildAddresses = (m) => {
    if (!m) return [];
    const arr = [];
    if (m.address) arr.push(m.address);
    if (m.shipping_address && m.shipping_address !== m.address) arr.push(m.shipping_address);
    return arr;
  };

  const totalMrp = products.reduce((sum, item) => {
    if (!item || !item.product) return sum;
    return sum + (item.product.price || 0) * (item.quantity || 1);
  }, 0);

  const selectedTypeObj = ORDER_TYPES.find(t => t.value === orderType);

  const eligibleProducts = products.filter(item =>
    isEligible(item.product?.order_types || [], orderType) === true
  );

  const eligibleTotal = eligibleProducts.reduce((sum, item) => {
    return sum + (item.product.price || 0) * (item.quantity || 1);
  }, 0);

  const subtotal = totalMrp - couponDiscount;

  const getWalletBalance = (wKey) => {
    if (!wallet) return 0;
    const meta = WALLET_META[wKey];
    return meta ? (wallet[meta.balKey] || 0) : 0;
  };

  const totalWalletDeduction = (selectedTypeObj?.wallets || []).reduce((sum, wKey) => {
    if (!walletToggles[wKey]) return sum;
    const balance = getWalletBalance(wKey);
    const maxUsable = Math.min(balance, eligibleTotal);
    const raw = walletAmounts[wKey];
    const amt = raw !== "" && !isNaN(parseFloat(raw))
      ? Math.min(parseFloat(raw), maxUsable)
      : maxUsable;
    return sum + amt;
  }, 0);

  const finalTotal = Math.max(subtotal - totalWalletDeduction, 0);

  const handleApplyCoupon = async () => {
    if (!couponInput.trim()) { setCouponError("Enter a coupon code"); return; }
    try {
      const res = await api.post("/apply-coupon", { code: couponInput, total: totalMrp });
      if (res.data.valid) {
        setCouponDiscount(res.data.discount || 0);
        setCouponApplied(true);
        setCouponError("");
      } else {
        setCouponError(res.data.message || "Invalid coupon");
        setCouponApplied(false);
        setCouponDiscount(0);
      }
    } catch { setCouponError("Invalid or expired coupon"); }
  };

  const handleRemoveCoupon = () => {
    setCouponApplied(false);
    setCouponDiscount(0);
    setCouponInput("");
    setCouponError("");
  };

  const handleCheckout = () => {
    const currentUser = JSON.parse(localStorage.getItem("user"));
    const isMember = memberChecked && wallet?.is_mlm;
   if (isMember && !orderType) {
  showToast("Please select an order type to continue", "error");
  return;
}
    if (!memberChecked && flowStep === "guest-form") {
      if (!guestName || !guestPhone || !guestAddress) { setGuestFormError("Name, phone and address are required."); return; }
    }
    console.log("CURRENT USER", currentUser);
const deliveryAddress =
  forOther && otherMember
    ? selectedAddress
    : memberChecked
      ? (
          currentUser?.shipping_address ||
          currentUser?.address ||
          ""
        )
      : guestAddress;
    const deliveryMemberId = forOther ? otherIdInput : memberInput;

    const walletBreakdown = {};
    (selectedTypeObj?.wallets || []).forEach(wKey => {
      if (walletToggles[wKey]) {
        const balance = getWalletBalance(wKey);
        const maxUsable = Math.min(balance, eligibleTotal);
        const raw = walletAmounts[wKey];
        const amt = raw !== "" && !isNaN(parseFloat(raw))
          ? Math.min(parseFloat(raw), maxUsable)
          : maxUsable;
        walletBreakdown[wKey] = amt;
      }
    });

    localStorage.setItem("checkoutItems", JSON.stringify(products));
    localStorage.setItem("walletBreakdown", JSON.stringify(walletBreakdown));
    localStorage.setItem("walletAmount", JSON.stringify(totalWalletDeduction));
    localStorage.setItem("mlmMemberId", JSON.stringify(memberInput));
    localStorage.setItem("forOtherMember", JSON.stringify(forOther));
    localStorage.setItem("deliveryMemberId", JSON.stringify(deliveryMemberId));
    localStorage.setItem("deliveryAddress", JSON.stringify(deliveryAddress));
    localStorage.setItem("otherMember", JSON.stringify(otherMember));
    localStorage.setItem("orderType", JSON.stringify(memberChecked ? orderType : ""));
    localStorage.setItem("orderTypeLabel", JSON.stringify(selectedTypeObj?.label || ""));
    localStorage.setItem("couponDiscount", JSON.stringify(couponDiscount));
    localStorage.setItem("couponCode", JSON.stringify(couponInput));
    localStorage.setItem("deliveryType", JSON.stringify(deliveryType));
    localStorage.setItem("guestName", JSON.stringify(guestName));
    localStorage.setItem("guestPhone", JSON.stringify(guestPhone));
    localStorage.setItem("guestRefId", JSON.stringify(guestRefId));

    navigate("/checkoutfinal");
  };

  const currentUser = JSON.parse(localStorage.getItem("user"));
 const isGuest =
  !memberChecked &&
  otpVerified;

  if (products.length === 0) {
    return (
      <div className="min-h-[70vh] flex flex-col items-center justify-center text-center px-4">
        <style>{`@keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-12px)} } .float-img{animation:float 3s ease-in-out infinite}`}</style>
        <img src="https://cdn-icons-png.flaticon.com/512/11329/11329060.png" alt="Empty Cart" className="w-52 sm:w-64 mb-6 float-img opacity-90" />
        <h2 className="text-2xl sm:text-3xl font-bold text-gray-800 mb-2">Your cart is empty 🛒</h2>
        <p className="text-gray-500 max-w-md mb-6 text-sm sm:text-base">Looks like you haven't added anything yet.</p>
        <div className="flex gap-3 flex-wrap justify-center">
          <button onClick={() => navigate('/allproduct')} className="bg-black text-white px-6 py-3 rounded-lg shadow-md transition">Continue Shopping</button>
          <button onClick={() => navigate('/')} className="border border-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-100 transition">Go to Home</button>
        </div>
      </div>
    );
  }
const hideGuestDetailsForm =
  !memberChecked &&
  otpVerified &&
  guestName &&
  guestPhone &&
  guestAddress;

  return (
    <>
     <Toast
      toast={toast}
      onDismiss={() => setToast(null)}
    />
      <style>{`
        .cart-scroll::-webkit-scrollbar{width:4px}.cart-scroll::-webkit-scrollbar-track{background:transparent}.cart-scroll::-webkit-scrollbar-thumb{background:#e5e7eb;border-radius:99px}
        @keyframes fadeInUp{from{opacity:0;transform:translateY(16px) scale(0.98)}to{opacity:1;transform:translateY(0) scale(1)}}
        .member-modal{animation:fadeInUp 0.22s ease both}
        @keyframes slideDown{from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:translateY(0)}}
        .slide-down{animation:slideDown 0.2s ease both}
        .tab-btn{transition:all 0.15s}
        @keyframes toastIn{
  from{
    opacity:0;
    transform:translateX(-50%) translateY(-12px) scale(0.95);
  }
  to{
    opacity:1;
    transform:translateX(-50%) translateY(0) scale(1);
  }
}

.toast-enter{
  animation:toastIn 0.22s ease both;
}
      `}</style>

      <div className="bg-[#f8f8f6] p-4 sm:p-6 min-h-screen">
        <div className="max-w-6xl mx-auto flex flex-col lg:flex-row gap-6 items-start mt-6 sm:mt-10">

          {/* LEFT */}
          <div className="w-full lg:flex-1 space-y-4">

            <div className="bg-white rounded-2xl shadow-sm p-4 sm:p-6 border border-gray-100">
              <h2 className="text-xl font-bold mb-5 text-gray-800 tracking-tight">
                My Bag <span className="text-gray-400 text-sm font-normal">({products.length} items)</span>
              </h2>
              <div className="cart-scroll flex flex-col divide-y divide-gray-100 overflow-y-auto max-h-[480px] pr-1">
                {products.filter(item => item && item.product).map((item) => {
                  const elig = isEligible(item.product?.order_types || [], orderType);
                  return (
                    <div key={item.id} className="py-4 first:pt-0 last:pb-0">
                      <div className="flex gap-3 sm:gap-5">
                        <div className="relative shrink-0">
                          <img src={
                            Array.isArray(item.product.image)
                              ? item.product.image[0]
                              : JSON.parse(item.product.image || "[]")[0]
                          } alt="product"
                            className="w-20 h-20 sm:w-28 sm:h-28 rounded-xl object-cover border border-gray-100 shadow-sm" />
                        </div>
                        <div className="flex-1 flex justify-between items-start">
                          <div className="flex-1 pr-2">
                            <p className="text-xs sm:text-sm text-gray-400 font-medium tracking-wide uppercase">{item.product.brand}</p>
                            <h3 className="font-semibold text-sm sm:text-base text-gray-800 mt-0.5">
                              {item.product.name}
                              {item.product.packing_size && (
                                <span className="text-gray-500 font-normal ml-1">
                                  ({item.product.packing_size})
                                </span>
                              )}
                            </h3>
                            <div className="flex items-center gap-2 mt-3 mb-2">
                              <button onClick={() => updateQty(item.id, item.quantity - 1)} className="w-7 h-7 border border-gray-200 rounded-lg bg-gray-50 text-gray-600 hover:bg-gray-100 transition flex items-center justify-center">−</button>
                              <span className="font-semibold text-sm w-5 text-center">{item.quantity}</span>
                              <button onClick={() => updateQty(item.id, item.quantity + 1)} className="w-7 h-7 border border-gray-200 rounded-lg bg-gray-50 text-gray-600 hover:bg-gray-100 transition flex items-center justify-center">+</button>
                            </div>
                            <p className="text-sm font-bold text-gray-900">
                              ₹{Number((item.product?.price || 0) * (item.quantity || 1)).toFixed(2)}
                            </p>
                            {isRealMember && item.product?.order_types?.length > 0 && (
                              <div className="mt-2">
                                <p className="text-[10px] text-gray-400 mb-1">Eligible for:</p>
                                <div className="flex flex-wrap gap-1">
                                  {item.product.order_types.map((type, i) => (
                                    <span key={i} className="text-[10px] px-2 py-1 rounded-full bg-blue-50 text-blue-600 border border-blue-100">
                                      {type.replace(/_/g, " ")}
                                    </span>
                                  ))}
                                </div>
                              </div>
                            )}
                            {isRealMember && orderType && elig === false && (
                              <div className="mt-2 flex items-center gap-1.5 bg-red-50 border border-red-100 rounded-lg px-2.5 py-1.5 slide-down">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#ef4444" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                                  <circle cx="12" cy="12" r="10" /><line x1="12" y1="8" x2="12" y2="12" /><line x1="12" y1="16" x2="12.01" y2="16" />
                                </svg>
                                <span className="text-xs text-red-600 font-medium">Not eligible for <strong>{selectedTypeObj?.label}</strong></span>
                              </div>
                            )}
                            {isRealMember && orderType && elig === true && (
                              <div className="mt-2 flex items-center gap-1.5 bg-green-50 border border-green-100 rounded-lg px-2.5 py-1.5 slide-down">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#16a34a" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                                  <polyline points="20 6 9 17 4 12" />
                                </svg>
                                <span className="text-xs text-green-700 font-medium">Eligible for {selectedTypeObj?.label}</span>
                              </div>
                            )}
                          </div>
                          <button onClick={() => handleDelete(item.id)} className="p-1.5 rounded-lg hover:bg-red-50 transition">
                            <FaTrash className="text-red-400 cursor-pointer text-sm" />
                          </button>
                        </div>
                      </div>
                    </div>
                  );
                })}
              </div>
            </div>

          {isGuest && !hideGuestDetailsForm && (
              <div className="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6 slide-down">
                <h3 className="font-bold text-gray-800 mb-4">Delivery Details</h3>
                {guestFormError && <p className="text-xs text-red-500 mb-3">{guestFormError}</p>}
                <div className="space-y-3">
                  <div>
                    <label className="text-xs font-medium text-gray-500 mb-1 block">Full Name *</label>
                    <input type="text" placeholder="Your full name" value={guestName} onChange={e => setGuestName(e.target.value)}
                      className="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-gray-400 transition" />
                  </div>
                  <div>
                    <label className="text-xs font-medium text-gray-500 mb-1 block">Phone Number *</label>
                    <input type="tel" placeholder="10-digit mobile number" value={guestPhone} onChange={e => setGuestPhone(e.target.value)}
                      className="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-gray-400 transition" />
                  </div>
                  <div>
                    <label className="text-xs font-medium text-gray-500 mb-1 block">Delivery Address *</label>
                    <textarea placeholder="Full delivery address" value={guestAddress} onChange={e => setGuestAddress(e.target.value)} rows={3}
                      className="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-gray-400 transition resize-none" />
                  </div>
                  <div>
                    <label className="text-xs font-medium text-gray-500 mb-1 block">Reference Member ID <span className="text-gray-300">(optional)</span></label>
                    <input type="text" placeholder="4step member ID who referred you" value={guestRefId} onChange={e => setGuestRefId(e.target.value)}
                      className="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-gray-400 transition" />
                  </div>
                  <div>
                    <label className="text-xs font-medium text-gray-500 mb-2 block">Delivery Type</label>
                    <div className="flex gap-2">
                      {["courier", "pickup"].map(dt => (
                        <button key={dt} onClick={() => setDeliveryType(dt)}
                          className={`flex-1 py-2.5 rounded-xl text-sm font-medium border transition tab-btn capitalize
                            ${deliveryType === dt ? "bg-gray-900 text-white border-gray-900" : "bg-white text-gray-600 border-gray-200 hover:border-gray-400"}`}>
                          {dt === "courier" ? "🚚 Courier" : "🏪 Pickup"}
                        </button>
                      ))}
                    </div>
                  </div>
                </div>
              </div>
            )}

            {memberChecked && (
              <div className="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-5 slide-down">
                <label className="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2 block">Delivery Type</label>
                <div className="flex gap-2">
                  {["courier", "pickup"].map(dt => (
                    <button key={dt} onClick={() => setDeliveryType(dt)}
                      className={`flex-1 py-2.5 rounded-xl text-sm font-medium border transition tab-btn capitalize
                        ${deliveryType === dt ? "bg-gray-900 text-white border-gray-900" : "bg-white text-gray-600 border-gray-200 hover:border-gray-400"}`}>
                      {dt === "courier" ? "🚚 Courier" : "🏪 Pickup"}
                    </button>
                  ))}
                </div>
              </div>
            )}

            <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
              <button onClick={() => setShowCouponBox(!showCouponBox)} className="w-full flex items-center justify-between px-4 sm:px-6 py-4 hover:bg-gray-50 transition">
                <div className="flex items-center gap-3">
                  <div className="w-8 h-8 rounded-lg bg-orange-50 flex items-center justify-center">
                    <BiSolidCoupon className="text-orange-500 text-lg" />
                  </div>
                  <div className="text-left">
                    <p className="text-sm font-semibold text-gray-800">
                      {couponApplied
                        ? <span className="text-green-600">Applied: <span className="font-bold uppercase">{couponInput}</span> · -₹{couponDiscount}</span>
                        : "Apply Coupon"}
                    </p>
                    <p className="text-xs text-gray-400 mt-0.5">{couponApplied ? "Discount applied to your order" : "Save more with coupons & offers"}</p>
                  </div>
                </div>
                <div className="flex items-center gap-2">
                  {couponApplied && (
                    <button onClick={e => { e.stopPropagation(); handleRemoveCoupon(); }} className="text-xs text-red-500 font-medium px-2 py-1 rounded-lg hover:bg-red-50 transition">Remove</button>
                  )}
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"
                    style={{ transform: showCouponBox ? "rotate(180deg)" : "rotate(0deg)", transition: "transform 0.2s" }}>
                    <polyline points="6 9 12 15 18 9" />
                  </svg>
                </div>
              </button>
              {showCouponBox && (
                <div className="slide-down px-4 sm:px-6 pb-4 border-t border-gray-50 pt-4">
                  <div className="flex gap-2">
                    <input type="text" placeholder="Enter coupon code" value={couponInput}
                      onChange={e => { setCouponInput(e.target.value.toUpperCase()); setCouponError(""); }}
                      className="flex-1 border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-800 placeholder-gray-300 outline-none focus:border-gray-400 transition uppercase" />
                    <button onClick={handleApplyCoupon} disabled={couponApplied} className="px-4 py-2.5 bg-black text-white text-sm font-medium rounded-xl hover:bg-gray-900 transition disabled:opacity-40 disabled:cursor-not-allowed">Apply</button>
                  </div>
                  {couponError && <p className="text-xs text-red-500 mt-2">{couponError}</p>}
                  {couponApplied && (
                    <div className="mt-2 flex items-center gap-1.5 bg-green-50 border border-green-100 rounded-lg px-3 py-2">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#16a34a" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><polyline points="20 6 9 17 4 12" /></svg>
                      <span className="text-xs text-green-700 font-medium">
                        ₹{Number(couponDiscount).toFixed(2)} discount applied!
                      </span>
                    </div>
                  )}
                </div>
              )}
            </div>

          </div>

          {/* RIGHT */}
          <div className="w-full lg:w-80 space-y-4">

            {isRealMember && (
              <div className="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-5 slide-down">
                <div className="flex items-center justify-between bg-gray-50 rounded-xl px-3 py-2.5 border border-gray-100 mb-3">
                  <div>
                    <p className="text-[11px] text-gray-400 font-medium">4Step Member</p>
                    <p className="text-sm font-bold text-gray-800">{memberInput || currentUser?.fullname}</p>
                  </div>
                  <div className="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#16a34a" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round"><polyline points="20 6 9 17 4 12" /></svg>
                  </div>
                </div>

                <div className="flex items-center justify-between py-1 mb-1">
                  <span className="text-xs text-gray-600 font-medium">Order for another member</span>
                  <div
                    className={`relative w-10 h-[22px] rounded-full cursor-pointer transition-colors duration-200 ${forOther ? "bg-gray-900" : "bg-gray-200"}`}
                    onClick={() => { setForOther(f => !f); setOtherMember(null); setOtherIdInput(""); setSelectedAddress(""); setOtherError(""); }}
                  >
                    <div className={`absolute top-[3px] w-4 h-4 bg-white rounded-full shadow-sm transition-all duration-200 ${forOther ? "left-[22px]" : "left-[3px]"}`} />
                  </div>
                </div>

                {forOther && (
                  <div className="slide-down space-y-2.5 mt-2">
                    <div className="flex gap-2">
                      <input type="text" placeholder="Recipient's Member ID" value={otherIdInput}
                        onChange={e => { setOtherIdInput(e.target.value); setOtherMember(null); setOtherError(""); }}
                        className="flex-1 border border-gray-200 rounded-xl px-3 py-2 text-sm outline-none focus:border-gray-400 transition" />
                      <button onClick={handleCheckOther} className="px-3 py-2 bg-black text-white text-xs font-semibold rounded-xl hover:bg-gray-900 transition whitespace-nowrap">Check</button>
                    </div>
                    {otherError && <p className="text-xs text-red-500">{otherError}</p>}
                    {otherMember && (
                      <div className="bg-blue-50 border border-blue-100 rounded-xl p-3">
                        <p className="text-xs font-semibold text-blue-800 mb-3">📦 Deliver to: {otherMember.fullname}</p>
                        <div className="space-y-3">
                          <div>
                            <label className="text-[11px] text-gray-500 font-medium block mb-1">Delivery Address</label>
                            <textarea value={selectedAddress} onChange={(e) => setSelectedAddress(e.target.value)} rows={3}
                              placeholder="Enter delivery address"
                              className="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm outline-none focus:border-gray-400 transition resize-none bg-white" />
                          </div>
                          <div className="flex flex-wrap gap-2">
                            {buildAddresses(otherMember).map((addr, i) => (
                              <button key={i} type="button" onClick={() => setSelectedAddress(addr)}
                                className={`text-xs px-3 py-1.5 rounded-lg border transition ${selectedAddress === addr ? "bg-black text-white border-black" : "bg-white border-gray-200 text-gray-600"}`}>
                                Address {i + 1}
                              </button>
                            ))}
                          </div>
                        </div>
                      </div>
                    )}
                  </div>
                )}

                <div className="mt-3.5">
                  <label className="text-[11px] font-semibold text-gray-400 uppercase tracking-wide mb-2 block">Order Type</label>
                  <div className="flex flex-wrap gap-2">
                    {ORDER_TYPES.map((opt) => {
                      const isActive = orderType === opt.value;
                      return (
                        <button key={opt.value} onClick={() => setOrderType(opt.value)}
                          className={`px-3 py-1.5 rounded-full text-sm border transition
                            ${isActive ? "bg-blue-600 text-white border-blue-600" : "bg-white text-gray-700 border-gray-300 hover:bg-gray-100"}`}>
                          {opt.label}
                        </button>
                      );
                    })}
                  </div>
                </div>

                {isRealMember && orderType && selectedTypeObj?.wallets?.length > 0 && (
                  <div className="mt-3.5 slide-down">
                    <label className="text-[11px] font-semibold text-gray-400 uppercase tracking-wide mb-2 block">
                      Wallets
                    </label>
                    {selectedTypeObj.wallets.map((wKey) => (
                      <WalletRow
                        key={wKey}
                        walletKey={wKey}
                        wallet={wallet}
                        enabled={!!walletToggles[wKey]}
                        onToggle={() => setWalletToggles(prev => ({ ...prev, [wKey]: !prev[wKey] }))}
                        manualAmount={walletAmounts[wKey] ?? ""}
                        onManualChange={(val) => setWalletAmounts(prev => ({ ...prev, [wKey]: val }))}
                        eligibleTotal={eligibleTotal}
                      />
                    ))}
                  </div>
                )}
              </div>
            )}

            {isGuest && (
              <div className="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 slide-down">
                <div className="flex items-center gap-3">
                  <div className="w-9 h-9 bg-gray-50 rounded-xl border border-gray-100 flex items-center justify-center">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#374151" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                      <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" /><circle cx="12" cy="7" r="4" />
                    </svg>
                  </div>
                  <div>
                    <p className="text-[11px] text-gray-400 font-medium">Ordering as Guest</p>
                    <p className="text-sm font-bold text-gray-800">{guestEmail}</p>
                  </div>
                </div>
              </div>
            )}

            <div className="bg-white rounded-2xl shadow-sm p-4 sm:p-6 border border-gray-100 sticky top-6">
              <h4 className="font-bold mb-4 text-gray-800 text-base tracking-tight">Price Details</h4>
              <div className="text-sm text-gray-600 space-y-3">
                <div className="flex justify-between">
                  <span>Total MRP</span>
                  <span className="font-medium text-gray-800">  ₹{Number(totalMrp).toFixed(2)}</span>
                </div>
                {couponApplied && couponDiscount > 0 && (
                  <div className="flex justify-between text-orange-600 font-medium">
                    <span>Coupon ({couponInput})</span>
                    <span>-₹{Number(couponDiscount).toFixed(2)}</span>
                  </div>
                )}
                {isRealMember && (selectedTypeObj?.wallets || []).map(wKey => {
                  if (!walletToggles[wKey]) return null;
                  const balance = getWalletBalance(wKey);
                  const maxUsable = Math.min(balance, eligibleTotal);
                  const raw = walletAmounts[wKey];
                  const amt = raw !== "" && !isNaN(parseFloat(raw))
                    ? Math.min(parseFloat(raw), maxUsable)
                    : maxUsable;
                  if (amt <= 0) return null;
                  const meta = WALLET_META[wKey];
                  return (
                    <div key={wKey} className={`flex justify-between font-medium ${meta?.text || "text-blue-600"}`}>
                      <span>{meta?.label} Deduction</span>
                      <span>-₹{Number(amt).toFixed(2)}</span>
                    </div>
                  );
                })}
              </div>
              <hr className="my-4 border-gray-100" />
              <div className="flex justify-between font-bold text-lg text-gray-900 pt-1">
                <span>Total</span>
                <span>₹{Number(finalTotal).toFixed(2)}</span>
              </div>
              <button
                className="w-full bg-gray-900 hover:bg-black active:scale-[0.98] text-white py-3.5 rounded-xl mt-5 font-semibold text-sm tracking-wide transition-all duration-150 shadow-lg shadow-gray-900/20"
                onClick={() => {
                  if (!memberChecked && !isGuest) { setShowModal(true); setFlowStep("choose"); }
                  else handleCheckout();
                }}
              >
                Proceed to Checkout
              </button>
              <p className="text-center text-xs text-gray-400 mt-3">🔒 Secure payment</p>
            </div>
          </div>
        </div>
      </div>

      {showModal && (
        <div
          className="fixed inset-0 z-50 flex items-center justify-center px-4"
          style={{ backdropFilter: "blur(6px)", WebkitBackdropFilter: "blur(6px)", backgroundColor: "rgba(0,0,0,0.35)" }}
          onClick={e => { if (e.target === e.currentTarget) setShowModal(false); }}
        >
          <div className="member-modal bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 border border-gray-100">
            <StepIndicator step={flowStep === "choose" ? 1 : 2} />

            {flowStep === "choose" && (
              <>
                <div className="flex flex-col items-center mb-6">
                  <div className="w-12 h-12 rounded-full bg-gray-50 border border-gray-100 flex items-center justify-center mb-3">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#374151" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
                      <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" /><circle cx="12" cy="7" r="4" />
                    </svg>
                  </div>
                  <h3 className="text-base font-bold text-gray-900">How would you like to proceed?</h3>
                  <p className="text-xs text-gray-400 mt-1 text-center">Choose your account type to continue</p>
                </div>
                <div className="space-y-3">
                  <button onClick={() => setFlowStep("member")}
                    className="w-full flex items-center gap-4 p-4 border-2 border-gray-900 rounded-xl bg-gray-50 hover:bg-gray-100 transition text-left">
                    <div className="w-10 h-10 rounded-xl bg-gray-900 flex items-center justify-center shrink-0">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                      </svg>
                    </div>
                    <div>
                      <p className="text-sm font-bold text-gray-900">Registered Member</p>
                      <p className="text-xs text-gray-500 mt-0.5">Login with your member ID & password</p>
                    </div>
                  </button>
                  <button onClick={() => setFlowStep("guest")}
                    className="w-full flex items-center gap-4 p-4 border border-gray-200 rounded-xl hover:bg-gray-50 transition text-left">
                    <div className="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center shrink-0">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#374151" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" /><circle cx="12" cy="7" r="4" />
                      </svg>
                    </div>
                    <div>
                      <p className="text-sm font-bold text-gray-900">Guest User</p>
                      <p className="text-xs text-gray-500 mt-0.5">Continue with email OTP verification</p>
                    </div>
                  </button>
                </div>
                <button onClick={() => setShowModal(false)} className="w-full text-xs text-gray-400 py-3 hover:text-gray-600 transition mt-2">Cancel</button>
              </>
            )}

            {flowStep === "member" && (
              <>
                <div className="flex items-center gap-2 mb-5">
                  <button onClick={() => setFlowStep("choose")} className="p-1.5 rounded-lg hover:bg-gray-100 transition">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#374151" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><polyline points="15 18 9 12 15 6" /></svg>
                  </button>
                  <h3 className="text-base font-bold text-gray-900">Fourstep Member Login</h3>
                </div>
                <div className="space-y-3 mb-4">
                  <div>
                    <label className="text-xs font-medium text-gray-500 mb-1.5 block">User ID</label>
                    <input type="text" placeholder="Enter your User ID" value={memberUserId}
                      onChange={e => { setMemberUserId(e.target.value); setMemberError(""); }}
                      className="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-800 placeholder-gray-300 outline-none focus:border-gray-400 transition" />
                  </div>
                  <div>
                    <label className="text-xs font-medium text-gray-500 mb-1.5 block">Password</label>
                    <input type="password" placeholder="Your password" value={memberPass}
                      onChange={e => { setMemberPass(e.target.value); setMemberError(""); }}
                      onKeyDown={e => e.key === "Enter" && handleMemberLogin()}
                      className="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-800 placeholder-gray-300 outline-none focus:border-gray-400 transition" />
                  </div>
                  {memberError && <p className="text-xs text-red-500">{memberError}</p>}
                </div>
                <button onClick={handleMemberLogin} disabled={loading}
                  className="w-full bg-black text-white py-2.5 rounded-xl text-sm font-medium mb-3 hover:bg-gray-900 transition disabled:opacity-40">
                  {loading ? "Verifying…" : "Login & Continue"}
                </button>
              </>
            )}

            {flowStep === "guest" && (
              <>
                <div className="flex items-center gap-2 mb-5">
                  <button onClick={() => setFlowStep("choose")} className="p-1.5 rounded-lg hover:bg-gray-100 transition">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#374151" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><polyline points="15 18 9 12 15 6" /></svg>
                  </button>
                  <h3 className="text-base font-bold text-gray-900">Continue as Guest</h3>
                </div>
                {!otpSent ? (
                  <>
                    <div className="mb-4">
                      <label className="text-xs font-medium text-gray-500 mb-1.5 block">Email Address</label>
                      <input type="email" placeholder="you@example.com" value={guestEmail}
                        onChange={e => { setGuestEmail(e.target.value); setGuestEmailError(""); }}
                        onKeyDown={e => e.key === "Enter" && handleSendOtp()}
                        className="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm placeholder-gray-300 outline-none focus:border-gray-400 transition" />
                      {guestEmailError && <p className="text-xs text-red-500 mt-1.5">{guestEmailError}</p>}
                    </div>
                    <button onClick={handleSendOtp} disabled={loading}
                      className="w-full bg-black text-white py-2.5 rounded-xl text-sm font-medium mb-3 hover:bg-gray-900 transition disabled:opacity-40">
                      {loading ? "Sending…" : "Send OTP"}
                    </button>
                  </>
                ) : (
                  <>
                    <div className="mb-3 bg-green-50 border border-green-100 rounded-xl px-3 py-2.5 text-xs text-green-700 font-medium">
                      OTP sent to <strong>{guestEmail}</strong>
                    </div>
                    <div className="mb-4">
                      <label className="text-xs font-medium text-gray-500 mb-1.5 block">Enter OTP</label>
                      <input type="text" placeholder="6-digit OTP" value={guestOtp} maxLength={6}
                        onChange={e => { setGuestOtp(e.target.value); setGuestOtpError(""); }}
                        onKeyDown={e => e.key === "Enter" && handleVerifyOtp()}
                        className="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm placeholder-gray-300 outline-none focus:border-gray-400 transition tracking-widest text-center font-bold" />
                      {guestOtpError && <p className="text-xs text-red-500 mt-1.5">{guestOtpError}</p>}
                    </div>
                    <button onClick={handleVerifyOtp} disabled={loading}
                      className="w-full bg-black text-white py-2.5 rounded-xl text-sm font-medium mb-2 hover:bg-gray-900 transition disabled:opacity-40">
                      {loading ? "Verifying…" : "Verify OTP"}
                    </button>
                    <button onClick={() => { setOtpSent(false); setGuestOtp(""); setGuestOtpError(""); }}
                      className="w-full text-xs text-gray-400 py-1.5 hover:text-gray-600 transition">
                      Change email
                    </button>
                  </>
                )}
              </>
            )}
          </div>
        </div>
      )}
    </>
  );
}

export default Checkout;