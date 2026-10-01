import Sidebar from "../components/Sidebar";
import Navbar from "../components/Navbar";
import React, { useEffect, useMemo, useState } from "react";

const API_BASE_URL =
  import.meta.env.VITE_API_BASE_URL || "https://fourstepretail.com/api";

const RoyaltyClubBonus = () => {
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

    const fetchRows = async () => {
      try {
        if (isMounted) {
          setError("");
        }

        const query = memberUserId
          ? `?user_id=${encodeURIComponent(memberUserId)}`
          : "";

        const response = await fetch(
          `${API_BASE_URL}/bonuses/royalty-club${query}`,
          {
            method: "GET",
            headers: {
              Accept: "application/json",
            },
          },
        );

        const data = await response.json();

        if (!response.ok) {
          throw new Error(
            data?.message || "Unable to fetch royalty bonus data",
          );
        }

        if (isMounted) {
          setRows(Array.isArray(data?.data) ? data.data : []);
        }
      } catch (fetchError) {
        if (isMounted) {
          setError(fetchError.message || "Unable to fetch royalty bonus data");
        }
      } finally {
        if (isMounted) {
          setIsLoading(false);
        }
      }
    };

    fetchRows();
    const intervalId = setInterval(fetchRows, 15000);

    return () => {
      isMounted = false;
      clearInterval(intervalId);
    };
  }, [memberUserId]);

  const formatCurrency = (value) => {
    return new Intl.NumberFormat("en-IN", {
      style: "currency",
      currency: "INR",
      maximumFractionDigits: 2,
    }).format(Number(value || 0));
  };

  return (
    <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">
      <Sidebar />

      <div className="flex-1 min-w-0 flex flex-col">
        <Navbar />
        <div className="p-8 bg-gray-100 min-h-screen">
          <h1 className="text-3xl font-bold text-[#B0422E] text-center mb-8">
            Royalty Club Bonus
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
                    <th className="p-3">Month</th>
                    <th className="p-3">Company Turnover</th>
                    <th className="p-3">Pool%</th>
                    <th className="p-3">Royalty Pool</th>
                    <th className="p-3">Eligible Members</th>
                    <th className="p-3">Earned</th>
                    <th className="p-3 rounded-r-xl">Status</th>
                  </tr>
                </thead>

                <tbody>
                  {!isLoading && rows.length === 0 && (
                    <tr className="border-b">
                      <td className="p-4" colSpan={8}>
                        No Royalty Club bonus data found
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
                      <td>{row.month || "-"}</td>
                      <td>{formatCurrency(row.monthly_turnover)}</td>
                      <td>{Number(row.pool_percentage || 0)}%</td>
                      <td>{formatCurrency(row.royalty_pool)}</td>
                      <td>{row.eligible_users_count ?? 0}</td>
                      <td>{formatCurrency(row.earned)}</td>
                      <td
                        className={
                          row.status === "paid"
                            ? "text-green-600 font-semibold"
                            : "text-amber-600 font-semibold"
                        }
                      >
                        {row.status === "paid" ? "Paid" : "Pending"}
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

export default RoyaltyClubBonus;
