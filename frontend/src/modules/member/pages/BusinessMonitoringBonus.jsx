import Sidebar from "../components/Sidebar";
import Navbar from "../components/Navbar";
import React, { useEffect, useMemo, useState } from "react";

const API_BASE_URL =
  import.meta.env.VITE_API_BASE_URL || "https://fourstepretail.com/api";

const BusinessMonitoringBonus = () => {
  const [rows, setRows] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState("");

  const sponsorUserId = useMemo(() => {
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

        const query = sponsorUserId
          ? `?sponsor_user_id=${encodeURIComponent(sponsorUserId)}`
          : "";

        const response = await fetch(
          `${API_BASE_URL}/bonuses/business-monitoring${query}`,
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
            data?.message || "Unable to fetch business monitoring bonus data",
          );
        }

        if (isMounted) {
          setRows(Array.isArray(data?.data) ? data.data : []);
        }
      } catch (fetchError) {
        if (isMounted) {
          setError(
            fetchError.message ||
              "Unable to fetch business monitoring bonus data",
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
  }, [sponsorUserId]);

  const formatDate = (dateValue) => {
    if (!dateValue) return "-";
    const date = new Date(dateValue);
    if (Number.isNaN(date.getTime())) return "-";

    const day = String(date.getDate()).padStart(2, "0");
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const year = date.getFullYear();
    return `${day}-${month}-${year}`;
  };

  const formatMonitoringPeriod = (dateValue) => {
    if (!dateValue) return "-";
    const date = new Date(dateValue);
    if (Number.isNaN(date.getTime())) return "-";

    return date.toLocaleString("en-IN", {
      month: "short",
      year: "numeric",
    });
  };

  const formatCurrency = (value) => {
    return new Intl.NumberFormat("en-IN", {
      style: "currency",
      currency: "INR",
      maximumFractionDigits: 2,
    }).format(Number(value || 0));
  };

  const formatStatus = (status) => {
    if (!status) return "Pending";
    return String(status).charAt(0).toUpperCase() + String(status).slice(1);
  };

  const walletText = (status) => {
    const normalized = String(status || "").toLowerCase();
    return normalized === "paid" ? "Credited" : "On Hold";
  };

  const walletClassName = (status) => {
    const normalized = String(status || "").toLowerCase();
    return normalized === "paid"
      ? "text-blue-600 font-semibold"
      : "text-green-600 font-semibold";
  };

  return (
    <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">
      <Sidebar />

      <div className="flex-1 min-w-0 flex flex-col">
        <Navbar />
        <div className="p-8 bg-gray-100 min-h-screen">
          <h1 className="text-3xl font-bold text-[#B0422E] text-center mb-8">
            Business Monitoring Bonus
          </h1>
          {isLoading && (
            <p className="text-center text-gray-500 mb-4">Loading bonuses...</p>
          )}
          {error && <p className="text-center text-red-500 mb-4">{error}</p>}

          <div className="bg-white rounded-2xl shadow p-6">
            <div className="overflow-x-auto">
              <table className="w-full text-center">
                <thead>
                  <tr className="bg-[#B0422E] text-white">
                    <th className="p-3 rounded-l-xl">Sr No</th>
                    <th className="p-3">Date</th>
                    <th className="p-3">Transaction ID</th>
                    <th className="p-3">Direct Member</th>
                    <th className="p-3">Direct Team Income</th>
                    <th className="p-3">Bonus %</th>
                    <th className="p-3">Earned</th>
                    <th className="p-3">Requirement</th>
                    <th className="p-3">Status</th>
                    <th className="p-3 rounded-r-xl">Wallet</th>
                  </tr>
                </thead>

                <tbody>
                  {!isLoading && rows.length === 0 && (
                    <tr className="border-b">
                      <td className="p-4" colSpan={10}>
                        No Business Monitoring bonus data found
                      </td>
                    </tr>
                  )}

                  {rows.map((row, index) => (
                    <tr
                      className="border-b"
                      key={row.id ?? `${row.transaction_id}-${index}`}
                    >
                      <td className="p-4">
                        {String(index + 1).padStart(2, "0")}
                      </td>
                      <td>{formatDate(row.cycle_date)}</td>
                      <td>{row.transaction_id || "-"}</td>
                      <td>{row.downline_user_id || "-"}</td>
                      <td>
                        {formatCurrency(
                          row.direct_team_income ?? row.matching_income,
                        )}
                      </td>
                      <td>{Number(row.bonus_percentage || 10)}%</td>
                      <td>{formatCurrency(row.bonus_amount)}</td>
                      <td>
                        {row.requirement_met
                          ? "4 Step + Working Direct"
                          : "Requirement Pending"}
                      </td>
                      <td>{formatStatus(row.status)}</td>
                      <td className={walletClassName(row.status)}>
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

export default BusinessMonitoringBonus;
