import React, { useEffect, useState, useRef } from "react";
import { useParams, useSearchParams } from "react-router-dom";
import { shoppeeApi as api } from "../api/axios";
import { useReactToPrint } from "react-to-print";

// npm install react-to-print

function InvoicePage() {
  const { id } = useParams();
  const [searchParams] = useSearchParams();
  const autoPrint = searchParams.get("print") === "true";

  const [order, setOrder] = useState(null);
  const invoiceRef = useRef(null);
  const hasPrinted = useRef(false); // prevent double-trigger

  useEffect(() => { fetchOrder(); }, [id]);

  const fetchOrder = async () => {
    try {
      const response = await api.get(`/order-details/${id}`);
      setOrder(response.data);
    } catch (error) {
      console.error("Invoice Error:", error);
    }
  };

  const handlePrint = useReactToPrint({
    contentRef: invoiceRef,
    documentTitle: order ? `Invoice-${order.order_no}` : "Invoice",
    pageStyle: `
      @page { size: A4; margin: 10mm; }
      @media print {
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        body { background: #fff !important; }
      }
    `,
  });

  // Auto-trigger print once order is loaded and autoPrint flag is set
  useEffect(() => {
    if (autoPrint && order && invoiceRef.current && !hasPrinted.current) {
      hasPrinted.current = true;
      // Small delay so the DOM is fully painted before printing
      setTimeout(() => { handlePrint(); }, 500);
    }
  }, [autoPrint, order, handlePrint]);

  if (!order) {
    return (
      <div className="min-h-screen bg-gray-100 flex items-center justify-center">
        <div className="flex flex-col items-center gap-3">
          <div className="w-10 h-10 border-4 border-[#AE4329] border-t-transparent rounded-full animate-spin" />
          <p className="text-gray-500 text-sm font-medium">Loading invoice...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-200 py-8 px-4">

      {/* Print button — outside the ref so it won't appear in print */}
      <div className="max-w-3xl mx-auto flex justify-end mb-4">
        <button
          onClick={handlePrint}
          className="flex items-center gap-2 bg-[#AE4329] hover:bg-[#8f3621] text-white text-sm font-bold px-5 py-2.5 rounded-lg shadow transition-colors"
        >
          <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
            <polyline points="6 9 6 2 18 2 18 9" />
            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
            <rect x="6" y="14" width="12" height="8" />
          </svg>
          Print / Save PDF
        </button>
      </div>

      {/* ── INVOICE CARD — only this div is printed ── */}
      <div
        ref={invoiceRef}
        className="max-w-3xl mx-auto bg-white rounded-2xl shadow-xl overflow-hidden"
      >
        {/* HEADER */}
        <div className="bg-[#12376D] text-white px-8 py-6">
          <div className="flex items-start justify-between">
            <div className="flex items-center gap-4">
              <div className="bg-white rounded-xl p-1.5 flex-shrink-0">
                <img
                  src="/images/fourstep_logo.png"
                  alt="Four Step Retail"
                  className="h-14 w-14 object-contain"
                />
              </div>
              <div>
                <h1 className="text-2xl font-extrabold tracking-wide leading-tight">
                  FOUR STEP RETAIL
                </h1>
                <p className="text-blue-200 text-xs font-semibold uppercase tracking-widest mt-0.5">
                  Branch Pickup Invoice
                </p>
              </div>
            </div>
            <div className="text-right">
              <p className="text-blue-200 text-xs uppercase tracking-widest">Date</p>
              <p className="text-white text-sm font-semibold mt-0.5">
                {new Date(order.order_date).toLocaleDateString("en-IN", {
                  day: "2-digit", month: "long", year: "numeric",
                })}
              </p>
            </div>
          </div>
          <div className="mt-5 pt-4 border-t border-white/20 grid grid-cols-2 gap-x-8 text-sm text-blue-100">
            <p className="font-bold text-white">{order.shoppee_name}</p>
            <p>{order.shoppee_email}</p>
            <p>{order.shoppee_address}</p>
            <p>{order.shoppee_mobile}</p>
            <p>{order.shoppee_city}, {order.shoppee_state} – {order.shoppee_pincode}</p>
          </div>
        </div>

        {/* BODY */}
        <div className="px-8 py-7">
          {/* 4-col details strip */}
          <div className="border border-gray-200 rounded-xl overflow-hidden mb-7">
            <div className="grid grid-cols-4 divide-x divide-gray-200">
              <DetailCol label="Invoice No.">
                <span className="font-bold text-[#12376D] text-sm">{order.order_no}</span>
              </DetailCol>
              <DetailCol label="Date & Status">
                <span className="font-semibold text-gray-800 text-sm">
                  {new Date(order.order_date).toLocaleDateString("en-IN", {
                    day: "2-digit", month: "short", year: "numeric",
                  })}
                </span>
                <span className="inline-block mt-1.5 bg-green-100 text-green-700 text-xs font-bold px-2.5 py-0.5 rounded-full uppercase tracking-wide">
                  {order.status}
                </span>
              </DetailCol>
              <DetailCol label="Order Type">
                <span className="font-semibold text-gray-800 text-sm">{order.order_type}</span>
              </DetailCol>
              <DetailCol label="Member">
                <span className="font-bold text-gray-800 text-sm">{order.customer_name}</span>
                <span className="text-gray-400 text-xs mt-0.5">ID: {order.user_id}</span>
              </DetailCol>
            </div>
          </div>

          {/* Product table */}
          <div className="rounded-xl border border-gray-200 overflow-hidden mb-7">
            <table className="w-full text-sm">
              <thead>
                <tr className="bg-[#12376D] text-white">
                  {[
                    { h: "#",            cls: "text-center w-10" },
                    { h: "Product Name", cls: "text-left" },
                    { h: "Pack Size",    cls: "text-center" },
                    { h: "Qty",          cls: "text-center w-14" },
                    { h: "MRP",          cls: "text-center w-24" },
                    { h: "PV",           cls: "text-center w-14" },
                    { h: "Amount",       cls: "text-right w-28" },
                  ].map(({ h, cls }) => (
                    <th key={h} className={`px-4 py-3 text-xs font-bold uppercase tracking-wide ${cls}`}>
                      {h}
                    </th>
                  ))}
                </tr>
              </thead>
              <tbody>
                {order.products?.map((item, index) => (
                  <tr key={index} className={index % 2 === 0 ? "bg-white" : "bg-gray-50"}>
                    <td className="px-4 py-3 text-center text-gray-400">{index + 1}</td>
                    <td className="px-4 py-3 font-semibold text-gray-800">{item.product_name}</td>
                    <td className="px-4 py-3 text-center text-gray-600">{item.package_size}</td>
                    <td className="px-4 py-3 text-center text-gray-600">{item.quantity}</td>
                    <td className="px-4 py-3 text-center text-gray-600">₹{Number(item.mrp).toFixed(2)}</td>
                    <td className="px-4 py-3 text-center text-gray-600">{item.pv}</td>
                    <td className="px-4 py-3 text-right font-bold text-gray-800">₹{Number(item.amount).toFixed(2)}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          {/* Summary */}
          <div className="flex justify-end mb-8">
            <div className="w-72 border border-gray-200 rounded-xl overflow-hidden text-sm">
              <SummaryRow label="Total Products" value={order.total_products} />
              <SummaryRow label="Total Quantity"  value={order.total_quantity} />
              <SummaryRow label="Total PV"        value={order.total_pv} />
              <div className="flex justify-between items-center px-5 py-4 bg-[#AE4329] text-white">
                <span className="font-bold text-base">Grand Total</span>
                <span className="font-extrabold text-xl">₹{Number(order.total_amount).toFixed(2)}</span>
              </div>
            </div>
          </div>

          {/* Footer */}
          <div className="pt-5 border-t border-dashed border-gray-300 text-center">
            <p className="text-gray-700 font-semibold text-sm">
              Thank you for choosing Four Step Retail — {order.shoppee_name}
            </p>
            <p className="text-gray-400 text-xs mt-1">
              This is a computer-generated invoice and does not require a signature.
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}

function DetailCol({ label, children }) {
  return (
    <div className="px-5 py-4 flex flex-col gap-0.5">
      <p className="text-xs font-bold text-[#AE4329] uppercase tracking-widest mb-1">{label}</p>
      {children}
    </div>
  );
}

function SummaryRow({ label, value }) {
  return (
    <div className="flex justify-between items-center px-5 py-3 border-b border-gray-100">
      <span className="text-gray-500">{label}</span>
      <span className="font-semibold text-gray-800">{value}</span>
    </div>
  );
}

export default InvoicePage;