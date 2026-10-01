import React, { useState, useRef, useCallback } from "react";
import Sidebar from "../components/sidebar";
import Navbar from "../components/navbar";
import { shoppeeApi as api } from "../api/axios";

// ─── Toast System ─────────────────────────────────────────────────
function Toast({ toasts, removeToast }) {
  return (
    <div className="fixed top-5 right-5 z-50 flex flex-col gap-3 w-80">
      {toasts.map((t) => (
        <div
          key={t.id}
          className={`flex items-start gap-3 px-4 py-3 rounded-xl shadow-md text-sm font-medium border transition-all
            ${t.type === "success" ? "bg-green-50 border-green-200 text-green-800" : ""}
            ${t.type === "error" ? "bg-red-50 border-red-200 text-red-800" : ""}
            ${t.type === "warning" ? "bg-yellow-50 border-yellow-200 text-yellow-800" : ""}
          `}
        >
          <span className="text-lg leading-none mt-0.5">
            {t.type === "success" && "✅"}
            {t.type === "error" && "❌"}
            {t.type === "warning" && "⚠️"}
          </span>
          <span className="flex-1 leading-snug">{t.message}</span>
          <button
            onClick={() => removeToast(t.id)}
            className="text-gray-400 hover:text-gray-600 leading-none text-base"
          >
            ✕
          </button>
        </div>
      ))}
    </div>
  );
}

function useToast() {
  const [toasts, setToasts] = useState([]);

  const showToast = useCallback((type, message) => {
    const id = Date.now();
    setToasts((prev) => [...prev, { id, type, message }]);
    setTimeout(() => {
      setToasts((prev) => prev.filter((t) => t.id !== id));
    }, 4000);
  }, []);

  const removeToast = useCallback((id) => {
    setToasts((prev) => prev.filter((t) => t.id !== id));
  }, []);

  return { toasts, showToast, removeToast };
}
// ──────────────────────────────────────────────────────────────────

