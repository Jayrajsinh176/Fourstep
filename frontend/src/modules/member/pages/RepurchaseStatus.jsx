import Sidebar from "../components/Sidebar";
import Navbar from "../components/Navbar";
import { IndianRupee } from "lucide-react";
import { useEffect, useMemo, useState } from "react";
import { requestMemberApi } from "../utils/apiClient";

const EMPTY_DATA = {
  month: "",
  minimum_purchase_required: 500,
  monthly_purchase_amount: 0,
  eligible: false,
  
  loyalty_bonus_eligible: false,
  royalty_eligible: false,
  bonus_percentage: 10,
  estimated_loyalty_bonus: 0,
  purchases: [],
};

const toNumber = (value) => {
  const normalized = Number(value);
  return Number.isFinite(normalized) ? normalized : 0;
};

const formatCurrency = (value) => {
  return new Intl.NumberFormat("en-IN", {
    style: "currency",
    currency: "INR",
    maximumFractionDigits: 2,
  }).format(toNumber(value));
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

const normalizeStatusPayload = (payload) => ({
  month: payload?.month || "",
  minimum_purchase_required: toNumber(payload?.minimum_purchase_required || 500),
  monthly_purchase_amount: toNumber(payload?.monthly_purchase_amount || 0),
  eligible: Boolean(payload?.eligible),
  cashback_eligible: Boolean(payload?.cashback_eligible),
  loyalty_bonus_eligible: Boolean(payload?.loyalty_bonus_eligible),
 
  bonus_percentage: toNumber(payload?.bonus_percentage || 10),
  estimated_loyalty_bonus: toNumber(payload?.estimated_loyalty_bonus || 0),
  purchases: Array.isArray(payload?.purchases) ? payload.purchases : [],
});

export default function RepurchaseStatus() {
  const [statusData, setStatusData] = useState(EMPTY_DATA);
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
          setError("Please sign in first.");
          setStatusData(EMPTY_DATA);
          setIsLoading(false);
        }
        return;
      }

      try {
        if (isMounted) {
          setError("");
        }

        const response = await requestMemberApi("/member/repurchase-status", {
          method: "GET",
          headers: {
            Accept: "application/json",
            "X-Auth-Member": memberUserId,
          },
        });

        if (!response.ok) {
          throw new Error(response.data?.message || "Unable to fetch Group Purchase data.");
        }

        if (isMounted) {
          setStatusData(normalizeStatusPayload(response.data?.data));
        }
      } catch (fetchError) {
        if (isMounted) {
          setError(fetchError.message || "Unable to fetch Group Purchase data.");
          setStatusData(EMPTY_DATA);
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

  return (
    <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">
      <Sidebar />

      <div className="flex-1 min-w-0 flex flex-col">
        <Navbar />

        <div className="text-center mt-6">
          <h1 className="text-3xl font-bold text-[#B0422E]">
            Group Purchase Status
          </h1>
        </div>

        {isLoading && <p className="text-center text-gray-500 mt-4">Loading status...</p>}
        {!isLoading && error && <p className="text-center text-red-500 mt-4">{error}</p>}

        <div className="p-6 space-y-6">

          <div className="bg-[#B0422E] rounded-2xl p-8 text-white shadow-md">

            <div className="flex items-center gap-4">
              <div className="bg-white/20 p-4 rounded-xl">
                <IndianRupee size={28} />
              </div>
              <div>
                <p className="uppercase text-semibold tracking-wide">
                  Monthly Purchase
                </p>
                <h2 className="text-3xl font-bold">{formatCurrency(statusData.monthly_purchase_amount)}</h2>
                <p className="text-sm text-white/80 mt-1">
                  Required: {formatCurrency(statusData.minimum_purchase_required)}+
                </p>
              </div>
            </div>

           <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">

  {/* Eligibility */}
  <div className="bg-[#CF9D94A1] rounded-xl p-6">
    <p className="uppercase text-xs tracking-wide text-white/70">
      Eligibility
    </p>
    <h3 className="text-2xl font-semibold mt-2">
      {statusData.eligible ? "Eligible" : "Not Eligible"}
    </h3>
  </div>

  {/* Repurchase Bonus */}
  <div className="bg-[#CF9D94A1] rounded-xl p-6">
    <p className="uppercase text-xs tracking-wide text-white/70">
      Repurchase Bonus
    </p>
    <h3 className="text-2xl font-semibold mt-2">
      {statusData.loyalty_bonus_eligible ? "Active" : "Inactive"}
    </h3>
  </div>

  {/* Purchase Status (NEW - IMPORTANT UX) */}
  <div className="bg-[#CF9D94A1] rounded-xl p-6">
    <p className="uppercase text-xs tracking-wide text-white/70">
      Purchase Status
    </p>
    <h3 className="text-2xl font-semibold mt-2">
      {statusData.monthly_purchase_amount >= statusData.minimum_purchase_required
        ? "Completed"
        : "Pending"}
    </h3>
  </div>

</div>

            <div className="mt-6 rounded-xl bg-[#CF9D94A1] p-6">
              <p className="uppercase text-semibold text-[#FFFFFFAD]">Estimated Loyalty Repurchase Bonus</p>
              <h3 className="text-2xl font-semibold mt-2">
                {formatCurrency(statusData.estimated_loyalty_bonus)} ({statusData.bonus_percentage}%)
              </h3>
            </div>

          </div>

          <div className="bg-white rounded-2xl shadow-sm p-6 overflow-x-auto">
            <table className="w-full min-w-175 text-sm">
              <thead>
                <tr className="bg-[#B0422E] text-white">
                  <th className="py-3 px-4 text-left rounded-l-xl">
                    Sr No
                  </th>
                  <th className="py-3 px-4 text-left">Date</th>
                  <th className="py-3 px-4 text-left">Invoice No</th>
                  <th className="py-3 px-4 text-left">Purchase Amount</th>
                  <th className="py-3 px-4 text-left">Requirement</th>
                  <th className="py-3 px-4 text-left rounded-r-xl">
                    Status
                  </th>
                </tr>
              </thead>

              <tbody>
                {!isLoading && statusData.purchases.length === 0 && (
                  <tr className="border-b">
                    <td className="py-4 px-4" colSpan={6}>No monthly purchases found</td>
                  </tr>
                )}

                {statusData.purchases.map((row, index) => (
                  <tr className="border-b" key={row.id ?? index}>
                    <td className="py-4 px-4">{String(index + 1).padStart(2, "0")}</td>
                    <td className="py-4 px-4">{formatDate(row.purchase_date)}</td>
                    <td className="py-4 px-4">{row.invoice_no || "-"}</td>
                    <td className="py-4 px-4">{formatCurrency(row.amount)}</td>
                    <td className="py-4 px-4">
                      {toNumber(row.amount) >= statusData.minimum_purchase_required
                        ? "Meets INR 500+ rule"
                        : "Below INR 500"}
                    </td>
                    <td className="py-4 px-4">{row.status || "approved"}</td>
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
