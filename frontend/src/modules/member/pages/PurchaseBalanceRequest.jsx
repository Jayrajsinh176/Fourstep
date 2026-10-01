import Sidebar from "../components/Sidebar";
import Navbar from "../components/Navbar";
import { Building2, IndianRupee, CreditCard, Upload } from "lucide-react";
import { useState } from "react";
import { requestMemberApi } from "../utils/apiClient";

export default function PurchaseBalanceRequest() {
  const [amount, setAmount] = useState("");
  const [mode, setMode] = useState("By Online Payment");
  const [transactionNo, setTransactionNo] = useState("");
  const [file, setFile] = useState(null);
  const [loading, setLoading] = useState(false);

  const member = JSON.parse(localStorage.getItem("memberData"));
  const memberId = member?.user_id;

  const handleSubmit = async () => {
    if (!amount || Number(amount) < 500) {
      alert("Minimum amount ₹500 required");
      return;
    }

    const formData = new FormData();
    formData.append("type", "purchase");
    formData.append("member_id", memberId);
    formData.append("amount", amount);
    formData.append("mode_of_payment", "By Online Payment");
    formData.append("transaction_no", transactionNo);
    if (file) formData.append("payment_slip", file);

    try {
      setLoading(true);
      const res = await requestMemberApi("/balance-request", {
        method: "POST",
        body: formData,
        // ← no Content-Type header — browser sets it automatically for FormData
      });

      if (res.ok) {
        alert("Request Submitted Successfully ✅");
        setAmount("");
        setTransactionNo("");
        setFile(null);
      } else {
        alert(res.data?.message || "Something went wrong ❌");
      }
    } catch (error) {
      console.error(error);
      alert("Something went wrong ❌");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">
      <Sidebar />
      <div className="flex-1 min-w-0 flex flex-col">
        <Navbar />

        <div className="text-center mt-6">
          <h1 className="text-3xl font-bold text-[#B0422E]">
            Purchase Balance Request
          </h1>
        </div>

        <div className="p-6 space-y-6">
          <div className="bg-[#B0422E] rounded-2xl p-8 text-white shadow-md">
            <div className="flex items-center gap-3 mb-6">
              <Building2 size={24} />
              <h2 className="text-xl font-semibold">ICICI BANK - Transfer Details</h2>
            </div>
           <div className="flex flex-col xl:flex-row gap-6">

              {/* Bank Details */}
              <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 flex-1">

                <div className="bg-white/20 rounded-xl p-4">
                  <p className="text-xs uppercase opacity-70">Account Name</p>
                  <h3 className="font-semibold mt-1">FOURSTEP RETAIL LIMITED</h3>
                </div>

                <div className="bg-white/20 rounded-xl p-4">
                  <p className="text-xs uppercase opacity-70">Account Type</p>
                  <h3 className="font-semibold mt-1">CURRENT</h3>
                </div>

                <div className="bg-white/20 rounded-xl p-4">
                  <p className="text-xs uppercase opacity-70">Account No.</p>
                  <h3 className="font-semibold mt-1">554905000020</h3>
                </div>

                <div className="bg-white/20 rounded-xl p-4">
                  <p className="text-xs uppercase opacity-70">IFSC Code</p>
                  <h3 className="font-semibold mt-1">ICIC0005549 / ICIC0000248</h3>
                </div>

                <div className="bg-white/20 rounded-xl p-4 sm:col-span-2 xl:col-span-4">
                  <p className="text-xs uppercase opacity-70">Branch</p>
                  <h3 className="font-semibold mt-1">KARELIBAG, VADODARA</h3>
                </div>

              </div>

              {/* QR Code */}
               <div className="bg-white rounded-xl p-4 flex flex-col items-center justify-center w-full xl:w-60 shrink-0">
                <img
                  src="/images/Qr_code.png"
                  alt="Payment QR"
                  className="w-40 h-40 sm:w-48 sm:h-48 object-contain"
                />
                <p className="text-black text-sm font-semibold mt-2 text-center">
                  Scan & Pay
                </p>
              </div>

            </div>
          </div>

          <div className="bg-white rounded-2xl shadow-sm p-8 space-y-6">
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-6">

              <div>
                <label className="text-sm font-medium flex items-center gap-2 mb-2">
                  <IndianRupee size={16} /> Enter Amount*
                </label>
                <input
                  type="number"
                  value={amount}
                  onChange={e => setAmount(e.target.value)}
                  placeholder="Min ₹500"
                  className="w-full border border-gray-400 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#B0422E]"
                />
              </div>

              {/* <div>
                <label className="text-sm font-medium flex items-center gap-2 mb-2">
                  <CreditCard size={16} /> Mode of Payment*
                </label>
                <select
                  value={mode}
                  onChange={e => setMode(e.target.value)}
                  className="w-full border border-gray-400 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#B0422E] text-gray-600"
                >
                  <option>By Online Payment</option>
                  <option>By Cheque Payment</option>
                  <option>By Cash Payment</option>
                </select>
              </div> */}

              <div>
                <label className="text-sm font-medium mb-2 block">
                  Transaction No.
                </label>
                <input
                  type="text"
                  value={transactionNo}
                  onChange={e => setTransactionNo(e.target.value)}
                  placeholder="Ex: AXIS12345678"
                  className="w-full border border-gray-400 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#B0422E]"
                />
              </div>

              <div>
                <label className="text-sm font-medium flex items-center gap-2 mb-2">
                  <Upload size={16} /> Payment Slip
                </label>
                <input
                  type="file"
                  onChange={e => setFile(e.target.files[0])}
                  className="w-full border border-gray-400 rounded-lg px-3 py-2 text-gray-600"
                />
              </div>

            </div>

            <hr className="border-gray-400" />

            <div className="flex flex-col sm:flex-row justify-between sm:items-center gap-4">
              <p className="text-[#0000005C]">
                * Minimum Amount ₹500 allowed for Purchase Balance request
              </p>
              <button
                onClick={handleSubmit}
                disabled={loading}
                className="bg-[#B0422E] text-white px-6 py-2 rounded-lg hover:bg-[#963826] transition disabled:opacity-50"
              >
                {loading ? "Submitting..." : "Submit Request"}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}