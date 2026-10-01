import React, { useEffect, useMemo, useState } from "react";
import { useNavigate } from "react-router-dom";
import api from "../api/axios";

const PAYMENT_METHODS = [
  {
    value: "purchase_balance",
    title: "Purchase Balance",
    subtitle: "Use your available wallet purchase balance",
    icon: (
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
        <rect x="2" y="6" width="20" height="12" rx="2" />
        <path d="M16 12h.01" />
      </svg>
    ),
  },
  {
    value: "cashback",
    title: "Cashback Points",
    subtitle: "Use your earned cashback balance",
    icon: (
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
        <circle cx="12" cy="12" r="10" />
        <path d="M8 12h8M12 8v8" />
      </svg>
    ),
  },
  {
    value: "online",
    title: "Credit / Debit Card",
    subtitle: "Visa, Mastercard, RuPay, Net Banking",
    icon: (
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
        <rect x="2" y="5" width="20" height="14" rx="2" />
        <line x1="2" y1="10" x2="22" y2="10" />
      </svg>
    ),
  },
  {
    value: "upi",
    title: "UPI Payment",
    subtitle: "Pay via any UPI app instantly",
    icon: (
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
        <path d="M12 2L2 7l10 5 10-5-10-5z" />
        <path d="M2 17l10 5 10-5" />
        <path d="M2 12l10 5 10-5" />
      </svg>
    ),
  },
];

const WALLET_META = {
  repurchase: { label: "Repurchase Wallet", text: "text-blue-600" },
  consistency: { label: "Consistency Wallet", text: "text-purple-600" },
  purchase:    { label: "Purchase Wallet",    text: "text-teal-600" },
  cashback:    { label: "Cashback Wallet",    text: "text-amber-600" },
};

const ORDER_TYPE_ID_MAP = {
  repurchase: 1,
  consistency: 2,
  builtup: 3,
  purchase_cashback: 4,
};

const CHECKOUT_STORAGE_KEYS = [
  "checkoutItems", "walletBreakdown", "walletAmount", "mlmMemberId",
  "forOtherMember", "deliveryMemberId", "deliveryAddress", "otherMember",
  "orderType", "orderTypeLabel", "couponDiscount", "couponCode",
  "deliveryType", "guestName", "guestPhone", "guestRefId",
];

const formatMoney = (amount) =>
  `₹${Number(amount || 0).toLocaleString("en-IN")}`;

