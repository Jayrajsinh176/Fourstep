import React, { useEffect, useMemo, useState } from "react";
import Sidebar from "../components/Sidebar";
import Navbar from "../components/Navbar";

const API_BASE_URL =
  import.meta.env.VITE_API_BASE_URL || "https://fourstepretail.com/api";

const PACKAGE_STEPS = [
  {
    step: 1,
    pairBv: 125,
    incomePerPair: 125,
    weeklyCap: 5000,
  },
  {
    step: 2,
    pairBv: 250,
    incomePerPair: 250,
    weeklyCap: 10000,
  },
  {
    step: 3,
    pairBv: 500,
    incomePerPair: 500,
    weeklyCap: 20000,
  },
  {
    step: 4,
    pairBv: 1000,
    incomePerPair: 1000,
    weeklyCap: 50000,
  },
];

const GroupBuiltupBonus = () => {
  const [rows, setRows] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState("");

  const memberUserId = useMemo(() => {
    try {
      const member = JSON.parse(localStorage.getItem("memberData") || "{}");

      console.log("memberData =", member);

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
          `${API_BASE_URL}/bonuses/group-builtup${query}`,
          {
            method: "GET",
            headers: {
              Accept: "application/json",
            },
          },
        );

        const data = await response.json();

        console.log("memberUserId =", memberUserId);
        console.log("API Response =", data);

        if (!response.ok) {
          throw new Error(
            data?.message || "Unable to fetch group builtup bonus data",
          );
        }

        if (isMounted) {
          console.log("Rows =", data?.data);

          setRows(Array.isArray(data?.data) ? data.data : []);
        }
      } catch (fetchError) {
        if (isMounted) {
          setError(
            fetchError.message || "Unable to fetch group builtup bonus data",
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

  const formatCurrency = (value) => {
    return new Intl.NumberFormat("en-IN", {
      style: "currency",
      currency: "INR",
      maximumFractionDigits: 2,
    }).format(Number(value || 0));
  };

  const walletText = (status) => {
    return String(status || "").toLowerCase() === "paid"
      ? "Credited"
      : "On Hold";
  };

  const walletClassName = (status) => {
    return String(status || "").toLowerCase() === "paid"
      ? "text-green-600 font-semibold"
      : "text-orange-600 font-semibold";
  };

  const formatStatus = (status) => {
    if (!status) return "Pending";
    return String(status).charAt(0).toUpperCase() + String(status).slice(1);
  };

  return (
    <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">
      <Sidebar />

      <div className="flex-1 min-w-0 flex flex-col">
        <Navbar />

        <div className="p-8 bg-gray-100 min-h-screen">
          <h1 className="text-3xl font-bold text-[#B0422E] text-center mb-8">
            Group Turnover Bonus
          </h1>
          {isLoading && (
            <p className="text-center text-gray-500 mb-4">Loading bonuses...</p>
          )}
          {error && <p className="text-center text-red-500 mb-4">{error}</p>}

          <div className="bg-white rounded-2xl shadow p-6 mb-6">
            <h2 className="text-xl font-bold text-[#B0422E] mb-4">
              Activation BV Table
            </h2>
            <div className="overflow-x-auto">
              <table className="w-full text-center">
                <thead>
                  <tr className="bg-[#B0422E] text-white">
                    <th className="p-3 rounded-l-xl">Step</th>
                    <th className="p-3">Pair BV</th>
                    <th className="p-3">Income / Pair</th>
                    <th className="p-3 rounded-r-xl">Maximum Weekly Income</th>
                  </tr>
                </thead>
                <tbody>
                  {PACKAGE_STEPS.map((item) => (
                    <tr
                      className="border-b hover:bg-gray-50 transition"
                      key={item.step}
                    >
                      <td className="py-4 px-3">{item.step}</td>
                      <td className="py-4 px-3">{item.pairBv}</td>
                      <td className="py-4 px-3">{formatCurrency(item.incomePerPair)}</td>
                      <td className="py-4 px-3">{formatCurrency(item.weeklyCap)}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>

          <div className="bg-white rounded-2xl shadow p-6">
            <div className="overflow-x-auto">
              <table className="w-full text-center">
                <thead>
                  <tr className="bg-[#B0422E] text-white">
                    <th className="py-4 px-3 rounded-l-xl">Sr No</th>
                    <th className="py-4 px-3">Date</th>
                    <th className="py-4 px-3">Closing Type</th>
                    <th className="py-4 px-3">Matching BV</th>
                    <th className="py-4 px-3">Income</th>
                    <th className="py-4 px-3">Status</th>
                    <th className="py-4 px-3 rounded-r-xl">Wallet</th>
                  </tr>
                </thead>

                <tbody>
                  {!isLoading && rows.length === 0 && (
                    <tr className="border-b">
                      <td className="p-4" colSpan={7}>
                        No Group Builtup bonus data found
                      </td>
                    </tr>
                  )}

                  {rows.map((row, index) => (
                    <tr className="border-b hover:bg-gray-50 transition" key={row.id ?? index}>
                      <td className="py-4 px-3">{String(index + 1).padStart(2, "0")}</td>

                      <td className="py-4 px-3">{formatDate(row.date)}</td>

                      <td className="py-4 px-3">Weekly Closing</td>

                      <td className="py-4 px-3">
                        {Number(row.group_amount || 0).toLocaleString("en-IN")} BV
                      </td>

                      <td className="py-4 px-3 font-medium">
                        {formatCurrency(row.earned)}
                      </td>

                      <td className="py-4 px-3">
                        <span
                          className={`px-3 py-1 rounded-full text-xs font-semibold ${row.status?.toLowerCase() === "paid"
                            ? "bg-green-100 text-green-700"
                            : "bg-orange-100 text-orange-700"
                            }`}
                        >
                          {formatStatus(row.status)}
                        </span>
                      </td>

                      <td className={`py-4 px-3 ${walletClassName(row.status)}`}>
                        {walletText(row.status)}
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

export default GroupBuiltupBonus;
