import Sidebar from "../components/sidebar";
import Navbar from "../components/navbar";
import React, { useEffect, useState } from "react";
import { shoppeeApi as api } from "../api/axios";

function Outbox() {
  const [messages, setMessages] = useState([]);
  const [selectedMessage, setSelectedMessage] = useState(null);
  const [showModal, setShowModal] = useState(false);
  const [currentPage, setCurrentPage] = useState(1);
  const itemsPerPage = 10;

  const user = JSON.parse(localStorage.getItem("user"));
  const userId = user?.id;

  useEffect(() => {
    const fetchMessages = async () => {
      try {
        const res = await api.get(`/helpdesk/${userId}`);
        setMessages(res.data);
      } catch (error) {
        console.error("Error fetching messages", error);
      }
    };

    if (userId) {
      fetchMessages();
    }
  }, [userId]);

  const totalPages = Math.ceil(messages.length / itemsPerPage);
  const currentPageSafe = Math.min(currentPage, totalPages || 1);
  const indexOfLastMessage = currentPageSafe * itemsPerPage;
  const indexOfFirstMessage = indexOfLastMessage - itemsPerPage;
  const currentMessages = messages.slice(
    indexOfFirstMessage,
    indexOfLastMessage
  );

  return (
    <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">
      <div className="flex-1 min-w-0 flex flex-col overflow-x-hidden">
        <div className="p-3 md:p-4 lg:p-6 bg-gray-100">
          <h1 className="text-2xl md:text-3xl font-bold text-[#B0422E] text-center mb-4 md:mb-8">
            Outbox
          </h1>

          <div className="bg-white rounded-2xl shadow p-3 md:p-6">
            <div className="overflow-x-auto">
              <table className="w-full min-w-max text-xs md:text-sm text-center">
                <thead>
                  <tr className="bg-[#B0422E] text-white">
                    <th className="p-2 md:p-3 rounded-l-xl">Sr</th>
                    <th className="p-2 md:p-3 whitespace-nowrap">From</th>
                    <th className="p-2 md:p-3 whitespace-nowrap">Subject</th>
                    <th className="p-2 md:p-3 whitespace-nowrap">Date</th>
                    <th className="p-2 md:p-3 whitespace-nowrap">Status</th>
                    <th className="p-2 md:p-3 rounded-r-xl whitespace-nowrap">
                      Action
                    </th>
                  </tr>
                </thead>

                <tbody className="font-medium">
                  {messages.length > 0 ? (
                    currentMessages.map((msg, index) => (
                      <tr
                        key={msg.id}
                        className="border-b border-gray-300 hover:bg-gray-50"
                      >
                        <td className="p-2 md:p-4">{index + 1}</td>
                        <td className="p-2 md:p-4">You</td>
                        <td className="p-2 md:p-4 truncate max-w-xs">
                          {msg.subject}
                        </td>

                        <td className="p-2 md:p-4 whitespace-nowrap">
                          {new Date(msg.created_at).toLocaleDateString()}
                        </td>

                        <td
                          className={`p-2 md:p-4 whitespace-nowrap font-semibold ${
                            msg.admin_reply
                              ? "text-green-600"
                              : "text-orange-500"
                          }`}
                        >
                          {msg.admin_reply ? "Replied" : "Pending"}
                        </td>

                        <td
                          onClick={() => {
                            setSelectedMessage(msg);
                            setShowModal(true);
                          }}
                          className="p-2 md:p-4 text-[#256BB1] font-semibold cursor-pointer hover:underline text-xs md:text-sm"
                        >
                          View
                        </td>
                      </tr>
                    ))
                  ) : (
                    <tr>
                      <td colSpan="6" className="p-4 md:p-6 text-gray-500 text-center">
                        No messages found
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>

              {totalPages > 1 && (
                <div className="mt-4 flex flex-wrap items-center justify-center gap-2">
                  <button
                    type="button"
                    onClick={() => setCurrentPage((prev) => Math.max(prev - 1, 1))}
                    disabled={currentPage === 1}
                    className="rounded-full border border-gray-300 bg-white px-3 py-1 text-sm font-medium disabled:cursor-not-allowed disabled:opacity-50"
                  >
                    Prev
                  </button>

                  {[...Array(totalPages)].map((_, idx) => (
                    <button
                      key={idx}
                      type="button"
                      onClick={() => setCurrentPage(idx + 1)}
                      className={`rounded-full border px-3 py-1 text-sm font-medium ${
                        currentPageSafe === idx + 1
                          ? "border-[#B0422E] bg-[#B0422E] text-white"
                          : "border-gray-300 bg-white text-gray-700"
                      }`}
                    >
                      {idx + 1}
                    </button>
                  ))}

                  <button
                    type="button"
                    onClick={() => setCurrentPage((prev) => Math.min(prev + 1, totalPages))}
                    disabled={currentPageSafe === totalPages}
                    className="rounded-full border border-gray-300 bg-white px-3 py-1 text-sm font-medium disabled:cursor-not-allowed disabled:opacity-50"
                  >
                    Next
                  </button>
                </div>
              )}
            </div>
          </div>
        </div>
      </div>

      {/* Modal for message details */}
      {showModal && selectedMessage && (
        <div className="fixed inset-0 flex items-center justify-center bg-black/50 backdrop-blur-md z-50 p-4">
          <div className="bg-white rounded-xl p-4 md:p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <h2 className="text-lg md:text-xl font-bold mb-4 text-[#B0422E]">
              Ticket Details
            </h2>

            <p className="mb-2 text-sm md:text-base">
              <strong>Subject:</strong> {selectedMessage.subject}
            </p>

            <p className="mb-2 text-sm md:text-base wrap-break-word">
              <strong>Your Message:</strong> {selectedMessage.message}
            </p>

            <p className="mb-4 text-sm md:text-base wrap-break-word">
              <strong>Admin Reply:</strong>{" "}
              {selectedMessage.admin_reply || "No reply yet"}
            </p>

            <button
              onClick={() => setShowModal(false)}
              className="bg-[#B0422E] text-white px-4 py-2 rounded text-sm md:text-base hover:bg-[#933d2e] transition"
            >
              Close
            </button>
          </div>
        </div>
      )}
    </div>
  );
}

export default Outbox;