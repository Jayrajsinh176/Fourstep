import React, { useState, useEffect } from "react";
import { FaStar, FaHeart } from "react-icons/fa";
import api from "../api/axios";
import { Link, useNavigate } from "react-router-dom";

// ── Toast Component ──────────────────────────────────────────────────────────
function Toast({ toasts }) {
  return (
    <div className="fixed top-4 right-4 z-50 flex flex-col gap-2 pointer-events-none">
      {toasts.map((t) => (
        <div
          key={t.id}
          className={`flex items-center gap-2 px-4 py-3 rounded-lg shadow-lg text-sm font-medium text-white
            transition-all duration-300 pointer-events-auto
            ${t.type === "success" ? "bg-[#AE4329]" : "bg-gray-800"}`}
        >
          <span>{t.type === "success" ? "✓" : "✕"}</span>
          <span>{t.message}</span>
        </div>
      ))}
    </div>
  );
}

// ── useToast Hook ────────────────────────────────────────────────────────────
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

// ── AllProducts ──────────────────────────────────────────────────────────────
function AllProducts() {
  const [Products, setProducts] = useState([]);
  const [categories, setCategories] = useState([]);
  const [activeCategory, setActiveCategory] = useState("All");
  const [wishlist, setWishlist] = useState({});
  const navigate = useNavigate();
  const { toasts, showToast } = useToast();

  const toggleWishlist = (id) =>
    setWishlist((prev) => ({ ...prev, [id]: !prev[id] }));

  useEffect(() => {
    api
      .get("/products")
      .then((res) => {
        console.log("PRODUCTS SAMPLE:", res.data[0]);
        setProducts(res.data);
      })
      .catch((err) => console.log(err));

    api
      .get("/categories")
      .then((res) => {
        console.log("CATEGORIES SAMPLE:", res.data[0]);
        setCategories(res.data);
      })
      .catch((err) => console.log(err));
  }, []);

  const handleAddToBag = (item) => {
    const user = JSON.parse(localStorage.getItem("user"));

    let guestId = localStorage.getItem("guest_id");
    if (!guestId) {
      guestId = Date.now().toString();
      localStorage.setItem("guest_id", guestId);
    }

    api
      .post("/add-to-cart", {
        member_id: user?.id || null,
        guest_id: guestId,
        product_id: item.id,
        variant_id: item.selected_variant || item.variants?.[0]?.id,
        quantity: 1,
      })
      .then(() => {
        window.dispatchEvent(new Event("cartUpdated"));
        showToast("Product added to cart", "success");
      })
      .catch((err) => {
        console.error("ADD TO CART ERROR:", err);
        showToast("Error adding to cart", "error");
      });
  };

  const filteredProducts =
    activeCategory === "All"
      ? Products
      : Products.filter((p) => p.category_id === activeCategory);

  return (
    <div className="min-h-screen bg-white px-3 sm:px-4 md:px-6 py-4">
      {/* Toast Notifications */}
      <Toast toasts={toasts} />

      <div className="max-w-7xl mx-auto">
        <div className="section-wrapper mt-8 sm:mt-10">
          <h2 className="text-xl sm:text-2xl font-semibold mb-4 sm:mb-6">
            Products
          </h2>

          {/* Category Filter Buttons */}
          <div className="flex flex-wrap gap-2 mb-6">
            <button
              onClick={() => setActiveCategory("All")}
              className={`px-4 py-1.5 text-xs sm:text-sm font-medium rounded-full border transition-all duration-200
                ${activeCategory === "All"
                  ? "bg-black text-white border-black shadow-md scale-105"
                  : "bg-gray-100 text-gray-600 border-gray-100 hover:bg-gray-200 hover:text-black"
                }`}
            >
              All
            </button>

            {categories.map((cat) => (
              <button
                key={cat.id}
                onClick={() => setActiveCategory(cat.id)}
                className={`px-4 py-1.5 text-xs sm:text-sm font-medium rounded-full border transition-all duration-200
                  ${activeCategory === cat.id
                    ? "bg-black text-white border-black shadow-md scale-105"
                    : "bg-gray-100 text-gray-600 border-gray-100 hover:bg-gray-200 hover:text-black"
                  }`}
              >
                {cat.name}
              </button>
            ))}
          </div>

          {/* 5 Column Responsive Grid */}
          <div className="px-0 sm:px-4 md:px-6 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 sm:gap-5 md:gap-6">
            {filteredProducts.map((item, index) => {
              const hasDiscount =
                item?.offer_price && item?.discount_percentage;

              return (
                <div key={index} className="flex flex-col group cursor-pointer">
                  {/* Image */}
                  <div className="relative rounded overflow-hidden bg-white">
                    <Link
                      to={`/product/${item.id}`}
                      onClick={(e) => {
                        e.preventDefault();
                        navigate(`/product/${item.id}`);
                      }}
                    >
                      <img
                        src={item.image?.[0] || "/default-product.png"}
                        alt="Product"
                        className="w-full h-32 sm:h-36 md:h-40 object-contain transition-transform duration-300 group-hover:scale-105"
                      />
                    </Link>
                    <FaHeart
                      className="absolute top-2 right-2 text-xs sm:text-sm cursor-pointer transition-colors duration-200"
                      style={{
                        color: wishlist[item.id] ? "#e53e3e" : "#d1d5db",
                      }}
                      onClick={(e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        toggleWishlist(item.id);
                      }}
                    />
                  </div>

                  {/* Content */}
                  <div className="mt-2 sm:mt-3">
                    <p className="text-xs sm:text-sm font-medium text-gray-400">
                      {item.brand}
                    </p>

                    {/* Name + Discount */}
                    <div className="flex items-start justify-between gap-2 mt-1 mb-2">
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

                      {hasDiscount && (
                        <span className="shrink-0 px-2 py-1 rounded-md bg-green-100 text-green-700 text-[11px] font-semibold">
                          {parseFloat(item.discount_percentage)}% OFF
                        </span>
                      )}
                    </div>

                    {/* Price */}
                    <div className="flex items-center gap-2 mt-2">
                      <span className="text-lg sm:text-xl font-bold text-gray-900">
                        ₹{(hasDiscount ? item.offer_price : item.price)?.toLocaleString()}
                      </span>

                      {hasDiscount && (
                        <span className="text-xs sm:text-sm text-gray-400 line-through">
                          MRP ₹{item.price?.toLocaleString()}
                        </span>
                      )}

                      {/* <span className="text-[11px] text-gray-400">
                        incl. taxes
                      </span> */}
                    </div>
                  </div>

                  {/* Button */}
                  <button
                    className="mt-2 sm:mt-3 bg-black text-white text-xs sm:text-sm py-1.5 sm:py-2 rounded
                      opacity-100 sm:opacity-0 translate-y-0 sm:translate-y-2
                      sm:group-hover:opacity-100 sm:group-hover:translate-y-0
                      transition-all duration-300"
                    onClick={() => handleAddToBag(item)}
                  >
                    Add to Bag
                  </button>
                </div>
              );
            })}
          </div>
        </div>
      </div>
    </div>
  );
}

export default AllProducts;