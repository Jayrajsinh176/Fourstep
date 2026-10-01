import React, { useEffect, useMemo, useState } from "react";
import Sidebar from "../components/Sidebar";
import Navbar from "../components/Navbar";
import { requestMemberApi } from "../utils/apiClient";

import {
  FaLink,
  FaIndianRupeeSign,
  FaUsers,
  FaUserCheck,
  FaCodeBranch,
} from "react-icons/fa6";
import { FiCopy } from "react-icons/fi";
import { LuWallet } from "react-icons/lu";
import { CiMedal } from "react-icons/ci";
import { HiOutlineDocumentText } from "react-icons/hi";
import { GoArrowUpRight } from "react-icons/go";
import { MdOutlineManageAccounts } from "react-icons/md";
import { GiAchievement } from "react-icons/gi";

const ACTIVATION_WINDOW_MS = 30 * 24 * 60 * 60 * 1000;

const DEFAULT_STATS = {
  total_team: 0,
  total_register_team: 0,
  total_active_team: 0,
  total_manager_left: 0,
  total_manager_right: 0,
  id_position_step: 0,
  leadership_rank: "N/A",
  rank_with_reward: "N/A",
  repurchase_balance: 0,
  consistency_balance: 0,
  cashback_balance: 0,
  earning_balance: 0,
  daily_earn: 0,
  // direct_id: 0,
  // direct_branch: 0,
  purchase_balance: 0,
  purchase_credit: 0,
  purchase_debit: 0,
  total_left_right_earn: 0,
};

const STEP_DETAILS = {
  1: { bv: 125, per_cycle_capping: 500 },
  2: { bv: 250, per_cycle_capping: 1000 },
  3: { bv: 500, per_cycle_capping: 2000 },
  4: { bv: 1000, per_cycle_capping: 5000 },
};

function getStoredMemberData() {
  try { return JSON.parse(localStorage.getItem("memberData") || "{}"); }
  catch { return {}; }
}
function saveMemberData(data) {
  localStorage.setItem("memberData", JSON.stringify(data));
}
function toNumber(value) {
  const num = Number(value);
  return Number.isFinite(num) ? num : 0;
}
function getStepFromPackageId(packageId) {
  const match = String(packageId || "").match(/step[-_]?(\d+)/i);
  return match ? Number(match[1]) : 0;
}
function getMemberStep(member) {
  return toNumber(
    member?.selected_package_step ??
    member?.package_step ??
    member?.step_level ??
    member?.current_step ??
    getStepFromPackageId(member?.selected_package_id)
  );
}
function isActivated(member) {
  const status = String(member?.status ?? "").toLowerCase().trim();
  const step = getMemberStep(member);
  return (
    ["1", "true", "active", "activated", "approved"].includes(status) ||
    step > 0 ||
    !!member?.activation_date
  );
}
function formatCountdown(ms) {
  if (ms <= 0) return "Expired";
  const totalSeconds = Math.floor(ms / 1000);
  const days    = Math.floor(totalSeconds / 86400);
  const hours   = Math.floor((totalSeconds % 86400) / 3600);
  const minutes = Math.floor((totalSeconds % 3600) / 60);
  const seconds = totalSeconds % 60;
  return `${String(days).padStart(2,"0")}d ${String(hours).padStart(2,"0")}h ${String(minutes).padStart(2,"0")}m ${String(seconds).padStart(2,"0")}s`;
}
function formatCurrency(value) {
  return `₹${new Intl.NumberFormat("en-IN", {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
  }).format(toNumber(value))}`;
}

// ─── Stat Card ────────────────────────────────────────────────
function StatCard({ title, amount, note, icon, color }) {
  return (
    <div className={`p-4 rounded-xl text-white shadow-md ${color} flex flex-col gap-2 min-h-[140px]`}>
      <div className="bg-white/20 w-10 h-10 rounded-lg flex items-center justify-center text-lg shrink-0">
        {icon}
      </div>
      <div className="flex-1 min-w-0">
        <p className="text-[11px] opacity-80 leading-tight">{title}</p>
        <h2 className="text-xl font-bold mt-0.5 break-words leading-tight">{amount}</h2>
        <p className="text-[10px] opacity-60 mt-1 leading-tight">{note}</p>
      </div>
    </div>
  );
}

