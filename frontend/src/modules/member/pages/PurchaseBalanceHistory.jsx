import Sidebar from "../components/Sidebar";
import Navbar from "../components/Navbar";
import { useEffect, useState } from "react";
import { requestMemberApi } from "../utils/apiClient";

export default function PurchaseBalanceHistory() {
  const [history, setHistory] = useState([]);
  const [loading, setLoading] = useState(true);

  const member = JSON.parse(localStorage.getItem("memberData"));
  const memberId = member?.user_id;

useEffect(() => {
  if (!memberId) return;

  const fetchHistory = async () => {
    try {
      const res = await requestMemberApi(`/balance-history?member_id=${memberId}`);
      if (res.ok) setHistory(res.data || []);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  fetchHistory();
}, [memberId]);

  return (
    <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">
      <Sidebar />
      <div className="flex-1 min-w-0 flex flex-col">
        <Navbar />

        <div className="text-center mt-6">
          <h1 className="text-3xl font-bold text-[#B0422E]">
            Purchase Balance History
          </h1>
        </div>

        <div className="p-6">
          <div className="bg-white rounded-2xl shadow-sm p-6 overflow-x-auto">
            <table className="w-full min-w-190 text-sm">
              <thead>
                <tr className="bg-[#B0422E] text-white">
                  <th className="py-3 px-4 text-left rounded-l-xl">Request No</th>
                  <th className="py-3 px-4 text-left">Request Date</th>
                  <th className="py-3 px-4 text-left">Amount</th>
                  <th className="py-3 px-4 text-left">Payment Mode</th>
                  <th className="py-3 px-4 text-left">Transaction Number</th>
                  <th className="py-3 px-4 text-left">Slip</th>
                  <th className="py-3 px-4 text-left rounded-r-xl">Status</th>
                </tr>
              </thead>
              <tbody>
                {loading ? (
                  <tr>
                    <td colSpan="7" className="py-6 text-center text-gray-400">
                      Loading...
                    </td>
                  </tr>
                ) : history.length === 0 ? (
                  <tr>
                    <td colSpan="7" className="py-6 text-center text-gray-400">
                      No records found
                    </td>
                  </tr>
                ) : (
                  history.map((item, index) => (
                    <tr key={item.id} className="border-b hover:bg-gray-50">
                      <td className="py-4 px-4">
                        {String(index + 1).padStart(2, "0")}
                      </td>
                      <td className="py-4 px-4">
                        {item.created_at
                          ? new Date(item.created_at).toLocaleDateString("en-IN")
                          : "--"}
                      </td>
                      <td className="py-4 px-4 font-medium">₹{item.amount}</td>
                      <td className="py-4 px-4">{item.mode_of_payment || "--"}</td>
                      <td className="py-4 px-4">{item.transaction_no || "--"}</td>
                      <td className="py-4 px-4">
  {item.payment_slip ? (
    <a
      href={`https://fourstepretail.com/storage/${item.payment_slip}`}
      target="_blank"
      rel="noreferrer"
      className="text-blue-600 underline text-xs font-medium"
    >
      View
    </a>
  ) : (
    "--"
  )}
</td>
                      <td className="py-4 px-4">
                        <span
                          className={`px-2.5 py-1 rounded-lg text-xs font-semibold ${
                            item.status === "Approved"
                              ? "bg-green-100 text-green-700"
                              : item.status === "pending"
                              ? "bg-yellow-100 text-yellow-700"
                              : "bg-red-100 text-red-700"
                          }`}
                        >
                          {item.status}
                        </span>
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
  );
}