import { useEffect, useMemo, useState } from "react";
import Sidebar from "../components/Sidebar";
import Navbar from "../components/Navbar";
import { requestMemberApi } from "../utils/apiClient";

const formatDate = (dateValue) => {
  if (!dateValue) return "-";
  const date = new Date(dateValue);
  if (Number.isNaN(date.getTime())) return "-";
  const day = String(date.getDate()).padStart(2, "0");
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const year = date.getFullYear();
  return `${year}-${month}-${day}`;
};

const Outbox = () => {
  const [rows, setRows] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState("");
  const [selectedMessage, setSelectedMessage] = useState(null);

  const memberData = useMemo(() => {
    try {
      return JSON.parse(localStorage.getItem("memberData") || "{}") || {};
    } catch {
      return {};
    }
  }, []);

  useEffect(() => {
    const loadOutbox = async () => {
      setIsLoading(true);
      setError("");

      const userId = memberData?.user_id || "";
      if (!userId) {
        setRows([]);
        setError("Please sign in first.");
        setIsLoading(false);
        return;
      }

      try {
        const response = await requestMemberApi(
          `/messages/outbox?user_id=${encodeURIComponent(userId)}`,
          {
            method: "GET",
            headers: {
              Accept: "application/json",
              "X-Auth-Member": userId,
            },
          },
        );

        if (!response.ok) {
          setError(response?.data?.message || "Unable to load outbox messages.");
          setRows([]);
          return;
        }

        setRows(Array.isArray(response?.data?.data) ? response.data.data : []);
      } catch {
        setError("Unable to connect to backend. Start backend: php artisan serve --host=127.0.0.1 --port=8000");
      } finally {
        setIsLoading(false);
      }
    };

    loadOutbox();
  }, [memberData]);

  return (
    <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">
          <Sidebar />
    
          <div className="flex-1 min-w-0 flex flex-col overflow-x-hidden">
            <Navbar />
            <div className="p-4 sm:p-6 bg-gray-100">

            <h1 className="text-3xl font-bold text-[#B0422E] text-center mb-8">
             Outbox
            </h1>
            {isLoading && <p className="text-sm text-gray-500 text-center mb-4">Loading messages...</p>}
            {error && <p className="text-sm text-red-500 text-center mb-4">{error}</p>}
            {selectedMessage && (
              <div className="bg-white rounded-xl border border-gray-200 p-4 mb-4">
                <p className="text-sm"><span className="font-semibold">To:</span> {selectedMessage.to || "-"}</p>
                <p className="text-sm"><span className="font-semibold">Subject:</span> {selectedMessage.subject || "-"}</p>
                <p className="text-sm mt-2"><span className="font-semibold">Message:</span> {selectedMessage.message_details || "-"}</p>
              </div>
            )}

            <div className="bg-white rounded-2xl shadow p-6">
                <div className="overflow-x-auto">
                    <table className="w-full text-center">

                                    <thead>
                                    <tr className="bg-[#B0422E] text-white">
                                    <th className="p-3 rounded-l-xl">Sr No</th>
                                    <th className="p-3">From</th>
                                    <th className="p-3">Subject</th>
                                    <th className="p-3">Date</th>
                                    <th className="p-3">Status</th>
                                    <th className="p-3 rounded-r-xl">Action</th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                    {rows.length === 0 ? (
                                      <tr className="border-b">
                                        <td className="p-4" colSpan={6}>No messages found</td>
                                      </tr>
                                    ) : (
                                      rows.map((row, index) => (
                                        <tr className="border-b" key={row.id || index}>
                                          <td className="p-4">{String(index + 1).padStart(2, "0")}</td>
                                          <td>{row.from || "-"}</td>
                                          <td>{row.subject || "-"}</td>
                                          <td>{formatDate(row.created_at)}</td>
                                          <td>{row.status || "Unread"}</td>
                                          <td>
                                            <button
                                              type="button"
                                              onClick={() => setSelectedMessage(row)}
                                              className="text-[#256BB1] font-semibold"
                                            >
                                              View
                                            </button>
                                          </td>
                                        </tr>
                                      ))
                                    )}
                             </tbody>

                        </table>
                     </div>
                 </div>
            </div>
        </div>
    </div>
  );
};

export default Outbox;
