import React, { useEffect, useMemo, useState } from "react";
import Sidebar from "../components/Sidebar";
import Navbar from "../components/Navbar";
import { Star } from "lucide-react";
import { requestMemberApi } from "../utils/apiClient";

export default function RankWithRewardStatus() {
  const [summary, setSummary] = useState({
    total_target: 0,
    total_achieved: 0,
    ranks_achieved: 0,
    total_ranks: 0,
  });
  const [rewards, setRewards] = useState([]);
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

  const activationDateText = useMemo(() => {
    try {
      const member = JSON.parse(localStorage.getItem("memberData") || "{}");
      const rawActivationDate = member?.activation_date;

      if (!rawActivationDate) {
        return "Not Activated";
      }

      const activationDate = new Date(rawActivationDate);
      if (Number.isNaN(activationDate.getTime())) {
        return "Not Activated";
      }

      const day = String(activationDate.getDate()).padStart(2, "0");
      const month = String(activationDate.getMonth() + 1).padStart(2, "0");
      const year = activationDate.getFullYear();
      return `${day}-${month}-${year}`;
    } catch {
      return "Not Activated";
    }
  }, []);

  useEffect(() => {
    let isMounted = true;

    const fetchRankRewards = async () => {
      if (!memberUserId) {
        if (isMounted) {
          setError("Member not found in local session.");
          setIsLoading(false);
        }
        return;
      }

      try {
        if (isMounted) {
          setError("");
          setIsLoading(true);
        }

        const response = await requestMemberApi("/rank-rewards", {
          headers: {
            Accept: "application/json",
            "X-Auth-Member": memberUserId,
          },
        });

        if (!response.ok) {
          throw new Error(response.data?.message || "Unable to load rank rewards.");
        }

        const responseSummary = response.data?.data?.summary || {};
        const responseRewards = response.data?.data?.rewards || response.data?.rewards || [];

        if (isMounted) {
          setSummary({
            total_target: Number(responseSummary.total_target) || 0,
            total_achieved: Number(responseSummary.total_achieved) || 0,
            ranks_achieved: Number(responseSummary.ranks_achieved) || 0,
            total_ranks: Number(responseSummary.total_ranks) || responseRewards.length,
          });
          setRewards(Array.isArray(responseRewards) ? responseRewards : []);
        }
      } catch (fetchError) {
        if (isMounted) {
          setError(fetchError.message || "Unable to load rank rewards.");
          setRewards([]);
        }
      } finally {
        if (isMounted) {
          setIsLoading(false);
        }
      }
    };

    fetchRankRewards();

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

  const statusClassName = (status) => {
    switch (String(status || "").toLowerCase()) {
      case "achieved":
        return "bg-green-100 text-green-700";

      case "approved":
        return "bg-green-100 text-green-700";

      case "pending":
        return "bg-yellow-100 text-yellow-700";

      default:
        return "bg-gray-100 text-gray-700";
    }
  };

  return (
    <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">
      <Sidebar />

      <div className="flex-1 min-w-0 flex flex-col">
        <Navbar />

        <div className="p-6">

          <h1 className="text-3xl font-bold text-center text-[#B0422E] mb-6">
            Rank with Reward Status
          </h1>

          {isLoading && <p className="text-center text-gray-500 mb-4">Loading rewards...</p>}
          {error && <p className="text-center text-red-500 mb-4">{error}</p>}

          <div className="bg-[#2A74B5] rounded-2xl p-6 text-white shadow-md">
            <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">

              <div className="bg-white/20 rounded-xl p-6">
                <p className="uppercase text-semibold">Total Target</p>
                <h2 className="text-2xl font-bold mt-2">{formatCurrency(summary.total_target)}</h2>
              </div>

              <div className="bg-white/20 rounded-xl p-6">
                <p className="uppercase text-semibold">Total Achieved</p>
                <h2 className="text-2xl font-bold mt-2">{formatCurrency(summary.total_achieved)}</h2>
              </div>

              <div className="bg-white/20 rounded-xl p-6">
                <p className="uppercase text-semibold">Ranks Achieved</p>
                <h2 className="text-2xl font-bold mt-2">{summary.ranks_achieved} / {summary.total_ranks}</h2>
              </div>

              <div className="bg-white/20 rounded-xl p-6">
                <p className="uppercase text-semibold">ID Activation Date</p>
                <h2 className="text-2xl font-bold mt-2">{activationDateText}</h2>
              </div>

            </div>
          </div>

          <div className="bg-white rounded-2xl shadow-sm mt-6 p-6">

            <h2 className="text-lg font-semibold mb-4">
              Reward Tiers
            </h2>

            <div className="overflow-x-auto">
              <table className="w-full min-w-[1100px] text-sm">

                <thead>
                  <tr className="bg-[#2A74B5] text-white text-semibold">
                    <th className="py-3 px-4 text-left align-middle rounded-l-xl">Sr No</th>
                    <th className="py-3 px-4 text-left align-middle">Rank</th>
                    <th className="py-3 px-4 text-left align-middle">Target</th>
                    <th className="py-3 px-4 text-left align-middle">Achieved</th>
                    <th className="py-3 px-4 text-left align-middle">Pending</th>
                    <th className="py-3 px-4 text-left align-middle">Progress</th>
                    <th className="py-3 px-4 text-left align-middle">Reward</th>
                    <th className="py-3 px-4 text-left align-middle rounded-r-xl">Status</th>
                  </tr>
                </thead>

                <tbody>
                  {!isLoading && rewards.length === 0 && (
                    <tr>
                      <td className="py-6 px-4 text-center align-middle text-gray-500" colSpan={8}>
                        No reward data found.
                      </td>
                    </tr>
                  )}

                  {rewards.map((item, index) => {
                    const progress = Number(item.progress) || 0;

                    return (
                      <tr key={item.id || item.rank || index} className="border-b last:border-none">

                        <td className="py-6 px-4 align-middle">
                          {String(index + 1).padStart(2, "0")}
                        </td>

                        <td className="py-6 px-4 align-middle">
                          <div className="flex items-center gap-2">
                            <div className="bg-blue-100 p-2 rounded-full flex items-center justify-center flex-shrink-0">
                              <Star size={14} className="text-blue-600" />
                            </div>
                            <span>{item.rank}</span>
                          </div>
                        </td>

                        <td className="py-6 px-4 align-middle">{formatCurrency(item.target)}</td>
                        <td className="py-6 px-4 align-middle">{formatCurrency(item.achieved)}</td>
                        <td className="py-6 px-4 align-middle text-[#F6A71E]">
                          {formatCurrency(item.pending)}
                        </td>

                        <td className="py-6 px-4 align-middle">
                          <div className="flex items-center gap-3">
                            <div className="w-32 h-2 bg-gray-200 rounded-full">
                              <div
                                className={`h-2 rounded-full ${
                                  progress >= 100 ? "bg-green-500" : "bg-[#2A74B5]"
                                }`}
                                style={{ width: `${Math.min(progress, 100)}%` }}
                              />
                            </div>
                            <span className="text-xs font-medium">
                              {progress}%
                            </span>
                          </div>
                        </td>

                        <td className="py-6 px-4 align-middle">
                          <div className="flex items-center gap-4">
                            <img
                              src={item.image}
                              alt={item.rank}
                              className="w-16 h-16 object-contain flex-shrink-0"
                              onError={(event) => {
                                event.currentTarget.src = "/rewards/image 10.png";
                              }}
                            />

                            <p className="text-sm text-gray-700 leading-5">
                              {item.reward_description}
                            </p>
                          </div>
                        </td>

                        <td className="py-6 px-4 align-middle">
                          <span className={`${statusClassName(item.status)} px-4 py-1 rounded-full text-xs font-medium`}>
                            {item.status || "Pending"}
                          </span>
                        </td>

                      </tr>
                    );
                  })}
                </tbody>

              </table>
            </div>
          </div>

        </div>
      </div>
    </div>
  );
}