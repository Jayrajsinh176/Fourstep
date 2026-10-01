import Sidebar from "../components/Sidebar";
import Navbar from "../components/Navbar";
import { IndianRupee, Gift, CalendarCheck } from "lucide-react";
import { useEffect, useState } from "react";
import { requestMemberApi } from "../utils/apiClient";

const EMPTY_DATA = {
  user_id: "",
  fullname: "",
  completed_months: 0,
  reward_percentage: 25,
  reward_earned: false,
  total_purchase: 0,
  reward_amount: 0,
  consistency_status: "Not Started",
  month_1_purchase: 0,
  month_2_purchase: 0,
  month_3_purchase: 0,
  month_4_purchase: 0,
  minimum_monthly_purchase: 500,
  months: [],
  transactions: [],
};

export default function ConsistencyStatus() {
  const [data, setData] = useState(EMPTY_DATA);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  const member = JSON.parse(localStorage.getItem("memberData") || "{}");
  const userId = member?.user_id || "";

  useEffect(() => {
    async function fetchData() {
      if (!userId) {
        setError("Please sign in first");
        setLoading(false);
        return;
      }

      try {
        const res = await requestMemberApi("/member/consistency-status", {
          method: "GET",
          headers: {
            Accept: "application/json",
            "X-Auth-Member": userId,
          },
        });

        if (!res.ok) {
          throw new Error(res.data?.message || "Unable to fetch consistency status");
        }

        const apiData = res.data?.data || {};

        setData({
          user_id: apiData.user_id || "",
          fullname: apiData.fullname || "",
          completed_months: Number(apiData.completed_months || 0),
          reward_percentage: Number(apiData.reward_percentage || 25),
          reward_earned: Boolean(apiData.reward_earned),
          reward_amount: Number(apiData.reward_amount || 0),
          consistency_status: apiData.consistency_status || "Not Started",
          month_1_purchase: Number(apiData.month_1_purchase || 0),
          month_2_purchase: Number(apiData.month_2_purchase || 0),
          month_3_purchase: Number(apiData.month_3_purchase || 0),
          month_4_purchase: Number(apiData.month_4_purchase || 0),
          minimum_monthly_purchase: Number(apiData.minimum_monthly_purchase || 500),
          months: Array.isArray(apiData.months) ? apiData.months : [],
          transactions: Array.isArray(apiData.transactions) ? apiData.transactions : [],
          total_purchase: Number(apiData.total_purchase || 0),
        });
      } catch (err) {
        setError(err.message || "Something went wrong");
      } finally {
        setLoading(false);
      }
    }

    fetchData();
  }, [userId]);

  const formatCurrency = (amount) => {
    return new Intl.NumberFormat("en-IN", {
      style: "currency",
      currency: "INR",
      maximumFractionDigits: 2,
    }).format(Number(amount || 0));
  };

  const formatDate = (date) => {
    if (!date) return "-";

    const d = new Date(date);
    if (Number.isNaN(d.getTime())) return "-";

    const day = String(d.getDate()).padStart(2, "0");
    const month = String(d.getMonth() + 1).padStart(2, "0");
    const year = d.getFullYear();

    return `${day}-${month}-${year}`;
  };

  const monthCards =
    data.months.length > 0
      ? data.months.map((item) => ({
        label: item.label || `Month ${item.month_index}`,
        amount: Number(item.purchase_amount || 0),
        completed: Boolean(item.completed),
        month_index: Number(item.month_index || 0),
      }))
      : [
        { label: "Month 1", amount: data.month_1_purchase, completed: Number(data.month_X_purchase) > 0, month_index: 1 },
        { label: "Month 2", amount: data.month_2_purchase, completed: Number(data.month_X_purchase) > 0, month_index: 2 },
        { label: "Month 3", amount: data.month_3_purchase, completed: Number(data.month_X_purchase) > 0, month_index: 3 },
        { label: "Month 4", amount: data.month_4_purchase, completed: Number(data.month_X_purchase) > 0, month_index: 4 },
      ];

  return (
    <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">
      <Sidebar />

      <div className="flex-1 flex flex-col min-w-0">
        <Navbar />

        <div className="text-center mt-6">
          <h1 className="text-3xl font-bold text-[#B0422E]">
            Consistency Status
          </h1>
        </div>

        {loading && <p className="text-center mt-4">Loading status...</p>}
        {error && <p className="text-center text-red-500 mt-4">{error}</p>}

        <div className="p-6 space-y-6">
          <div className="bg-[#B0422E] rounded-2xl p-6 text-white">
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div className="flex items-center gap-4 bg-white/10 rounded-xl p-4">
                <div className="bg-white/20 p-4 rounded-xl">
                  <CalendarCheck size={28} />
                </div>
                <div>
                  <p className="text-sm font-semibold">Completed Months</p>
                  <h2 className="text-3xl font-bold">{data.completed_months}/4</h2>
                </div>
              </div>

              <div className="flex items-center gap-4 bg-white/10 rounded-xl p-4">
                <div className="bg-white/20 p-4 rounded-xl">
                  <Gift size={28} />
                </div>
                <div>
                  <p className="text-sm font-semibold">Reward Status</p>
                  <h2 className="text-2xl font-bold">
                    {data.reward_earned ? "Reward Achieved" : "Not Achieved"}
                  </h2>
                </div>
              </div>

              <div className="flex items-center gap-4 bg-white/10 rounded-xl p-4">
                <div className="bg-white/20 p-4 rounded-xl">
                  <IndianRupee size={28} />
                </div>
                <div>
                  <p className="text-sm font-semibold">Reward Amount</p>
                  <h2 className="text-3xl font-bold">
                    {formatCurrency(data.reward_amount)}
                  </h2>
                </div>
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
              <div className="bg-[#CF9D94A1] rounded-xl p-6">
                <p className="text-sm">Consistency Status</p>
                <h3 className="text-2xl font-bold mt-2">{data.consistency_status}</h3>
              </div>

              <div className="bg-[#CF9D94A1] rounded-xl p-6">
                <p className="text-sm">Reward Percentage</p>
                <h3 className="text-2xl font-bold mt-2">
                  {data.reward_percentage}%
                </h3>
              </div>
            </div>
          </div>

          <div className="bg-white rounded-2xl shadow-sm p-6">
            <h2 className="text-xl font-bold text-[#B0422E] mb-4">
              Month Wise Purchase Status
            </h2>

            <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
              {monthCards.map((item, index) => {
                const completed = Boolean(item.completed);

                return (
                  <div
                    key={item.label}
                    className={`rounded-xl border p-4 ${completed
                        ? "bg-green-50 border-green-200"
                        : "bg-gray-50 border-gray-200"
                      }`}
                  >
                    <p className="text-sm text-gray-500">{item.label}</p>
                    <p className="text-lg font-bold mt-2">
                      {completed ? "Completed" : "Not Completed"}
                    </p>
                    <p
                      className={`text-sm mt-2 font-medium ${completed ? "text-green-600" : "text-gray-500"
                        }`}
                    >
                      {completed ? "Completed" : "Pending"}
                    </p>

                    {data.completed_months === 4 && (
                      <p className="text-xs text-[#B0422E] font-semibold mt-2">
                        Reward Qualified
                      </p>
                    )}            </div>
                );
              })}
            </div>
          </div>

          <div className="bg-white rounded-2xl shadow-sm p-6 overflow-x-auto">
            <h2 className="text-xl font-bold text-[#B0422E] mb-4">
              Consistency History
            </h2>

            <table className="w-full text-sm">
              <thead>
                <tr className="bg-[#B0422E] text-white">
                  <th className="py-3 px-4 text-left">Sr No</th>
                  <th className="py-3 px-4 text-left">Date</th>
                  <th className="py-3 px-4 text-left">Detail</th>
                  <th className="py-3 px-4 text-left">Credit Amount</th>
                  {/* <th className="py-3 px-4 text-left">Debit Amount</th>
                  <th className="py-3 px-4 text-left">Balance</th> */}
                </tr>
              </thead>

              <tbody>
                {data.transactions.length === 0 && !loading && (
                  <tr>
                    <td colSpan="6" className="py-4 px-4 text-center">
                      No consistency transactions found
                    </td>
                  </tr>
                )}

                {data.transactions.map((row, index) => (
                  <tr key={index} className="border-b">
                    <td className="py-4 px-4">{index + 1}</td>
                    <td className="py-4 px-4">{formatDate(row.date)}</td>
                    <td className="py-4 px-4">{row.description || "-"}</td>
                    <td className="py-4 px-4">
                      {Number(row.credit_amount) > 0
                        ? formatCurrency(row.credit_amount)
                        : "--"}
                    </td>
                    {/* <td className="py-4 px-4">
                      {Number(row.debit_amount) > 0
                        ? formatCurrency(row.debit_amount)
                        : "--"}
                    </td>
                    <td className="py-4 px-4">
                      {row.balance_after !== null && row.balance_after !== undefined
                        ? formatCurrency(row.balance_after)
                        : "--"}
                    </td> */}
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  );
}