// ─── ID Position Card — FIXED mobile layout ───────────────────
function IdPositionCard({ step, dailyEarn, totalEarn, icon, color }) {
  const safeStep = Math.min(Math.max(toNumber(step), 0), 6);
  const stepInfo = STEP_DETAILS[safeStep];

  return (
    <div className={`p-4 rounded-xl text-white shadow-md ${color} flex flex-col min-h-[140px]`}>
      {/* Header row */}
      <div className="flex items-center gap-2 mb-3">
        <div className="bg-white/20 w-10 h-10 rounded-lg flex items-center justify-center text-lg shrink-0">
          {icon}
        </div>
        <div>
          <p className="text-[10px] opacity-75 leading-none">ID Position</p>
          <p className="text-lg font-bold leading-tight">
            {safeStep > 0 ? `Step ${safeStep}` : "N/A"}
          </p>
        </div>
      </div>

      {/* Stats — each on its own row, label left / value right */}
      {stepInfo ? (
        <div className="flex flex-col gap-1 mt-auto">
          <div className="flex justify-between items-center">
     <span className="text-[10px] opacity-70">BV</span>
<span className="text-[11px] font-semibold">{stepInfo.bv} BV</span>
          </div>
          <div className="flex justify-between items-center">
            <span className="text-[10px] opacity-70">Per Cycle</span>
            <span className="text-[11px] font-semibold">{formatCurrency(stepInfo.per_cycle_capping)}</span>
          </div>
          
          
        </div>
      ) : (
        <p className="text-[11px] opacity-70 mt-auto">No step activated yet</p>
      )}
    </div>
  );
}

// ─── Referral Card ────────────────────────────────────────────
function ReferralCard({ title, link, onCopy }) {
  return (
    <div className="bg-white p-3 rounded-xl shadow-sm flex justify-between items-center gap-2">
      <div className="flex gap-2 min-w-0 items-start">
        <FaLink className="text-blue-500 mt-0.5 shrink-0 text-xs" />
        <div className="min-w-0">
          <p className="text-gray-500 text-xs font-medium">{title}</p>
          <p className="text-[11px] text-gray-600 truncate">{link}</p>
        </div>
      </div>
      <button
        onClick={onCopy}
        className="bg-gray-100 hover:bg-gray-200 p-2 rounded-md shrink-0 transition-colors"
        title="Copy link"
      >
        <FiCopy className="text-xs" />
      </button>
    </div>
  );
}

// ─── Purchase Balance Card ────────────────────────────────────
function PurchaseBalanceCard({ balance, total_credit, total_debit, color }) {
  return (
    <div className={`p-4 rounded-xl text-white shadow-md ${color} flex flex-col min-h-[140px]`}>
      <div className="flex items-center gap-2 mb-auto">
        <div className="bg-white text-[#B74331] w-10 h-10 rounded-lg flex items-center justify-center text-base font-bold shrink-0">
          ₹
        </div>
        <div>
          <p className="text-[10px] opacity-70 uppercase tracking-wide">Purchase Wallet</p>
          <h2 className="text-lg font-bold leading-tight">{formatCurrency(balance)}</h2>
        </div>
      </div>
      <div className="border-t border-white/20 pt-2 mt-3 grid grid-cols-2 gap-1">
        <div>
          <p className="text-[9px] opacity-60 uppercase tracking-wide">Credit</p>
          <p className="text-sm font-semibold">{formatCurrency(total_credit)}</p>
        </div>
        <div>
          <p className="text-[9px] opacity-60 uppercase tracking-wide">Debit</p>
          <p className="text-sm font-semibold">{formatCurrency(total_debit)}</p>
        </div>
      </div>
    </div>
  );
}

