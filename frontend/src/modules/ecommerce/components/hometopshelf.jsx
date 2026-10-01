import React, { useState, useEffect } from "react";
import { GoVerified } from "react-icons/go";
import { FaShippingFast, FaHeart, FaShoppingBag } from "react-icons/fa";
import { TbTruckReturn } from "react-icons/tb";
import { GrUserExpert } from "react-icons/gr";
import api from "../api/axios";
import { Link, useNavigate } from "react-router-dom";
import ProgressBar from "./progressbar";

const TRUST_ITEMS = [
  { icon: <GoVerified size={22} />, title: "100% Authentic", desc: "All products directly sourced from brands" },
  { icon: <FaShippingFast size={22} />, title: "Free Shipping", desc: "On all orders above ₹299" },
  { icon: <GrUserExpert size={22} />, title: "Certified Advisors", desc: "Get expert consultations anytime" },
  { icon: <TbTruckReturn size={22} />, title: "Easy Returns", desc: "Hassle-free pick-ups & refunds" },
];

const isValid = (val) => {
  if (val === null || val === undefined) return false;
  const num = parseFloat(String(val).replace(/,/g, ""));
  return !isNaN(num) && num > 0;
};

const toNum = (val) => parseFloat(String(val).replace(/,/g, "")) || 0;

