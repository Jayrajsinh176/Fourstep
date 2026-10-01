import { useEffect, useMemo, useState } from "react";
import { CalendarDays, CheckCircle2, CircleDashed, IndianRupee, UserCircle2 } from "lucide-react";
import Sidebar from "../components/Sidebar";
import Navbar from "../components/Navbar";
import { requestMemberApi } from "../utils/apiClient";

const BONUS_FILTERS = [
  "All",
  "Group Builtup Bonus",
  // "Diwali Celebration Bonus",
  "Business Monitoring Bonus",
  "Repurchase Bonus",
  "Consistency Bonus",
  "Royalty Club Bonus",
  "Leadership Rank Bonus",
  "Family Saver Bonus",
  // "Branch Turnover Bonus",
];

const formatDate = (dateValue) => {
  if (!dateValue) return "-";
  const date = new Date(dateValue);
  if (Number.isNaN(date.getTime())) return "-";

  const day = String(date.getDate()).padStart(2, "0");
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const year = date.getFullYear();
  return `${day}-${month}-${year}`;
};

const formatMoney = (value) => {
  const amount = Number(value || 0);
  return `₹${new Intl.NumberFormat("en-IN", {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
  }).format(amount)}`;
};

const toNumber = (value) => {
  const parsed = Number(value ?? 0);
  return Number.isFinite(parsed) ? parsed : 0;
};

const toStatusLabel = (status) => {
  if (!status) return "Pending";
  const asText = String(status);
  return asText.charAt(0).toUpperCase() + asText.slice(1);
};

const makeDetailText = (parts) => parts.filter(Boolean).join(" | ") || "-";