function CheckoutFinal() {
  const navigate = useNavigate();

  const [orders, setOrders] = useState([]);
  const [paymentMethod, setPaymentMethod] = useState("online");
  const [loading, setLoading] = useState(false);
  const [success, setSuccess] = useState(false);

  const user = JSON.parse(localStorage.getItem("user"));

  const walletBreakdown =
    JSON.parse(localStorage.getItem("walletBreakdown")) || {};

  const walletAmount =
    JSON.parse(localStorage.getItem("walletAmount")) || 0;

  const couponDiscount =
    JSON.parse(localStorage.getItem("couponDiscount")) || 0;

  const couponCode =
    JSON.parse(localStorage.getItem("couponCode")) || "";

  const otherMember =
    JSON.parse(localStorage.getItem("otherMember")) || null;

  const storedDeliveryAddress =
    JSON.parse(localStorage.getItem("deliveryAddress"));

  const deliveryAddress =
    storedDeliveryAddress &&
    storedDeliveryAddress !== "undefined" &&
    storedDeliveryAddress !== "null"
      ? storedDeliveryAddress
      : (
          user?.shipping_address ||
          user?.address ||
          otherMember?.shipping_address ||
          otherMember?.address ||
          ""
        );

  const deliveryMemberId =
    JSON.parse(localStorage.getItem("deliveryMemberId")) || "";

  const orderTypeLabel =
    JSON.parse(localStorage.getItem("orderTypeLabel")) || "";

  const rawOrderType =
    JSON.parse(localStorage.getItem("orderType")) || "";

  const orderTypeId =
    ORDER_TYPE_ID_MAP[rawOrderType] || null;

  const deliveryType =
    JSON.parse(localStorage.getItem("deliveryType")) || "courier";

  const guestName =
    JSON.parse(localStorage.getItem("guestName")) || "";

  const guestPhone =
    JSON.parse(localStorage.getItem("guestPhone")) || "";

  const guestRefId =
    JSON.parse(localStorage.getItem("guestRefId")) || "";

  const forOther =
    JSON.parse(localStorage.getItem("forOtherMember")) || false;

  const mlmMemberId =
    JSON.parse(localStorage.getItem("mlmMemberId")) || null;

  const deliveryName =
    forOther && otherMember?.fullname
      ? otherMember.fullname
      : (user?.fullname || guestName || "Customer");

  const walletBreakdownEntries = Object.entries(walletBreakdown).filter(
    ([, amt]) => Number(amt) > 0
  );

  useEffect(() => {
    const data = JSON.parse(localStorage.getItem("checkoutItems")) || [];
    setOrders(Array.isArray(data) ? data.filter((i) => i?.product) : []);
  }, []);

  const itemTotal = useMemo(
    () => orders.reduce((sum, item) => sum + (item.product?.price || 0) * (item.quantity || 0), 0),
    [orders]
  );

  const finalTotal = Math.max(itemTotal - couponDiscount - walletAmount, 0);

  const cleanupCheckoutStorage = () => {
    CHECKOUT_STORAGE_KEYS.forEach((key) => localStorage.removeItem(key));
  };

  const handlePlaceOrder = async () => {
    if (!orders.length) return alert("No items to place");

    setLoading(true);
    try {
      await api.post("/place-order", {
        member_id: user?.id || null,
        guest_id: user ? null : localStorage.getItem("guest_id"),

     mlm_member_id:
  mlmMemberId &&
  !mlmMemberId.startsWith("GUEST")
    ? mlmMemberId
    : null,

        order_type: rawOrderType,
        order_type_id: orderTypeId,

        delivery_member_id: forOther ? deliveryMemberId : null,
        delivery_address: deliveryAddress,

        delivery_name: forOther
          ? (otherMember?.fullname || "")
          : deliveryName,

        payment_method: paymentMethod,
        delivery_type: deliveryType,

        wallet_used: walletAmount > 0 ? 1 : 0,
        wallet_amount: walletAmount,
        wallet_breakdown: walletBreakdown,

        coupon_code: couponCode || null,
        coupon_discount: couponDiscount,

        total_amount: itemTotal,
        amount_paid: finalTotal,

        guest_name: guestName || null,
        guest_phone: guestPhone || null,
        referral_member_id: guestRefId || null,
      });

      await api.post("/clear-cart", {
        member_id: user?.id || null,
        guest_id: user ? null : localStorage.getItem("guest_id"),
      });

      cleanupCheckoutStorage();
      setSuccess(true);
      setTimeout(() => navigate("/"), 2200);
    } catch (err) {
      console.error("FULL ERROR:", err);
      if (err.response) {
        console.log("BACKEND ERROR:", err.response.data);
        alert(err.response.data.message || "API Error");
      } else {
        alert("Network or server error");
      }
    }

    setLoading(false);
  };

  if (orders.length === 0 && !success) {
    return (
      <div className="min-h-[70vh] flex flex-col items-center justify-center text-center px-4 bg-[#f8f8f6]">
        <h2 className="text-2xl font-bold text-gray-900">No checkout data found</h2>
        <p className="text-gray-500 mt-2 text-sm">Your session may have expired.</p>
        <button onClick={() => navigate("/checkout")} className="mt-5 px-6 py-3 rounded-xl bg-gray-900 text-white font-semibold hover:bg-black transition">
          Back to Checkout
        </button>
      </div>
    );
  }

  return (
    <>
      <style>{`
        @keyframes fadeInUp{from{opacity:0;transform:translateY(16px) scale(0.98)}to{opacity:1;transform:translateY(0) scale(1)}}
        @keyframes slideDown{from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:translateY(0)}}
        @keyframes checkPop{0%{transform:scale(0);opacity:0}60%{transform:scale(1.15)}100%{transform:scale(1);opacity:1}}
        @keyframes progressFill{from{width:0%}to{width:100%}}
        .fade-in-up{animation:fadeInUp 0.25s ease both}
        .slide-down{animation:slideDown 0.2s ease both}
        .check-pop{animation:checkPop 0.4s cubic-bezier(.34,1.56,.64,1) both}
        .progress-fill{animation:progressFill 2s linear forwards}
        .payment-card{transition:border-color 0.15s,box-shadow 0.15s}
        .payment-card:hover{border-color:#d1d5db}
        .payment-card.active{border-color:#111827;box-shadow:0 0 0 1px #111827;background:#fafafa}
        .checkout-btn{transition:all 0.15s}
        .checkout-btn:active{transform:scale(0.98)}
      `}</style>

      {success && (
        <div className="fixed inset-0 z-50 flex items-center justify-center px-4"
          style={{ backdropFilter: "blur(6px)", WebkitBackdropFilter: "blur(6px)", backgroundColor: "rgba(0,0,0,0.35)" }}>
          <div className="fade-in-up bg-white rounded-2xl shadow-2xl w-full max-w-sm p-8 border border-gray-100 text-center">
            <div className="check-pop mx-auto w-16 h-16 bg-green-50 border border-green-100 rounded-full flex items-center justify-center mb-4">
              <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#16a34a" strokeWidth="2.6" strokeLinecap="round" strokeLinejoin="round">
                <path d="M20 6L9 17l-5-5" />
              </svg>
            </div>
            <h2 className="text-xl font-bold text-gray-900">Order Confirmed! 🎉</h2>
            <p className="text-sm text-gray-500 mt-1">Your order has been placed successfully.</p>
            <div className="mt-5 h-1 w-full bg-gray-100 rounded-full overflow-hidden">
              <div className="progress-fill h-full bg-gray-900 rounded-full" />
            </div>
            <p className="text-xs text-gray-400 mt-2">Redirecting you home…</p>
          </div>
        </div>
      )}

      <div className="bg-[#f8f8f6] min-h-screen p-4 sm:p-6">
        <div className="max-w-6xl mx-auto flex flex-col lg:flex-row gap-6 items-start mt-6 sm:mt-10">

          {/* LEFT */}
          <div className="w-full lg:flex-1 space-y-4">

            <div className="bg-white rounded-2xl shadow-sm p-4 sm:p-6 border border-gray-100 fade-in-up">
              <div className="flex items-start justify-between gap-4">
                <div className="flex items-start gap-3">
                  <div className="w-9 h-9 rounded-xl bg-gray-50 border border-gray-100 flex items-center justify-center shrink-0 mt-0.5">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#374151" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                      <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" /><circle cx="12" cy="10" r="3" />
                    </svg>
                  </div>
                  <div>
                    <p className="text-[11px] font-semibold text-gray-400 uppercase tracking-wide mb-0.5">Delivering to</p>
                    <h3 className="font-bold text-gray-900 text-base">{deliveryName}</h3>
                    {guestPhone && <p className="text-xs text-gray-400 mt-0.5">📱 {guestPhone}</p>}
                    <p className="text-sm text-gray-500 mt-0.5 leading-relaxed max-w-sm">
                      {deliveryType === "pickup"
                        ? "🏪 Store Pickup — No delivery address needed"
                        : (deliveryAddress || "Address not available")}
                    </p>
                    <div className="flex flex-wrap gap-2 mt-2">
                      {orderTypeLabel && (
                        <span className="text-[11px] font-semibold bg-gray-100 text-gray-600 px-2.5 py-1 rounded-lg">
                          {orderTypeLabel} Order
                        </span>
                      )}
                      <span className={`text-[11px] font-semibold px-2.5 py-1 rounded-lg capitalize
                        ${deliveryType === "pickup" ? "bg-amber-50 text-amber-700" : "bg-blue-50 text-blue-700"}`}>
                        {deliveryType === "pickup" ? "🏪 Pickup" : "🚚 Courier"}
                      </span>
                    </div>
                    {guestRefId && (
                      <p className="text-[11px] text-gray-400 mt-1.5">Ref: <span className="font-semibold text-gray-600">{guestRefId}</span></p>
                    )}
                  </div>
                </div>
              </div>
            </div>

            <div className="bg-white rounded-2xl shadow-sm p-4 sm:p-6 border border-gray-100 fade-in-up">
              <h2 className="text-xl font-bold mb-5 text-gray-800 tracking-tight">
                Order Summary{" "}
                <span className="text-gray-400 text-sm font-normal">({orders.length} item{orders.length !== 1 ? "s" : ""})</span>
              </h2>
              <div className="flex flex-col divide-y divide-gray-100">
                {orders.map((item, idx) => (
                  <div key={idx} className="py-4 first:pt-0 last:pb-0">
                    <div className="flex gap-3 sm:gap-4">
                      <img src={
                        Array.isArray(item.product.image)
                          ? item.product.image[0]
                          : JSON.parse(item.product.image || "[]")[0]
                      } alt={item.product.name}
                        className="w-16 h-16 sm:w-20 sm:h-20 rounded-xl object-cover border border-gray-100 shadow-sm shrink-0" />
                      <div className="flex-1 flex justify-between items-start">
                        <div>
                          <p className="text-xs text-gray-400 font-medium tracking-wide uppercase">{item.product.brand}</p>
                          <h4 className="font-semibold text-sm sm:text-base text-gray-800 mt-0.5">{item.product.name}</h4>
                          <p className="text-xs text-gray-500">
  {item.product.packing_size}
</p>
                          <p className="text-xs text-gray-400 mt-1">Qty: {item.quantity}</p>
                        </div>
                        <p className="text-sm font-bold text-gray-900 shrink-0">
                          {formatMoney((item.product?.price || 0) * (item.quantity || 1))}
                        </p>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </div>

            <div className="bg-white rounded-2xl shadow-sm p-4 sm:p-6 border border-gray-100 fade-in-up">
              <h2 className="text-xl font-bold mb-5 text-gray-800 tracking-tight">Payment Method</h2>
              <div className="space-y-2.5">
                {PAYMENT_METHODS.map((m) => {
                  const active = paymentMethod === m.value;
                  return (
                    <label key={m.value} className="block cursor-pointer">
                      <div className={`payment-card flex items-start gap-3 p-4 rounded-xl border ${active ? "active" : "border-gray-200"}`}>
                        <input type="radio" name="paymentMethod" value={m.value} checked={active}
                          onChange={() => setPaymentMethod(m.value)} className="mt-0.5 h-4 w-4 accent-gray-900 shrink-0" />
                        <div className={`w-8 h-8 rounded-lg flex items-center justify-center shrink-0 ${active ? "bg-gray-900 text-white" : "bg-gray-50 text-gray-500"}`}>
                          {m.icon}
                        </div>
                        <div className="flex-1">
                          <p className="text-sm font-semibold text-gray-800">{m.title}</p>
                          <p className="text-xs text-gray-400 mt-0.5">{m.subtitle}</p>
                          {m.value === "online" && active && (
                            <div className="flex flex-wrap gap-1.5 mt-2.5 slide-down">
                              {["VISA", "Mastercard", "RuPay", "Net Banking"].map(c => (
                                <span key={c} className="text-[11px] border border-gray-200 bg-white text-gray-500 px-2 py-1 rounded-md font-medium">{c}</span>
                              ))}
                            </div>
                          )}
                        </div>
                        {active && (
                          <div className="w-5 h-5 bg-gray-900 rounded-full flex items-center justify-center shrink-0">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="white" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round">
                              <polyline points="20 6 9 17 4 12" />
                            </svg>
                          </div>
                        )}
                      </div>
                    </label>
                  );
                })}
              </div>
              <p className="text-center text-xs text-gray-400 mt-3">🔒 Secure payment · SSL encrypted</p>
            </div>

          </div>

          {/* RIGHT */}
          <div className="w-full lg:w-80 space-y-4">
            <div className="bg-white rounded-2xl shadow-sm p-4 sm:p-6 border border-gray-100 sticky top-6 fade-in-up">
              <h4 className="font-bold mb-4 text-gray-800 text-base tracking-tight">Price Details</h4>
              <div className="text-sm text-gray-600 space-y-3">
                <div className="flex justify-between">
                  <span>Item Total</span>
                  <span className="font-medium text-gray-800">{formatMoney(itemTotal)}</span>
                </div>
                <div className="flex justify-between">
                  <span>Delivery</span>
                  <span className="font-medium text-green-600">{deliveryType === "pickup" ? "FREE (Pickup)" : "FREE"}</span>
                </div>
                {couponDiscount > 0 && (
                  <div className="flex justify-between text-orange-600 font-medium">
                    <span>Coupon {couponCode && <span className="font-bold uppercase">({couponCode})</span>}</span>
                    <span>-{formatMoney(couponDiscount)}</span>
                  </div>
                )}
                {walletBreakdownEntries.map(([wKey, amt]) => {
                  const meta = WALLET_META[wKey];
                  return (
                    <div key={wKey} className={`flex justify-between font-medium ${meta?.text || "text-blue-600"}`}>
                      <span>{meta?.label || wKey}</span>
                      <span>-{formatMoney(amt)}</span>
                    </div>
                  );
                })}
              </div>
              <hr className="my-4 border-gray-100" />
              <div className="flex justify-between font-bold text-lg text-gray-900 pt-1">
                <span>Total</span>
                <span>{formatMoney(finalTotal)}</span>
              </div>

              {(couponDiscount > 0 || walletAmount > 0) && (
                <div className="mt-3 bg-green-50 border border-green-100 rounded-xl px-3 py-2.5 flex items-center gap-2 slide-down">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#16a34a" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                    <polyline points="20 6 9 17 4 12" />
                  </svg>
                  <span className="text-xs text-green-700 font-medium">
                    You're saving {formatMoney(couponDiscount + walletAmount)} on this order!
                  </span>
                </div>
              )}

              <button onClick={handlePlaceOrder} disabled={loading}
                className="checkout-btn w-full bg-gray-900 hover:bg-black text-white py-3.5 rounded-xl mt-5 font-semibold text-sm tracking-wide shadow-lg shadow-gray-900/20 disabled:opacity-40 disabled:cursor-not-allowed">
                {loading ? (
                  <span className="flex items-center justify-center gap-2">
                    <svg className="animate-spin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" strokeWidth="2.5">
                      <path d="M21 12a9 9 0 1 1-6.219-8.56" />
                    </svg>
                    Processing…
                  </span>
                ) : "Place Order"}
              </button>
              <p className="text-center text-xs text-gray-400 mt-3">🔒 Secure payment</p>
              <div className="flex justify-center gap-5 mt-4 text-xs text-gray-400">
                <span>🚚 Free Shipping</span>
                <span>🔄 Easy Returns</span>
              </div>
            </div>
          </div>

        </div>
      </div>
    </>
  );
}

export default CheckoutFinal;