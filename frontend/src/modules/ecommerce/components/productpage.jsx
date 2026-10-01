import React from "react";
import { FiShare2, FiTruck, FiThumbsUp, FiChevronLeft, FiChevronRight } from "react-icons/fi";
import { FaStar, FaRegStar } from "react-icons/fa";
import { MdVerified } from "react-icons/md";
import { CiLocationOn } from "react-icons/ci";
import { useEffect, useState } from "react";
import api from "../api/axios";
import { useNavigate } from "react-router-dom";
import { useParams } from "react-router-dom";
import ImageZoom from "../components/ImageZoom";

const SHADE_COLORS = [
  "bg-red-400",
  "bg-orange-400",
  "bg-pink-400",
  "bg-red-600",
  "bg-orange-600",
  "bg-red-300",
];

function ProductPage() {
  const [product, setProduct] = useState(null);
  const [qty, setQty] = useState(1);
  const { id } = useParams();
  const navigate = useNavigate();
  const [activeImage, setActiveImage] = useState("");
  const [selectedVariant, setSelectedVariant] = useState(null);
  const [toasts, setToasts] = useState([]);

  const showToast = (title, sub, type = "success") => {
    const id = Date.now();
    setToasts((prev) => [...prev, { id, title, sub, type }]);
    setTimeout(() => {
      setToasts((prev) => prev.filter((t) => t.id !== id));
    }, 3500);
  };
  useEffect(() => {
    api.get(`/product/${id}`)
      .then((res) => {
        setProduct(res.data);
        if (res.data.variants?.length > 0) {
          setSelectedVariant(res.data.variants[0]);
        }
        // ✅ set first image as default
        if (res.data.image?.length) {
          setActiveImage(res.data.image[0]);
        }
      })
      .catch((err) => {

        if (err.response?.status === 403) {
          navigate("/comingsoon");
          return;
        }

        console.error(err);
      });
  }, [id]);

  const handleAddToCart = () => {

    if (!product || !product.id) {
      showToast("Something went wrong", "Product not loaded properly", "error");
      return;
    }

    let guestId = localStorage.getItem("guest_id");

    if (!guestId) {
      guestId = Date.now().toString();
      localStorage.setItem("guest_id", guestId);
    }

    const user = JSON.parse(localStorage.getItem("user"));

    api.post("/add-to-cart", {
      member_id: user?.id || null,
      guest_id: guestId,
      product_id: product.id,
      variant_id: selectedVariant?.id,
      quantity: qty,
    })
      .then(() => {

        showToast("Added to bag!", product.name, "success");

        window.dispatchEvent(new Event("cartUpdated"));

        navigate("/checkout");

      })
      .catch((err) => {
        console.error(err);
        showToast("Something went wrong", "Could not add to cart", "error");
      });
  };

  const handlePrevImage = () => {
    if (!product?.image?.length) return;
    const currentIndex = product.image.indexOf(activeImage);
    const prevIndex = currentIndex <= 0 ? product.image.length - 1 : currentIndex - 1;
    setActiveImage(product.image[prevIndex]);
  };

  const handleNextImage = () => {
    if (!product?.image?.length) return;
    const currentIndex = product.image.indexOf(activeImage);
    const nextIndex = currentIndex === -1 || currentIndex === product.image.length - 1 ? 0 : currentIndex + 1;
    setActiveImage(product.image[nextIndex]);
  };

  const handleWishlist = () => {
    let wishlist = JSON.parse(localStorage.getItem("wishlist")) || [];
    const exists = wishlist.find((item) => item.id === product.id);
    if (exists) { showToast("Already in wishlist", product.name, "error"); return; }
    wishlist.push(product);
    localStorage.setItem("wishlist", JSON.stringify(wishlist));
    showToast("Saved to wishlist!", product.name, "success");
  };

  if (!product) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-center">
          <div className="w-8 h-8 border-2 border-gray-300 border-t-black rounded-full animate-spin mx-auto mb-3"></div>
          <p className="text-gray-500 text-sm">Loading...</p>
        </div>
      </div>
    );
  }

  // const hasDiscount = product?.offer_price && product?.discount_percentage;

  return (
    <>
      <style>{`
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
      .toast-item.success .toast-progress { background: #c8e6c9; }
      .toast-item.error   .toast-progress { background: #ffcdd2; }
      @keyframes toastIn {
        from { opacity: 0; transform: translateY(16px) scale(0.96); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
      }
      @keyframes shrinkBar {
        from { transform: scaleX(1); }
        to   { transform: scaleX(0); }
      }
    `}</style>
      <div className="bg-gray-50 min-h-screen py-4 sm:py-6 md:py-8">
        <div className="max-w-6xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8">
          <div className="bg-white rounded-lg shadow-sm overflow-hidden">

            {/* Product Detail Section */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-0 lg:gap-8">

              {/* Left — Image */}

              <div className="relative bg-gray-50 p-4 sm:p-6 lg:p-8">
                <div className="relative aspect-square max-w-md mx-auto">

                  {/* MAIN IMAGE */}
                  <ImageZoom
                    image={activeImage || product.image?.[0]}
                  />

                  {/* SLIDE ARROWS */}
                  {product.image?.length > 1 && (
                    <>
                      <button
                        type="button"
                        onClick={handlePrevImage}
                        aria-label="Previous image"
                        className="absolute left-2 top-1/2 -translate-y-1/2 z-10 w-9 h-9 flex items-center justify-center rounded-full bg-white/80 border border-gray-200 shadow-sm hover:bg-white transition-colors"
                      >
                        <FiChevronLeft className="text-gray-700 text-lg" />
                      </button>
                      <button
                        type="button"
                        onClick={handleNextImage}
                        aria-label="Next image"
                        className="absolute right-2 top-1/2 -translate-y-1/2 z-10 w-9 h-9 flex items-center justify-center rounded-full bg-white/80 border border-gray-200 shadow-sm hover:bg-white transition-colors"
                      >
                        <FiChevronRight className="text-gray-700 text-lg" />
                      </button>
                    </>
                  )}

                  {/* THUMBNAILS */}
                  <div className="flex gap-2 mt-4 flex-wrap justify-center">
                    {product.image?.map((img, index) => (
                      <img
                        key={index}
                        src={img}
                        alt="thumb"
                        onClick={() => setActiveImage(img)}
                        className={`w-16 h-16 object-cover rounded cursor-pointer border-2 
              ${activeImage === img ? "border-black" : "border-gray-200"}`}
                      />
                    ))}
                  </div>

                </div>
              </div>

              {/* Right — Product Info */}
              <div className="p-4 sm:p-6 lg:p-8 lg:pl-0 flex flex-col">

                {/* Breadcrumb & Share */}
                <div className="flex justify-between items-start mb-4">
                  <p className="text-xs text-gray-500 mb-1 underline underline-offset-2 hover:text-gray-700 cursor-pointer">
                    {product?.brand}
                  </p>
                  <button className="p-2 rounded-full hover:bg-gray-100 transition-colors">
                    <FiShare2 className="text-gray-500 text-lg hover:text-gray-700" />
                  </button>
                </div>

                {/* Product Title */}
                {/* Product Title */}
                <h1 className="text-xl sm:text-2xl lg:text-3xl font-semibold text-gray-900 leading-tight mb-2">
                  {product?.name}
                </h1>

                {product?.short_description && (
                  <p className="text-gray-600 text-sm sm:text-base leading-6 mb-4">
                    {product.short_description}
                  </p>
                )}

                {/* Price Section */}
                <div className="flex items-baseline flex-wrap gap-2 sm:gap-3 mb-2">

                  <span className="text-2xl sm:text-3xl font-bold text-gray-900">
                    ₹
                    {
                      selectedVariant?.offer_price ||
                      product?.variants?.[0]?.offer_price ||
                      selectedVariant?.price ||
                      product?.variants?.[0]?.price ||
                      0
                    }
                  </span>

                  {(selectedVariant?.discount_percentage ||
                    product?.variants?.[0]?.discount_percentage) > 0 && (
                      <>
                        <span className="line-through text-gray-400 text-base sm:text-lg">
                          MRP  ₹
                          {
                            selectedVariant?.price ||
                            product?.variants?.[0]?.price
                          }
                        </span>

                        <span className="text-green-600 text-sm sm:text-base font-semibold bg-green-50 px-2 py-0.5 rounded">
                          {parseFloat(
                            selectedVariant?.discount_percentage ||
                            product?.variants?.[0]?.discount_percentage ||
                            0
                          )}% Off
                        </span>
                      </>
                    )}

                </div>

                <p className="text-xs text-gray-400 mb-4">Inclusive of all taxes</p>

                <p className="text-sm font-semibold text-gray-800 mb-3">
                  About this item
                </p>


                <ul className="list-disc pl-6 space-y-4 text-[15px] leading-7 text-gray-700">
                  {(product?.description?.includes("|")
                    ? product.description.split("|")
                    : [product?.description]
                  )
                    .filter((item) => item?.trim())
                    .slice(0, 6)
                    .map((item, index) => {
                      const [title, ...rest] = item.split(":");

                      return (
                        <li key={index}>
                          {rest.length > 0 ? (
                            <>
                              <span className="font-semibold text-gray-900">
                                {title.trim()}:
                              </span>{" "}
                              {rest.join(":").trim()}
                            </>
                          ) : (
                            item.trim()
                          )}
                        </li>
                      );
                    })}
                </ul>

                {product?.variants?.length > 0 && (
                  <div className="mt-5">
                    <p className="text-sm font-semibold text-gray-800 mb-3">
                      Package Size
                    </p>

                    <div className="flex flex-wrap gap-3">
                      {product.variants.map((variant) => (
                        <div
                          key={variant.id}
                          onClick={() => setSelectedVariant(variant)}
                          className={`cursor-pointer border rounded-xl px-4 py-3 min-w-[140px] transition-all
          ${selectedVariant?.id === variant.id
                              ? "border-black bg-black text-white"
                              : "border-gray-200 bg-white text-gray-800"
                            }`}
                        >
                          <p className="font-semibold text-sm">
                            {variant.packing_size}
                          </p>

                          <p className="text-sm mt-1">
                            ₹{variant.offer_price || variant.price}
                          </p>
                        </div>
                      ))}
                    </div>
                  </div>
                )}
                {/* Divider */}
                <div className="border-t border-gray-100 my-4"></div>


                {/* Quantity */}
                <div className="mb-6">
                  <p className="text-sm font-medium text-gray-700 mb-3">Quantity</p>
                  <div className="flex items-center gap-1 border border-gray-200 rounded-lg w-fit">
                    <button
                      onClick={() => setQty(qty > 1 ? qty - 1 : 1)}
                      className="w-10 h-10 flex items-center justify-center hover:bg-gray-100 rounded-l-lg text-lg font-medium text-gray-600 transition-colors"
                    >
                      −
                    </button>
                    <span className="w-12 text-center font-semibold text-gray-900">{qty}</span>
                    <button
                      onClick={() => setQty(qty + 1)}
                      className="w-10 h-10 flex items-center justify-center hover:bg-gray-100 rounded-r-lg text-lg font-medium text-gray-600 transition-colors"
                    >
                      +
                    </button>
                  </div>
                </div>

                {/* Buttons */}
                <div className="flex flex-col sm:flex-row gap-3 mt-auto">
                  <button
                    onClick={handleAddToCart}
                    className="flex-1 bg-black rounded-lg text-white py-3.5 px-6 text-sm font-semibold hover:bg-gray-800 active:scale-[0.98] transition-all shadow-sm"
                  >
                    Add to Cart
                  </button>
                  <button
                    onClick={handleWishlist}
                    className="flex-1 border-2 border-gray-200 py-3.5 px-6 rounded-lg text-sm font-semibold text-gray-700 hover:border-gray-300 hover:bg-gray-50 active:scale-[0.98] transition-all"
                  >
                    Save to Wishlist
                  </button>
                </div>

              </div>
            </div>
          </div>
        </div>

      </div>
      {/* Toast Container */}
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
              <p className="toast-title">{t.title}</p>
              <p className="toast-sub">{t.sub}</p>
            </div>
            <div className="toast-progress" />
          </div>
        ))}
      </div>
    </>
  );
}

export default ProductPage;