const normalizeRows = (bonusName, rows) => {
  const safeRows = Array.isArray(rows) ? rows : [];

  return safeRows.map((row, index) => {
    if (bonusName === "Group Builtup Bonus") {
      return {
        key: `${bonusName}-${row.id ?? row.transaction_id ?? index}`,
        bonus_name: bonusName,
        date: row.date,
        transaction_id: row.transaction_id || "-",
        bonus_month: row.group_period || "-",
        earned_bonus: toNumber(row.earned),
        status: toStatusLabel(row.status),
        details: makeDetailText([
          `Group Amount ${formatMoney(row.group_amount)}`,
          `Wallet ${String(row.status || "").toLowerCase() === "paid" ? "Credited" : "On Hold"}`,
        ]),
      };
    }

    if (bonusName === "Diwali Celebration Bonus") {
      return {
        key: `${bonusName}-${row.id ?? row.transaction_id ?? index}`,
        bonus_name: bonusName,
        date: row.date,
        transaction_id: row.transaction_id || "-",
        bonus_month: row.festival_year || "-",
        earned_bonus: toNumber(row.earned),
        status: toStatusLabel(row.status),
        details: makeDetailText([
          `Lapsed Turnover ${formatMoney(row.lapsed_matching_turnover)}`,
          `${toNumber(row.percentage)}%`,
        ]),
      };
    }

    if (bonusName === "Business Monitoring Bonus") {
      return {
        key: `${bonusName}-${row.id ?? row.transaction_id ?? index}`,
        bonus_name: bonusName,
        date: row.cycle_date,
        transaction_id: row.transaction_id || "-",
        bonus_month: row.monitoring_period || "-",
        earned_bonus: toNumber(row.bonus_amount ?? row.earned),
        status: toStatusLabel(row.status),
        details: makeDetailText([
          `Direct ${row.downline_user_id || "-"}`,
          `Team Income ${formatMoney(row.direct_team_income ?? row.matching_income)}`,
        ]),
      };
    }

    if (bonusName === "Repurchase Bonus") {
      return {
        key: `${bonusName}-${row.id ?? row.transaction_id ?? index}`,
        bonus_name: bonusName,
        date: row.date,
        transaction_id: row.transaction_id || "-",
        bonus_month: row.month || "-",
        earned_bonus: toNumber(row.earned),
        status: toStatusLabel(row.status),
        details: makeDetailText([
          `Repurchase ${formatMoney(row.repurchase_amount)}`,
          `${toNumber(row.percentage)}%`,
        ]),
      };
    }

    if (bonusName === "Consistency Bonus") {
      return {
        key: `${bonusName}-${row.id ?? row.transaction_id ?? index}`,
        bonus_name: bonusName,
        date: row.date,
        transaction_id: row.transaction_id || "-",
        bonus_month: row.month || "-",
        earned_bonus: toNumber(row.earned),
        status: toStatusLabel(row.status),
        details: makeDetailText([
          `Qualified Purchase ${formatMoney(row.repurchase_amount)}`,
          `${toNumber(row.percentage)}%`,
        ]),
      };
    }

    if (bonusName === "Royalty Club Bonus") {
      return {
        key: `${bonusName}-${row.id ?? row.transaction_id ?? index}`,
        bonus_name: bonusName,
        date: row.date,
        transaction_id: row.transaction_id || "-",
        bonus_month: row.month || "-",
        earned_bonus: toNumber(row.earned),
        status: toStatusLabel(row.status),
        details: makeDetailText([
          `Turnover ${formatMoney(row.monthly_turnover)}`,
          `Pool ${toNumber(row.pool_percentage)}%`,
        ]),
      };
    }

    if (bonusName === "Leadership Rank Bonus") {
      return {
        key: `${bonusName}-${row.id ?? row.transaction_id ?? index}`,
        bonus_name: bonusName,
        date: row.date,
        transaction_id: row.transaction_id || "-",
        bonus_month: row.rank_achieved_date || "-",
        earned_bonus: toNumber(row.earned),
        status: toStatusLabel(row.status),
        details: makeDetailText([
          `Rank ${row.rank_name || "-"}`,
          `Volume ${formatMoney(row.business_volume)}`,
        ]),
      };
    }

    if (bonusName === "Family Saver Bonus") {
      return {
        key: `${bonusName}-${row.id ?? row.transaction_id ?? index}`,
        bonus_name: bonusName,
        date: row.date,
        transaction_id: row.transaction_id || "-",
        bonus_month: row.month || "-",
        earned_bonus: toNumber(row.earned),
        status: toStatusLabel(row.status),
        details: makeDetailText([
          `Family ${row.family_id || "-"}`,
          `Deceased ${row.deceased_member || "-"}`,
        ]),
      };
    }

    // Branch Turnover Bonus is disabled across the project.
    // return {
    //   key: `${bonusName}-${row.id ?? row.transaction_id ?? index}`,
    //   bonus_name: "Branch Turnover Bonus",
    //   date: row.date,
    //   transaction_id: row.transaction_id || "-",
    //   bonus_month: row.month || "-",
    //   earned_bonus: toNumber(row.earned),
    //   status: toStatusLabel(row.status),
    //   details: makeDetailText([
    //     `Branch ${row.branch_name || "-"}`,
    //     `Turnover ${formatMoney(row.turnover)}`,
    //   ]),
    // };
    return null;
  }).filter(Boolean);
};