const shuffleArray = (array) => {
  return [...array].sort(() => Math.random() - 0.5);
};
function HomeTopShelf() {
  const [viralProducts, setViralProducts] = useState([]);
  const [wishlist, setWishlist] = useState({});
  const [toasts, setToasts] = useState([]);
  const navigate = useNavigate();

  useEffect(() => {
    api.get("/viral-products")
      .then((res) => setViralProducts(res.data))
      .catch((err) => console.error(err));
  }, []);

  const toggleWishlist = (id) =>
    setWishlist((prev) => ({ ...prev, [id]: !prev[id] }));



  const showToast = (name, type = "success") => {
    const id = Date.now();
    setToasts((prev) => [...prev, { id, name, type }]);
    setTimeout(() => {
      setToasts((prev) => prev.filter((t) => t.id !== id));
    }, 3500);
  };

  const handleAddToBag = (item) => {

    const user = JSON.parse(localStorage.getItem("user"));

    let guestId = localStorage.getItem("guest_id");

    // create guest id if not exists
    if (!guestId) {
      guestId = Date.now().toString();
      localStorage.setItem("guest_id", guestId);
    }

    api.post("/add-to-cart", {
      member_id: user?.id || null,
      guest_id: guestId,
      product_id: item.id,
      variant_id:
        item.selected_variant ||
        item.variants?.[0]?.id,
      quantity: 1,
    })
      .then(() => {

        window.dispatchEvent(new Event("cartUpdated"));

        showToast(item.name, "success");

      })
      .catch((err) => {
        console.error("ADD TO CART ERROR:", err);
        showToast("Could not add item — please try again", "error");
      });
  };
  return (
    <>
      <style>{`
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600;700&display=swap');

        .tsh-wrap { font-family: 'DM Sans', sans-serif; }

        .tsh-card {
          background: #fff;
          border: 1px solid #f0f0f0;
          border-radius: 14px;
          overflow: hidden;
          transition: box-shadow 0.28s ease, transform 0.28s ease;
        }
        .tsh-card:hover {
          box-shadow: 0 10px 32px rgba(0,0,0,0.09);
          transform: translateY(-3px);
        }
        .tsh-img-wrap {
          position: relative;
          background: #f8f8f8;
          overflow: hidden;
        }
        .tsh-img-wrap img {
          width: 100%; object-fit: contain;
          transition: transform 0.4s ease; display: block;
        }
        .tsh-card:hover .tsh-img-wrap img { transform: scale(1.06); }

        .tsh-wish {
          position: absolute; top: 9px; right: 9px;
          width: 30px; height: 30px; border-radius: 50%;
          background: #fff; border: none; cursor: pointer;
          display: flex; align-items: center; justify-content: center;
          box-shadow: 0 2px 8px rgba(0,0,0,0.10);
          transition: transform 0.18s;
        }
        .tsh-wish:hover { transform: scale(1.18); }

        .tsh-body { padding: 12px 14px 14px; }
        .tsh-brand {
          font-size: 10px; font-weight: 600; color: #aaa;
          text-transform: uppercase; letter-spacing: 0.07em; margin-bottom: 3px;
        }
        .tsh-name {
          font-size: 13px; font-weight: 500; color: #1a1a1a;
          line-height: 1.4;
          display: -webkit-box; -webkit-line-clamp: 2;
          -webkit-box-orient: vertical; overflow: hidden; min-height: 36px;
        }
        .tsh-price { font-size: 19px; font-weight: 700; color: #111; letter-spacing: -0.01em; line-height: 1; }
        .tsh-price-label { font-size: 11px; color: #aaa; margin-left: 5px; }

        .tsh-btn {
          width: 100%; margin-top: 10px;
          background: #111; color: #fff; border: none;
          border-radius: 8px; padding: 9px 0;
          font-size: 12px; font-weight: 600; cursor: pointer;
          letter-spacing: 0.02em;
          display: flex; align-items: center; justify-content: center; gap: 6px;
          transition: background 0.2s; opacity: 1; transform: translateY(0);
        }
        @media(min-width:640px){
          .tsh-btn { opacity: 0; transform: translateY(5px); transition: opacity 0.25s, transform 0.25s, background 0.2s; }
          .tsh-card:hover .tsh-btn { opacity: 1; transform: translateY(0); }
        }
        .tsh-btn:hover { background: #333; }

        .trust-bar {
          display: grid; grid-template-columns: repeat(4,1fr);
          border: 1px solid #ebebeb; border-radius: 14px; overflow: hidden;
        }
        @media(max-width:768px){ .trust-bar { grid-template-columns: repeat(2,1fr); } }

        .trust-item {
          padding: 22px 18px; display: flex; align-items: flex-start; gap: 13px;
          border-right: 1px solid #ebebeb; transition: background 0.2s;
        }
        .trust-item:last-child { border-right: none; }
        @media(max-width:768px){
          .trust-item:nth-child(2) { border-right: none; }
          .trust-item:nth-child(3) { border-right: 1px solid #ebebeb; border-top: 1px solid #ebebeb; }
          .trust-item:nth-child(4) { border-top: 1px solid #ebebeb; border-right: none; }
        }
        .trust-item:hover { background: #fafafa; }
        .trust-icon {
          width: 38px; height: 38px; border-radius: 10px;
          background: #f4f4f4; display: flex; align-items: center;
          justify-content: center; color: #111; flex-shrink: 0;
        }
        .trust-title { font-size: 13px; font-weight: 600; color: #111; margin-bottom: 3px; }
        .trust-desc  { font-size: 12px; color: #888; line-height: 1.45; }

        .tsh-section-title {
          font-family: 'Playfair Display', serif;
          font-size: clamp(20px, 2.5vw, 28px);
          font-weight: 700; color: #111; letter-spacing: -0.01em;
        }
          .toast-wrap {
  position: fixed; bottom: 28px; right: 28px;
  z-index: 9999; display: flex; flex-direction: column; gap: 10px;
  pointer-events: none;
}
.toast-item {
  display: flex; align-items: center; gap: 12px;
  background: #fff; color: #1a1a1a;
  padding: 14px 16px; border-radius: 14px;
  min-width: 260px; max-width: 320px;
  pointer-events: all;
  border: 1px solid #f0f0f0;
  box-shadow: 0 8px 32px rgba(0,0,0,0.12), 0 1px 4px rgba(0,0,0,0.06);
  position: relative; overflow: hidden;
  animation: toastIn 0.35s cubic-bezier(.22,1,.36,1);
}
.toast-item::before {
  content: ''; position: absolute; left: 0; top: 0; bottom: 0;
  width: 4px; border-radius: 14px 0 0 14px;
}
.toast-item.success::before { background: #2e7d32; }
.toast-item.error::before   { background: #c0392b; }
.toast-icon {
  width: 36px; height: 36px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.toast-item.success .toast-icon { background: #e8f5e9; }
.toast-item.error   .toast-icon { background: #fdecea; }
.toast-title { font-weight: 600; font-size: 13px; color: #1a1a1a; margin: 0 0 2px; }
.toast-sub   { font-size: 11.5px; color: #888; margin: 0; }
.toast-progress {
  position: absolute; bottom: 0; left: 4px; right: 0; height: 2px;
  transform-origin: left;
  animation: shrinkBar 3.5s linear forwards;
}
.toast-item.success .toast-progress { background: #e8f5e9; }
.toast-item.error   .toast-progress { background: #fdecea; }
@keyframes toastIn {
  from { opacity: 0; transform: translateY(16px) scale(0.96); }
  to   { opacity: 1; transform: translateY(0) scale(1); }
}
@keyframes shrinkBar {
  from { transform: scaleX(1); }
  to   { transform: scaleX(0); }
}
      `}</style>

      <div className="bg-white tsh-wrap">
        <div className="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-10 py-8 space-y-14">

          {/* ── Top Shelf Banner ── */}
          <div className="rounded-2xl overflow-hidden" style={{ boxShadow: '0 2px 16px rgba(0,0,0,0.07)' }}>
            <img src="/images/B1.jpg" alt="Top Shelf" className="w-full h-auto object-contain block" />
          </div>

          {/* ── Trending Now ── */}
          <div>
            <div className="flex items-center justify-between mb-6">
              <div>
                <p className="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-1">✦ Curated for you</p>
                <h2 className="text-2xl sm:text-3xl font-bold text-gray-900">Trending Now</h2>
              </div>
              <button
                onClick={() => navigate("/allproduct")}
                className="hidden sm:inline-flex items-center gap-1.5 text-sm font-semibold text-gray-700 border border-gray-200 rounded-full px-4 py-2 hover:border-gray-800 hover:text-gray-900 transition-all"
              >
                View All →
              </button>
            </div>

            <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-5 lg:gap-6">
              {shuffleArray(viralProducts).slice(0, 4).map((item, index) => {
                const offerValid = isValid(item.offer_price);
                const discountValid = isValid(item.discount_percentage);
                const displayPrice = offerValid ? toNum(item.offer_price) : toNum(item.price);

                return (
                  <div key={index} className="tsh-card">
                    <Link
                      to={`/product/${item.id}`}
                      className="tsh-img-wrap"
                      style={{
                        height: "clamp(150px, 20vw, 230px)",
                        display: "block",
                      }}
                      onClick={(e) => {
                        e.preventDefault();
                        navigate(`/product/${item.id}`);
                      }}
                    >
                      <img
                        src={item.image?.[0] || "/default-product.png"}
                        alt={item.name}
                        style={{ height: "100%" }}
                      />

                      <button
                        className="tsh-wish"
                        onClick={(e) => {
                          e.preventDefault();
                          e.stopPropagation();
                          toggleWishlist(item.id);
                        }}
                      >
                        <FaHeart
                          size={12}
                          style={{
                            color: wishlist[item.id] ? "#e53e3e" : "#ccc",
                            transition: "color 0.2s",
                          }}
                        />
                      </button>
                    </Link>

                    <div className="tsh-body">
                      <p className="tsh-brand">{item.brand}</p>

                      {/* Name + Discount badge */}
                      <div style={{ display: "flex", alignItems: "flex-start", justifyContent: "space-between", gap: 6, marginBottom: 8 }}>
                        <Link
                          to={`/product/${item.id}`}
                          className="tsh-name"
                          style={{
                            textDecoration: "none",
                            color: "inherit",
                          }}
                          onClick={(e) => {
                            e.preventDefault();
                            navigate(`/product/${item.id}`);
                          }}
                        >
                          {item.name}
                        </Link>
                        {discountValid && (
                          <span style={{
                            flexShrink: 0, fontSize: 13, fontWeight: 700,
                            background: "#e8f5e9", color: "#2e7d32",
                            borderRadius: 6, padding: "3px 7px",
                            lineHeight: 1.4, marginTop: 2,
                          }}>
                            {parseFloat(item.discount_percentage)}% off
                          </span>
                        )}
                      </div>

                      {/* Price row */}
                      <div style={{ display: "flex", alignItems: "baseline", gap: 6 }}>
                        <span className="tsh-price">₹{displayPrice}</span>
                        {offerValid && (
                          <span style={{ fontSize: 13, color: "#aaa", textDecoration: "line-through", fontWeight: 400 }}>
                            MRP ₹{toNum(item.price)}
                          </span>
                        )}
                        {/* <span className="tsh-price-label">incl. taxes</span> */}
                      </div>

                      <button className="tsh-btn" onClick={() => handleAddToBag(item)}>
                        <FaShoppingBag size={11} /> Add to Bag
                      </button>
                    </div>
                  </div>
                );
              })}
            </div>

            {/* Mobile view all */}
            <div className="mt-6 text-center sm:hidden">
              <button
                onClick={() => navigate("/allproduct")}
                className="inline-flex items-center gap-2 text-sm font-semibold border border-gray-800 text-gray-800 rounded-full px-6 py-2.5 hover:bg-gray-800 hover:text-white transition-all"
              >
                View All Products →
              </button>
            </div>
          </div>

          {/* ── Chat Banner ── */}
          <div className="rounded-2xl overflow-hidden" style={{ boxShadow: '0 2px 16px rgba(0,0,0,0.07)' }}>
            <img src="/images/B2.jpg" alt="Chat Now" className="w-full object-contain block" style={{ maxHeight: 260 }} />
          </div>

          {/* ── Trust Bar ── */}
          <ProgressBar />
        </div>
      </div>
      <div className="toast-wrap">
        {toasts.map((t) => (
          <div key={t.id} className={`toast-item ${t.type}`}>
            <div className="toast-icon">
              {t.type === "success" ? (
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                  <path d="M3 8l3.5 3.5L13 5" stroke="#2e7d32" strokeWidth="2"
                    strokeLinecap="round" strokeLinejoin="round" />
                </svg>
              ) : (
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                  <path d="M8 5v4M8 11v.5" stroke="#c0392b" strokeWidth="2" strokeLinecap="round" />
                </svg>
              )}
            </div>
            <div style={{ flex: 1 }}>
              <p className="toast-title">
                {t.type === "success" ? "Added to bag!" : "Something went wrong"}
              </p>
              <p className="toast-sub">{t.name}</p>
            </div>
            <div className="toast-progress" />
          </div>
        ))}
      </div>
    </>
  );
}

export default HomeTopShelf;