// ─── Direct ID / Branch Card ──────────────────────────────────
// function DirectIdBranchCard({ direct_id, direct_branch, color }) {
//   return (
//     <div className={`p-4 rounded-xl text-white shadow-md ${color} flex flex-col min-h-[140px]`}>
//       <div className="bg-white/20 w-10 h-10 rounded-lg flex items-center justify-center text-lg shrink-0 mb-2">
//         <FaCodeBranch />
//       </div>
//       <div className="grid grid-cols-2 gap-1 flex-1">
//         <div className="flex flex-col justify-center">
//           <p className="text-[10px] opacity-70">Direct ID</p>
//           <h2 className="text-2xl font-bold leading-tight">{direct_id}</h2>
//           <p className="text-[9px] opacity-50">Sponsored</p>
//         </div>
//         <div className="flex flex-col justify-center border-l border-white/20 pl-2">
//           <p className="text-[10px] opacity-70">Branch</p>
//           <h2 className="text-2xl font-bold leading-tight">{direct_branch}</h2>
//           <p className="text-[9px] opacity-50">Branches</p>
//         </div>
//       </div>
//     </div>
//   );
// }

// ─── Earning Balance Card — FIXED, no redundant label ─────────
function EarningBalanceCard({ earning_balance, color }) {
  return (
    <div className={`p-4 rounded-xl text-white shadow-md ${color} flex flex-col gap-2 min-h-[140px]`}>
      <div className="bg-white/20 w-10 h-10 rounded-lg flex items-center justify-center text-lg shrink-0">
        <FaIndianRupeeSign />
      </div>

      <div className="flex-1 min-w-0">
        <p className="text-[11px] opacity-80 leading-tight">
          Total Earning
        </p>

        <h2 className="text-xl font-bold mt-0.5 break-words leading-tight">
          {formatCurrency(earning_balance)}
        </h2>

        <p className="text-[10px] opacity-60 mt-1 leading-tight">
          Total earning wallet
        </p>
      </div>
    </div>
  );
}

