import React, { useEffect, useState } from "react";
import Sidebar from "../components/Sidebar";
import Navbar from "../components/Navbar";
import { requestMemberApi } from "../utils/apiClient";

export default function TravelclubStatus() {

    const [travelStatus, setTravelStatus] = useState({
        gb_bonus: 0,
        pr_bonus: 0,
        total_bonus: 0,
        reward: "Not Qualified",
        trip_type: "-",
        duration: "-",
        overall_progress: 0,
        next_target: null,
        milestones: []
    });

    const [loading, setLoading] = useState(true);
    const [error, setError] = useState("");


    // get user id
    const member = JSON.parse(localStorage.getItem("memberData") || "{}");
    const userId = member?.user_id || "";


    useEffect(() => {

        async function fetchStatus() {

            if (!userId) {
                setError("Member not found");
                setLoading(false);
                return;
            }

            try {

                const res = await requestMemberApi("/bonuses/travel-club", {
                    headers: {
                        Accept: "application/json",
                        "X-Auth-Member": userId
                    }
                });
                console.log("Travel API Response:", res);
                console.log("Travel API Data:", res.data);
                if (!res.ok) {
                    throw new Error(res.data?.message || "Unable to fetch travel status");
                }

                const data = res.data?.data || {};

                setTravelStatus({
                    gb_bonus: Number(data.gb_bonus || 0),
                    pr_bonus: Number(data.pr_bonus || 0),
                    total_bonus: Number(data.total_bonus || 0),
                    reward: data.reward || "Not Qualified",
                    trip_type: data.trip_type || "-",
                    duration: data.duration || "-",
                    overall_progress: Number(data.overall_progress || 0),
                    next_target: data.next_target || null,
                    milestones: data.milestones || []
                });

            } catch (err) {
                setError(err.message);
            }

            setLoading(false);

        }

        fetchStatus();

    }, [userId]);


    // currency format
    const formatCurrency = (value) => {
        return new Intl.NumberFormat("en-IN", {
            style: "currency",
            currency: "INR"
        }).format(value || 0);
    };


    // status color
    const statusColor = (status) => {
        return status === "Achieved"
            ? "bg-green-100 text-green-700"
            : "bg-yellow-100 text-yellow-700";
    };


    return (

        <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">

            <Sidebar />

            <div className="flex-1 flex flex-col">

                <Navbar />

                <div className="p-3 sm:p-6">

                    <h1 className="text-2xl sm:text-3xl font-bold text-center text-[#B0422E] mb-4 sm:mb-6">
                        Travel Club Status
                    </h1>


                    {loading && <p className="text-center">Loading travel status...</p>}
                    {error && <p className="text-center text-red-500">{error}</p>}


                    {/* Top Card */}

                    <div className="bg-[linear-gradient(120deg,#B0422E,#D76549)] text-white rounded-2xl p-3 sm:p-6">

                        <div className="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-4">

                            <div className="bg-white/20 rounded-xl p-2 sm:p-4">
                                <p className="text-xs sm:text-sm">GB Bonus</p>
                                <h2 className="text-lg sm:text-2xl font-bold">
                                    {formatCurrency(travelStatus.gb_bonus)}
                                </h2>
                            </div>

                            <div className="bg-white/20 rounded-xl p-2 sm:p-4">
                                <p className="text-xs sm:text-sm">PR Bonus</p>
                                <h2 className="text-lg sm:text-2xl font-bold">
                                    {formatCurrency(travelStatus.pr_bonus)}
                                </h2>
                            </div>

                            <div className="bg-white/20 rounded-xl p-2 sm:p-4">
                                <p className="text-xs sm:text-sm">Total Travel Bonus</p>
                                <h2 className="text-lg sm:text-2xl font-bold">
                                    {formatCurrency(travelStatus.total_bonus)}
                                </h2>
                            </div>

                            <div className="bg-white/20 rounded-xl p-2 sm:p-4">
                                <p className="text-xs sm:text-sm">Current Reward</p>
                                <h2 className="text-base sm:text-xl font-bold">{travelStatus.reward}</h2>
                                <p className="text-xs sm:text-sm">
                                    {travelStatus.trip_type} • {travelStatus.duration}
                                </p>
                            </div>

                        </div>

                    </div>


                    {/* Progress */}

                    <div className="mt-5">

                        <div className="flex justify-between text-sm mb-2">
                            <span>Overall Progress</span>
                            <span>{travelStatus.overall_progress}%</span>
                        </div>

                        <div className="w-full h-3 bg-gray-200 rounded-full overflow-hidden">
                            <div
                                className="h-full bg-[#B0422E] transition-all duration-500"
                                style={{ width: `${travelStatus.overall_progress}%` }}
                            />
                        </div>

                    </div>

                </div>


                {/* Table */}

                <div className="bg-white rounded-2xl mt-4 sm:mt-6 p-3 sm:p-6">

                    <h2 className="text-base sm:text-lg font-semibold mb-3 sm:mb-4">
                        Qualification Milestones
                    </h2>

                    <div className="overflow-x-auto">
                        <table className="w-full text-xs sm:text-sm whitespace-nowrap">

                            <thead>

                                <tr className="bg-[#B0422E] text-white">

                                    <th className="py-2 sm:py-3 px-2 sm:px-4 text-left  rounded-l-xl">Tier</th>
                                    <th className="py-2 sm:py-3 px-2 sm:px-4 text-left">Reward</th>
                                    <th className="py-2 sm:py-3 px-2 sm:px-4 text-left">Trip Type</th>
                                    <th className="py-2 sm:py-3 px-2 sm:px-4 text-left">Duration</th>
                                    <th className="py-2 sm:py-3 px-2 sm:px-4 text-left">Target</th>
                                    <th className="py-2 sm:py-3 px-2 sm:px-4 text-left">Achieved</th>
                                    <th className="py-2 sm:py-3 px-2 sm:px-4 text-left">Pending</th>
                                    <th className="py-2 sm:py-3 px-2 sm:px-4 text-left">Progress</th>
                                    <th className="py-2 sm:py-3 px-2 sm:px-4 text-left  rounded-r-xl">Status</th>

                                </tr>

                            </thead>

                            <tbody>

                                {travelStatus.milestones.length === 0 && !loading && (
                                    <tr>
                                        <td colSpan="9" className="text-center py-4 sm:py-6">
                                            No milestone data found
                                        </td>
                                    </tr>
                                )}

                                {travelStatus.milestones.map((item) => (
                                    <tr key={item.key} className="border-b">

                                        <td className="py-2 sm:py-4 px-2 sm:px-4">{item.label}</td>

                                        <td className="py-2 sm:py-4 px-2 sm:px-4">{item.reward}</td>

                                        <td className="py-2 sm:py-4 px-2 sm:px-4">{item.trip_type}</td>

                                        <td className="py-2 sm:py-4 px-2 sm:px-4">{item.duration}</td>

                                        <td className="py-2 sm:py-4 px-2 sm:px-4">
                                            {formatCurrency(item.target)}
                                        </td>

                                        <td className="py-2 sm:py-4 px-2 sm:px-4">
                                            {formatCurrency(item.achieved)}
                                        </td>

                                        <td className="py-2 sm:py-4 px-2 sm:px-4 text-[#B0422E]">
                                            {formatCurrency(item.pending)}
                                        </td>

                                        <td className="py-2 sm:py-4 px-2 sm:px-4">

                                            <div className="flex items-center gap-1 sm:gap-3">

                                                <div className="w-16 sm:w-28 h-2 bg-gray-200 rounded-full">

                                                    <div
                                                        className="h-full bg-[#B0422E]"
                                                        style={{ width: `${item.progress}%` }}
                                                    ></div>

                                                </div>

                                                <span className="text-xs font-semibold">
                                                    {item.progress}%
                                                </span>

                                            </div>

                                        </td>

                                        <td className="py-2 sm:py-4 px-2 sm:px-4">

                                            <span className={`${statusColor(item.status)} px-3 py-1 rounded-full text-xs`}>
                                                {item.status}
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