import { useState } from "react";
import { Send } from "lucide-react";
import Sidebar from "../components/Sidebar";
import Navbar from "../components/Navbar";

const API_BASE_URL =
  import.meta.env.VITE_API_BASE_URL || "https://fourstepretail.com/api";

export default function ComposeMessage() {
  const [form, setForm] = useState({
    send_to: "",
    subject: "",
    message_details: "",
  });
  const [isSending, setIsSending] = useState(false);
  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");

  const handleChange = (event) => {
    const { name, value } = event.target;
    setForm((prev) => ({ ...prev, [name]: value }));
  };

  const handleSubmit = async (event) => {
    event.preventDefault();
    setError("");
    setSuccess("");

    if (
      !form.send_to.trim() ||
      !form.subject.trim() ||
      !form.message_details.trim()
    ) {
      setError("Please fill all required fields.");
      return;
    }

    let memberData = {};
    try {
      memberData = JSON.parse(localStorage.getItem("memberData") || "{}") || {};
    } catch {
      memberData = {};
    }

    const senderUserId = memberData?.user_id || "";

    if (!senderUserId) {
      setError("Please sign in first.");
      return;
    }

    try {
      setIsSending(true);

      const response = await fetch(`${API_BASE_URL}/messages/compose`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
          "X-Auth-Member": senderUserId,
        },
        body: JSON.stringify({
          send_to: form.send_to.trim(),
          subject: form.subject.trim(),
          message_details: form.message_details.trim(),
          sender_user_id: senderUserId,
        }),
      });

      const data = await response.json();

      if (!response.ok) {
        setError(data?.message || "Unable to send message.");
        return;
      }

      setSuccess(data?.message || "Message sent successfully.");
      setForm({
        send_to: "",
        subject: "",
        message_details: "",
      });
    } catch {
      setError("Unable to connect to backend.");
    } finally {
      setIsSending(false);
    }
  };

  return (
    <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">
      <Sidebar />

      <div className="flex-1 min-w-0 flex flex-col">
        <Navbar />

        <div className="p-4 sm:p-6">
          <h1 className="text-3xl font-bold text-center text-[#B0422E] mb-6 sm:mb-8">
            Compose a Message
          </h1>
          {error && (
            <p className="text-sm text-red-500 text-center mb-4">{error}</p>
          )}
          {success && (
            <p className="text-sm text-green-600 text-center mb-4">{success}</p>
          )}

          <form
            onSubmit={handleSubmit}
            className="bg-white rounded-2xl shadow-sm  p-5 sm:p-8"
          >
            <div className="space-y-5">
              <div className="grid grid-cols-1 md:grid-cols-12 gap-3 md:gap-4 items-center">
                <label className="md:col-span-2 font-semibold text-[#000000]">
                  Send To<span className="text-red-500">*</span>
                </label>
                <input
                  type="text"
                  name="send_to"
                  value={form.send_to}
                  onChange={handleChange}
                  className="md:col-span-10 h-11 w-full bg-gray-100 rounded-xl px-4 outline-none"
                />
              </div>

              <div className="grid grid-cols-1 md:grid-cols-12 gap-3 md:gap-4 items-center">
                <label className="md:col-span-2 font-semibold text-[#000000]">
                  Subject<span className="text-red-500">*</span>
                </label>
                <input
                  type="text"
                  name="subject"
                  value={form.subject}
                  onChange={handleChange}
                  className="md:col-span-10 h-11 w-full bg-gray-100 rounded-xl px-4 outline-none"
                />
              </div>

              <div className="grid grid-cols-1 md:grid-cols-12 gap-3 md:gap-4 items-start">
                <label className="md:col-span-2 font-semibold pt-2 text-[#000000]">
                  Message Details<span className="text-red-500">*</span>
                </label>

                <div className="md:col-span-10">
                  <div className="h-11 bg-gray-100 rounded-xl px-4 flex items-center gap-3 sm:gap-5 text-gray-700 border-b border-gray-300 overflow-x-auto whitespace-nowrap">
                    <span className="text-sm">A Normal text</span>
                    <span className="font-semibold">Black</span>
                    <span className="text-xl leading-none">|</span>
                    <span className="font-semibold underline">U</span>
                    <span className="font-semibold italic">I</span>
                    <span className="font-bold text-lg">B</span>
                    <span className="text-xl leading-none">|</span>
                    <span>≡</span>
                    <span>≡</span>
                    <span>≡</span>
                    <span className="text-xl leading-none">|</span>
                    <span>🔗</span>
                    <span>🖼</span>
                  </div>

                  <textarea
                    rows="12"
                    name="message_details"
                    value={form.message_details}
                    onChange={handleChange}
                    className="w-full bg-gray-100 rounded-xl mt-4 p-4 outline-none resize-none"
                  />
                </div>
              </div>
            </div>

            <div className="flex justify-center mt-6">
              <button
                type="submit"
                disabled={isSending}
                className="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white px-7 sm:px-10 py-2.5 rounded-xl font-semibold"
              >
                <Send size={16} />
                {isSending ? "Sending..." : "Send Message"}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}
