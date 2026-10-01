import React, { useState } from "react";
import api from "../api/axios";

function UpFooter() {
  const [email, setEmail] = useState("");
  const [toasts, setToasts] = useState([]);
  const handleClick = async () => {
    if (!email) {
      showToast("Validation", "Please enter your email.");
      return;
    }

    const valid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

    if (!valid) {
      showToast("Validation", "Please enter a valid email address.");
      return;
    }

    try {
      const response = await api.post("/newsletter/subscribe", {
        email,
      });

      showToast("Success", response.data.message);

      setEmail("");
    } catch (error) {
      showToast(
        "Error",
        error.response?.data?.message || "Something went wrong."
      );
    }
  };
  const showToast = (title, message) => {
    const id = Date.now();

    setToasts((prev) => [...prev, { id, title, message }]);

    setTimeout(() => {
      setToasts((prev) => prev.filter((t) => t.id !== id));
    }, 3500);
  };
  return (
    <section className="py-12 bg-white text-white ">
      <style>{`
.toast-wrap {
  position: fixed;
  bottom: 28px;
  right: 28px;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.toast-item {
  display: flex;
  align-items: center;
  gap: 12px;
  background: #fff;
  color: #1a1a1a;
  padding: 14px 16px;
  border-radius: 14px;
  min-width: 280px;
  border: 1px solid #f0f0f0;
  box-shadow: 0 8px 32px rgba(0,0,0,0.12);
  position: relative;
  overflow: hidden;
  animation: toastIn .35s ease;
}

.toast-item::before{
  content:'';
  position:absolute;
  left:0;
  top:0;
  bottom:0;
  width:4px;
  background:#2e7d32;
}

.toast-icon{
  width:36px;
  height:36px;
  border-radius:10px;
  background:#e8f5e9;
  display:flex;
  align-items:center;
  justify-content:center;
}

.toast-title{
  font-size:13px;
  font-weight:600;
}

.toast-sub{
  font-size:12px;
  color:#777;
}

.toast-progress{
  position:absolute;
  bottom:0;
  left:4px;
  right:0;
  height:2px;
  background:#2e7d32;
  transform-origin:left;
  animation:shrinkBar 3.5s linear forwards;
}

@keyframes toastIn{
  from{
    opacity:0;
    transform:translateY(15px);
  }
  to{
    opacity:1;
    transform:translateY(0);
  }
}

@keyframes shrinkBar{
  from{transform:scaleX(1);}
  to{transform:scaleX(0);}
}
`}</style>
      <div className="mx-auto w-full px-4 text-center">
        <div className="border-t border-gray-400 mt-5 mb-15"></div>

        <h2 className="text-xl text-gray-700 font-semibold mb-2 ">
          Be the first to hear about all things 4step
        </h2>

        <p className="text-sm text-gray-700 font-semibold mt-4 mb-5 max-w-xl mx-auto">
          Stay connected for exclusive offers and latest updates, delivered straight to your inbox
        </p>

        <div className="flex flex-col sm:flex-row justify-center gap-3 items-center">
          <div className="w-full sm:w-auto px-5 sm:px-10 py-2.5 rounded-xl text-sm font-semibold bg-gray-100 text-primar">
            <input
              id="email"
              type="email"
              placeholder="Enter your email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              className="bg-transparent border-0 outline-none text-gray-500 w-full sm:w-auto"
            />
          </div>

          <button
            type="button"
            className="px-5 py-2.5 rounded-xl text-sm text-gray-500 font-semibold bg-gray-300 hover:bg-gray-200"
            onClick={handleClick}
          >
            Send
          </button>
        </div>

        <p className="text-sm text-primary-50 mt-4 max-w-xl mx-auto">
          No spam, unsubscribe anytime. We respect your privacy.
        </p>
      </div>
      <div className="toast-wrap">
        {toasts.map((t) => (
          <div key={t.id} className="toast-item">
            <div className="toast-icon">
              <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                <path
                  d="M3 8l3.5 3.5L13 5"
                  stroke="#2e7d32"
                  strokeWidth="2"
                  strokeLinecap="round"
                  strokeLinejoin="round"
                />
              </svg>
            </div>

            <div style={{ flex: 1 }}>
              <div className="toast-title">{t.title}</div>
              <div className="toast-sub">{t.message}</div>
            </div>

            <div className="toast-progress" />
          </div>
        ))}
      </div>
    </section>
  );
}

export default UpFooter;