export default function IncomeReport() {
  const [rows, setRows] = useState([]);
  const [member, setMember] = useState(null);
  const [selectedBonus, setSelectedBonus] = useState("All");
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState("");

  const [fromDate, setFromDate] = useState("");
  const [toDate, setToDate] = useState("");
  const [appliedFromDate, setAppliedFromDate] = useState("");
  const [appliedToDate, setAppliedToDate] = useState("");

  const memberUserId = useMemo(() => {
    try {
      const memberData = JSON.parse(localStorage.getItem("memberData") || "{}") || {};
      return memberData?.user_id || "";
    } catch {
      return "";
    }
  }, []);

  useEffect(() => {
    let isMounted = true;

    const loadReport = async () => {
      try {
        if (isMounted) {
          setError("");
        }

        if (!memberUserId) {
          throw new Error("Please sign in to view income report.");
        }

        const memberResponse = await requestMemberApi("/member/income-report", {
          method: "GET",
          headers: {
            Accept: "application/json",
            "X-Auth-Member": memberUserId,
          },
        });

        if (!memberResponse.ok) {
          throw new Error(memberResponse.data?.message || "Unable to load income report.");
        }

        const [
          groupBuiltupRes,
          // diwaliRes,
          businessMonitoringRes,
          repurchaseRes,
          consistencyRes,
          royaltyRes,
          leadershipRes,
          familyRes,
          // branchRes, // Branch Turnover Bonus disabled
        ] = await Promise.allSettled([
          requestMemberApi(`/bonuses/group-builtup?user_id=${encodeURIComponent(memberUserId)}`),
          // requestMemberApi(`/bonuses/diwali?user_id=${encodeURIComponent(memberUserId)}`),
          requestMemberApi(`/bonuses/business-monitoring?sponsor_user_id=${encodeURIComponent(memberUserId)}`),
          requestMemberApi(`/bonuses/loyalty?user_id=${encodeURIComponent(memberUserId)}&type=monthly`),
          requestMemberApi(`/bonuses/loyalty?user_id=${encodeURIComponent(memberUserId)}&type=consistency`),
          requestMemberApi(`/bonuses/royalty-club?user_id=${encodeURIComponent(memberUserId)}`),
          requestMemberApi(`/bonuses/leadership-rank?user_id=${encodeURIComponent(memberUserId)}`),
          requestMemberApi(`/bonuses/family-saver?user_id=${encodeURIComponent(memberUserId)}`),
          // requestMemberApi("/bonuses/branch-turnover"), // Branch Turnover Bonus disabled
        ]);

        const payload = memberResponse.data?.data || {};

        const normalized = [];
        const errors = [];

        const collectRows = (result, bonusName) => {
          if (result.status !== "fulfilled") {
            errors.push(`${bonusName}: request failed`);
            return;
          }

          if (!result.value?.ok) {
            errors.push(`${bonusName}: ${result.value?.data?.message || "unable to load"}`);
            return;
          }

          normalized.push(...normalizeRows(bonusName, result.value?.data?.data));
        };

        collectRows(groupBuiltupRes, "Group Builtup Bonus");
        // collectRows(diwaliRes, "Diwali Celebration Bonus");
        collectRows(businessMonitoringRes, "Business Monitoring Bonus");
        collectRows(repurchaseRes, "Repurchase Bonus");
        collectRows(consistencyRes, "Consistency Bonus");
        collectRows(royaltyRes, "Royalty Club Bonus");
        collectRows(leadershipRes, "Leadership Rank Bonus");
        collectRows(familyRes, "Family Saver Bonus");
        

        normalized.sort((a, b) => {
          const left = a?.date ? new Date(a.date).getTime() : 0;
          const right = b?.date ? new Date(b.date).getTime() : 0;
          return right - left;
        });

        if (isMounted) {
          setRows(normalized);
          setMember(payload?.member || null);
          if (errors.length > 0) {
            setError(`Some bonus data could not be loaded: ${errors.join(", ")}`);
          }
        }
      } catch (loadError) {
        if (isMounted) {
          setRows([]);
          setMember(null);
          setError(loadError.message || "Unable to load income report.");
        }
      } finally {
        if (isMounted) {
          setIsLoading(false);
        }
      }
    };

    loadReport();
    const refreshId = setInterval(loadReport, 20000);

    return () => {
      isMounted = false;
      clearInterval(refreshId);
    };
  }, [memberUserId]);

  const accountIsActive = String(member?.account_status || "").toLowerCase() === "active";

  const filteredRows = rows.filter((row) => {
    const rowDate = row.date ? new Date(row.date) : null;

    const bonusMatch =
      selectedBonus === "All" || row.bonus_name === selectedBonus;

    let dateMatch = true;

    if (appliedFromDate && appliedToDate) {
      dateMatch =
        new Date(appliedFromDate) <= rowDate &&
        new Date(appliedToDate) >= rowDate;
    }

    return bonusMatch && dateMatch;
  });

  const totalEarned = filteredRows.reduce((sum, row) => sum + toNumber(row.earned_bonus), 0);

  return (
    <div className="flex flex-col lg:flex-row bg-slate-100 min-h-screen">
      <Sidebar />

      <div className="flex-1 min-w-0 flex flex-col overflow-x-hidden">
        <Navbar />

        <div className="p-4 sm:p-6 lg:p-8">
          <h1 className="text-2xl sm:text-3xl font-bold text-[#b0422e] text-center mb-6 sm:mb-8">
            Income Report
          </h1>

          <div className="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mb-6">
            <div className="flex flex-wrap lg:flex-nowrap gap-3 items-center">

              {/* Bonus Dropdown */}
              <select
                value={selectedBonus}
                onChange={(event) => setSelectedBonus(event.target.value)}
                className="px-4 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-700 font-semibold w-full lg:w-auto"
              >
                {BONUS_FILTERS.map((bonusName) => (
                  <option key={bonusName} value={bonusName}>
                    {bonusName}
                  </option>
                ))}
              </select>

              {/* From Date */}
              <input
                type="date"
                value={fromDate}
                onChange={(e) => setFromDate(e.target.value)}
                className="px-4 py-2.5 rounded-xl border border-slate-300 w-full lg:w-auto"
              />

              {/* To Date */}
              <input
                type="date"
                value={toDate}
                onChange={(e) => setToDate(e.target.value)}
                className="px-4 py-2.5 rounded-xl border border-slate-300 w-full lg:w-auto"
              />

              {/* Apply Button */}
              <button
                onClick={() => {
                  if (!fromDate || !toDate) {
                    alert("Please select both From and To dates");
                    return;
                  }

                  if (new Date(fromDate) > new Date(toDate)) {
                    alert("From date cannot be greater than To date");
                    return;
                  }

                  setAppliedFromDate(fromDate);
                  setAppliedToDate(toDate);
                }}
                className="px-5 py-2.5 bg-[#b0422e] text-white rounded-xl font-semibold hover:bg-[#933826] w-full lg:w-auto"
              >
                Apply
              </button>

              {/* Reset Button */}
              <button
                onClick={() => {
                  setFromDate("");
                  setToDate("");
                  setAppliedFromDate("");
                  setAppliedToDate("");
                  setSelectedBonus("All");
                }}
                className="px-5 py-2.5 bg-gray-200 text-slate-700 rounded-xl font-semibold hover:bg-gray-300 w-full lg:w-auto"
              >
                Reset
              </button>

            </div>
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-12 gap-5 mb-6">
            <section className="lg:col-span-8 bg-white rounded-3xl shadow-sm p-4 sm:p-6 border border-slate-100">
              <div className="flex flex-col sm:flex-row sm:items-start gap-4 sm:gap-6">
                <div className="h-24 w-24 rounded-full overflow-hidden bg-slate-100 border border-slate-200 flex items-center justify-center shrink-0">
                  {member?.photo_url ? (
                    <img
                      src={member.photo_url}
                      alt="Profile"
                      className="h-full w-full object-cover"
                    />
                  ) : (
                    <UserCircle2 className="h-14 w-14 text-slate-400" />
                  )}
                </div>

                <div className="min-w-0 flex-1">
                  <p className="text-sm text-slate-500 mb-1">Member Name</p>
                  <p className="text-xl font-bold text-slate-800 wrap-break-word">
                    {member?.name || "-"}
                  </p>
                  <p className="text-xs text-slate-500 mt-1">Profile</p>

                  <div className="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                      <p className="text-xs uppercase tracking-wide text-slate-500">Member ID</p>
                      <p className="text-base font-semibold text-slate-800">{member?.user_id || "-"}</p>
                    </div>
                    <div>
                      <p className="text-xs uppercase tracking-wide text-slate-500">Activation Date</p>
                      <p className="text-base font-semibold text-slate-800 flex items-center gap-1">
                        <CalendarDays size={16} className="text-slate-500" />
                        {formatDate(member?.activation_date)}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </section>

            <section className="lg:col-span-4 bg-white rounded-3xl shadow-sm p-5 sm:p-6 border border-slate-100 flex flex-col justify-between">
              <div>
                <p className="text-sm text-slate-500 mb-1">Current Status</p>
                <div
                  className={`inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-semibold ${accountIsActive ? "bg-emerald-100 text-emerald-700" : "bg-amber-100 text-amber-700"
                    }`}
                >
                  {accountIsActive ? <CheckCircle2 size={16} /> : <CircleDashed size={16} />}
                  {member?.account_status || "Inactive"}
                </div>
              </div>

              <div className="mt-6 rounded-2xl bg-linear-to-r from-[#b0422e] to-[#d07848] text-white p-4">
                <p className="text-white/80 text-sm">Total Earned Bonus</p>
                <p className="text-2xl font-bold mt-1 flex items-center gap-1">
                  <IndianRupee size={22} />
                  {new Intl.NumberFormat("en-IN", {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 2,
                  }).format(totalEarned)}
                </p>
              </div>
            </section>
          </div>

          {isLoading && <p className="text-center text-slate-500 mb-4">Loading income report...</p>}
          {!isLoading && error && <p className="text-center text-red-500 mb-4">{error}</p>}

          <div className="bg-white rounded-3xl shadow-sm border border-slate-100 p-3 sm:p-5">
            <div className="overflow-x-auto">
              <table className="min-w-190 w-full text-sm">
                <thead>
                  <tr className="bg-[#b0422e] text-white">
                    <th className="px-4 py-3 text-left rounded-l-xl">Sr No</th>
                    <th className="px-4 py-3 text-left">Date</th>
                    <th className="px-4 py-3 text-left">Transaction ID</th>
                    <th className="px-4 py-3 text-left">Bonus Name</th>
                    <th className="px-4 py-3 text-left">Month</th>
                    <th className="px-4 py-3 text-left">Details</th>
                    <th className="px-4 py-3 text-left">Earned Bonus</th>
                    <th className="px-4 py-3 text-left rounded-r-xl">Status</th>
                  </tr>
                </thead>

                <tbody>
                  {!isLoading && filteredRows.length === 0 && (
                    <tr className="border-b border-slate-200">
                      <td className="px-4 py-4 text-center text-slate-500" colSpan={8}>
                        No income report records found
                      </td>
                    </tr>
                  )}

                  {filteredRows.map((row, index) => (
                    <tr key={row.key ?? index} className="border-b border-slate-100 last:border-b-0">
                      <td className="px-4 py-4 font-semibold text-slate-700">
                        {String(index + 1).padStart(2, "0")}
                      </td>
                      <td className="px-4 py-4 text-slate-600">{formatDate(row.date)}</td>
                      <td className="px-4 py-4 text-slate-700 font-medium">{row.transaction_id || "-"}</td>
                      <td className="px-4 py-4 text-slate-600">{row.bonus_name || "-"}</td>
                      <td className="px-4 py-4 text-slate-600">{row.bonus_month || "-"}</td>
                      <td className="px-4 py-4 text-slate-600">{row.details || "-"}</td>
                      <td className="px-4 py-4 text-[#b0422e] font-semibold">{formatMoney(row.earned_bonus)}</td>
                      <td className="px-4 py-4">
                        <span
                          className={`inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ${String(row.status || "").toLowerCase() === "paid"
                              ? "bg-blue-100 text-blue-700"
                              : String(row.status || "").toLowerCase() === "approved"
                                ? "bg-emerald-100 text-emerald-700"
                                : "bg-amber-100 text-amber-700"
                            }`}
                        >
                          {row.status || "Pending"}
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
    </div>
  );
}
