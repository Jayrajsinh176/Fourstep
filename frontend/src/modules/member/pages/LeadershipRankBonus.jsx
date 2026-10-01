import Sidebar from "../components/Sidebar";
import Navbar from "../components/Navbar";
import React, { useEffect, useMemo, useState } from "react";

const API_BASE_URL =
  import.meta.env.VITE_API_BASE_URL || "https://fourstepretail.com/api";

const LeadershipRankBonus = () => {
  const [giftStatus, setGiftStatus] = useState(null);
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
          `${API_BASE_URL}/bonuses/leadership-gift/history${query}`,
          {
            method: "GET",
            headers: {
              Accept: "application/json",
            },
          },
        );

        const data = await response.json();

        const statusResponse = await fetch(
          `${API_BASE_URL}/bonuses/leadership-gift${query}`,
          {
            method: "GET",
            headers: {
              Accept: "application/json",
            },
          },
        );

        const statusData = await statusResponse.json();

        if (isMounted) {
          setGiftStatus(statusData?.data || null);
        }

        if (!response.ok) {
          throw new Error(
            data?.message || "Unable to fetch leadership gift bonus data",
          );
        }

        if (isMounted) {
          setRows(Array.isArray(data?.data) ? data.data : []);
        }
      } catch (fetchError) {
        if (isMounted) {
          setError(
            fetchError.message || "Unable to fetch leadership gift bonus data",
          );
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

  const formatDate = (dateValue) => {
    if (!dateValue) return "-";
    const date = new Date(dateValue);
    if (Number.isNaN(date.getTime())) return "-";

    const day = String(date.getDate()).padStart(2, "0");
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const year = date.getFullYear();
    return `${day}-${month}-${year}`;
  };

  const statusClassName = (status) => {
    const normalized = String(status || "").toLowerCase();
    if (normalized === "delivered") return "text-green-600 font-semibold";
    if (normalized === "approved") return "text-blue-600 font-semibold";
    return "text-yellow-600 font-semibold";
  };

  const statusLabel = (status) => {
    if (!status) return "Pending";
    return String(status).charAt(0).toUpperCase() + String(status).slice(1);
  };

  const yesNo = (value) => (value ? "Yes" : "No");

  return (
    <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">
      <Sidebar />

      <div className="flex-1 min-w-0 flex flex-col">
        <Navbar />
        <div className="p-8 bg-gray-100 min-h-screen">
          <h1 className="text-3xl font-bold text-[#B0422E] text-center mb-8">
            Leadership Gift Bonus
          </h1>
          {isLoading && (
            <p className="text-center text-gray-500 mb-4">Loading bonuses...</p>
          )}
          {error && <p className="text-center text-red-500 mb-4">{error}</p>}
          <div className="bg-[#D65F41] rounded-3xl p-6 mb-8">
            <div className="grid grid-cols-1 md:grid-cols-4 gap-5">

              <div className="bg-[#D8836C] rounded-2xl p-5">
                <p className="text-white text-lg">Current Rank</p>
                <h2 className="text-white text-4xl font-bold">
                  {giftStatus?.current_rank || "-"}
                </h2>
              </div>

              <div className="bg-[#D8836C] rounded-2xl p-5">
                <p className="text-white text-lg">Current Gift</p>
                <h2 className="text-white text-2xl font-bold">
                  {giftStatus?.current_gift || "-"}
                </h2>
              </div>

              <div className="bg-[#D8836C] rounded-2xl p-5">
                <p className="text-white text-lg">Manager Qualified</p>
                <h2 className="text-white text-4xl font-bold">
                  {yesNo(giftStatus?.manager?.qualified)}
                </h2>
              </div>

              <div className="bg-[#D8836C] rounded-2xl p-5">
                <p className="text-white text-lg">Area Manager Qualified</p>
                <h2 className="text-white text-4xl font-bold">
                  {yesNo(giftStatus?.area_manager?.qualified)}
                </h2>
              </div>

            </div>
          </div>
          <div className="bg-white rounded-2xl shadow p-6">
            <div className="overflow-x-auto">
              <table className="w-full text-center">
                <thead>
                  <tr className="bg-[#B0422E] text-white">
                    <th className="p-3 rounded-l-xl">Sr No</th>
                    <th className="p-3">Member ID</th>
                    <th className="p-3">Member Name</th>
                    <th className="p-3">Rank</th>
                    <th className="p-3">Gift</th>
                    <th className="p-3">Qualified Date</th>
                    <th className="p-3 rounded-r-xl">Status</th>
                  </tr>
                </thead>

                <tbody>
                  {!isLoading && rows.length === 0 && (
                    <tr className="border-b">
                      <td className="p-4" colSpan={7}>
                        No Leadership Gift bonus data found
                      </td>
                    </tr>
                  )}

                  {rows.map((row, index) => (
                    <tr className="border-b" key={row.id ?? index}>
                      <td className="p-4">
                        {String(index + 1).padStart(2, "0")}
                      </td>
                      <td>{row.member_id || "-"}</td>
                      <td>{row.member_name || "-"}</td>
                      <td>{row.rank_name || "-"}</td>
                      <td>{row.gift_name || "-"}</td>
                      <td>{formatDate(row.qualified_date)}</td>
                      <td className={statusClassName(row.status)}>
                        {statusLabel(row.status)}
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

export default LeadershipRankBonus;
