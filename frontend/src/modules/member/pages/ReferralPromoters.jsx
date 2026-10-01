import { useMemo, useState } from "react";
import Sidebar from "../components/Sidebar";
import Navbar from "../components/Navbar";
import ReferralTableCard from "../components/ReferralTableCard";
import useReferralDownline from "../hooks/useReferralDownline";

const formatDate = (dateValue) => {
  if (!dateValue) return "--";
  const date = new Date(dateValue);
  if (Number.isNaN(date.getTime())) return "--";
  const day = String(date.getDate()).padStart(2, "0");
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const year = date.getFullYear();
  return `${day}-${month}-${year}`;
};

const getDays = (joinDate, activationDate) => {
  if (!joinDate || !activationDate) return "--";
  const start = new Date(joinDate);
  const end = new Date(activationDate);
  if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime())) return "--";
  const diffInDays = Math.floor((end - start) / (1000 * 60 * 60 * 24));
  return diffInDays >= 0 ? diffInDays : "--";
};

// Maps package_step (1-4) to label
const PACKAGES = [
  { step: 1, label: "1 Step" },
  { step: 2, label: "2 Step" },
  { step: 3, label: "3 Step" },
  { step: 4, label: "4 Step" },
];

const getPackageLabel = (packageStep) => {
  if (!packageStep || packageStep === 0) return "--";
  const pkg = PACKAGES.find((p) => p.step === Number(packageStep));
  return pkg ? pkg.label : "--";
};

// Only direct referrals (sponsor_id === myId) are shown on this page
const getDirectReferrals = (allRows, rootId) => {
  if (!rootId || !allRows?.length) return [];
  return allRows.filter((row) => Number(row.sponsor_id) === Number(rootId));
};

export default function ReferralPromoters() {
  const { rows, isLoading, error } = useReferralDownline();
  const [selectedGen, setSelectedGen] = useState("all");

  const memberData = JSON.parse(localStorage.getItem("memberData") || "{}");
  const myId = memberData?.id;

  const directReferrals = useMemo(
    () => getDirectReferrals(rows, myId),
    [rows, myId]
  );

  const maxGen = directReferrals.length > 0 ? 1 : 0;

  const filteredRows = directReferrals;

  const columns = [
    {
      key: "id",
      header: "ID",
      render: (row) => (
        <div>
          <div>{row.user_id || "--"}</div>
          <div className="text-gray-500 text-xs">{row.fullname || "--"}</div>
        </div>
      ),
    },
    {
      key: "package",
      header: "Package",
      render: (row) => getPackageLabel(row.package_step),
    },
    {
      key: "days",
      header: "Days",
      render: (row) => getDays(row.created_at, row.activation_date),
    },
    {
      key: "state",
      header: "State",
      render: (row) => row.state || "--",
    },
    {
      key: "city",
      header: "City",
      render: (row) => row.city || "--",
    },
    {
      key: "sponsored_side",
      header: "Sponsored Side",
      render: (row) => row.sponsored_side || "--",
    },
    {
      key: "join_date",
      header: "Join Date",
      render: (row) => formatDate(row.created_at),
    },
    {
      key: "activation_date",
      header: "Activation Date",
      render: (row) => formatDate(row.activation_date),
    },
  ];

  return (
    <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">
      <Sidebar />

      <div className="flex-1 min-w-0 flex flex-col">
        <Navbar />

        <div className="p-6">
          <h1 className="text-xl font-semibold text-gray-800 mb-6">
            Referral Promoters
          </h1>

          <div className="bg-white rounded-xl shadow-sm">

            {/* Filter Bar */}
            <div className="flex flex-wrap items-center gap-3 px-6 pt-5 pb-4 border-b border-gray-100">
              <label className="text-sm text-gray-500 font-medium">
                Generation:
              </label>

              <select
                value={selectedGen}
                onChange={(e) => setSelectedGen(e.target.value)}
                className="text-sm border border-gray-300 rounded-md px-3 py-1.5 outline-none focus:border-[#bd422e] transition-colors bg-white text-gray-700"
              >
                <option value="all">All Generations</option>
                {Array.from({ length: maxGen }, (_, i) => i + 1).map((gen) => (
                  <option key={gen} value={gen}>
                    Generation {gen}{" "}
                    {gen === 1
                      ? "(My Direct Referrals)"
                      : `(Referrals of Gen ${gen - 1})`}
                  </option>
                ))}
              </select>

              <span className="ml-auto text-sm text-gray-400">
                {filteredRows.length} member{filteredRows.length !== 1 ? "s" : ""}
              </span>
            </div>

            <ReferralTableCard
              title=""
              columns={columns}
              rows={filteredRows}
              isLoading={isLoading}
              error={error}
              emptyMessage="No referral promoters found"
            />
          </div>
        </div>
      </div>
    </div>
  );
}