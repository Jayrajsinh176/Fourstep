import { IndianRupee } from "lucide-react";
import { useEffect, useState } from "react";
import Sidebar from "../components/Sidebar";
import Navbar from "../components/Navbar";
import { requestMemberApi } from "../utils/apiClient";

const RECORDS_PER_PAGE = 10;

function ConsistencyWallet() {
    const [summary, setSummary] = useState({
        balance: 0,
        total_credit: 0,
        total_debit: 0,
        transactions: [],
    });
    const [currentPage, setCurrentPage] = useState(1);

    const member = JSON.parse(localStorage.getItem("memberData"));
    const memberId = member?.user_id;

    useEffect(() => {
        if (!memberId) return;

        const fetchTransactions = async () => {
            try {
                const res = await requestMemberApi(
                    `/consistency-wallet/transactions?member_id=${memberId}`
                );

                if (res.ok) {
                    setSummary({
                        balance: res.data.balance || 0,
                        total_credit: res.data.total_credit || 0,
                        total_debit: res.data.total_debit || 0,
                        transactions: res.data.transactions || [],
                    });
                      setCurrentPage(1);
                }
            } catch (error) {
                console.error(error);
            }
        };

        fetchTransactions();
    }, [memberId]);

    const totalPages = Math.ceil(
        summary.transactions.length / RECORDS_PER_PAGE
    );

    const paginatedTransactions = summary.transactions.slice(
        (currentPage - 1) * RECORDS_PER_PAGE,
        currentPage * RECORDS_PER_PAGE
    );

    return (
        <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">
            <Sidebar />

            <div className="flex-1 min-w-0 flex flex-col">
                <Navbar />

                <div className="text-center mt-6">
                    <h1 className="text-3xl font-bold text-[#B0422E]">
                        Consistency Wallet
                    </h1>
                </div>

                <div className="p-6 space-y-6">

                    <div className="bg-gradient-to-r from-[#B0422E] to-[#D45A45] rounded-2xl p-8 text-white shadow-md">

                        <div className="flex items-center gap-4">

                            <div className="bg-white p-4 rounded-2xl shadow">
                                <IndianRupee
                                    size={28}
                                    className="text-[#B0422E]"
                                />
                            </div>

                            <div>
                                <p className="uppercase font-semibold tracking-wide text-sm opacity-90">
                                    Consistency Wallet
                                </p>

                                <h2 className="text-4xl font-bold">
                                    ₹{Number(summary.balance).toFixed(2)}
                                </h2>
                            </div>

                        </div>

                        <hr className="border-white/30 my-6" />

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">

                            <div className="bg-white rounded-xl p-6 shadow-sm">

                                <p className="uppercase font-semibold text-gray-500 text-xs tracking-wide">
                                    Total Credit
                                </p>

                                <h3 className="text-3xl font-bold text-[#B0422E] mt-2">
                                    ₹{Number(summary.total_credit).toFixed(2)}
                                </h3>

                            </div>

                            <div className="bg-white rounded-xl p-6 shadow-sm">

                                <p className="uppercase font-semibold text-gray-500 text-xs tracking-wide">
                                    Total Debit
                                </p>

                                <h3 className="text-3xl font-bold text-[#B0422E] mt-2">
                                    ₹{Number(summary.total_debit).toFixed(2)}
                                </h3>

                            </div>

                        </div>

                    </div>

                    <div className="bg-white rounded-2xl shadow-sm p-6 overflow-x-auto">

                        <table className="w-full min-w-[900px] text-sm">

                            <thead>

                                <tr className="bg-gradient-to-r from-[#B0422E] to-[#D45A45] text-white text-center font-semibold">

                                    <th className="py-3 px-4 rounded-l-xl">
                                        Sr No
                                    </th>

                                    <th className="py-3 px-4">
                                        Date
                                    </th>

                                    <th className="py-3 px-4">
                                        Detail
                                    </th>

                                    <th className="py-3 px-4">
                                        Credit Amount
                                    </th>

                                    <th className="py-3 px-4">
                                        Debit Amount
                                    </th>

                                    <th className="py-3 px-4 rounded-r-xl">
                                        Balance
                                    </th>

                                </tr>

                            </thead>

                            <tbody className="font-medium text-center">
                                {summary.transactions.length > 0 ? (
                                    paginatedTransactions.map((item, index) => (
                                        <tr
                                            key={item.id}
                                            className="border-b border-gray-200 hover:bg-orange-50 transition"
                                        >
                                            <td className="py-4 px-4">
                                                {(currentPage - 1) * RECORDS_PER_PAGE + index + 1}
                                            </td>

                                            <td className="py-4 px-4">
                                                {item.date
                                                    ? new Date(
                                                        item.date
                                                    ).toLocaleDateString(
                                                        "en-IN"
                                                    )
                                                    : "--"}
                                            </td>

                                            <td className="py-4 px-4">

                                                <div className="flex flex-col items-center gap-1">

                                                    <span
                                                        className={`px-3 py-1 rounded-full text-xs font-semibold ${Number(item.credit_amount) > 0
                                                            ? "bg-orange-100 text-[#B0422E]"
                                                            : "bg-red-100 text-red-700"
                                                            }`}
                                                    >
                                                        {Number(item.credit_amount) > 0
                                                            ? "Consistency Bonus"
                                                            : "Purchase"}
                                                    </span>

                                                    <span className="text-gray-600 text-sm">
                                                        {item.detail}
                                                    </span>

                                                </div>

                                            </td>

                                            <td className="py-4 px-4 text-[#B0422E] font-semibold">
                                                {Number(item.credit_amount) > 0
                                                    ? `₹${Number(
                                                        item.credit_amount
                                                    ).toFixed(2)}`
                                                    : "--"}
                                            </td>

                                            <td className="py-4 px-4 text-red-500 font-semibold">
                                                {Number(item.debit_amount) > 0
                                                    ? `₹${Number(
                                                        item.debit_amount
                                                    ).toFixed(2)}`
                                                    : "--"}
                                            </td>

                                            <td className="py-4 px-4">
                                                <span className="bg-orange-100 text-[#B0422E] px-3 py-1 rounded-full font-semibold">
                                                    ₹{Number(item.balance).toFixed(2)}
                                                </span>
                                            </td>

                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td
                                            colSpan="6"
                                            className="py-6 text-gray-400"
                                        >
                                            No Consistency Transactions Found
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>

                    </div>
                    {totalPages > 1 && (
                        <div className="mt-5 flex items-center justify-between flex-wrap gap-3">

                            <p className="text-sm text-gray-500">
                                Showing{" "}
                                <span className="font-semibold text-gray-700">
                                    {(currentPage - 1) * RECORDS_PER_PAGE + 1}
                                </span>
                                –
                                <span className="font-semibold text-gray-700">
                                    {Math.min(
                                        currentPage * RECORDS_PER_PAGE,
                                        summary.transactions.length
                                    )}
                                </span>
                                {" "}of{" "}
                                <span className="font-semibold text-gray-700">
                                    {summary.transactions.length}
                                </span>
                                {" "}transactions
                            </p>

                            <div className="flex items-center gap-1.5">

                                <button
                                    onClick={() =>
                                        setCurrentPage((p) => Math.max(1, p - 1))
                                    }
                                    disabled={currentPage === 1}
                                    className="px-3 py-1.5 rounded-lg border text-sm font-medium disabled:opacity-40 disabled:cursor-not-allowed bg-white border-gray-200 hover:border-[#B0422E] hover:text-[#B0422E]"
                                >
                                    ← Prev
                                </button>

                                {Array.from(
                                    { length: totalPages },
                                    (_, i) => i + 1
                                ).map((page) => (
                                    <button
                                        key={page}
                                        onClick={() => setCurrentPage(page)}
                                        className={`w-8 h-8 rounded-lg text-sm font-semibold ${page === currentPage
                                                ? "bg-[#B0422E] text-white"
                                                : "bg-white border border-gray-200 text-gray-600 hover:border-[#B0422E] hover:text-[#B0422E]"
                                            }`}
                                    >
                                        {page}
                                    </button>
                                ))}

                                <button
                                    onClick={() =>
                                        setCurrentPage((p) =>
                                            Math.min(totalPages, p + 1)
                                        )
                                    }
                                    disabled={currentPage === totalPages}
                                    className="px-3 py-1.5 rounded-lg border text-sm font-medium disabled:opacity-40 disabled:cursor-not-allowed bg-white border-gray-200 hover:border-[#B0422E] hover:text-[#B0422E]"
                                >
                                    Next →
                                </button>

                            </div>

                        </div>
                    )}

                </div>

            </div>

        </div>
    );
}

export default ConsistencyWallet;