// ─── Main Dashboard ───────────────────────────────────────────
export default function Dashboard() {
  const [memberData, setMemberData] = useState(getStoredMemberData());
  const [stats, setStats]           = useState(DEFAULT_STATS);
  const [countdown, setCountdown]   = useState("");

  const memberUserId = memberData?.user_id || "";
  const welcomeName  = memberData?.fullname || memberUserId || "Member";
  const customerId   = memberUserId ? `MLM-${memberUserId}` : "MLM-00000";
  const memberStep   = useMemo(() => getMemberStep(memberData), [memberData]);
  const safeStep     = memberData?.package_step ?? 0;
  const activeStatus = useMemo(() => isActivated(memberData), [memberData]);

  function getActivationDeadline(member) {
    const createdAt = member?.created_at;
    if (createdAt) {
      const t = new Date(createdAt).getTime();
      if (!Number.isNaN(t)) return t + ACTIVATION_WINDOW_MS;
    }
    return Date.now() + ACTIVATION_WINDOW_MS;
  }

  useEffect(() => {
    if (!memberUserId) return;
    const fetch = async () => {
      try {
        const res = await requestMemberApi("/member/dashboard", {
          headers: { "X-Auth-Member": memberUserId },
        });
        if (!res?.ok) return;
        const data = res.data || {};
        const updated = {
          ...memberData,
          selected_package_id:   data.selected_package_id   ?? memberData?.selected_package_id,
          selected_package_step: data.selected_package_step ?? memberData?.selected_package_step,
          package_step:          data.package_step          ?? memberData?.package_step,
          step_level:            data.step_level            ?? memberData?.step_level,
          current_step:          data.current_step          ?? memberData?.current_step,
          status:                data.status                ?? memberData?.status,
          activation_date:       data.activation_date       ?? memberData?.activation_date,
          created_at:            data.created_at            ?? memberData?.created_at,
          fullname:              data.fullname              ?? memberData?.fullname,
          user_id:               data.user_id               ?? memberData?.user_id,
        };
        setMemberData(updated);
        saveMemberData(updated);
      } catch (e) { console.error(e); }
    };
    fetch();
  }, [memberUserId]);

  useEffect(() => {
    if (!memberUserId) return;
    if (activeStatus) {
      setCountdown(memberStep > 0 ? `Step ${memberStep}` : "Active");
      return;
    }
    const deadline = getActivationDeadline(memberData);
    const update = () => setCountdown(formatCountdown(deadline - Date.now()));
    update();
    const iv = setInterval(update, 1000);
    return () => clearInterval(iv);
  }, [memberUserId, activeStatus, memberData, memberStep]);

  useEffect(() => {
    if (!memberUserId) return;
    const load = async () => {
      try {
        const res = await requestMemberApi("/member/dashboard-stats", {
          headers: { "X-Auth-Member": memberUserId },
        });
        if (!res?.ok) return;
        const data = res?.data?.data || {};
        setStats({
          total_team:            toNumber(data.total_team),
          total_register_team:   toNumber(data.total_register_team ?? data.total_team),
          total_active_team:     toNumber(data.total_active_team),
          total_manager_left:    toNumber(data.total_manager_left),
          total_manager_right:   toNumber(data.total_manager_right),
          id_position_step:      toNumber(data.id_position_step || data.package_step || data.step_level),
          leadership_rank:       data.leadership_rank  || "N/A",
          rank_with_reward:      data.rank_with_reward || "N/A",
          repurchase_balance:    toNumber(data.repurchase_balance),
          consistency_balance:   toNumber(data.consistency_balance),
          cashback_balance:      toNumber(data.cashback_balance),
          earning_balance:       toNumber(data.earning_balance),
          total_left_right_earn: toNumber(data.total_left_right_earn),
          daily_earn:            toNumber(data.daily_earn),
          // direct_id:             toNumber(data.direct_id),
          // direct_branch:         toNumber(data.direct_branch),
          purchase_balance:      toNumber(data.purchase_balance),
          purchase_credit:       toNumber(data.purchase_credit),
          purchase_debit:        toNumber(data.purchase_debit),
        });
      } catch (e) { console.error(e); }
    };
    load();
    const iv = setInterval(load, 30000);
    return () => clearInterval(iv);
  }, [memberUserId]);

  const signupBaseUrl     = `${window.location.origin}/member/signup`;
  const leftReferralLink  = `${signupBaseUrl}?sponsorId=${memberUserId}&position=left`;
  const rightReferralLink = `${signupBaseUrl}?sponsorId=${memberUserId}&position=right`;

  const copyToClipboard = async (text) => {
    try { await navigator.clipboard.writeText(text); alert("Link copied!"); }
    catch (e) { console.error(e); }
  };

  const topCards = [
     {
      type:  "id_position",
      step:  memberStep,
      icon:  <HiOutlineDocumentText />,
      color: "bg-[linear-gradient(135deg,#B74331,#8A3225)]",
    },
     {
      title:  "Total Register Team/Group",
      amount: stats.total_register_team,
      note:   "Registered members",
      icon:   <FaUsers />,
      color:  "bg-[linear-gradient(135deg,#45B0D7,#268CB1)]",
    },
        {
      title:  "Total Active Team/Group",
      amount: stats.total_active_team,
      note:   "Members with active status",
      icon:   <FaUserCheck />,
      color:  "bg-[linear-gradient(135deg,#2ecc89,#1a9e68)]",
    },
    {
      title:  "Total Team/Group",
      amount: stats.total_team,
      note:   "Total members in your network",
      icon:   <FaUsers />,
      color:  "bg-[linear-gradient(135deg,#3483D2,#2262A1)]",
    },
    {
      title:  "Manager (L / R)",
      amount: `${stats.total_manager_left} / ${stats.total_manager_right}`,
      note:   "Team managers by side",
      icon:   <MdOutlineManageAccounts />,
      color:  "bg-[linear-gradient(135deg,#2DA5D2,#2874BE)]",
    },
    {
      title:  "Leadership Rank",
      amount: stats.leadership_rank,
      note:   "Current leadership rank",
      icon:   <GiAchievement />,
      color:  "bg-[linear-gradient(135deg,#2A9EC9,#266DB2)]",
    },
    {
      title:  "Rank With Reward",
      amount: stats.rank_with_reward,
      note:   "Reward linked to rank",
      icon:   <CiMedal />,
      color:  "bg-[linear-gradient(135deg,#9B4032,#2864A3)]",
    },
    {
      title:  "Group Purchase Balance",
      amount: formatCurrency(stats.repurchase_balance),
      note:   "Purchase wallet",
      icon:   <LuWallet />,
      color:  "bg-[linear-gradient(135deg,#3483D2,#2262A1)]",
    },
    {
      title:  "Consistency Balance",
      amount: formatCurrency(stats.consistency_balance),
      note:   "Consistency wallet",
      icon:   <GoArrowUpRight />,
      color:  "bg-[linear-gradient(135deg,#45B0D7,#268CB1)]",
    },
    {
  title:  "Cashback Wallet",
  amount: formatCurrency(stats.cashback_balance),
  note:   "Cashback wallet",
  icon:   <LuWallet />,
  color:  "bg-[linear-gradient(135deg,#2ecc89,#1a9e68)]",
},
    {
      type:            "earning_balance",
      earning_balance: stats.earning_balance,
      color:           "bg-[linear-gradient(135deg,#2DA5D2,#2874BE)]",
    },
    // {
    //   type:          "direct_id_branch",
    //   direct_id:     stats.direct_id,
    //   direct_branch: stats.direct_branch,
    //   color:         "bg-[linear-gradient(135deg,#B74331,#8A3225)]",
    // },
    {
      type:         "purchase_balance",
      balance:      stats.purchase_balance,
      total_credit: stats.purchase_credit,
      total_debit:  stats.purchase_debit,
      color:        "bg-[linear-gradient(135deg,#7B3F8A,#4A2560)]",
    },
  ];
console.log("Dashboard Render", memberUserId);
  return (
    <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">
      <Sidebar />

      <div className="flex-1 min-w-0 flex flex-col">
        <Navbar />

        <div className="p-3 sm:p-4 lg:p-5">

          {/* Welcome */}
          <div className="bg-gradient-to-r from-blue-600 to-blue-500 text-white px-4 py-2.5 rounded-lg shadow text-sm font-medium truncate">
            Welcome back, {welcomeName} ({customerId})
          </div>

          {/* Activation status */}
          <div className="bg-gradient-to-r from-blue-600 to-blue-500 text-white px-4 py-2.5 rounded-lg shadow mt-2 flex justify-between flex-wrap gap-1 text-xs sm:text-sm">
            <span>
              {activeStatus
                ? `ID Active — Step ${safeStep}`
                : "ID Must Be Activated Within 30 Days"}
            </span>
            <span className="font-semibold tabular-nums">{countdown}</span>
          </div>

          {/* Referral links */}
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-2">
            <ReferralCard
              title="Left Referral"
              link={leftReferralLink}
              onCopy={() => copyToClipboard(leftReferralLink)}
            />
            <ReferralCard
              title="Right Referral"
              link={rightReferralLink}
              onCopy={() => copyToClipboard(rightReferralLink)}
            />
          </div>

          {/* Cards: 2 cols mobile → 3 cols md → 4 cols xl */}
          <div className="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-2 sm:gap-3 mt-3">
            {topCards.map((card, index) =>
              card.type === "id_position" ? (
                <IdPositionCard
                  key={`id-position-${index}`}
                  step={card.step}
                  dailyEarn={stats.daily_earn}
                  totalEarn={stats.total_left_right_earn}
                  icon={card.icon}
                  color={card.color}
                />
              ) : card.type === "earning_balance" ? (
                <EarningBalanceCard
                  key="earning-balance"
                  earning_balance={card.earning_balance}
                  color={card.color}
                />
              ) 
              // : card.type === "direct_id_branch" ? (
              //   <DirectIdBranchCard
              //     key="direct-id-branch"
              //     direct_id={card.direct_id}
              //     direct_branch={card.direct_branch}
              //     color={card.color}
              //   />
              // ) 
              : card.type === "purchase_balance" ? (
                <PurchaseBalanceCard
                  key="purchase-balance"
                  balance={card.balance}
                  total_credit={card.total_credit}
                  total_debit={card.total_debit}
                  color={card.color}
                />
              ) : (
                <StatCard key={card.title} {...card} />
              )
            )}
          </div>

        </div>
      </div>
    </div>
  );
}