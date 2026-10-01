import React, { useEffect, useState } from "react";
import api from "../api/axios";
import { useNavigate } from "react-router-dom";
function Trackorder() {
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);
  const user = JSON.parse(localStorage.getItem("user"));

  useEffect(() => {
    if (!user) {
      setLoading(false);
      return;
    }

    api.get(`/orders/${user.id}`)
      .then(res => {
        const sortedOrders = res.data.sort(
          (a, b) => new Date(b.created_at) - new Date(a.created_at)
        );
        setOrders(sortedOrders);
        setLoading(false);
      })
      .catch(err => {
        console.log(err);
        setLoading(false);
      });
  }, []);

    const steps = ["pending", "processing", "dispatched", "delivered"];

    const statusLabels = {
      pending: "Order Placed",
      processing: "Processing",
      dispatched: "Shipped",
      delivered: "Delivered",
      cancelled: "Cancelled"
    };

    const statusColors = {
      pending: "bg-yellow-50 text-yellow-700 border-yellow-200",
      processing: "bg-blue-50 text-blue-700 border-blue-200",
      dispatched: "bg-purple-50 text-purple-700 border-purple-200",
      delivered: "bg-green-50 text-green-700 border-green-200",
      cancelled: "bg-red-50 text-red-700 border-red-200"
    };

    const formatDate = (date) => {
      if (!date) return "";
      return new Date(date).toLocaleDateString("en-IN", {
        day: "2-digit",
        month: "short",
        year: "numeric"
      });
    };

    if (loading) {
      return (
        <div className="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center">
          <div className="text-center">
            <div className="animate-spin rounded-full h-14 w-14 border-4 border-gray-200 border-t-blue-600 mx-auto mb-4"></div>
            <p className="text-gray-600 font-medium">Loading your orders...</p>
          </div>
        </div>
      );
    }

    return (
      <div className="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 px-4 py-8">
        <div className="max-w-5xl mx-auto">

          <div className="mb-8">
            <h2 className="text-3xl font-bold text-gray-900 mb-2">Track Your Orders</h2>
          </div>

          {orders.length === 0 ? (
            <div className="bg-white rounded-2xl shadow-sm border p-12 text-center">
              <div className="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg className="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
              </div>
              <h3 className="text-xl font-semibold text-gray-900 mb-2">No orders yet</h3>
              <p className="text-gray-500 mb-6">Start shopping to see your orders here</p>
              <button className="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition-colors">
                Start Shopping
              </button>
            </div>
          ) : (
            <div className="space-y-6">

              {orders.map((order, index) => {
                const currentStep = steps.indexOf(order.status);
                const isCancelled = order.status === "cancelled";

                return (
                  <div key={index} className="bg-white rounded-2xl shadow-sm border border-gray-200 hover:shadow-lg transition-all duration-300">

                    {/* Header with product info */}
                    <div className="p-6 border-b border-gray-100">
                      <div className="flex gap-5 items-start">
                        <img
                          src={order.image}
                          alt="product"
                          className="w-28 h-28 rounded-xl object-cover border-2 border-gray-100"
                        />

                        <div className="flex-1 min-w-0">
                          <div className="flex items-start justify-between gap-4 mb-3">
                            <h3 className="font-semibold text-gray-900 text-lg leading-tight">
                              {order.product_name}
                            </h3>

                            <span className={`px-3 py-1.5 rounded-lg text-sm font-semibold border whitespace-nowrap ${statusColors[order.status]}`}>
                              {statusLabels[order.status]}
                            </span>
                          </div>

                          <div className="space-y-2">
                            <p className="text-gray-600">
                              Quantity: <span className="font-medium text-gray-900">{order.quantity}</span>
                            </p>

                            <p className="text-2xl font-bold text-gray-900">
                              ₹{order.total_amount?.toLocaleString('en-IN')}
                            </p>

                            <p className="text-sm text-gray-500">
                              Ordered on {formatDate(order.created_at)}
                            </p>
                          </div>
                        </div>
                      </div>
                    </div>

                    {/* Tracking section */}
                    <div className="p-6">
                      {!isCancelled ? (
                        <div>
                          <h4 className="text-sm font-semibold text-gray-700 mb-6">Order Status</h4>

                          {/* Progress steps */}
                          <div className="relative">

                            {/* Connecting line */}
                            <div className="absolute top-5 left-0 w-full h-0.5 bg-gray-200"
                              style={{ zIndex: 0 }}></div>

                            <div className="absolute top-5 left-0 h-0.5 bg-green-500 transition-all duration-700"
                              style={{
                                width: `${(currentStep / (steps.length - 1)) * 100}%`,
                                zIndex: 0
                              }}></div>

                            {/* Steps */}
                            <div className="relative flex justify-between" style={{ zIndex: 1 }}>
                              {steps.map((step, i) => {
                                const isActive = i === currentStep;
                                const isCompleted = i < currentStep;
                                const isPending = i > currentStep;

                                return (
                                  <div key={i} className="flex flex-col items-center flex-1">

                                    {/* Circle */}
                                    <div className={`
                                    w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm
                                    transition-all duration-300 border-4 border-white
                                    ${isCompleted ? 'bg-green-500 text-white shadow-lg' : ''}
                                    ${isActive ? 'bg-blue-500 text-white shadow-lg ring-4 ring-blue-100' : ''}
                                    ${isPending ? 'bg-gray-200 text-gray-400' : ''}
                                  `}>
                                      {isCompleted ? '✓' : i + 1}
                                    </div>

                                    {/* Label */}
                                    <p className={`
                                    mt-3 text-xs font-medium text-center max-w-[80px]
                                    ${i <= currentStep ? 'text-gray-900' : 'text-gray-400'}
                                  `}>
                                      {statusLabels[step]}
                                    </p>

                                  </div>
                                );
                              })}
                            </div>
                          </div>

                          {/* Delivery dates */}
                          {(order.dispatched_at || order.delivered_at) && (
                            <div className="mt-6 pt-6 border-t space-y-2">
                              {order.dispatched_at && (
                                <div className="flex items-center gap-2 text-sm text-gray-600">
                                  <span className="w-2 h-2 bg-purple-500 rounded-full"></span>
                                  <span>Dispatched on {formatDate(order.dispatched_at)}</span>
                                </div>
                              )}
                              {order.delivered_at && (
                                <div className="flex items-center gap-2 text-sm text-gray-600">
                                  <span className="w-2 h-2 bg-green-500 rounded-full"></span>
                                  <span>Delivered on {formatDate(order.delivered_at)}</span>
                                </div>
                              )}
                            </div>
                          )}
                        </div>
                      ) : (
                        <div className="bg-red-50 border border-red-200 rounded-xl p-4 flex items-center gap-3">
                          <div className="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg className="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                            </svg>
                          </div>
                          <div>
                            <p className="font-semibold text-red-900">Order Cancelled</p>
                            <p className="text-sm text-red-700">This order has been cancelled</p>
                          </div>
                        </div>
                      )}
                    </div>

                  </div>
                );
              })}

            </div>
          )}

        </div>
      </div>
    );
  }

export default Trackorder;