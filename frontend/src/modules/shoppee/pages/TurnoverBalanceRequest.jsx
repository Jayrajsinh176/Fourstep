import Sidebar from "../components/sidebar";
import Navbar from "../components/navbar";
import { Building2, IndianRupee, CreditCard, Upload } from "lucide-react";
import { useState } from "react";
import { shoppeeApi as api } from "../api/axios";
import { ToastContainer, useToast } from "../components/Toast";

function TurnoverBalance() {
  const [amount, setAmount] = useState("");
  const mode = "By Online Payment";
  const [transactionNo, setTransactionNo] = useState("");
  const [file, setFile] = useState(null);
  const [filePreview, setFilePreview] = useState(null);
  const [loading, setLoading] = useState(false);
  const { toasts, showToast, removeToast } = useToast();

  const handleSubmit = async (Turnover) => {

    if (amount < 500) {
      showToast("warning", "Minimum amount ₹500 required");
      return;
    }

    if (!transactionNo.trim()) {
      showToast("warning", "Transaction Number is required");
      return;
    }

    if (!file) {
      showToast("warning", "Please upload payment slip");
      return;
    }

    const storedUser = JSON.parse(localStorage.getItem("user"));

    const memberId = storedUser?.id;

    const formData = new FormData();

    formData.append("member_id", memberId);
    formData.append("type", Turnover);
    formData.append("amount", amount);
    formData.append("mode_of_payment", mode);
    formData.append("transaction_no", transactionNo);

    formData.append("payment_slip", file);

    try {
      setLoading(true);

      await api.post(
        "/balance-request",
        formData,
        {
          headers: {
            "Content-Type": "multipart/form-data",
          },
        }
      );

      showToast("success", "Request Submitted Successfully ✅");

  setAmount("");
setTransactionNo("");
setFile(null);
setFilePreview(null);

    } catch (error) {

      console.log(error);

      if (error.response) {
        console.log(error.response.data);

        showToast(
          "error",
          error.response.data.message ||
          JSON.stringify(error.response.data)
        );
      } else {
        showToast("error", "Network Error");
      }

    } finally {
      setLoading(false);
    }

    setTimeout(() => {
      window.location.reload();
    }, 1200);
  };
  return (
    <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">


      <div className="flex-1 min-w-0 flex flex-col">


        <div className="text-center mt-6">
          <h1 className="text-3xl font-bold text-[#B0422E]">
            Turnover Balance Request
          </h1>
        </div>

        <div className="p-6 space-y-6">

          <div className="bg-[#B0422E] rounded-2xl p-8 text-white shadow-md">

            <div className="flex items-center gap-3 mb-6">
              <Building2 size={24} />
              <h2 className="text-xl font-semibold">
                ICICI BANK - Transfer Details
              </h2>
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

            <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">

              <div>
                <label className="text-sm font-medium flex items-center gap-2 mb-2">
                  <IndianRupee size={16} />
                  Enter Amount*
                </label>
                <input
                  type="text"
                  value={amount}
                  onChange={(e) => setAmount(e.target.value)}
                  placeholder="Min ₹500"
                  className="w-full border  border-gray-400 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#B0422E]"
                />
              </div>

              <div>
                <label className="text-sm font-medium flex items-center gap-2 mb-2">
                  <CreditCard size={16} />
                  Mode of Payment*
                </label>

                <input
                  type="text"
                  value="By Online Payment"
                  disabled
                  className="w-full border border-gray-400 rounded-lg px-4 py-2 bg-gray-100 text-gray-600"
                />
              </div>

              <div>
                <label className="text-sm font-medium mb-2 block">
                  Transaction No.
                </label>
                <input
                  type="text"
                  value={transactionNo}
                  onChange={(e) => setTransactionNo(e.target.value)}
                  placeholder="Ex:AXIS12345678"
                  className="w-full border  border-gray-400 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#B0422E]"
                />
              </div>

              <div>
                <label className="text-sm font-medium flex items-center gap-2 mb-2">
                  <Upload size={16} />
                  Payment Slip
                </label>
                <input
                  type="file"
                  accept="image/*"
                  onChange={(e) => {
                    const selectedFile = e.target.files[0];

                    if (selectedFile) {
                      setFile(selectedFile);
                      setFilePreview(URL.createObjectURL(selectedFile));
                    }
                  }}
                  className="w-full border border-gray-400 text-gray-600 rounded-lg px-3 py-2"
                />{filePreview && (
                  <div className="mt-3 border rounded-lg p-3">
                    <img
                      src={filePreview}
                      alt="Payment Slip"
                      className="w-40 rounded-lg border"
                    />

                    <div className="mt-2 flex justify-between items-center">
                      <span className="text-sm break-all">
                        {file?.name}
                      </span>

                      <button
                        type="button"
                        onClick={() => {
                          setFile(null);
                          setFilePreview(null);
                        }}
                        className="text-red-600 text-sm"
                      >
                        Remove
                      </button>
                    </div>
                  </div>
                )}
              </div>

            </div>

            <hr className=" border-gray-400" />

            <div className="flex flex-col sm:flex-row justify-between sm:items-center gap-4">
              <p className="text-[#0000005C] text-medium">
             * Minimum Amount ₹500 allowed for Turnover Balance request
              </p>

              <button
                onClick={() => handleSubmit("turnover")}
                disabled={loading}
                className="bg-[#B0422E] text-white px-6 py-2 rounded-lg hover:bg-[#963826] transition">
                Submit Request
              </button>
            </div>

          </div>

        </div>
      </div>
      <ToastContainer toasts={toasts} removeToast={removeToast} />
    </div>
  );
}
export default TurnoverBalance;