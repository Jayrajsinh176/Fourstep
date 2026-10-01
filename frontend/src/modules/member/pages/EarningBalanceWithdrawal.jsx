import Sidebar from "../components/Sidebar";
import Navbar from "../components/Navbar";
import React, { useEffect, useState } from "react";
import { requestMemberApi } from "../utils/apiClient";

const formatMoney = (value) => {
  const amount = Number(value || 0);
  return `₹${new Intl.NumberFormat("en-IN", {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
  }).format(amount)}`;
};

const EarningBalanceWithdrawal = () => {
  const [rows, setRows] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState("");
  const [summary, setSummary] = useState({
    total_records: 0,
    total_withdrawal: 0,
  });

  useEffect(() => {
    let isMounted = true;

    const fetchRows = async () => {
      try {
        setError("");

        let storedMember = {};
        try {
          storedMember = JSON.parse(localStorage.getItem("memberData") || "{}");
        } catch {
          storedMember = {};
        }

        const userId = storedMember?.user_id || "";
        if (!userId) {
          throw new Error("Member not found in session. Please sign in again.");
        }

        const response = await requestMemberApi(
          `/member/earning-balance-withdrawal?user_id=${encodeURIComponent(userId)}`,
          {
          method: "GET",
          headers: {
            Accept: "application/json",
            "X-Auth-Member": userId,
          },
          }
        );

        if (!response.ok) {
          throw new Error(response.data?.message || "Unable to load earning balance withdrawal.");
        }

        if (isMounted) {
          const payload = response.data || {};
          setRows(Array.isArray(payload.data) ? payload.data : []);
          setSummary({
            total_records: Number(payload.summary?.total_records || 0),
            total_withdrawal: Number(payload.summary?.total_withdrawal || 0),
          });
        }
      } catch (fetchError) {
        if (isMounted) {
          setRows([]);
          setSummary({
            total_records: 0,
            total_withdrawal: 0,
          });
          setError(fetchError.message || "Unable to load earning balance withdrawal.");
        }
      } finally {
        if (isMounted) {
          setIsLoading(false);
        }
      }
    };

    fetchRows();
    const intervalId = setInterval(fetchRows, 30000);

    return () => {
      isMounted = false;
      clearInterval(intervalId);
    };
  }, []);

  return (
    <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">
          <Sidebar />
    
          <div className="flex-1 min-w-0 flex flex-col overflow-x-hidden">
            <Navbar />
            <div className="p-4 sm:p-6 bg-gray-100">

            <h1 className="text-3xl font-bold text-[#B0422E] text-center mb-8">
             Earning Balance Withdrawal
            </h1>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
              <div className="bg-white rounded-xl shadow p-4 text-center">
                <p className="text-sm text-gray-500">Total Requests</p>
                <p className="text-xl font-bold text-[#B0422E]">{summary.total_records}</p>
              </div>
              <div className="bg-white rounded-xl shadow p-4 text-center">
                <p className="text-sm text-gray-500">Total Withdrawal</p>
                <p className="text-xl font-bold text-[#B0422E]">{formatMoney(summary.total_withdrawal)}</p>
              </div>
            </div>

            {isLoading && <p className="text-center text-gray-500 mb-4">Loading withdrawals...</p>}
            {!isLoading && error && <p className="text-center text-[#B0422E] mb-4">{error}</p>}

            <div className="bg-white rounded-2xl shadow p-6">
                <div className="overflow-x-auto">
                    <table className="w-full text-center">

                                    <thead>
                                    <tr className="bg-[#B0422E] text-white">
                                    <th className="p-3 rounded-l-xl">Sr No</th>
                                    <th className="p-3">Member ID</th>
                                    <th className="p-3">Member Name</th>
                                    <th className="p-3">Payment Date</th>
                                    <th className="p-3">Payment Amount</th>
                                    <th className="p-3">Reference No.</th>
                                    <th className="p-3 rounded-r-xl">Status</th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                    {!isLoading && rows.length === 0 && (
                                      <tr className="border-b">
                                        <td className="p-4" colSpan={7}>No earning balance withdrawal records found</td>
                                      </tr>
                                    )}

                                    {rows.map((row, index) => (
                                      <tr className="border-b" key={`${row.user_id || ""}-${row.payment_date || ""}-${row.reference_no || ""}-${index}`}>
                                        <td className="p-4">{String(row.sr_no || index + 1).padStart(2, "0")}</td>
                                        <td>{row.user_id || "-"}</td>
                                        <td>{row.member_name || "-"}</td>
                                        <td>{row.payment_date || "-"}</td>
                                        <td>{formatMoney(row.payment_amount)}</td>
                                        <td>{row.reference_no || "-"}</td>
                                        <td>{row.status || "Pending"}</td>
                                      </tr>
                                    ))}
                             </tbody>

                        </table>
                     </div>
                 </div>
            </div>
        </div>
    </div>
  );
};

export default EarningBalanceWithdrawal;
