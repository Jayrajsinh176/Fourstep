import Sidebar from "../components/Sidebar";
import Navbar from "../components/Navbar";
import { useEffect, useState } from "react";
import axios from "axios";
import jsPDF from "jspdf";
import autoTable from "jspdf-autotable";

const API_BASE_URL =
    import.meta.env.VITE_API_BASE_URL || "https://fourstepretail.com/api";

const ORDERS_PER_PAGE = 10;

const STATUS_OPTIONS = ["all", "pending", "processing", "delivered", "cancelled"];

const statusStyles = {
    delivered: "bg-emerald-50 text-emerald-700 border border-emerald-200",
    pending: "bg-amber-50 text-amber-700 border border-amber-200",
    processing: "bg-blue-50 text-blue-700 border border-blue-200",
    cancelled: "bg-red-50 text-red-600 border border-red-200",
};

const statusDot = {
    delivered: "bg-emerald-500",
    pending: "bg-amber-400",
    processing: "bg-blue-500",
    cancelled: "bg-red-500",
};

export default function OrderHistory() {
    const [orders, setOrders] = useState([]);
    const [loading, setLoading] = useState(true);
    const [selectedOrder, setSelectedOrder] = useState(null);
    const [showModal, setShowModal] = useState(false);
    const [statusFilter, setStatusFilter] = useState("all");
    const [currentPage, setCurrentPage] = useState(1);

    const [showDownloadModal, setShowDownloadModal] = useState(false);
    const [fromDate, setFromDate] = useState("");
    const [toDate, setToDate] = useState("");

    const memberData = JSON.parse(localStorage.getItem("memberData") || "{}");
    const memberUserId = memberData?.user_id || "";
    const memberName = memberData?.fullname || memberData?.name || "-";

    const fetchOrders = async () => {
        try {
            const response = await axios.get(`${API_BASE_URL}/member/order-history`, {
                headers: { "member-id": memberUserId },
            });
            setOrders(response.data.orders || []);
        } catch (error) {
            console.log(error);
        } finally {
            setLoading(false);
        }
    };

    const viewInvoice = async (id) => {
        try {
            const response = await axios.get(
                `${API_BASE_URL}/member/order-invoice/${id}`,
                { headers: { "member-id": memberUserId } }
            );
            setSelectedOrder(response.data.order);
            setShowModal(true);
        } catch (error) {
            console.log(error);
        }
    };

    const handlePrint = () => {
        const printContents = document.getElementById("invoice-print").innerHTML;
        const originalContents = document.body.innerHTML;
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
        window.location.reload();
    };

    const handleDownloadOrdersPDF = async () => {
        if (!fromDate || !toDate) {
            alert("Please select From Date and To Date.");
            return;
        }

        if (new Date(fromDate) > new Date(toDate)) {
            alert("From Date cannot be greater than To Date.");
            return;
        }

        const filteredOrders = orders.filter((order) => {
            const orderDate = new Date(order.created_at);
            const start = new Date(fromDate);
            const end = new Date(toDate);

            end.setHours(23, 59, 59, 999);

            return orderDate >= start && orderDate <= end;
        });

        if (filteredOrders.length === 0) {
            alert("No orders found for the selected date range.");
            return;
        }

        const pdf = new jsPDF("p", "mm", "a4");

        let y = 15;
        // Company Name
        pdf.setFontSize(20);
        pdf.setTextColor(176, 66, 46);
        pdf.setFontSize(20);
        pdf.setTextColor(176, 66, 46);
        pdf.text("FourStep Retail", 105, y, { align: "center" });

        y += 8;

        pdf.setFontSize(16);
        pdf.setTextColor(0, 0, 0);
        pdf.text("Order History Report", 105, y, { align: "center" });

        y += 8;

        // Member Details
        pdf.setFontSize(11);
        pdf.text(`Member Name : ${memberName}`, 14, y);

        y += 6;
        pdf.text(`Member ID : ${memberUserId}`, 14, y);

        y += 6;
        pdf.text(`Report From : ${fromDate}`, 14, y);

        y += 6;
        pdf.text(`Report To : ${toDate}`, 14, y);

        y += 6;
        pdf.text(
            `Generated On : ${new Date().toLocaleString("en-IN")}`,
            14,
            y
        );
        pdf.setDrawColor(176, 66, 46);
        pdf.line(14, y + 2, 196, y + 2);

        y += 8;

        y += 10;

        // Table Header
        const tableData = filteredOrders.map((order, index) => [
            index + 1,
            order.invoice_id || "-",
            order.delivery_member_id
                ? order.delivery_name
                : order.mlm_member?.fullname || "-",
            new Date(order.created_at).toLocaleDateString("en-IN"),
            order.status,
            `₹${Number(order.total_amount).toLocaleString("en-IN", {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            })}`,
            order.delivery_member_id ? "Other" : "Self",
        ]);

        const totalAmount = filteredOrders.reduce(
            (sum, order) => sum + Number(order.total_amount),
            0
        );

        const selfOrders = filteredOrders.filter(
            (order) => !order.delivery_member_id
        ).length;

        const otherMemberOrders = filteredOrders.filter(
            (order) => order.delivery_member_id
        ).length;

        // Summary

        pdf.setFontSize(12);
        pdf.setTextColor(0, 0, 0);

        pdf.text(`Total Orders : ${filteredOrders.length}`, 14, y);
        const formattedTotal = new Intl.NumberFormat("en-IN", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }).format(totalAmount);

        pdf.text("Total Amount :", 80, y);
        pdf.text(`₹ ${formattedTotal}`, 125, y);

        y += 8;

        pdf.text(`Self Orders : ${selfOrders}`, 14, y);
        pdf.text(`Other Member Orders : ${otherMemberOrders}`, 80, y);

        y += 10;


        autoTable(pdf, {
            startY: y,
            head: [[
                "Sr.",
                "Invoice",
                "Member",
                "Date",
                "Status",
                "Amount",
                "Type",
            ]],
            body: tableData,
            theme: "grid",
            headStyles: {
                fillColor: [176, 66, 46],
                textColor: [255, 255, 255],
                fontStyle: "bold",
            },
            styles: {
                fontSize: 10,
                cellPadding: 3,
            },
        });


        const pageCount = pdf.internal.getNumberOfPages();

        for (let i = 1; i <= pageCount; i++) {
            pdf.setPage(i);

            pdf.setFontSize(10);
            pdf.setTextColor(120);
            pdf.setFontSize(9);

            pdf.text(
                "Generated by FourStep Retail Inventory Management System",
                14,
                pdf.internal.pageSize.getHeight() - 10
            );
            pdf.text(
                `Page ${i} of ${pageCount}`,
                pdf.internal.pageSize.getWidth() - 40,
                pdf.internal.pageSize.getHeight() - 10
            );
        }

        pdf.save(`Order-History-${fromDate}-to-${toDate}.pdf`);

        setFromDate("");
        setToDate("");
        setShowDownloadModal(false);
    };

    useEffect(() => { fetchOrders(); }, []);

    // Filter + Paginate
    const filtered = statusFilter === "all"
        ? orders
        : orders.filter((o) => o.status === statusFilter);

    const totalPages = Math.ceil(filtered.length / ORDERS_PER_PAGE);
    const paginated = filtered.slice(
        (currentPage - 1) * ORDERS_PER_PAGE,
        currentPage * ORDERS_PER_PAGE
    );

    const handleFilterChange = (status) => {
        setStatusFilter(status);
        setCurrentPage(1);
    };

    return (
        <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">
            <Sidebar />
            <div className="flex-1 min-w-0 flex flex-col">
                <Navbar />
                <div className="p-4 md:p-8 bg-gray-100 min-h-screen">

                    {/* Header */}
                    <div className="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h1 className="text-2xl md:text-3xl font-bold text-[#B0422E]">
                                Member Order History
                            </h1>
                            <p className="text-gray-500 mt-1 text-sm">
                                View self orders and other member orders
                            </p>
                        </div>

                        <button
                            onClick={() => setShowDownloadModal(true)}
                            className="bg-[#B0422E] hover:bg-[#933826] text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow transition-colors"
                        >
                            📄 Download Orders
                        </button>
                    </div>

                    {/* Filter Bar */}
                    <div className="mb-5 flex flex-wrap gap-2 items-center">
                        <span className="text-sm font-medium text-gray-500 mr-1">Filter:</span>
                        {STATUS_OPTIONS.map((s) => (
                            <button
                                key={s}
                                onClick={() => handleFilterChange(s)}
                                className={`px-4 py-1.5 rounded-full text-xs font-semibold capitalize transition-all border ${statusFilter === s
                                    ? "bg-[#B0422E] text-white border-[#B0422E] shadow-sm"
                                    : "bg-white text-gray-500 border-gray-200 hover:border-[#B0422E] hover:text-[#B0422E]"
                                    }`}
                            >
                                {s === "all" ? "All Orders" : s}
                                {s !== "all" && (
                                    <span className="ml-1.5 opacity-70">
                                        ({orders.filter((o) => o.status === s).length})
                                    </span>
                                )}
                                {s === "all" && (
                                    <span className="ml-1.5 opacity-70">({orders.length})</span>
                                )}
                            </button>
                        ))}
                    </div>

                    {/* Loading */}
                    {loading && (
                        <div className="bg-white rounded-2xl shadow p-10 text-center text-gray-400">
                            <svg className="animate-spin h-8 w-8 mx-auto mb-3 text-[#B0422E]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                                <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                            </svg>
                            Loading orders...
                        </div>
                    )}

                    {!loading && (
                        <>
                            {/* Mobile Cards */}
                            <div className="grid grid-cols-1 gap-3 lg:hidden">
                                {paginated.length > 0 ? (
                                    paginated.map((order) => (
                                        <div key={order.id} className="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                                            <div className="flex justify-between items-start mb-3">
                                                <div>
                                                    <h2 className="font-bold text-sm text-gray-800">{order.invoice_id}</h2>
                                                    <p className="text-xs text-gray-400 mt-0.5">
                                                        {new Date(order.created_at).toLocaleDateString("en-IN", { day: "2-digit", month: "short", year: "numeric" })}
                                                    </p>
                                                </div>
                                                <span className={`px-2.5 py-1 rounded-full text-xs font-semibold flex items-center gap-1.5 ${statusStyles[order.status] || "bg-gray-100 text-gray-600"}`}>
                                                    <span className={`w-1.5 h-1.5 rounded-full ${statusDot[order.status] || "bg-gray-400"}`} />
                                                    {order.status}
                                                </span>
                                            </div>
                                            <div className="grid grid-cols-3 gap-2 text-xs mb-3">
                                                <div className="bg-gray-50 rounded-lg p-2 text-center">
                                                    <div className="text-gray-400 mb-0.5">Amount</div>
                                                    <div className="font-bold text-gray-800">₹{Number(order.total_amount).toLocaleString("en-IN", {
                                                        minimumFractionDigits: 2,
                                                        maximumFractionDigits: 2,
                                                    })}</div>
                                                </div>
                                                <div className="bg-gray-50 rounded-lg p-2 text-center">
                                                    <div className="text-gray-400 mb-0.5">Type</div>
                                                    <div className="font-semibold text-gray-700">{order.delivery_member_id ? "Other" : "Self"}</div>
                                                </div>
                                                <div className="bg-gray-50 rounded-lg p-2 text-center">
                                                    <div className="text-gray-400 mb-0.5">Delivery</div>
                                                    <div className="font-semibold text-gray-700 truncate">{order.delivery_type}</div>
                                                </div>
                                            </div>
                                            <button
                                                onClick={() => viewInvoice(order.id)}
                                                className="w-full bg-[#B0422E] hover:bg-[#933826] text-white py-2 rounded-lg text-sm font-medium transition-colors"
                                            >
                                                View Invoice
                                            </button>
                                        </div>
                                    ))
                                ) : (
                                    <div className="bg-white rounded-xl shadow p-10 text-center text-gray-400">
                                        No orders found for this filter.
                                    </div>
                                )}
                            </div>

                            {/* Desktop Table */}
                            <div className="hidden lg:block bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                                <table className="w-full">
                                    <thead>
                                        <tr className="bg-[#B0422E] text-white text-sm">
                                            <th className="px-5 py-3.5 text-left font-semibold">Invoice</th>
                                            <th className="px-5 py-3.5 text-left font-semibold">Amount</th>
                                            <th className="px-5 py-3.5 text-left font-semibold">Status</th>
                                            <th className="px-5 py-3.5 text-left font-semibold">Member</th>
                                            <th className="px-5 py-3.5 text-left font-semibold">Order Type</th>
                                            <th className="px-5 py-3.5 text-left font-semibold">Delivery</th>
                                            <th className="px-5 py-3.5 text-left font-semibold">Date</th>
                                            <th className="px-5 py-3.5 text-center font-semibold">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-gray-50">
                                        {paginated.length > 0 ? (
                                            paginated.map((order, i) => (
                                                <tr key={order.id} className={`text-sm transition-colors hover:bg-red-50/30 ${i % 2 === 0 ? "" : "bg-gray-50/50"}`}>
                                                    <td className="px-5 py-3.5 font-semibold text-gray-800">{order.invoice_id}</td>
                                                    <td className="px-5 py-3.5 font-medium text-gray-700">₹{Number(order.total_amount).toLocaleString("en-IN", {
                                                        minimumFractionDigits: 2,
                                                        maximumFractionDigits: 2,
                                                    })}</td>
                                                    <td className="px-5 py-3.5">
                                                        <span className={`px-2.5 py-1 rounded-full text-xs font-semibold inline-flex items-center gap-1.5 ${statusStyles[order.status] || "bg-gray-100 text-gray-600 border border-gray-200"}`}>
                                                            <span className={`w-1.5 h-1.5 rounded-full ${statusDot[order.status] || "bg-gray-400"}`} />
                                                            {order.status}
                                                        </span>
                                                    </td>
                                                    <td className="px-5 py-3.5">
                                                        <div className="font-semibold text-gray-800">
                                                            {order.delivery_member_id
                                                                ? order.delivery_name
                                                                : order.mlm_member?.fullname}
                                                        </div>
                                                        <div className="text-xs text-gray-500">
                                                            {order.delivery_member_id
                                                                ? order.delivery_member_id
                                                                : order.mlm_member?.user_id}
                                                        </div>
                                                    </td>
                                                    <td className="px-5 py-3.5 text-gray-600">
                                                        <span
                                                            className={`inline-flex items-center px-3 py-1 rounded-full text-xs font-medium whitespace-nowrap ${order.delivery_member_id
                                                                ? "bg-purple-100 text-purple-700"
                                                                : "bg-gray-100 text-gray-700"
                                                                }`}
                                                        >
                                                            {order.delivery_member_id
                                                                ? "Other Member"
                                                                : "Self"}
                                                        </span>
                                                    </td>
                                                    <td className="px-5 py-3.5 text-gray-600 capitalize">{order.delivery_type}</td>
                                                    <td className="px-5 py-3.5 text-gray-500">
                                                        {new Date(order.created_at).toLocaleDateString("en-IN", { day: "2-digit", month: "short", year: "numeric" })}
                                                    </td>
                                                    <td className="px-5 py-3.5 text-center">
                                                        <button
                                                            onClick={() => viewInvoice(order.id)}
                                                            className="bg-[#B0422E] hover:bg-[#933826] text-white px-4 py-1.5 rounded-lg text-xs font-medium transition-colors"
                                                        >
                                                            View
                                                        </button>
                                                    </td>
                                                </tr>
                                            ))
                                        ) : (
                                            <tr>
                                                <td colSpan="7" className="py-16 text-center text-gray-400">
                                                    No orders found for this filter.
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>

                            {/* Pagination */}
                            {totalPages > 1 && (
                                <div className="mt-5 flex items-center justify-between flex-wrap gap-3">
                                    <p className="text-sm text-gray-500">
                                        Showing <span className="font-semibold text-gray-700">{(currentPage - 1) * ORDERS_PER_PAGE + 1}</span>–<span className="font-semibold text-gray-700">{Math.min(currentPage * ORDERS_PER_PAGE, filtered.length)}</span> of <span className="font-semibold text-gray-700">{filtered.length}</span> orders
                                    </p>
                                    <div className="flex items-center gap-1.5">
                                        <button
                                            onClick={() => setCurrentPage((p) => Math.max(1, p - 1))}
                                            disabled={currentPage === 1}
                                            className="px-3 py-1.5 rounded-lg border text-sm font-medium disabled:opacity-40 disabled:cursor-not-allowed bg-white border-gray-200 hover:border-[#B0422E] hover:text-[#B0422E] transition-colors"
                                        >
                                            ← Prev
                                        </button>
                                        {Array.from({ length: totalPages }, (_, i) => i + 1).map((page) => (
                                            <button
                                                key={page}
                                                onClick={() => setCurrentPage(page)}
                                                className={`w-8 h-8 rounded-lg text-sm font-semibold transition-colors ${page === currentPage
                                                    ? "bg-[#B0422E] text-white shadow-sm"
                                                    : "bg-white border border-gray-200 text-gray-600 hover:border-[#B0422E] hover:text-[#B0422E]"
                                                    }`}
                                            >
                                                {page}
                                            </button>
                                        ))}
                                        <button
                                            onClick={() => setCurrentPage((p) => Math.min(totalPages, p + 1))}
                                            disabled={currentPage === totalPages}
                                            className="px-3 py-1.5 rounded-lg border text-sm font-medium disabled:opacity-40 disabled:cursor-not-allowed bg-white border-gray-200 hover:border-[#B0422E] hover:text-[#B0422E] transition-colors"
                                        >
                                            Next →
                                        </button>
                                    </div>
                                </div>
                            )}
                        </>
                    )}
                </div>
            </div>

            {/* Invoice Modal — Compact */}
            {showModal && selectedOrder && (
                <div
                    className="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4"
                    onClick={(e) => { if (e.target === e.currentTarget) setShowModal(false); }}
                >
                    <div
                        id="invoice-print"
                        className="bg-white w-full max-w-3xl rounded-2xl shadow-2xl max-h-[90vh] flex flex-col"
                    >
                        {/* Modal Header */}
                        <div className="flex justify-between items-center border-b px-5 py-4 flex-shrink-0">
                            <div className="flex items-center gap-3">
                                <div>
                                    <h2 className="text-lg font-bold text-[#B0422E] leading-tight">Invoice</h2>
                                    <p className="text-xs text-gray-400 font-mono mt-0.5">{selectedOrder.invoice_id}</p>
                                </div>
                                <span className={`px-2.5 py-1 rounded-full text-xs font-semibold inline-flex items-center gap-1.5 ${statusStyles[selectedOrder.status] || "bg-gray-100 text-gray-600 border border-gray-200"}`}>
                                    <span className={`w-1.5 h-1.5 rounded-full ${statusDot[selectedOrder.status] || "bg-gray-400"}`} />
                                    {selectedOrder.status}
                                </span>
                            </div>
                            <div className="flex gap-2">
                                <button
                                    onClick={handlePrint}
                                    className="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-lg text-sm font-medium transition-colors"
                                >
                                    🖨 Print
                                </button>
                                <button
                                    onClick={() => setShowModal(false)}
                                    className="bg-gray-100 hover:bg-gray-200 text-gray-600 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors"
                                >
                                    ✕ Close
                                </button>
                            </div>
                        </div>

                        {/* Scrollable Body */}
                        <div className="overflow-y-auto flex-1 px-5 py-4 space-y-4">

                            {/* Order + Delivery Info — Compact 2-col */}
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div className="bg-gray-50 rounded-xl p-4">
                                    <h3 className="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Order Details</h3>
                                    <div className="space-y-1.5 text-sm">
                                        <div className="flex justify-between">
                                            <span className="text-gray-500">Payment</span>
                                            <span className="font-medium text-gray-800">{selectedOrder.payment_method}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-gray-500">Delivery</span>
                                            <span className="font-medium text-gray-800 capitalize">{selectedOrder.delivery_type}</span>
                                        </div>
                                    </div>
                                </div>
                                <div className="bg-gray-50 rounded-xl p-4">
                                    <h3 className="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Delivery Details</h3>
                                    <div className="space-y-1.5 text-sm">
                                        <div className="flex justify-between gap-2">
                                            <span className="text-gray-500 shrink-0">Name</span>
                                            <span className="font-medium text-gray-800 text-right">
                                                {selectedOrder.delivery_member_id
                                                    ? (selectedOrder.delivery_name || "-")
                                                    : (selectedOrder.mlm_member?.fullname || "-")}
                                            </span>
                                        </div>
                                        <div className="flex justify-between gap-2">
                                            <span className="text-gray-500 shrink-0">Address</span>
                                            <span className="font-medium text-gray-800 text-right text-xs leading-snug">
                                                {selectedOrder.delivery_member_id
                                                    ? (selectedOrder.delivery_address || "-")
                                                    : (selectedOrder.mlm_member?.address || "-")}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Products Table */}
                            <div className="overflow-x-auto rounded-xl border border-gray-100">
                                <table className="w-full min-w-[540px] text-sm">
                                    <thead>
                                        <tr className="bg-[#B0422E] text-white text-xs">
                                            <th className="px-4 py-3 text-left font-semibold">Product</th>
                                            <th className="px-4 py-3 text-left font-semibold">Variant</th>
                                            <th className="px-4 py-3 text-center font-semibold">Qty</th>
                                            <th className="px-4 py-3 text-right font-semibold">Price</th>
                                            <th className="px-4 py-3 text-right font-semibold">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-gray-50">
                                        {selectedOrder.items?.length > 0 ? (
                                            selectedOrder.items.map((item, i) => {
                                                const price = item.variant?.offer_price || item.price;
                                                const total = price * item.quantity;
                                                return (
                                                    <tr key={item.id} className={`text-xs ${i % 2 === 0 ? "bg-white" : "bg-gray-50/60"}`}>
                                                        <td className="px-4 py-3 font-semibold text-gray-800">{item.product?.name}</td>
                                                        <td className="px-4 py-3 text-gray-600">
                                                            <div>{item.variant?.packing_size || "-"}</div>
                                                            <div className="text-gray-400 text-[10px] mt-0.5">
                                                                PV: {item.variant?.pv || 0} | BV: {item.variant?.bv || 0}
                                                            </div>
                                                        </td>
                                                        <td className="px-4 py-3 text-center font-medium">{item.quantity}</td>
                                                        <td className="px-4 py-3 text-right text-gray-600">₹{Number(price).toLocaleString("en-IN", {
                                                            minimumFractionDigits: 2,
                                                            maximumFractionDigits: 2,
                                                        })}</td>
                                                        <td className="px-4 py-3 text-right font-bold text-gray-800">₹{Number(total).toLocaleString("en-IN", {
                                                            minimumFractionDigits: 2,
                                                            maximumFractionDigits: 2,
                                                        })}</td>
                                                    </tr>
                                                );
                                            })
                                        ) : (
                                            <tr>
                                                <td colSpan="5" className="py-8 text-center text-gray-400 text-xs">No products found</td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>

                            {/* Totals — Compact */}
                            <div className="flex justify-end">
                                <div className="w-full md:w-72 bg-gray-50 rounded-xl overflow-hidden border border-gray-100 text-sm">
                                    <div className="flex justify-between px-4 py-2.5 border-b border-gray-100">
                                        <span className="text-gray-500">Total Amount</span>
                                        <span className="font-semibold">₹{Number(selectedOrder.total_amount).toLocaleString("en-IN", {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2,
                                        })}</span>
                                    </div>
                                    <div className="flex justify-between px-4 py-2.5 border-b border-gray-100">
                                        <span className="text-gray-500">Coupon Discount</span>
                                        <span className="font-semibold text-emerald-600">- ₹{Number(selectedOrder.coupon_discount || 0).toLocaleString("en-IN", {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2,
                                        })}</span>
                                    </div>
                                    <div className="flex justify-between px-4 py-2.5 border-b border-gray-100">
                                        <span className="text-gray-500">Wallet Used</span>
                                        <span className="font-semibold text-blue-600">- ₹{Number(selectedOrder.wallet_amount || 0).toLocaleString("en-IN", {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2,
                                        })}</span>
                                    </div>
                                    <div className="flex justify-between px-4 py-3 bg-[#B0422E] text-white font-bold">
                                        <span>Amount Paid</span>
                                        <span>₹{Number(selectedOrder.amount_paid).toLocaleString("en-IN", {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2,
                                        })}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            )}
            {/* Download Pdf Modal */}
            {showDownloadModal && (
                <div
                    className="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
                    onClick={(e) => {
                        if (e.target === e.currentTarget) {
                            setShowDownloadModal(false);
                        }
                    }}
                >
                    <div className="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">

                        <h2 className="text-xl font-bold text-[#B0422E] mb-5">
                            Download Order History
                        </h2>

                        <div className="space-y-4">

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    From Date
                                </label>

                                <input
                                    type="date"
                                    value={fromDate}
                                    onChange={(e) => setFromDate(e.target.value)}
                                    className="w-full border rounded-lg px-3 py-2"
                                />
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    To Date
                                </label>

                                <input
                                    type="date"
                                    value={toDate}
                                    onChange={(e) => setToDate(e.target.value)}
                                    className="w-full border rounded-lg px-3 py-2"
                                />
                            </div>

                        </div>

                        <div className="flex justify-end gap-3 mt-6">

                            <button
                                onClick={() => setShowDownloadModal(false)}
                                className="px-4 py-2 rounded-lg border"
                            >
                                Cancel
                            </button>

                            <button
                                onClick={handleDownloadOrdersPDF}
                                className="bg-[#B0422E] hover:bg-[#933826] text-white px-5 py-2 rounded-lg"
                            >
                                Download PDF
                            </button>

                        </div>

                    </div>
                </div>
            )}
        </div>
    );
}