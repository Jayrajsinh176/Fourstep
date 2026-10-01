import { useEffect, useState } from "react";
import { CalendarDays } from "lucide-react";
import Sidebar from "../components/Sidebar";
import Navbar from "../components/Navbar";
import { requestMemberApi } from "../utils/apiClient";

const formatMoney = (value) => {
  const amount = Number(value || 0);
  return `₹${new Intl.NumberFormat("en-IN", {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
  }).format(amount)}`;
};

export default function IncomeSummary() {
  const [fromDate, setFromDate] = useState("");
  const [toDate, setToDate] = useState("");
  const [rows, setRows] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState("");

  const loadIncomeSummary = async (filter = {}) => {
    try {
      setError("");

      let memberData = {};
      try {
        memberData = JSON.parse(localStorage.getItem("memberData") || "{}") || {};
      } catch {
        memberData = {};
      }

      if (!memberData?.user_id) {
        setRows([]);
        setError("Please sign in to view income summary.");
        return;
      }

      const query = new URLSearchParams();
      if (filter.from_date) query.set("from_date", filter.from_date);
      if (filter.to_date) query.set("to_date", filter.to_date);

      const response = await requestMemberApi(
        `/member/income-summary${query.toString() ? `?${query.toString()}` : ""}`,
        {
          method: "GET",
          headers: {
            Accept: "application/json",
            "X-Auth-Member": memberData.user_id,
          },
        },
      );

      if (!response.ok) {
        throw new Error(response.data?.message || "Unable to load income summary.");
      }

      setRows(Array.isArray(response.data?.data) ? response.data.data : []);
    } catch (loadError) {
      setRows([]);
      setError(loadError.message || "Unable to load income summary.");
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    loadIncomeSummary();
  }, []);

  const handleSubmit = async (event) => {
    event.preventDefault();
    setIsLoading(true);

    await loadIncomeSummary({
      from_date: fromDate || undefined,
      to_date: toDate || undefined,
    });
  };

  return (
    <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">
      <Sidebar />

      <div className="flex-1 min-w-0 flex flex-col overflow-x-hidden">
        <Navbar />
        <div className="bg-gray-100 p-4 sm:p-6">
          <h1 className="text-3xl font-bold text-center text-[#b3432f] mb-8">
            Income Summary
          </h1>

          <form className="bg-white rounded-2xl shadow-sm p-6 mb-8" onSubmit={handleSubmit}>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label className="block font-medium mb-2">From date</label>
                <div className="flex items-center bg-gray-100 rounded-xl px-4 py-3">
                  <CalendarDays size={18} className="text-gray-500 mr-2" />
                  <input
                    type="date"
                    value={fromDate}
                    onChange={(event) => setFromDate(event.target.value)}
                    className="bg-transparent outline-none w-full text-gray-600"
                  />
                </div>
              </div>

              <div>
                <label className="block font-medium mb-2">To date</label>
                <div className="flex items-center bg-gray-100 rounded-xl px-4 py-3">
                  <CalendarDays size={18} className="text-gray-500 mr-2" />
                  <input
                    type="date"
                    value={toDate}
                    onChange={(event) => setToDate(event.target.value)}
                    className="bg-transparent outline-none w-full text-gray-600"
                  />
                </div>
              </div>
            </div>

            <button
              type="submit"
              className="mt-6 bg-blue-600 hover:bg-blue-700 text-white px-8 py-2 rounded-lg disabled:bg-blue-300"
              disabled={isLoading}
            >
              {isLoading ? "Loading..." : "Submit"}
            </button>
          </form>

          {!isLoading && error && (
            <div className="mb-4 text-sm text-red-500">{error}</div>
          )}

          <div className="bg-white rounded-2xl shadow-sm p-6 overflow-x-auto">
            <table className="min-w-full text-sm">
              <thead>
                <tr className="bg-[#b3432f] text-white">
                  <th className="px-4 py-3 text-left">Sr No</th>
                  <th className="px-4 py-3 text-left">Date</th>
                  <th className="px-4 py-3 text-left">Description</th>
                  <th className="px-4 py-3 text-left">Credit (₹)</th>
                  <th className="px-4 py-3 text-left">Debit (₹)</th>
                  <th className="px-4 py-3 text-left">Balance (₹)</th>
                </tr>
              </thead>
              <tbody>
                {!isLoading && rows.length === 0 && (
                  <tr className="border-b">
                    <td className="px-4 py-4 text-center" colSpan={6}>
                      No income summary records found
                    </td>
                  </tr>
                )}

                {rows.map((row, index) => (
                  <tr className="border-b" key={`${row.date || ""}-${row.description || ""}-${index}`}>
                    <td className="px-4 py-4">{String(row.sr_no || index + 1).padStart(2, "0")}</td>
                    <td className="px-4 py-4">{row.date || "-"}</td>
                    <td className="px-4 py-4">{row.description || "-"}</td>
                    <td className="px-4 py-4">{Number(row.credit_amount) > 0 ? formatMoney(row.credit_amount) : "--"}</td>
                    <td className="px-4 py-4">{Number(row.debit_amount) > 0 ? formatMoney(row.debit_amount) : "--"}</td>
                    <td className="px-4 py-4">{formatMoney(row.balance_amount)}</td>
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
