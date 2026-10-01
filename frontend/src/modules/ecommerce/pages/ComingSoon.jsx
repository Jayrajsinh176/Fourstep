import React, { useEffect, useState, useCallback } from "react";
import { useNavigate } from "react-router-dom";
import api from "../api/axios";

// Toast Component
function Toast({ toasts, removeToast }) {
  return (
    <div className="fixed bottom-6 right-6 z-50 flex flex-col gap-2 pointer-events-none">
      {toasts.map((toast) => (
        <div
          key={toast.id}
          className="
            flex items-center gap-3
            bg-gray-900 text-white
            text-sm font-medium
            px-4 py-3 rounded-xl
            shadow-xl
            pointer-events-auto
            animate-slide-in
          "
        >
          <span className="text-orange-400 text-base">🔔</span>
          <span>{toast.message}</span>
          <button
            onClick={() => removeToast(toast.id)}
            className="ml-2 text-gray-400 hover:text-white transition-colors text-xs"
          >
            ✕
          </button>
        </div>
      ))}
    </div>
  );
}

function ComingSoon() {
  const navigate = useNavigate();

  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [toasts, setToasts] = useState([]);

  useEffect(() => {
    api
      .get("/coming-soon-products")
      .then((res) => setProducts(res.data))
      .catch((err) => console.log(err))
      .finally(() => setLoading(false));
  }, []);

  const removeToast = useCallback((id) => {
    setToasts((prev) => prev.filter((t) => t.id !== id));
  }, []);

  const showToast = useCallback(
    (message) => {
      const id = Date.now();
      setToasts((prev) => [...prev, { id, message }]);
      setTimeout(() => removeToast(id), 3500);
    },
    [removeToast]
  );

  const handleNotify = (item) => {
    showToast(`You'll be notified when "${item.name}" launches!`);
  };

  return (
    <div className="min-h-screen bg-white">
      <Toast toasts={toasts} removeToast={removeToast} />

      <style>{`
        @keyframes slide-in {
          from { opacity: 0; transform: translateY(16px) scale(0.96); }
          to   { opacity: 1; transform: translateY(0)    scale(1);    }
        }
        .animate-slide-in {
          animation: slide-in 0.25s ease-out forwards;
        }
      `}</style>

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {/* Title */}
        <div className="mb-8 text-center">
          <h2 className="text-2xl sm:text-3xl font-semibold text-orange-500 tracking-tight">
            What's New
          </h2>
          <p className="text-gray-500 mt-2 text-sm sm:text-base">
            Coming Soon Products
          </p>
          <div className="flex items-center justify-center gap-2 mt-3">
            <span className="w-8 h-px bg-orange-500"></span>
            <span className="w-2 h-2 bg-orange-500 rounded-full"></span>
            <span className="w-8 h-px bg-orange-500"></span>
          </div>
        </div>

        {/* Loading */}
        {loading ? (
          <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 sm:gap-6">
            {[...Array(10)].map((_, i) => (
              <div key={i} className="animate-pulse bg-white rounded-lg shadow-sm overflow-hidden">
                <div className="bg-gray-200 h-40 sm:h-48 md:h-56"></div>
                <div className="p-4">
                  <div className="h-3 bg-gray-200 rounded w-1/3 mb-3"></div>
                  <div className="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                  <div className="h-4 bg-gray-200 rounded w-1/2 mb-4"></div>
                  <div className="h-10 bg-gray-200 rounded"></div>
                </div>
              </div>
            ))}
          </div>

        ) : products.length === 0 ? (
          <div className="text-center py-16">
            <p className="text-gray-500 text-lg">No Coming Soon Products</p>
          </div>

        ) : (
          <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 sm:gap-6">
            {products.map((item) => (
              <div
                key={item.id}
                className="group bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden flex flex-col h-full"
              >
                {/* Product Image */}
                <div className="relative bg-gray-50 overflow-hidden">
                  <img
  src={item.image?.[0] || "/default-product.png"}
  alt={item.name}
  onClick={() => navigate(`/coming-soon/${item.id}`)}
  className="
    w-full h-40 sm:h-48 md:h-56
    object-contain p-4 cursor-pointer
    transition-transform duration-300
    group-hover:scale-105
  "
/>
                  <div className="absolute top-3 left-3">
                    <span className="bg-orange-500 text-white text-[10px] sm:text-xs font-semibold px-2 py-1 rounded-full shadow-sm">
                      COMING SOON
                    </span>
                  </div>
                </div>

                {/* Content */}
                <div className="p-3 sm:p-4 flex flex-col flex-1">
                  <p className="text-xs text-gray-400 uppercase tracking-wide">{item.brand}</p>
                  <p
                 onClick={() => navigate(`/coming-soon/${item.id}`)}
                    className="
text-sm text-gray-800 mt-1 font-medium
leading-6 line-clamp-3
cursor-pointer hover:text-blue-600
transition-colors min-h-[72px]
"
                  >
                    {item.name}
                  </p>

                  {/* Notify Button */}
                  <button
                    onClick={() => handleNotify(item)}
                    className="
w-full mt-auto flex items-center justify-center gap-1.5
bg-orange-500 text-white
text-xs sm:text-sm font-medium
py-2.5 rounded-md
hover:bg-orange-600
transition-all duration-300
"
                  >
                    🔔 Notify Me
                  </button>
                </div>
              </div>
            ))}
          </div>
        )}

      </div>
    </div>
  );
}

export default ComingSoon;