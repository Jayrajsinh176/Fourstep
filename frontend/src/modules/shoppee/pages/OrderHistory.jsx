import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { shoppeeApi as api } from "../api/axios";
import { MdOutlineFileDownload } from "react-icons/md";
import { jsPDF } from "jspdf";
import autoTable from "jspdf-autotable";

function OrderHistory() {
  const navigate = useNavigate();

  const [orderHistory, setOrderHistory] = useState([]);
  const [loading, setLoading] = useState(true);
  const [currentPage, setCurrentPage] = useState(1);

  const itemsPerPage = 10;

  useEffect(() => { fetchOrderHistory(); }, []);

  const fetchOrderHistory = async () => {
    try {
      const storedUser = JSON.parse(localStorage.getItem("user"));
      const userId = storedUser?.id;
      const response = await api.get(`/order-history/${userId}`);
      const sortedData =
        response?.data?.data && Array.isArray(response.data.data)
          ? response.data.data.sort((a, b) => b.id - a.id)
          : [];
      setOrderHistory(sortedData);
      setCurrentPage(1);
    } catch (error) {
      console.error("Error fetching order history:", error);
      setOrderHistory([]);
    } finally {
      setLoading(false);
    }
  };


  // ── PAGINATION ──
  const indexOfLastItem = currentPage * itemsPerPage;
  const indexOfFirstItem = indexOfLastItem - itemsPerPage;
  const currentOrders = Array.isArray(orderHistory)
    ? orderHistory.slice(indexOfFirstItem, indexOfLastItem)
    : [];
  const totalPages = Math.max(1, Math.ceil(orderHistory.length / itemsPerPage));

  const getPageNumbers = (current, total) => {
    if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
    if (current <= 4) return [1, 2, 3, 4, 5, "ellipsis-end", total];
    if (current >= total - 3) return [1, "ellipsis-start", total - 4, total - 3, total - 2, total - 1, total];
    return [1, "ellipsis-start", current - 1, current, current + 1, "ellipsis-end", total];
  };

  const handleDownloadPDF = () => {
  const doc = new jsPDF({
    orientation: "landscape",
  });

  doc.setFontSize(18);
  doc.text("Delivery Order History", 14, 15);

  const tableColumn = [
    "Sr",
    "Order No",
    "Date",
    "Customer",
    "User ID",
    "Type",
    "Status",
    "Amount",
  ];

  const tableRows = orderHistory.map((order, index) => [
    index + 1,
    order.order_no,
    order.order_date,
    order.customer_name || "N/A",
    order.user_id || "N/A",
    order.order_type || "N/A",
    order.status,
  Number(order.total_amount || 0).toLocaleString("en-IN", {
  minimumFractionDigits: 2,
  maximumFractionDigits: 2,
}),
  ]);

  autoTable(doc, {
    head: [tableColumn],
    body: tableRows,
    startY: 22,
    theme: "grid",
    styles: {
      fontSize: 8,
    },
    headStyles: {
      fillColor: [176, 66, 46],
    },
  });

  doc.save("Delivery_Order_History.pdf");
};

  return (
    <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">
      <div className="flex-1 min-w-0 flex flex-col">

        <div className="text-center mt-4 md:mt-6">
          <h1 className="text-2xl md:text-3xl font-bold text-[#B0422E]">
            Delivery Order History
          </h1>
        </div>

        <div className="p-3 md:p-6">
          <div className="bg-white rounded-2xl shadow-sm p-3 md:p-6 overflow-x-auto">

            {loading ? (
              <p className="text-center py-4 text-sm md:text-base">Loading...</p>
            ) : (
              <>

              <div className="flex justify-end mb-4">
                    <button
                      onClick={handleDownloadPDF}
                      className="text-xs md:text-sm font-semibold bg-blue-500 text-white px-3 md:px-4 py-2 rounded-lg hover:bg-blue-600 flex items-center gap-2"
                    >
                      <MdOutlineFileDownload className="text-lg" />
                      <span className="hidden sm:inline">
                        Download
                      </span>
                    </button>
                  </div>

                <table className="w-full min-w-[1100px] text-xs md:text-sm">

                
                  <thead>
                    <tr className="bg-[#B0422E] text-white">
                      <th className="py-3 px-3 rounded-l-xl">Sr</th>
                      <th className="py-3 px-3 whitespace-nowrap">Order No.</th>
                      <th className="py-3 px-3 whitespace-nowrap">Date</th>
                      <th className="py-3 px-3 whitespace-nowrap">Customer</th>
                      <th className="py-3 px-3 whitespace-nowrap">User ID</th>
                      <th className="py-3 px-3 whitespace-nowrap">Type</th>
                      <th className="py-3 px-3 whitespace-nowrap">Status</th>
                      <th className="py-3 px-3 whitespace-nowrap">Amount</th>
                      <th className="py-3 px-3 whitespace-nowrap">Details</th>
                      <th className="py-3 px-3 rounded-r-xl whitespace-nowrap">Invoice</th>
                    </tr>
                  </thead>

                  <tbody className="font-medium">
                    {currentOrders.length > 0 ? (
                      currentOrders.map((order, index) => (
                        <tr className="border-b border-gray-300" key={order.id}>

                          <td className="py-4 px-3 text-center">
                            {indexOfFirstItem + index + 1}
                          </td>

                          <td className="py-4 px-3 whitespace-nowrap">{order.order_no}</td>
                          <td className="py-4 px-3 whitespace-nowrap">{order.order_date}</td>
                          <td className="py-4 px-3 whitespace-nowrap">{order.customer_name || "N/A"}</td>
                          <td className="py-4 px-3 whitespace-nowrap">{order.user_id || "N/A"}</td>
                          <td className="py-4 px-3 whitespace-nowrap">{order.order_type || "N/A"}</td>

                          <td className="py-4 px-3 whitespace-nowrap">
                            <span className={`px-3 py-1 rounded-full text-xs font-semibold ${order.status === "delivered" || order.status === "Delivered"
                              ? "bg-green-100 text-green-700"
                              : order.status === "pending" || order.status === "Pending"
                                ? "bg-yellow-100 text-yellow-700"
                                : "bg-blue-100 text-blue-700"
                              }`}>
                              {order.status}
                            </span>
                          </td>

                          <td className="py-4 px-3 whitespace-nowrap">₹{order.total_amount}</td>

                          {/* VIEW → opens modal */}
                          <td className="py-4 px-3">
                            <button
                              onClick={() =>
                                window.open(
                                  `/shoppee/invoice/${order.id}`,
                                  "_blank"
                                )
                              }
                              className="flex items-center gap-1 text-blue-600 hover:text-blue-800 font-semibold text-xs transition-colors"
                            >
                              <svg xmlns="http://www.w3.org/2000/svg" className="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2.5">
                                <path strokeLinecap="round" strokeLinejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path strokeLinecap="round" strokeLinejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                              </svg>
                              View
                            </button>
                          </td>

                          {/* PRINT → opens invoice page and auto-triggers print */}
                          <td className="py-4 px-3">
                            <button
                              onClick={() =>
                                window.open(
                                  `/shoppee/invoice/${order.id}?print=true`,
                                  "_blank"
                                )
                              }
                              className="flex items-center gap-1 text-[#B0422E] hover:text-[#8f3621] font-semibold text-xs transition-colors"
                            >
                              <svg xmlns="http://www.w3.org/2000/svg" className="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2.5">
                                <polyline strokeLinecap="round" strokeLinejoin="round" points="6 9 6 2 18 2 18 9" />
                                <path strokeLinecap="round" strokeLinejoin="round" d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                                <rect strokeLinecap="round" strokeLinejoin="round" x="6" y="14" width="12" height="8" />
                              </svg>
                              Print
                            </button>
                          </td>

                        </tr>
                      ))
                    ) : (
                      <tr>
                        <td colSpan="10" className="py-4 px-4 text-gray-500 text-center">
                          No order history found
                        </td>
                      </tr>
                    )}
                  </tbody>
                </table>

                {/* PAGINATION */}
                <div className="flex flex-col items-center gap-2 mt-6">
                  <div className="flex items-center gap-1 flex-wrap justify-center">
                    <button
                      disabled={currentPage === 1}
                      onClick={() => setCurrentPage((p) => p - 1)}
                      aria-label="Previous page"
                      className="w-9 h-9 flex items-center justify-center rounded-md bg-gray-200 text-gray-600 disabled:opacity-40 hover:bg-gray-300 transition text-lg"
                    >
                      ‹
                    </button>

                    {getPageNumbers(currentPage, totalPages).map((page, i) =>
                      typeof page === "string" ? (
                        <span key={page + i} className="w-9 h-9 flex items-center justify-center text-gray-400 text-sm select-none">…</span>
                      ) : (
                        <button
                          key={page}
                          onClick={() => setCurrentPage(page)}
                          className={`w-9 h-9 flex items-center justify-center rounded-md text-sm font-medium transition ${currentPage === page
                            ? "bg-[#B0422E] text-white"
                            : "bg-gray-200 text-gray-700 hover:bg-gray-300"
                            }`}
                        >
                          {page}
                        </button>
                      )
                    )}

                    <button
                      disabled={currentPage === totalPages}
                      onClick={() => setCurrentPage((p) => p + 1)}
                      aria-label="Next page"
                      className="w-9 h-9 flex items-center justify-center rounded-md bg-gray-200 text-gray-600 disabled:opacity-40 hover:bg-gray-300 transition text-lg"
                    >
                      ›
                    </button>
                  </div>

                  <p className="text-xs text-gray-400">
                    Page {currentPage} of {totalPages}
                    &nbsp;·&nbsp;
                    Showing {indexOfFirstItem + 1}–{Math.min(indexOfLastItem, orderHistory.length)} of {orderHistory.length} orders
                  </p>
                </div>
              </>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}

export default OrderHistory;
