import React, { useEffect, useState } from "react";
import { Link, useParams, useNavigate } from "react-router-dom";
import api from "../api/axios";
import { FaHeart } from "react-icons/fa";

// ── Toast hook ────────────────────────────────────────────────
function useToast() {
  const [toasts, setToasts] = useState([]);

  const showToast = (message, type = "success") => {
    const id = Date.now();
    setToasts((prev) => [...prev, { id, message, type }]);
    setTimeout(() => {
      setToasts((prev) => prev.filter((t) => t.id !== id));
    }, 3000);
  };

  return { toasts, showToast };
}

// ── Toast UI ──────────────────────────────────────────────────
function ToastContainer({ toasts }) {
  return (
    <div className="fixed top-5 right-5 z-50 flex flex-col gap-2 pointer-events-none">
      {toasts.map((toast) => (
        <div
          key={toast.id}
          className={`
            flex items-center gap-3 px-4 py-3 rounded-lg shadow-lg text-sm font-medium
            text-white min-w-[220px] max-w-xs
            animate-fade-in-down
            ${toast.type === "error" ? "bg-red-500" : "bg-[#AE4329]"}
          `}
        >
          <span className="text-base">
            {toast.type === "error" ? "✕" : "✓"}
          </span>
          {toast.message}
        </div>
      ))}
    </div>
  );
}

