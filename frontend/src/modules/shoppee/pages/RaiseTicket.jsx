import { useState } from "react";
import { Send } from "lucide-react";
import Sidebar from "../components/sidebar";
import Navbar from "../components/navbar";
import { shoppeeApi as api } from "../api/axios";
import { ToastContainer, useToast } from "../components/Toast";

function RaiseTicket() {
  const [sendTo, setSendTo] = useState("");
  const [subject, setSubject] = useState("");
  const [message, setMessage] = useState("");
  const { toasts, showToast, removeToast } = useToast();

  const handleSubmit = async () => {
    try {
      const user = JSON.parse(localStorage.getItem("user"));

      if (!subject || !message) {
        showToast("warning", "Please fill all fields");
        return;
      }

      const res = await api.post("/helpdesk", {
        user_id: user.id,
        subject,
        message,
      });

      console.log(res.data);

      showToast("success", res.data.message);

      setSendTo("");
      setSubject("");
      setMessage("");
    } catch (error) {
      console.log(error.response?.data);

      showToast(
        "error",
        error.response?.data?.message || "Error sending message"
      );
    }
  };

  return (
    <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">
      <div className="flex-1 min-w-0 flex flex-col">
        <div className="p-3 md:p-4 lg:p-6">
          <h1 className="text-2xl md:text-3xl font-bold text-center text-[#B0422E] mb-4 md:mb-8">
            Compose a Message
          </h1>

          <div className="bg-white rounded-2xl shadow-sm p-4 md:p-6 lg:p-8">
            <div className="space-y-4 md:space-y-5">
              <div className="grid grid-cols-1 md:grid-cols-12 gap-2 md:gap-3 lg:gap-4 items-center">
                <label className="md:col-span-2 font-semibold text-xs md:text-sm text-[#000000]">
                  Send To<span className="text-red-500">*</span>
                </label>
                <input
                  type="text"
                  value={sendTo}
                  onChange={(e) => setSendTo(e.target.value)}
                  className="md:col-span-10 h-9 md:h-11 w-full bg-gray-100 rounded-xl px-3 md:px-4 outline-none text-sm"
                />
              </div>

              <div className="grid grid-cols-1 md:grid-cols-12 gap-2 md:gap-3 lg:gap-4 items-center">
                <label className="md:col-span-2 font-semibold text-xs md:text-sm text-[#000000]">
                  Subject<span className="text-red-500">*</span>
                </label>
                <input
                  type="text"
                  value={subject}
                  onChange={(e) => setSubject(e.target.value)}
                  className="md:col-span-10 h-9 md:h-11 w-full bg-gray-100 rounded-xl px-3 md:px-4 outline-none text-sm"
                />
              </div>

              <div className="grid grid-cols-1 md:grid-cols-12 gap-2 md:gap-3 lg:gap-4 items-start">
                <label className="md:col-span-2 font-semibold pt-2 text-xs md:text-sm text-[#000000]">
                  Message<span className="text-red-500">*</span>
                </label>

                <div className="md:col-span-10 w-full">
                  <div className="h-9 md:h-11 bg-gray-100 rounded-xl px-2 md:px-4 flex items-center gap-2 md:gap-3 text-gray-700 border-b border-gray-300 overflow-x-auto whitespace-nowrap text-xs md:text-sm">
                    <span>A Normal text</span>
                    <span className="font-semibold">Black</span>
                    <span>|</span>
                    <span className="font-semibold underline">U</span>
                    <span className="font-semibold italic">I</span>
                    <span className="font-bold">B</span>
                    <span>|</span>
                    <span>≡</span>
                    <span>≡</span>
                    <span>≡</span>
                    <span>|</span>
                    <span>🔗</span>
                    <span>🖼</span>
                  </div>

                  <textarea
                    rows="10"
                    value={message}
                    onChange={(e) => setMessage(e.target.value)}
                    className="w-full bg-gray-100 rounded-xl mt-3 md:mt-4 p-3 md:p-4 outline-none resize-none text-sm"
                  />
                </div>
              </div>
            </div>

            <div className="flex justify-center mt-4 md:mt-6">
              <button
                onClick={handleSubmit}
                className="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 md:px-8 py-2 md:py-2.5 rounded-xl font-semibold text-sm md:text-base transition"
              >
                <Send size={16} />
                Send Message
              </button>
            </div>
          </div>
        </div>
      </div>
      <ToastContainer toasts={toasts} removeToast={removeToast} />
    </div>
  );
}

export default RaiseTicket;