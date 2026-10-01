import Sidebar from "../components/Sidebar";
import Navbar from "../components/Navbar";
import React, { useEffect, useMemo, useState } from "react";

const API_BASE_URL =
  import.meta.env.VITE_API_BASE_URL || "https://fourstepretail.com/api";

const DiwaliCelebrationBonus = () => {
  const [rows, setRows] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState("");

  const memberUserId = useMemo(() => {
    try {
      const member = JSON.parse(localStorage.getItem("memberData") || "{}");
      return member?.user_id || "";
    } catch {
      return "";
    }
  }, []);

  useEffect(() => {
    let isMounted = true;

    const loadBonuses = async () => {
      try {
        if (isMounted) {
          setError("");
        }

        const query = memberUserId
          ? `?user_id=${encodeURIComponent(memberUserId)}`
          : "";

        const response = await fetch(`${API_BASE_URL}/bonuses/diwali${query}`, {
          method: "GET",
          headers: {
            Accept: "application/json",
          },
        });

        const data = await response.json();

        if (!response.ok) {
          throw new Error(data?.message || "Unable to fetch Diwali bonus data");
        }

        if (isMounted) {
          setRows(Array.isArray(data?.data) ? data.data : []);
        }
      } catch (fetchError) {
        if (isMounted) {
          setError(fetchError.message || "Unable to fetch Diwali bonus data");
        }
      } finally {
        if (isMounted) {
          setIsLoading(false);
        }
      }
    };

    loadBonuses();
    const intervalId = setInterval(loadBonuses, 15000);

    return () => {
      isMounted = false;
      clearInterval(intervalId);
    };
  }, [memberUserId]);

  const formatDate = (dateValue) => {
    if (!dateValue) return "-";
    const date = new Date(dateValue);
    if (Number.isNaN(date.getTime())) return "-";

    const day = String(date.getDate()).padStart(2, "0");
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const year = date.getFullYear();
    return `${day}-${month}-${year}`;
  };

  const formatCurrency = (amount) => {
    return new Intl.NumberFormat("en-IN", {
      style: "currency",
      currency: "INR",
      maximumFractionDigits: 2,
    }).format(Number(amount || 0));
  };

  return (
    <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">
      <Sidebar />

      <div className="flex-1 min-w-0 flex flex-col">
        <Navbar />
        <div className="p-8 bg-gray-100 min-h-screen">
          <h1 className="text-3xl font-bold text-[#B0422E] text-center mb-8">
            Diwali Celebration Bonus
          </h1>
          {isLoading && (
            <p className="text-center text-gray-500 mb-4">Loading bonuses...</p>
          )}
          {error && <p className="text-center text-red-500 mb-4">{error}</p>}

          <div className="bg-white rounded-2xl shadow p-6">
            <div className="overflow-x-auto">
              <table className="w-full text-center">
                <thead>
                  <tr className="bg-[#B0422E] text-white">
                    <th className="p-3 rounded-l-xl">Sr No</th>
                    <th className="p-3">Date</th>
                    <th className="p-3">Transaction ID</th>
                    <th className="p-3">Festival Year</th>
                    <th className="p-3">Lapsed Matching Turnover</th>
                    <th className="p-3">Bonus %</th>
                    <th className="p-3">Earned</th>
                    <th className="p-3">Requirement</th>
                    <th className="p-3 rounded-r-xl">Status</th>
                  </tr>
                </thead>

                <tbody>
                  {!isLoading && rows.length === 0 && (
                    <tr className="border-b">
                      <td className="p-4" colSpan={9}>
                        No Diwali bonus data found
                      </td>
                    </tr>
                  )}

                  {rows.map((row, index) => (
                    <tr
                      className="border-b"
                      key={row.id ?? `${row.transaction_id}-${index}`}
                    >
                      <td className="p-4">
                        {String(index + 1).padStart(2, "0")}
                      </td>
                      <td>{formatDate(row.date)}</td>
                      <td>{row.transaction_id || "-"}</td>
                      <td>{row.festival_year || "-"}</td>
                      <td>{formatCurrency(row.lapsed_matching_turnover)}</td>
                      <td>{Number(row.percentage || 0)}%</td>
                      <td>{formatCurrency(row.earned)}</td>
                      <td>
                        {row.id_active_4_step
                          ? "4 Step ID Active"
                          : "Not 4 Step Active"}
                      </td>
                      <td
                        className={
                          row.status === "Approved"
                            ? "text-green-600 font-semibold"
                            : "text-orange-600 font-semibold"
                        }
                      >
                        {row.status || "Approved"}
                      </td>
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

export default DiwaliCelebrationBonus;
