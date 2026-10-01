import { useEffect, useState } from "react";
import { shoppeeApi as api } from "../api/axios";

function BalanceHistory() {
  const [history, setHistory] = useState([]);
  const [filter, setFilter] = useState("all");
  const [currentPage, setCurrentPage] = useState(1);
  const [showReasonModal, setShowReasonModal] = useState(false);
  const [selectedReason, setSelectedReason] = useState("");
  const itemsPerPage = 10;

  useEffect(() => {
    const fetchHistory = async () => {
      try {
        const user = JSON.parse(localStorage.getItem("user"));

        const res = await api.get(
          `/balance-history?type=${filter}&member_id=${user.id}`
        );

        setHistory(res.data);
      } catch (error) {
        console.error(error);
      }
    };

    fetchHistory();
  }, [filter]);

  const totalPages = Math.ceil(history.length / itemsPerPage);
  const currentPageSafe = Math.min(currentPage, totalPages || 1);
  const indexOfLastHistoryItem = currentPageSafe * itemsPerPage;
  const indexOfFirstHistoryItem = indexOfLastHistoryItem - itemsPerPage;
  const currentHistory = history.slice(
    indexOfFirstHistoryItem,
    indexOfLastHistoryItem
  );

  return (
    <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">
      <div className="flex-1 min-w-0 flex flex-col">
        <div className="text-center mt-4 md:mt-6">
          <h1 className="text-2xl md:text-3xl font-bold text-[#B0422E]">
            History
          </h1>
        </div>

        <div className="p-3 md:p-6">
          <div className="bg-white rounded-2xl shadow-sm p-3 md:p-6 overflow-x-auto">
            <div className="flex justify-end mb-3">
              <select
                className="border border-gray-200 rounded-lg px-2 py-1 text-xs md:text-sm bg-gray-200"
                value={filter}
                onChange={(e) => setFilter(e.target.value)}
              >
                <option value="all">All</option>
                <option value="purchase">Purchase Balance</option>
                <option value="turnover">Turnover Balance</option>
              </select>
            </div>

            <table className="w-full min-w-max text-xs md:text-sm">
              <thead>
                <tr className="bg-[#B0422E] text-white text-center">
                  <th className="py-2 md:py-3 px-2 md:px-4 rounded-l-xl">
                    Req No
                  </th>
                  <th className="py-2 md:py-3 px-2 md:px-4 whitespace-nowrap">
                    Date
                  </th>
                  <th className="py-2 md:py-3 px-2 md:px-4 whitespace-nowrap">
                    Amount
                  </th>
                  <th className="py-2 md:py-3 px-2 md:px-4 whitespace-nowrap">
                    Mode
                  </th>
                  <th className="py-2 md:py-3 px-2 md:px-4 whitespace-nowrap">
                    Type
                  </th>
                  <th className="py-2 md:py-3 px-2 md:px-4 whitespace-nowrap">
                    Status
                  </th>
                  <th className="py-2 md:py-3 px-2 md:px-4 rounded-r-xl whitespace-nowrap">
                    Reason
                  </th>
                </tr>
              </thead>

              <tbody className="font-medium text-black text-center">
                {history.length === 0 ? (
                  <tr>
                    <td colSpan="6" className="text-center py-4">
                      No Records Found
                    </td>
                  </tr>
                ) : (
                  currentHistory.map((item, index) => (
                    <tr key={item.id} className="border-b border-gray-400">
                      <td className="py-2 md:py-4 px-2 md:px-4">{index + 1}</td>
                      <td className="py-2 md:py-4 px-2 md:px-4 whitespace-nowrap">
                        {new Date(item.created_at).toLocaleDateString()}
                      </td>
                      <td className="py-2 md:py-4 px-2 md:px-4 whitespace-nowrap">
                        {item.amount}₹
                      </td>
                      <td className="py-2 md:py-4 px-2 md:px-4 truncate max-w-xs">
                        {item.mode_of_payment}
                      </td>
                      <td className="py-2 md:py-4 px-2 md:px-4 whitespace-nowrap">
                        {item.type === "purchase"
                          ? "Purchase"
                          : "Turnover"}
                      </td>
                      <td className="py-2 md:py-4 px-2 md:px-4 whitespace-nowrap">
                        {item.status === "Approved" ? (
                          <span className="text-green-600 font-semibold">
                            Approved
                          </span>
                        ) : item.status === "Rejected" ? (
                          <span className="text-red-600 font-semibold">
                            Rejected
                          </span>
                        ) : (
                          <span className="text-orange-500 font-semibold">
                            Pending
                          </span>
                        )}
                      </td>
                      <td className="py-2 md:py-4 px-2 md:px-4">
                        {item.status === "Rejected" ? (
                          <button
                            onClick={() => {
                              setSelectedReason(item.reject_reason);
                              setShowReasonModal(true);
                            }}
                            className="px-3 py-1 text-xs bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200"
                          >
                            View Reason
                          </button>
                        ) : (
                          "-"
                        )}
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
            {showReasonModal && (
              <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
                <div className="bg-white rounded-lg p-6 w-[600px] max-w-[90vw] shadow-xl">

                  <h3 className="text-xl font-bold mb-4">
                    Rejection Reason
                  </h3>

                  <p className="text-gray-700 break-words whitespace-pre-wrap max-h-60 overflow-y-auto">
                    {selectedReason}
                  </p>

                  <button
                    onClick={() => setShowReasonModal(false)}
                    className="mt-5 bg-[#B0422E] text-white px-4 py-2 rounded"
                  >
                    Close
                  </button>

                </div>
              </div>
            )}
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
                    className={`rounded-full border px-3 py-1 text-sm font-medium ${currentPageSafe === idx + 1
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
  );
}

export default BalanceHistory;