function CreatePromoterSale() {

  const { toasts, showToast, removeToast } = useToast();

  const [userId, setUserId] = useState("");

  const [error, setError] = useState("");
  const [searched, setSearched] = useState(false);

  const [orders, setOrders] = useState([]);

  const [selectedOrderId, setSelectedOrderId] = useState(null);

  const [showOTPBox, setShowOTPBox] = useState(false);

  const [loading, setLoading] = useState(false);

  const [dispatchLoading, setDispatchLoading] = useState(null);

  const [verifyLoading, setVerifyLoading] = useState(false);

  const [currentPage, setCurrentPage] = useState(1);

  const ordersPerPage = 5;

  const [otp, setOtp] = useState(["", "", "", "", "", ""]);

  const inputsRef = useRef([]);

  // PAGINATION
  const indexOfLastOrder = currentPage * ordersPerPage;
  const indexOfFirstOrder = indexOfLastOrder - ordersPerPage;
  const currentOrders = orders.slice(indexOfFirstOrder, indexOfLastOrder);
  const totalPages = Math.ceil(orders.length / ordersPerPage);

  // FETCH PICKUP ORDERS
  const handleSubmit = async (e) => {
    e.preventDefault();

    if (!userId.trim()) {
      showToast("warning", "Please enter User ID");
      return;
    }

    try {
      setLoading(true);
      setError("");
      setSearched(false);

      const orderRes = await api.get(`/pickup-orders/${userId}`);

      if (!orderRes.data.success) {
        showToast("error", "Invalid User ID");
        setOrders([]);
        return;
      }

      const fetchedOrders = orderRes.data.orders || [];
      setOrders(fetchedOrders);
      setCurrentPage(1);
      setSearched(true);

    } catch (err) {
      console.log(err.response?.data || err);
      if (err.response?.status === 404) {
        showToast("error", "Invalid User ID");
      } else {
        showToast("error", "Something went wrong");
      }
      setOrders([]);
    } finally {
      setLoading(false);
    }
  };

  // DISPATCH ORDER
  const handleDispatch = async (orderId) => {
    try {
      setDispatchLoading(orderId);

      const user = JSON.parse(localStorage.getItem("user"));

      const res = await api.post(
        `/dispatch-order/${orderId}`,
        {
          shoppee_member_id: user.id
        }
      );
      if (res.data.success) {
        showToast("success", res.data.message);
        setSelectedOrderId(orderId);
        setShowOTPBox(true);
      } else {
        showToast("error", res.data.message);
      }
    } catch (err) {
      console.log(err.response?.data || err);
      showToast("error", "Something went wrong");
    } finally {
      setDispatchLoading(null);
    }
  };

  // OTP INPUT
  const handleOtpChange = (value, index) => {
    if (!/^[0-9]?$/.test(value)) return;

    const newOtp = [...otp];
    newOtp[index] = value;
    setOtp(newOtp);

    if (value && index < 5) {
      inputsRef.current[index + 1].focus();
    }
  };

  // VERIFY OTP
  const handleVerify = async () => {
    try {
      setVerifyLoading(true);

      const enteredOtp = otp.join("");

      const user = JSON.parse(localStorage.getItem("user"));

      const res = await api.post(
        "/verify-otp",
        {
          order_id: selectedOrderId,
          otp: enteredOtp,
          shoppee_member_id: user.id
        }
      );

      if (res.data.success) {
        showToast("success", "Order Delivered Successfully");
        window.location.reload();
      } else {
        showToast("error", res.data.message);
      }
    } catch (err) {
      console.log(err.response?.data || err);
      showToast("error", "Verification failed");
    } finally {
      setVerifyLoading(false);
    }
  };

  return (

    <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">

      <Toast toasts={toasts} removeToast={removeToast} />

      <div className="flex-1 min-w-0 flex flex-col">

        <div className="text-center mt-6">

          <h1 className="text-center text-3xl font-bold text-[#B0422E] mb-4">
            IBO Delivery Sale
          </h1>

          <div className="p-6">

            {/* SEARCH BOX */}
            <div className="bg-white rounded-xl border border-gray-200 shadow-sm mb-6 p-6">

              <form onSubmit={handleSubmit}>

                <div className="flex items-center gap-4 flex-wrap">

                  <label className="text-gray-600 font-semibold">
                    Enter User ID
                    <span className="text-red-500">*</span>
                  </label>

                  <input
                    type="text"
                    value={userId}
                    onChange={(e) => setUserId(e.target.value)}
                    className="w-75 h-10 bg-gray-100 border border-gray-300 rounded-md px-3 focus:outline-none focus:ring-2 focus:ring-blue-400"
                  />

                </div>

                {error && (
                  <p className="text-red-500 text-sm mt-2 text-start">{error}</p>
                )}

                <div className="flex justify-center mt-6">
                  <button
                    type="submit"
                    disabled={loading}
                    className="px-10 py-2 text-white rounded-md bg-[#2273C3] hover:bg-blue-600 disabled:opacity-50"
                  >
                    {loading ? "Loading..." : "Submit"}
                  </button>
                </div>

              </form>

            </div>

            {/* ORDERS */}
            {searched && currentOrders.length > 0 ? (

              currentOrders.map((order) => (

                <div
                  key={order.id}
                  className="bg-white rounded-xl border border-gray-200 shadow-sm mb-6 overflow-x-auto"
                >

                  {/* ORDER HEADER */}
                  <div className="border-b border-gray-200 px-6 py-4">
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-700">

                      <p>
                        <span className="font-semibold">Order ID:</span>{" "}
                        #{order.invoice_id}
                      </p>

                      <p>
                        <span className="font-semibold">User ID:</span>{" "}
                        {order.mlm_member_id || order.guest_id || order.member_id || "N/A"}
                      </p>

                      <p>
                        <span className="font-semibold">Customer:</span>{" "}
                        {order.delivery_name || "Customer"}
                      </p>

                    </div>
                  </div>

                  {/* TABLE */}
                  <div className="overflow-x-auto">
                    <table className="w-full min-w-[900px]">

                      <thead>
                        <tr className="bg-[#B0422E] text-white text-center">
                          <th className="py-4 px-3 rounded-l-xl">Sr No</th>
                          <th className="py-4 px-3">Product Name</th>
                          <th className="py-4 px-3">Quantity</th>
                          <th className="py-4 px-3">Price</th>
                          <th className="py-4 px-3">Total Amount</th>
                          <th className="py-4 px-3">Status</th>
                          <th className="py-4 px-3 rounded-r-xl">Action</th>
                        </tr>
                      </thead>

                      <tbody>

                        {order.items?.map((item, index) => (
                          <tr
                            key={index}
                            className="text-center border-b border-gray-200"
                          >

                            <td className="py-5 px-3">{index + 1}</td>

                      <td className="py-5 px-3">
  {item.product?.name || "Product"}
  ({item.variant?.packing_size || "-"})
</td>

                            <td className="py-5 px-3">{item.quantity}</td>

                            <td className="py-5 px-3">₹{item.price || 0}</td>

                            <td className="py-5 px-3">
                              ₹{(item.price || 0) * item.quantity}
                            </td>

                            <td className="py-5 px-3">
                              <span className={`px-3 py-1 rounded-full text-xs font-semibold ${order.status === "pending"
                                  ? "bg-yellow-100 text-yellow-700"
                                  : order.status === "dispatched"
                                    ? "bg-blue-100 text-blue-700"
                                    : "bg-green-100 text-green-700"
                                }`}>
                                {order.status}
                              </span>
                            </td>

                            <td
                              className="py-5 px-3"
                              rowSpan={order.items.length}
                            >

                              {index === 0 && (

                                <button
                                  onClick={() => handleDispatch(order.id)}
                                  disabled={dispatchLoading === order.id}
                                  className="px-5 py-2 text-white rounded-md shadow-md bg-[#0D9488] hover:bg-green-700 disabled:opacity-50"
                                >
                                  {dispatchLoading === order.id
                                    ? "Dispatching..."
                                    : "Dispatch"}
                                </button>

                              )}

                            </td>

                          </tr>
                        ))}

                        {/* OTP ROW — inside table, visible only when this order is selected */}
                        {showOTPBox && selectedOrderId === order.id && (

                          <tr className="bg-orange-50 border-t border-orange-200">

                            <td colSpan={7} className="px-6 py-4">

                              <div className="flex items-center justify-between flex-wrap gap-4">

                                <span className="text-gray-600 font-semibold text-sm">
                                  Enter OTP to confirm delivery
                                </span>

                                <div className="flex items-center gap-2">
                                  {otp.map((digit, index) => (
                                    <input
                                      key={index}
                                      ref={(el) => (inputsRef.current[index] = el)}
                                      type="text"
                                      value={digit}
                                      maxLength="1"
                                      onChange={(e) => handleOtpChange(e.target.value, index)}
                                      className="w-10 h-10 text-center border border-gray-300 rounded-md text-lg focus:outline-none focus:ring-2 focus:ring-orange-400"
                                    />
                                  ))}
                                </div>

                                <button
                                  onClick={handleVerify}
                                  disabled={verifyLoading}
                                  className="px-8 py-2 text-white rounded-md shadow-md bg-orange-400 hover:bg-orange-600 disabled:opacity-50 text-sm font-semibold"
                                >
                                  {verifyLoading ? "Verifying..." : "Verify OTP"}
                                </button>

                              </div>

                            </td>

                          </tr>

                        )}

                      </tbody>

                    </table>
                  </div>

                </div>

              ))

            ) : searched ? (

              <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-10 text-center text-gray-500 text-lg font-medium">
                Currently there is no order
              </div>

            ) : null}

            {/* PAGINATION */}
            {orders.length > 5 && (

              <div className="flex justify-center items-center gap-2 mt-6 flex-wrap">

                <button
                  disabled={currentPage === 1}
                  onClick={() => setCurrentPage(currentPage - 1)}
                  className="px-4 py-2 rounded-md bg-gray-200 disabled:opacity-50"
                >
                  Prev
                </button>

                {[...Array(totalPages)].map((_, index) => (
                  <button
                    key={index}
                    onClick={() => setCurrentPage(index + 1)}
                    className={`px-4 py-2 rounded-md ${currentPage === index + 1
                        ? "bg-[#B0422E] text-white"
                        : "bg-gray-200"
                      }`}
                  >
                    {index + 1}
                  </button>
                ))}

                <button
                  disabled={currentPage === totalPages}
                  onClick={() => setCurrentPage(currentPage + 1)}
                  className="px-4 py-2 rounded-md bg-gray-200 disabled:opacity-50"
                >
                  Next
                </button>

              </div>

            )}

          </div>

        </div>

      </div>

    </div>

  );
}

export default CreatePromoterSale;