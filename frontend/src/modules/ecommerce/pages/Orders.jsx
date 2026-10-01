import React, { useEffect, useState } from "react";
import api from "../api/axios";
import {
  Package, Calendar, IndianRupee, ShoppingBag,
  X, MapPin, CreditCard, Receipt
} from "lucide-react";

function OrderPage() {
  const [orders, setOrders] = useState([]);
  const [selectedOrder, setSelectedOrder] = useState(null);
  const user = JSON.parse(localStorage.getItem("user"));

  useEffect(() => {
    if (!user) return;
    api.get(`/orders/${user.id}`)
      .then(res => {
        const sortedOrders = res.data.sort(
          (a, b) => new Date(b.created_at) - new Date(a.created_at)
        );
        setOrders(sortedOrders);
      })
      .catch(err => console.log(err));
  }, []);

  const statusConfig = {
    pending: {
      color: "bg-yellow-50 text-yellow-700 border-yellow-200",
      dotColor: "bg-yellow-500",
      modalBg: "from-yellow-50 to-orange-50",
      badgeBg: "bg-yellow-100 text-yellow-800 border border-yellow-200"
    },
    processing: {
      color: "bg-blue-50 text-blue-700 border-blue-200",
      dotColor: "bg-blue-500",
      modalBg: "from-blue-50 to-indigo-50",
      badgeBg: "bg-blue-100 text-blue-800 border border-blue-200"
    },
    dispatched: {
      color: "bg-purple-50 text-purple-700 border-purple-200",
      dotColor: "bg-purple-500",
      modalBg: "from-purple-50 to-violet-50",
      badgeBg: "bg-purple-100 text-purple-800 border border-purple-200"
    },
    delivered: {
      color: "bg-green-50 text-green-700 border-green-200",
      dotColor: "bg-green-500",
      modalBg: "from-green-50 to-emerald-50",
      badgeBg: "bg-green-100 text-green-800 border border-green-200"
    },
    cancelled: {
      color: "bg-red-50 text-red-700 border-red-200",
      dotColor: "bg-red-500",
      modalBg: "from-red-50 to-rose-50",
      badgeBg: "bg-red-100 text-red-800 border border-red-200"
    }
  };

  const formatDate = (date) => {
    if (!date) return "";
    return new Date(date).toLocaleDateString("en-IN", {
      day: "2-digit",
      month: "short",
      year: "numeric"
    });
  };

  const config = selectedOrder ? (statusConfig[selectedOrder.status] || statusConfig.pending) : null;

  return (
    <div className="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 px-4 py-8">
      <div className="max-w-5xl mx-auto">

        {/* Header */}
        <div className="mb-8">
          <div className="flex items-center gap-3 mb-2">
            <div className="p-2 bg-blue-600 rounded-lg">
              <ShoppingBag className="w-6 h-6 text-white" />
            </div>
            <h1 className="text-3xl font-bold text-gray-900">My Orders</h1>
          </div>
        </div>

        {/* Orders List */}
        {orders.length === 0 ? (
          <div className="bg-white rounded-2xl shadow-sm border border-gray-200 p-12 text-center">
            <div className="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <Package className="w-10 h-10 text-gray-400" />
            </div>
            <h3 className="text-xl font-semibold text-gray-900 mb-2">No orders yet</h3>
            <p className="text-gray-500 mb-6">Looks like you haven't placed any orders</p>
          </div>
        ) : (
          <div className="space-y-4">
            {orders.map((order, index) => (
              <div
                key={index}
                className="bg-white rounded-2xl shadow-sm border border-gray-200 hover:shadow-md transition-shadow"
              >
                <div className="p-5 sm:p-6">
                  <div className="flex flex-col sm:flex-row gap-4">
                    <div className="flex-shrink-0">
                      <img
                        src={order.image}
                        alt={order.product_name}
                        className="w-full sm:w-28 sm:h-28 h-48 rounded-xl object-cover border border-gray-200"
                      />
                    </div>
                    <div className="flex-1">
                      <div className="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                        <div className="flex-1">
                          <h3 className="font-semibold text-gray-900 text-lg mb-2">{order.product_name}</h3>
                          <span className={`inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border capitalize ${statusConfig[order.status]?.color || 'bg-gray-100 text-gray-700'}`}>
                            <span className={`w-1.5 h-1.5 rounded-full ${statusConfig[order.status]?.dotColor}`}></span>
                            {order.status}
                          </span>
                        </div>
                        <div className="text-right">
                          <div className="flex items-center gap-1 text-2xl font-bold text-gray-900">
                            <IndianRupee className="w-5 h-5" />
                            {order.total_amount?.toLocaleString('en-IN')}
                          </div>
                        </div>
                      </div>
                      <div className="flex flex-wrap items-center gap-6 mt-4 text-sm text-gray-600">
                        <div className="flex items-center gap-2">
                          <Package className="w-4 h-4 text-gray-400" />
                          <span>Qty: <span className="font-medium text-gray-900">{order.quantity}</span></span>
                        </div>
                        <div className="flex items-center gap-2">
                          <Calendar className="w-4 h-4 text-gray-400" />
                          <span>{formatDate(order.created_at)}</span>
                        </div>
                      </div>
                      <div className="flex gap-3 mt-4">
                        <button
                          onClick={() => setSelectedOrder(order)}
                          className="text-sm text-blue-600 hover:text-blue-700 font-medium"
                        >
                          View Details
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>

      {/* ── PROFESSIONAL MODAL ── */}
      {selectedOrder && (
        <div
          className="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4"
          style={{ backgroundColor: "rgba(0,0,0,0.55)", backdropFilter: "blur(4px)" }}
          onClick={() => setSelectedOrder(null)}
        >
          <div
            className="bg-white w-full sm:max-w-lg rounded-t-3xl sm:rounded-2xl overflow-hidden shadow-2xl"
            style={{ maxHeight: "92vh" }}
            onClick={e => e.stopPropagation()}
          >

            {/* Modal Header */}
            <div className={`bg-gradient-to-r ${config.modalBg} px-6 pt-6 pb-5 relative`}>
              <div className="w-10 h-1 bg-gray-300 rounded-full mx-auto mb-4 sm:hidden" />

              <button
                onClick={() => setSelectedOrder(null)}
                className="absolute top-4 right-4 p-1.5 rounded-full bg-white/70 hover:bg-white text-gray-500 hover:text-gray-700 transition-colors"
              >
                <X className="w-4 h-4" />
              </button>

              <div className="flex items-center gap-4">
                <img
                  src={selectedOrder.image}
                  alt={selectedOrder.product_name}
                  className="w-16 h-16 rounded-xl object-cover border-2 border-white shadow-sm flex-shrink-0"
                />
                <div className="flex-1 min-w-0">
                  <p className="text-xs font-medium text-gray-500 uppercase tracking-wider mb-0.5">Order Summary</p>
                  <h2 className="font-bold text-gray-900 text-base leading-tight truncate">
                    {selectedOrder.product_name}
                  </h2>
                  <div className="mt-2">
                    <span className={`inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold capitalize ${config.badgeBg}`}>
                      <span className={`w-1.5 h-1.5 rounded-full ${statusConfig[selectedOrder.status]?.dotColor}`} />
                      {selectedOrder.status}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            {/* Modal Body */}
            <div className="overflow-y-auto px-6 py-5 space-y-5" style={{ maxHeight: "60vh" }}>

              {/* Price highlight */}
              <div className="flex items-center justify-between bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
                <span className="text-sm text-gray-500 font-medium">Total Amount</span>
                <span className="text-xl font-bold text-gray-900 flex items-center gap-0.5">
                  <IndianRupee className="w-4 h-4" />
                  {selectedOrder.total_amount?.toLocaleString('en-IN')}
                </span>
              </div>

              {/* Info rows */}
              <div className="divide-y divide-gray-100 rounded-xl border border-gray-100 overflow-hidden">
                <InfoRow
                  icon={<Package className="w-4 h-4 text-gray-400" />}
                  label="Quantity"
                  value={`${selectedOrder.quantity} item${selectedOrder.quantity > 1 ? 's' : ''}`}
                />
                <InfoRow
                  icon={<Calendar className="w-4 h-4 text-gray-400" />}
                  label="Ordered On"
                  value={formatDate(selectedOrder.created_at)}
                />
                <InfoRow
                  icon={<CreditCard className="w-4 h-4 text-gray-400" />}
                  label="Payment"
                  value={selectedOrder.payment_method || "N/A"}
                />
                <InfoRow
                  icon={<Receipt className="w-4 h-4 text-gray-400" />}
                  label="Invoice ID"
                  value={selectedOrder.invoice_id || "N/A"}
                  mono
                />
              </div>

              {/* Delivery Address */}
              <div>
                <div className="flex items-center gap-2 mb-2">
                  <MapPin className="w-4 h-4 text-gray-400" />
                  <span className="text-xs font-semibold text-gray-500 uppercase tracking-wider">Delivery Address</span>
                </div>
                <div className="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100 text-sm text-gray-700 leading-relaxed">
                  {selectedOrder.address || "No address provided"}
                </div>
              </div>

            </div>

            {/* Modal Footer */}
            <div className="px-6 py-4 border-t border-gray-100 bg-gray-50">
              <button
                onClick={() => setSelectedOrder(null)}
                className="w-full py-2.5 rounded-xl bg-gray-900 text-white text-sm font-semibold hover:bg-gray-800 transition-colors"
              >
                Close
              </button>
            </div>

          </div>
        </div>
      )}
    </div>
  );
}

function InfoRow({ icon, label, value, mono = false }) {
  return (
    <div className="flex items-center justify-between px-4 py-3 bg-white">
      <div className="flex items-center gap-2 text-sm text-gray-500">
        {icon}
        <span>{label}</span>
      </div>
      <span className={`text-sm font-semibold text-gray-900 ${mono ? "font-mono text-xs" : ""}`}>
        {value}
      </span>
    </div>
  );
}

export default OrderPage;