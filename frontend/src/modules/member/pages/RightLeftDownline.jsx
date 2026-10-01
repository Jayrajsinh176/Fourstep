import Sidebar from "../components/Sidebar";
import Navbar from "../components/Navbar";
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

const toNumber = (value) => {
  const num = Number(value);
  return Number.isFinite(num) ? num : 0;
};

const resolveStep = (member) => {
  if (!member || typeof member !== "object") return 0;

  const possibleStep =
    member.current_step ??
    member.step_level ??
    member.package_step ??
    member.step ??
    0;

  const stepNumber = toNumber(possibleStep);

  if (stepNumber >= 1 && stepNumber <= 6) {
    return stepNumber;
  }

  const pv = toNumber(member.pv ?? member.current_pv ?? member.total_pv ?? 0);

  if (pv >= 4000) return 6;
  if (pv >= 2000) return 5;
  if (pv >= 1000) return 4;
  if (pv >= 500) return 3;
  if (pv >= 250) return 2;
  if (pv >= 125) return 1;

  return 0;
};

const getActivationText = (member) => {
  const isActive =
    Number(member?.status) === 1 ||
    String(member?.activation_status || "")
      .toLowerCase()
      .trim() === "activated";

  if (!isActive) {
    return "Inactive";
  }

  const step = resolveStep(member);

  if (step > 0) {
    return `Activated (${step} Step)`;
  }

  return "Activated";
};

export default function ReferralLeftRight({ side }) {
  const { rows, isLoading, error } = useReferralDownline();

  const safeRows = Array.isArray(rows) ? rows : [];
  const leftData = safeRows.filter((row) => row.sponsored_side === "L");
  const rightData = safeRows.filter((row) => row.sponsored_side === "R");
  const showSingleTable = side === "left" || side === "right";
  const singleTableTitle = side === "left" ? "Left Associates" : "Right Associates";
  const singleTableData = side === "left" ? leftData : rightData;

  return (
    <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">
      <Sidebar />

      <div className="flex-1 min-w-0 flex flex-col">
        <Navbar />

        <div className="text-center mt-6">
          <h1 className="text-xl font-bold text-[#B0422E]">
            {showSingleTable ? singleTableTitle : "Left-Right Downline"}
          </h1>
        </div>

        <div className="p-6">
          <div
            className={`bg-white rounded-2xl shadow-sm p-6 font-medium ${
              showSingleTable ? "" : "grid grid-cols-1 xl:grid-cols-2 gap-6"
            }`}
          >
            {isLoading && (
              <p className={`${showSingleTable ? "" : "xl:col-span-2"} text-center text-gray-500`}>
                Loading downline...
              </p>
            )}

            {!isLoading && error && (
              <p className={`${showSingleTable ? "" : "xl:col-span-2"} text-center text-red-500`}>
                {error}
              </p>
            )}

            {!isLoading && !error && showSingleTable && (
              <Table title={singleTableTitle} data={singleTableData} />
            )}

            {!isLoading && !error && !showSingleTable && (
              <>
                <Table title="Left Associates" data={leftData} />
                <Table title="Right Associates" data={rightData} />
              </>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}

function Table({ title, data = [] }) {
  return (
    <div>
      <h2 className="font-semibold mb-3">{title}</h2>

      <div className="overflow-x-auto">
        <table className="w-full min-w-175 text-sm">
          <thead>
            <tr className="bg-[#B0422E] text-white">
              <th className="py-3 px-4 text-left rounded-l-xl">Date</th>
              <th className="py-3 px-4 text-left">ID</th>
              <th className="py-3 px-4 text-left">Name</th>
              <th className="py-3 px-4 text-left">City</th>
              <th className="py-3 px-4 text-left">State</th>
              <th className="py-3 px-4 text-left rounded-r-xl">Activation</th>
            </tr>
          </thead>

          <tbody>
            {Array.isArray(data) && data.length > 0 ? (
              data.map((member, index) => (
                <tr key={member.id || member.user_id || index} className="border-b hover:bg-gray-50">
                  <td className="py-3 px-4">
                    {formatDate(member.created_at)}
                  </td>
                  <td className="py-3 px-4">
                    <div className="font-semibold text-gray-900">{member.user_id || "--"}</div>
                  </td>
                  <td className="py-3 px-4">
                    <div className="text-gray-900">{member.fullname || member.name || "--"}</div>
                  </td>
                  <td className="py-3 px-4">{member.city || "--"}</td>
                  <td className="py-3 px-4">{member.state || "--"}</td>
                  <td className="py-3 px-4">
                    {getActivationText(member)}
                  </td>
                </tr>
              ))
            ) : (
              <tr>
                <td colSpan="6" className="text-center py-4 text-gray-500">
                  No Members Found
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>
    </div>
  );
}