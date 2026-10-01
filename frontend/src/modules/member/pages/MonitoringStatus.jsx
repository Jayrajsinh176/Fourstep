import { useEffect, useMemo, useState } from "react";
import Sidebar from "../components/Sidebar";
import Navbar from "../components/Navbar";

const API_BASE_URL =
  import.meta.env.VITE_API_BASE_URL || "https://fourstepretail.com/api";

export default function MonitoringStatus() {
  const [statusData, setStatusData] = useState(null);
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

    const fetchStatus = async () => {
      if (!memberUserId) {
        if (isMounted) {
          setError("Please sign in first");
          setIsLoading(false);
        }
        return;
      }

      try {
        if (isMounted) {
          setError("");
          setIsLoading(true);
        }

        const response = await fetch(
          `${API_BASE_URL}/member/business-monitoring-status?user_id=${encodeURIComponent(memberUserId)}`,
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
            data?.message || "Unable to load business monitoring status",
          );
        }

        if (isMounted) {
          setStatusData(data?.data || null);
        }
      } catch (fetchError) {
        if (isMounted) {
          setError(
            fetchError.message || "Unable to load business monitoring status",
          );
          setStatusData(null);
        }
      } finally {
        if (isMounted) {
          setIsLoading(false);
        }
      }
    };

    fetchStatus();

    return () => {
      isMounted = false;
    };
  }, [memberUserId]);

  const formatCurrency = (value) => {
    return new Intl.NumberFormat("en-IN", {
      style: "currency",
      currency: "INR",
      maximumFractionDigits: 2,
    }).format(Number(value || 0));
  };

  const rows = Array.isArray(statusData?.rows) ? statusData.rows : [];

  return (
    <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">
      <Sidebar />

      <div className="flex-1 min-w-0 flex flex-col">
        <Navbar />

        <div className="text-center mt-6">
          <h1 className="text-3xl font-bold text-[#B0422E]">
            Business Monitoring Status
          </h1>
        </div>

        {isLoading && (
          <p className="text-center text-gray-500 mt-4">Loading status...</p>
        )}
        {!isLoading && error && (
          <p className="text-center text-red-500 mt-4">{error}</p>
        )}

        <div className="p-6 space-y-6">
          <div className="bg-[#B0422E] rounded-2xl p-8 shadow-md">
            <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-8">
              <div className="bg-[#CF9D94A1] rounded-xl p-6 text-white">
                <p className="uppercase text-semibold tracking-wide">
                  4 Step ID Active
                </p>
                <h2 className="text-2xl font-bold mt-2">
                  {statusData?.id_active_4_step ? "Yes" : "No"}
                </h2>
              </div>

              <div className="bg-[#CF9D94A1] rounded-xl p-6 text-white">
                <p className="uppercase text-semibold tracking-wide">
                  Direct Working Members
                </p>
                <h2 className="text-2xl font-bold mt-2">
                  {statusData?.direct_referrals_count ?? 0}
                </h2>
              </div>

              <div className="bg-[#CF9D94A1] rounded-xl p-6 text-white">
                <p className="uppercase text-semibold tracking-wide">
                  Direct Team Income
                </p>
                <h2 className="text-2xl font-bold mt-2">
                  {formatCurrency(statusData?.total_direct_team_income)}
                </h2>
              </div>

              <div className="bg-[#CF9D94A1] rounded-xl p-6 text-white">
                <p className="uppercase text-semibold tracking-wide">
                  Your Income (10%)
                </p>
                <h2 className="text-2xl font-bold mt-2">
                  {formatCurrency(statusData?.estimated_income)}
                </h2>
              </div>
            </div>
          </div>

          <div className="bg-white rounded-2xl shadow-sm p-6 overflow-x-auto">
            <table className="w-full min-w-175 text-semibold">
              <thead>
                <tr className="bg-[#B0422E] text-white">
                  <th className="py-3 px-4 text-left rounded-l-xl">Sr No</th>
                  <th className="py-3 px-4 text-left">Direct Member ID</th>
                  <th className="py-3 px-4 text-left">Direct Member Name</th>
                  <th className="py-3 px-4 text-left">Direct Team Income</th>
                  <th className="py-3 px-4 text-left">Bonus %</th>
                  <th className="py-3 px-4 text-left">
                    Your Monitoring Income
                  </th>
                  <th className="py-3 px-4 text-left rounded-r-xl">
                    Eligibility
                  </th>
                </tr>
              </thead>

              <tbody>
                {!isLoading && rows.length === 0 && (
                  <tr>
                    <td
                      colSpan="7"
                      className="py-10 text-center text-gray-400 font-medium"
                    >
                      No Record Found...
                    </td>
                  </tr>
                )}

                {rows.map((row, index) => (
                  <tr
                    key={`${row.downline_user_id}-${index}`}
                    className="border-b"
                  >
                    <td className="py-3 px-4">
                      {String(index + 1).padStart(2, "0")}
                    </td>
                    <td className="py-3 px-4">{row.downline_user_id || "-"}</td>
                    <td className="py-3 px-4">{row.downline_name || "-"}</td>
                    <td className="py-3 px-4">
                      {formatCurrency(row.direct_team_income)}
                    </td>
                    <td className="py-3 px-4">
                      {Number(row.bonus_percentage || 10)}%
                    </td>
                    <td className="py-3 px-4">
                      {formatCurrency(row.monitoring_bonus)}
                    </td>
                    <td className="py-3 px-4">
                      <span
                        className={
                          statusData?.eligible
                            ? "text-green-600 font-semibold"
                            : "text-orange-600 font-semibold"
                        }
                      >
                        {statusData?.eligible ? "Eligible" : "Not Eligible"}
                      </span>
                    </td>
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
