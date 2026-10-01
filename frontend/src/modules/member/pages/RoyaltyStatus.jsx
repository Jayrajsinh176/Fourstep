import { useEffect, useMemo, useState } from "react";
import Sidebar from "../components/Sidebar";
import Navbar from "../components/Navbar";

const API_BASE_URL =
  import.meta.env.VITE_API_BASE_URL || "https://fourstepretail.com/api";

export default function RoyaltyStatus() {
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
          `${API_BASE_URL}/member/royalty-status?user_id=${encodeURIComponent(memberUserId)}`,
          {
            method: "GET",
            headers: {
              Accept: "application/json",
            },
          },
        );

        const data = await response.json();

        if (!response.ok) {
          throw new Error(data?.message || "Unable to fetch royalty status");
        }

        if (isMounted) {
          setStatusData(data?.data || null);
        }
      } catch (fetchError) {
        if (isMounted) {
          setError(fetchError.message || "Unable to fetch royalty status");
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
          <h1 className="text-3xl font-bold text-[#B0422E]">Royalty Status</h1>
        </div>

        {isLoading && (
          <p className="text-center text-gray-500 mt-4">Loading status...</p>
        )}
        {!isLoading && error && (
          <p className="text-center text-red-500 mt-4">{error}</p>
        )}

        <div className="p-6 space-y-6">
          <div className="bg-[#B0422E] rounded-2xl p-8 shadow-md">
            <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-8">
              <div className="bg-[#CF9D94A1] rounded-xl p-6 text-white">
                <p className="uppercase text-semibold tracking-wide">
                  4 Step or Above
                </p>
                <h2 className="text-2xl font-bold mt-2">
                  {statusData?.step_eligible ? "Yes" : "No"}
                </h2>
              </div>

              <div className="bg-[#CF9D94A1] rounded-xl p-6 text-white">
                <p className="uppercase text-semibold tracking-wide">
                  Monthly INR 500 Repurchase
                </p>
                <h2 className="text-2xl font-bold mt-2">
                  {statusData?.monthly_purchase_met ? "Yes" : "No"}
                </h2>
              </div>

              <div className="bg-[#CF9D94A1] rounded-xl p-6 text-white">
                <p className="uppercase text-semibold tracking-wide">
                  Matching BV Active
                </p>
                <h2 className="text-2xl font-bold mt-2">
                  {statusData?.matching_bv_active ? "Yes" : "No"}
                </h2>
              </div>

              <div className="bg-[#CF9D94A1] rounded-xl p-6 text-white">
                <p className="uppercase text-semibold tracking-wide">
                  6 Direct 4 Step+ Within 30 Days
                </p>
                <h2 className="text-2xl font-bold mt-2">
                  {statusData?.direct_referral_rule_met ? "Yes" : "No"}
                </h2>
                <p className="text-sm text-white/80 mt-1">
                  {Number(statusData?.qualified_direct_referrals || 0)} /{" "}
                  {Number(statusData?.required_direct_referrals || 6)} qualified
                </p>
              </div>

              <div className="bg-[#CF9D94A1] rounded-xl p-6 text-white">
                <p className="uppercase text-semibold tracking-wide">
                  Estimated Royalty Income
                </p>
                <h2 className="text-2xl font-bold mt-2">
                  {formatCurrency(statusData?.estimated_income)}
                </h2>
              </div>
            </div>
          </div>

          <div className="bg-white rounded-2xl shadow-sm p-6 overflow-x-auto">
            <table className="w-full min-w-175 text-sm">
              <thead>
                <tr className="bg-[#B0422E] text-white">
                  <th className="py-3 px-4 text-left rounded-l-xl">Sr No</th>
                  <th className="py-3 px-4 text-left">Month</th>
                  <th className="py-3 px-4 text-left">Company Turnover</th>
                  <th className="py-3 px-4 text-left">Pool %</th>
                  <th className="py-3 px-4 text-left">Royalty Pool</th>
                  <th className="py-3 px-4 text-left">Eligible Members</th>
                  <th className="py-3 px-4 text-left">Earned</th>
                  <th className="py-3 px-4 text-left rounded-r-xl">Status</th>
                </tr>
              </thead>

              <tbody>
                {!isLoading && rows.length === 0 && (
                  <tr>
                    <td
                      colSpan="8"
                      className="py-10 text-center text-gray-400 font-medium"
                    >
                      No Record Found...
                    </td>
                  </tr>
                )}

                {rows.map((row, index) => (
                  <tr className="border-b" key={`${row.month}-${index}`}>
                    <td className="py-4 px-4">
                      {String(index + 1).padStart(2, "0")}
                    </td>
                    <td className="py-4 px-4">{row.month || "-"}</td>
                    <td className="py-4 px-4">
                      {formatCurrency(row.monthly_turnover)}
                    </td>
                    <td className="py-4 px-4">
                      {Number(row.pool_percentage || 0)}%
                    </td>
                    <td className="py-4 px-4">
                      {formatCurrency(row.royalty_pool)}
                    </td>
                    <td className="py-4 px-4">
                      {row.eligible_users_count ?? 0}
                    </td>
                    <td className="py-4 px-4">{formatCurrency(row.earned)}</td>
               <td
  className={`py-4 px-4 font-semibold ${
    row.status === "paid"
      ? "text-green-600"
      : "text-amber-600"
  }`}
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
  );
}
