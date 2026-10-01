import React, { useEffect, useState } from "react";
import Sidebar from "../components/sidebar";
import Navbar from "../components/navbar";
import { shoppeeApi as api } from "../api/axios";
import jsPDF from "jspdf";
import autoTable from "jspdf-autotable";

function BranchOrderHistory() {

    const [fromDate, setFromDate] = useState("");
    const [toDate, setToDate] = useState("");

    const [pdfFromDate, setPdfFromDate] = useState("");
    const [pdfToDate, setPdfToDate] = useState("");

    const [orderHistory, setOrderHistory] = useState([]);

    const [loading, setLoading] = useState(true);

    const [selectedOrder, setSelectedOrder] = useState(null);


    const [showModal, setShowModal] = useState(false);
    const [showPdfModal, setShowPdfModal] = useState(false);

    const [currentPage, setCurrentPage] = useState(1);


    const itemsPerPage = 10;

    useEffect(() => {
        fetchOrderHistory();
    }, []);



    const fetchOrderHistory = async () => {

        try {

            const storedUser = JSON.parse(
                localStorage.getItem("user")
            );



            const response = await api.get(
                `/branch-sale-history/${storedUser.member_id}`,
                {
                    params: {
                        from_date: fromDate,
                        to_date: toDate,
                    },
                }
            );
            const sortedData =
                response?.data?.data &&
                    Array.isArray(response.data.data)
                    ? response.data.data.sort(
                        (a, b) => b.id - a.id
                    )
                    : [];

            setOrderHistory(sortedData);

            setCurrentPage(1);

        } catch (error) {

            console.error(
                "Error fetching order history:",
                error
            );

            setOrderHistory([]);

        } finally {

            setLoading(false);

        }
    };

    const handleViewDetails = async (id) => {

        try {

            setSelectedOrder(null);

            const response = await api.get(
                `/branch-sale-details/${id}`
            );

            setSelectedOrder(response.data);

            setShowModal(true);

        } catch (error) {

            console.error(
                "Error fetching order details:",
                error
            );

        }
    };

    const handlePrint = async (id) => {
        try {

            const response = await api.get(`/branch-sale-details/${id}`);

            const order = response.data.order;
            const products = response.data.products;
            const member = response.data.member;

            const doc = new jsPDF();
            doc.setFont("helvetica", "bold");
            doc.setFontSize(20);
            doc.text("FOURSTEP SHOPPEE", 105, 15, { align: "center" });

            doc.setFont("helvetica", "normal");
            doc.setFontSize(11);
            doc.text("Branch Order Invoice", 105, 22, { align: "center" });

            doc.setFontSize(9);

            doc.text(
                member.branch_name || "",
                105,
                28,
                { align: "center" }
            );

            doc.text(
                `${member.address}, ${member.city}, ${member.state} - ${member.pin_code}`,
                105,
                34,
                { align: "center" }
            );

            doc.text(
                `GST: ${member.gst_no || "-"}   Mobile: ${member.mobile_no}`,
                105,
                40,
                { align: "center" }
            );

            doc.text(
                `Email: ${member.email}`,
                105,
                46,
                { align: "center" }
            );

            doc.line(14, 50, 196, 50);

            doc.setFontSize(11);

            doc.setFont(undefined, "bold");
            doc.text("Order ID", 14, 60);
            doc.text("Date", 110, 60);

            doc.setFont(undefined, "normal");
            doc.text(`#${order.id}`, 45, 60);
            doc.text(
                new Date(order.created_at).toLocaleDateString("en-GB"),
                130,
                60
            );

            doc.setFont(undefined, "bold");
            doc.text("Customer ID", 14, 68);
            doc.text("Amount", 110, 68);

            doc.setFont(undefined, "normal");
            doc.text(order.customer_user_id || "-", 45, 68);
            doc.text("₹", 130, 68);

            doc.text(
                Number(order.total_amount).toLocaleString("en-IN", {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                }),
                136,
                68
            );

            autoTable(doc, {
                startY: 80,
                head: [[
                    "Sr",
                    "Product",
                    "Package",
                    "Qty",
                    "MRP",
                    "PV",
                    "BV",
                    "Amount"
                ]],

                body: products.map((item, index) => [

                    index + 1,

                    item.product_name,

                    item.packing_size || "-",

                    item.quantity,

                    item.price,

                    item.pv,

                    item.bv,

                    item.amount

                ])

            });

            doc.setFont("helvetica", "bold");

            doc.text(
                "Grand Total :",
                14,
                doc.lastAutoTable.finalY + 15
            );

            doc.text(
                "₹",
                55,
                doc.lastAutoTable.finalY + 15
            );

            doc.text(
                Number(order.total_amount).toLocaleString("en-IN", {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                }),
                61,
                doc.lastAutoTable.finalY + 15
            );
            doc.setFont("helvetica", "normal");

            doc.setFontSize(10);

            doc.text(
                "Thank you for shopping with FourStep.",
                105,
                doc.lastAutoTable.finalY + 30,
                { align: "center" }
            );

            doc.text(
                "This is a computer-generated invoice.",
                105,
                doc.lastAutoTable.finalY + 36,
                { align: "center" }
            );

            const blobUrl = doc.output("bloburl");

            const printWindow = window.open(blobUrl);

            if (printWindow) {

                printWindow.onload = () => {

                    printWindow.focus();

                    printWindow.print();

                };

            }

        } catch (error) {

            console.error(error);

        }
    };

    const handleDownloadPdf = async () => {

        try {

            if (!pdfFromDate || !pdfToDate) {

                alert("Please select From Date and To Date.");

                return;

            }

            const storedUser = JSON.parse(
                localStorage.getItem("user")
            );

            const response = await api.get(
                `/branch-sale-history/${storedUser.member_id}`,
                {
                    params: {
                        from_date: pdfFromDate,
                        to_date: pdfToDate,
                    },
                }
            );

            const orders =
                response?.data?.data || [];

            if (orders.length === 0) {

                alert("No orders found for the selected date range.");

                return;

            }

            const doc = new jsPDF();

            doc.setFont("helvetica", "bold");
            doc.setFontSize(20);
            doc.text("FOURSTEP SHOPPEE", 105, 15, {
                align: "center",
            });

            doc.setFont("helvetica", "normal");
            doc.setFontSize(11);
            doc.text("Branch Order Report", 105, 22, {
                align: "center",
            });

            doc.setFontSize(9);

            doc.text(
                `Generated On : ${new Date().toLocaleDateString("en-GB")}`,
                105,
                27,
                { align: "center" }
            );

            doc.line(14, 32, 196, 32);


            doc.setFontSize(10);

            doc.text(
                `From Date : ${new Date(pdfFromDate).toLocaleDateString("en-GB")}`,
                14,
                42
            );

            doc.text(
                `To Date : ${new Date(pdfToDate).toLocaleDateString("en-GB")}`,
                120,
                42
            );

            autoTable(doc, {

                startY: 52,

                head: [[

                    "Sr",

                    "Date",

                    "Customer ID",

                    "Amount",

                    "Status"

                ]],

                body: orders.map((order, index) => [

                    index + 1,

                    new Date(order.created_at)
                        .toLocaleDateString("en-GB"),

                    order.customer_user_id || "-",

                    Number(order.total_amount)
                        .toLocaleString("en-IN", {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2,
                        }),

                    order.status

                ])

            });
            const totalAmount = orders.reduce(
                (sum, order) => sum + Number(order.total_amount),
                0
            );

            doc.setFont("helvetica", "bold");

            doc.text(
                `Total Orders : ${orders.length}`,
                14,
                doc.lastAutoTable.finalY + 15
            );

            doc.text(
                "Total Amount :",
                14,
                doc.lastAutoTable.finalY + 25
            );

            doc.text(
                "₹",
                52,
                doc.lastAutoTable.finalY + 25
            );

            doc.text(
                totalAmount.toLocaleString("en-IN", {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                }),
                58,
                doc.lastAutoTable.finalY + 25
            );

            doc.setFont("helvetica", "normal");
            doc.setFontSize(10);

            doc.text(
                "Thank you for shopping with FourStep.",
                105,
                doc.lastAutoTable.finalY + 40,
                {
                    align: "center",
                }
            );

            doc.text(
                "This is a computer-generated report.",
                105,
                doc.lastAutoTable.finalY + 46,
                {
                    align: "center",
                }
            );

            doc.save(
                `Branch_Order_Report_${pdfFromDate}_to_${pdfToDate}.pdf`
            );

            setShowPdfModal(false);

            setPdfFromDate("");
            setPdfToDate("");

        } catch (error) {

            console.error("Download PDF Error:", error);

        }

    };


    // PAGINATION
    const indexOfLastItem =
        currentPage * itemsPerPage;

    const indexOfFirstItem =
        indexOfLastItem - itemsPerPage;

    const currentOrders = Array.isArray(orderHistory)
        ? orderHistory.slice(
            indexOfFirstItem,
            indexOfLastItem
        )
        : [];

    const totalPages = Math.max(
        1,
        Math.ceil(
            orderHistory.length / itemsPerPage
        )
    );

    const getPageNumbers = (current, total) => {
        if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
        if (current <= 4) return [1, 2, 3, 4, 5, "ellipsis-end", total];
        if (current >= total - 3) return [1, "ellipsis-start", total - 4, total - 3, total - 2, total - 1, total];
        return [1, "ellipsis-start", current - 1, current, current + 1, "ellipsis-end", total];
    };
    console.log("ORDER HISTORY:", orderHistory);

    console.log("CURRENT ORDERS:", currentOrders);
    return (

        <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">

            <div className="flex-1 min-w-0 flex flex-col">

                <div className="text-center mt-4 md:mt-6">

                    <h1 className="text-2xl md:text-3xl font-bold text-[#B0422E]">
                        Branch Order History
                    </h1>

                </div>

                <div className="p-3 md:p-6">

                    <div className="bg-white rounded-2xl shadow-sm p-3 md:p-6 overflow-x-auto">

                        {loading ? (

                            <p className="text-center py-4 text-sm md:text-base">
                                Loading...
                            </p>

                        ) : (

                            <>
                                <div className="flex flex-wrap justify-between items-end gap-4 mb-5">

                                    <div className="flex flex-wrap items-end gap-4">

                                        <div className="flex flex-col">
                                            <label className="block text-sm font-medium mb-1">
                                                From Date
                                            </label>

                                            <input
                                                type="date"
                                                value={fromDate}
                                                onChange={(e) => setFromDate(e.target.value)}
                                                className="border rounded-lg px-3 py-2"
                                            />
                                        </div>

                                        <div className="flex flex-col">
                                            <label className="block text-sm font-medium mb-1">
                                                To Date
                                            </label>

                                            <input
                                                type="date"
                                                value={toDate}
                                                onChange={(e) => setToDate(e.target.value)}
                                                className="border rounded-lg px-3 py-2"
                                            />
                                        </div>

                                        <button
                                            onClick={fetchOrderHistory}
                                            className="bg-[#B0422E] text-white px-6 py-2.5 rounded-lg"
                                        >
                                            Search
                                        </button>

                                    </div>

                                    <button
                                        onClick={() => setShowPdfModal(true)}
                                        className="bg-red-600 text-white px-5 py-2 rounded-lg"
                                    >
                                        Download PDF
                                    </button>

                                </div>
                                <table className="w-full min-w-[1100px] text-xs md:text-sm">

                                    <thead>

                                        <tr className="bg-[#B0422E] text-white">

                                            <th className="py-3 px-3 rounded-l-xl">
                                                Sr
                                            </th>

                                            <th className="py-3 px-3 whitespace-nowrap">
                                                Date
                                            </th>

                                            <th className="py-3 px-3 whitespace-nowrap">
                                                Customer ID
                                            </th>



                                            <th className="py-3 px-3 whitespace-nowrap">
                                                Amount
                                            </th>

                                            <th className="py-3 px-3 whitespace-nowrap">
                                                Status
                                            </th>

                                            <th className="py-3 px-3 rounded-r-xl whitespace-nowrap">
                                                Details
                                            </th>

                                        </tr>

                                    </thead>

                                    <tbody className="font-medium">

                                        {currentOrders.length > 0 ? (

                                            currentOrders.map((order, index) => (

                                                <tr
                                                    className="border-b border-gray-300"
                                                    key={order.id}
                                                >

                                                    <td className="py-4 px-3 text-center ">
                                                        {indexOfFirstItem + index + 1}
                                                    </td>


                                                    <td className="py-4 px-3 whitespace-nowrap text-center">
                                                        {new Date(order.created_at)
                                                            .toLocaleDateString("en-GB")}
                                                    </td>

                                                    <td className="py-4 px-3 whitespace-nowrap text-center">
                                                        {order.customer_user_id || "-"}
                                                    </td>



                                                    <td className="py-4 px-3 whitespace-nowrap text-center">
                                                        ₹{order.total_amount}
                                                    </td>
                                                    <td className="py-4 px-3 whitespace-nowrap text-center">

                                                        <span
                                                            className={`px-3 py-1 rounded-full text-xs font-semibold ${order.status === "delivered" ||
                                                                order.status === "Delivered"
                                                                ? "bg-green-100 text-green-700"
                                                                : order.status === "pending" ||
                                                                    order.status === "Pending"
                                                                    ? "bg-yellow-100 text-yellow-700"
                                                                    : "bg-blue-100 text-blue-700"
                                                                }`}
                                                        >
                                                            {order.status}
                                                        </span>

                                                    </td>
                                                    <td className="py-4 px-3 text-center">

                                                        <div className="flex justify-center items-center gap-3">

                                                            <button
                                                                className="text-blue-600 hover:underline"
                                                                onClick={async () => {
                                                                    setSelectedOrder(null);
                                                                    await handleViewDetails(order.id);
                                                                }}
                                                            >
                                                                View
                                                            </button>

                                                            <button
                                                                className="text-green-600 hover:underline"
                                                                onClick={() => handlePrint(order.id)}
                                                            >
                                                                Print
                                                            </button>

                                                        </div>

                                                    </td>

                                                </tr>

                                            ))

                                        ) : (

                                            <tr>

                                                <td
                                                    colSpan="7"
                                                    className="py-4 px-4 text-gray-500 text-center"
                                                >
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
                                                <span
                                                    key={page + i}
                                                    className="w-9 h-9 flex items-center justify-center text-gray-400 text-sm select-none"
                                                >
                                                    …
                                                </span>
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

            {/* MODAL */}
            {showModal && selectedOrder && (

                <div className="fixed inset-0 bg-black/50 backdrop-blur-md flex justify-center items-center z-50 p-4">

                    <div
                        className="bg-white w-full max-w-5xl rounded-2xl p-4 md:p-6 shadow-lg overflow-y-auto max-h-[90vh]"
                    >

                        <div className="flex justify-between items-center mb-4">

                            <h2 className="text-xl md:text-2xl font-bold text-[#B0422E]">
                                Order Details
                            </h2>

                            <button
                                className="text-red-600 font-bold text-2xl"
                                onClick={() => setShowModal(false)}
                            >
                                ×
                            </button>

                        </div>

                        <div className="mb-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <p>
                                <strong>Order ID:</strong>
                                #{selectedOrder.order.id}
                            </p>

                            <p>
                                <strong>Date:</strong>
                                {new Date(
                                    selectedOrder.order.created_at
                                ).toLocaleDateString("en-GB")}
                            </p>

                            <p>
                                <strong>Customer ID:</strong>
                                {selectedOrder.order.customer_user_id || "-"}
                            </p>

                            <p>
                                <strong>Amount:</strong>
                                ₹{selectedOrder.order.total_amount}
                            </p>

                        </div>

                        <div className="overflow-x-auto">

                            <table className="w-full min-w-[900px] text-xs md:text-sm border">

                                <thead>

                                    <tr className="bg-[#B0422E] text-white">

                                        <th className="py-3 px-3">
                                            Sr
                                        </th>

                                        <th className="py-3 px-3">
                                            Product
                                        </th>

                                        <th className="py-3 px-3">
                                            Package
                                        </th>

                                        <th className="py-3 px-3">
                                            Qty
                                        </th>

                                        <th className="py-3 px-3">
                                            MRP
                                        </th>

                                        <th className="py-3 px-3">
                                            PV
                                        </th>

                                        <th className="py-3 px-3">
                                            BV
                                        </th>

                                        <th className="py-3 px-3">
                                            Amount
                                        </th>

                                    </tr>

                                </thead>

                                <tbody>

                                    {selectedOrder.products &&
                                        selectedOrder.products.length > 0 ? (

                                        selectedOrder.products.map((item, index) => (

                                            <tr
                                                key={index}
                                                className="border-b"
                                            >

                                                <td className="py-3 px-3 text-center">
                                                    {index + 1}
                                                </td>

                                                <td className="py-3 px-3">
                                                    {item.product_name || "Product"}
                                                </td>

                                                <td className="py-3 px-3 text-center">
                                                    {item.packing_size || "-"}
                                                </td>

                                                <td className="py-3 px-3 text-center">
                                                    {item.quantity}
                                                </td>

                                                <td className="py-3 px-3 text-center">
                                                    ₹{item.price || 0}
                                                </td>

                                                <td className="py-3 px-3 text-center">
                                                    {item.pv || 0}
                                                </td>

                                                <td className="py-3 px-3 text-center">
                                                    {item.bv || 0}
                                                </td>

                                                <td className="py-3 px-3 text-center">
                                                    ₹{item.amount || 0}
                                                </td>

                                            </tr>

                                        ))

                                    ) : (

                                        <tr>

                                            <td
                                                colSpan="8"
                                                className="py-3 text-center"
                                            >
                                                No products found
                                            </td>

                                        </tr>

                                    )}

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            )}

            {showPdfModal && (

                <div className="fixed inset-0 bg-black/50 backdrop-blur-sm flex justify-center items-center z-50">

                    <div className="bg-white rounded-xl shadow-xl w-[420px] p-6">

                        <h2 className="text-xl font-bold text-[#B0422E] mb-5">
                            Download Order Report
                        </h2>

                        <div className="space-y-4">

                            <div>

                                <label className="block text-sm font-medium mb-1">
                                    From Date
                                </label>
                                <input
                                    type="date"
                                    value={pdfFromDate}
                                    onChange={(e) => setPdfFromDate(e.target.value)}
                                    className="border rounded-lg w-full px-3 py-2"
                                />

                            </div>

                            <div>

                                <label className="block text-sm font-medium mb-1">
                                    To Date
                                </label>

                                <input
                                    type="date"
                                    value={pdfToDate}
                                    onChange={(e) => setPdfToDate(e.target.value)}
                                    className="border rounded-lg w-full px-3 py-2"
                                />

                            </div>

                        </div>

                        <div className="flex justify-end gap-3 mt-6">

                            <button
                                onClick={() => {

                                    setPdfFromDate("");

                                    setPdfToDate("");

                                    setShowPdfModal(false);

                                }}
                                className="px-4 py-2 rounded-lg border"
                            >
                                Cancel
                            </button>

                            <button
                                onClick={handleDownloadPdf}
                                className="bg-[#B0422E] text-white px-5 py-2 rounded-lg"
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

export default BranchOrderHistory;