// ── Main Component ────────────────────────────────────────────
function CategoryProducts() {
  const { id } = useParams();
  const navigate = useNavigate();

  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [wishlist, setWishlist] = useState({});
  const { toasts, showToast } = useToast();

  useEffect(() => {
    api
      .get(`/products/category/${id}`)
      .then((res) => setProducts(res.data))
      .catch((err) => console.log(err))
      .finally(() => setLoading(false));
  }, [id]);

  const handleWhishlist = (itemId) => {
    setWishlist((prev) => ({
      ...prev,
      [itemId]: !prev[itemId],
    }));
  };

  const handleAddToBag = (item) => {
    const user = JSON.parse(localStorage.getItem("user"));

    let guestId = localStorage.getItem("guest_id");

    if (!guestId) {
      guestId = Date.now().toString();
      localStorage.setItem("guest_id", guestId);
    }

    console.log("ADD TO CART", {
      product_id: item.id,
      variant_id: item.selected_variant || item.variants?.[0]?.id,
    });

    api
      .post("/add-to-cart", {
        member_id: user?.id || null,
        guest_id: guestId,
        product_id: item.id,
        variant_id: item.selected_variant || item.variants?.[0]?.id,
        quantity: 1,
      })
      .then(() => {
        showToast("Added to cart");
        window.dispatchEvent(new Event("cartUpdated"));
        navigate("/checkout");
      })
      .catch((err) => {
        console.error(err);
        showToast("Error adding to cart", "error");
      });
  };

  return (
    <div className="min-h-screen bg-white">
      {/* Toast Notifications */}
      <ToastContainer toasts={toasts} />

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Category Title */}
        <div className="mb-8 border-gray-200 pb-4 text-center">
          <h2 className="text-2xl sm:text-3xl font-semibold text-blue-600 tracking-tight">
            {products[0]?.category_name || "Products"}
          </h2>
          <div className="flex items-center justify-center gap-2 mt-3">
            <span className="w-8 h-px bg-blue-600"></span>
            <span className="w-2 h-2 bg-blue-600 rounded-full"></span>
            <span className="w-8 h-px bg-blue-600"></span>
          </div>
        </div>

        {/* Loading */}
        {loading ? (
          <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 sm:gap-6">
            {[...Array(10)].map((_, i) => (
              <div key={i} className="animate-pulse">
                <div className="bg-gray-200 rounded-lg h-40 sm:h-48 md:h-56"></div>
                <div className="mt-3 space-y-2">
                  <div className="h-3 bg-gray-200 rounded w-1/2"></div>
                  <div className="h-4 bg-gray-200 rounded w-3/4"></div>
                  <div className="h-4 bg-gray-200 rounded w-1/3"></div>
                </div>
              </div>
            ))}
          </div>
        ) : products.length === 0 ? (
          <div className="text-center py-16">
            <p className="text-gray-500 text-lg">No products found</p>
          </div>
        ) : (
          <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 sm:gap-6">
            {products.map((item) => (
              <div
                key={item.id}
                className="group bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300"
              >
                {/* Image */}
                <div className="relative rounded-t-lg overflow-hidden bg-gray-50">
                  <Link
                    to={`/product/${item.id}`}
                    className="block"
                    style={{ textDecoration: "none", color: "inherit" }}
                    onClick={(e) => {
                      e.preventDefault();
                      navigate(`/product/${item.id}`);
                    }}
                  >
                    <img
                      src={item.image?.[0] || "/default-product.png"}
                      alt={item.name}
                      className="w-full h-40 sm:h-48 md:h-56 object-contain p-4 cursor-pointer transition-transform duration-300 group-hover:scale-105"
                    />
                  </Link>

                  {/* Wishlist Icon */}
                  <button
                    onClick={(e) => {
                      e.preventDefault();
                      e.stopPropagation();
                      handleWhishlist(item.id);
                    }}
                    className={`
                      absolute top-3 right-3
                      w-8 h-8
                      flex items-center justify-center
                      rounded-full
                      bg-white shadow-md
                      transition-all duration-200
                      hover:scale-110
                      ${wishlist[item.id] ? "text-red-500" : "text-gray-300 hover:text-red-400"}
                    `}
                  >
                    <FaHeart className="text-sm" />
                  </button>
                </div>

                {/* Content */}
                <div className="p-3 sm:p-4">
                  <p className="text-xs text-gray-400 uppercase tracking-wide">
                    {item.brand}
                  </p>

                  <div className="mt-1 mb-2 flex items-start justify-between gap-2">
                    <Link
                      to={`/product/${item.id}`}
                      className="flex-1 no-underline"
                      style={{ color: "inherit", textDecoration: "none" }}
                      onClick={(e) => {
                        e.preventDefault();
                        navigate(`/product/${item.id}`);
                      }}
                    >
                          <p className="text-xs sm:text-sm text-gray-800 font-semibold leading-tight line-clamp-2">
                        {item.name}
                      </p>
                    </Link>

                    {item.offer_price && item.discount_percentage > 0 && (
                      <span
                        className="shrink-0 px-2.5 py-1 rounded-md text-xs font-semibold"
                        style={{
                          background: "#E8F5E9",
                          color: "#2E7D32",
                        }}
                      >
                        {parseFloat(item.discount_percentage)}% OFF
                      </span>
                    )}
                  </div>

                  <div className="flex items-center gap-2">
                    <span className="text-xl font-bold text-gray-900">
                      ₹
                      {(item.offer_price || item.price)?.toLocaleString()}
                    </span>

                    {item.offer_price && item.discount_percentage > 0 && (
                      <span className="text-sm text-gray-400 line-through">
                        MRP ₹{item.price?.toLocaleString()}
                      </span>
                    )}

                  </div>

                  {/* Button */}
                  <button
                    className="
                      w-full mt-3
                      bg-gray-900 text-white
                      text-xs sm:text-sm font-medium
                      py-2 sm:py-2.5
                      rounded-md
                      opacity-100 sm:opacity-0
                      translate-y-0 sm:translate-y-2
                      sm:group-hover:opacity-100
                      sm:group-hover:translate-y-0
                      hover:bg-gray-800
                      transition-all duration-300
                    "
                    onClick={() => handleAddToBag(item)}
                  >
                    Add to Bag
                  </button>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>

      {/* Toast animation style */}
      <style>{`
        @keyframes fade-in-down {
          from { opacity: 0; transform: translateY(-8px); }
          to   { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-down {
          animation: fade-in-down 0.25s ease-out forwards;
        }
      `}</style>
    </div>
  );
}

export default CategoryProducts;