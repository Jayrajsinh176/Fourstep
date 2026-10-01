import React from "react";
import { useEffect, useState } from "react";
import api from "../api/axios";

import { useParams } from "react-router-dom";
import ImageZoom from "../components/ImageZoom";


function ComingSoonDetails() {
    const [product, setProduct] = useState(null);

    const { id } = useParams();
    const [activeImage, setActiveImage] = useState("");

    const [toasts, setToasts] = useState([]);

    const showToast = (title, sub, type = "success") => {
        const id = Date.now();
        setToasts((prev) => [...prev, { id, title, sub, type }]);
        setTimeout(() => {
            setToasts((prev) => prev.filter((t) => t.id !== id));
        }, 3500);
    };
    useEffect(() => {
        api
            .get(`/coming-soon-product/${id}`)
            .then((res) => {
                setProduct(res.data);

                if (res.data.image?.length) {
                    setActiveImage(res.data.image[0]);
                }
            })
            .catch((err) => {
                console.error(err);
            });
    }, [id]);

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
         <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">

                            {/* Left — Image */}

                            <div className="relative bg-gray-50 p-4 sm:p-6 lg:p-8">
                                <div className="relative aspect-square max-w-md mx-auto">

                                    {/* MAIN IMAGE */}
                                    <ImageZoom
                                        image={activeImage || product.image?.[0]}
                                    />

                                    {/* THUMBNAILS */}
                                    <div className="flex gap-2 mt-4 flex-wrap justify-center">
                                        {product.image?.map((img, index) => (
                                            <img
                                                key={index}
                                                src={img}
                                                alt="thumb"
                                                onClick={() => setActiveImage(img)}
                                                className={`w-14 h-14 object-cover rounded-sm cursor-pointer border 
              ${activeImage === img ? "border-black border-2" : "border-gray-200 hover:border-gray-400"}`}
                                            />
                                        ))}
                                    </div>

                                </div>
                            </div>

                            {/* Right — Product Info */}
                            <div className="p-6 lg:p-8 lg:pl-0 flex flex-col justify-center h-full">

                                <p className="text-xs uppercase tracking-wide text-gray-500 mb-2">
                                    {product?.brand}
                                </p>

                                <h1 className="text-2xl lg:text-3xl font-semibold text-gray-900 leading-tight mb-6">
                                    {product?.name}
                                </h1>
<h2 className="text-lg font-semibold text-gray-900 mb-4">
                                    {product?.short_description}
                                </h2>

                                <h2 className="text-lg font-semibold text-gray-900 mb-4">
                                    About this item
                                </h2>

                                <ul className="list-disc pl-6 space-y-3 text-gray-700 leading-7 mb-8">
                                    {(product?.description?.includes("|")
                                        ? product.description.split("|")
                                        : [product?.description]
                                    )
                                        .filter((item) => item.trim())
                                        .map((item, index) => {
                                            const [title, ...rest] = item.split(":");

                                            return (
                                                <li key={index}>
                                                    {rest.length ? (
                                                        <>
                                                            <span className="font-semibold">
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

                                <button
                                    onClick={() =>
                                        showToast(
                                            "Notification Registered",
                                            `We'll notify you when "${product.name}" launches`
                                        )
                                    }
                                    className="
  w-full
  flex items-center
  justify-center
  gap-2
  bg-orange-500
  hover:bg-orange-600
  text-white
  py-3.5
  rounded-lg
  font-semibold
  transition-all
  duration-300
"
                                >
                                    🔔 Notify Me
                                </button>

                            </div>
                        </div>
                    </div>
                </div>
            </div>


            {/* Toast Container */}
       <div className="toast-wrap">
                {
                    toasts.map((t) => (
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
                    ))
                }
            </div>
        </>
    );
}

export default ComingSoonDetails;