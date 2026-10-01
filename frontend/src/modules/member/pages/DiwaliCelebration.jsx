import { useEffect, useMemo, useState } from "react";
import Sidebar from "../components/Sidebar";
import Navbar from "../components/Navbar";

const API_BASE_URL =
  import.meta.env.VITE_API_BASE_URL || "https://fourstepretail.com/api";

export default function DiwaliCelebration() {
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

    const loadStatus = async () => {
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
          `${API_BASE_URL}/member/diwali-status?user_id=${encodeURIComponent(memberUserId)}`,
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
            data?.message || "Unable to load Diwali celebration status",
          );
        }

        if (isMounted) {
          setStatusData(data?.data || null);
        }
      } catch (fetchError) {
        if (isMounted) {
          setError(
            fetchError.message || "Unable to load Diwali celebration status",
          );
          setStatusData(null);
        }
      } finally {
        if (isMounted) {
          setIsLoading(false);
        }
      }
    };

    loadStatus();

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

  const formatDate = (dateValue) => {
    if (!dateValue) return "-";
    const date = new Date(dateValue);
    if (Number.isNaN(date.getTime())) return "-";

    const day = String(date.getDate()).padStart(2, "0");
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const year = date.getFullYear();
    return `${day}-${month}-${year}`;
  };

  const rows = Array.isArray(statusData?.rows) ? statusData.rows : [];

  return (
    <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">
      <Sidebar />

      <div className="flex-1 min-w-0 flex flex-col">
        <Navbar />

        {/* Page Title */}
        <div className="text-center mt-6">
          <h1 className="text-3xl font-bold text-[#B0422E]">
            Diwali Celebration Status
          </h1>
        </div>

        {isLoading && (
          <p className="text-center text-gray-500 mt-4">Loading status...</p>
        )}
        {!isLoading && error && (
          <p className="text-center text-red-500 mt-4">{error}</p>
        )}

        <div className="p-6 space-y-6">
          {/* Top Summary Card */}
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
                  Matching Turnover Exists
                </p>
                <h2 className="text-2xl font-bold mt-2">
                  {statusData?.matching_turnover_exists ? "Yes" : "No"}
                </h2>
              </div>

              <div className="bg-[#CF9D94A1] rounded-xl p-6 text-white">
                <p className="uppercase text-semibold tracking-wide">
                  Lapsed Matching Turnover
                </p>
                <h2 className="text-2xl font-bold mt-2">
                  {formatCurrency(statusData?.lapsed_matching_turnover)}
                </h2>
              </div>

              <div className="bg-[#CF9D94A1] rounded-xl p-6 text-white">
                <p className="uppercase text-semibold tracking-wide">
                  Income (5%)
                </p>
                <h2 className="text-2xl font-bold mt-2">
                  {formatCurrency(statusData?.estimated_income)}
                </h2>
              </div>
            </div>
          </div>

          {/* Table Section */}
          <div className="bg-white rounded-2xl shadow-sm p-6 overflow-x-auto">
            <table className="w-full min-w-175 text-semibold">
              <thead>
                <tr className="bg-[#B0422E] text-white">
                  <th className="py-3 px-4 text-left rounded-l-xl">Sr No</th>
                  <th className="py-3 px-4 text-left">Date</th>
                  <th className="py-3 px-4 text-left">Left BV</th>
                  <th className="py-3 px-4 text-left">Right BV</th>
                  <th className="py-3 px-4 text-left">
                    Lapsed Matching Turnover
                  </th>
                  <th className="py-3 px-4 text-left">Income (5%)</th>
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
                  <tr key={`${row.date}-${index}`} className="border-b">
                    <td className="py-3 px-4">
                      {String(index + 1).padStart(2, "0")}
                    </td>
                    <td className="py-3 px-4">{formatDate(row.date)}</td>
                    <td className="py-3 px-4">{Number(row.left_pv || 0)}</td>
                    <td className="py-3 px-4">{Number(row.right_pv || 0)}</td>
                    <td className="py-3 px-4">
                      {formatCurrency(row.matching_turnover)}
                    </td>
                    <td className="py-3 px-4">{formatCurrency(row.income)}</td>
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
