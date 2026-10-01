import { IndianRupee } from "lucide-react";
import { useEffect, useState } from "react";
import Sidebar from "../components/Sidebar";
import Navbar from "../components/Navbar";
import { requestMemberApi } from "../utils/apiClient";

function CashbackHistory() {
    const [summary, setSummary] = useState({
        balance: 0,
        total_credit: 0,
        total_debit: 0,
        transactions: [],
    });

    const member = JSON.parse(localStorage.getItem("memberData"));
    const memberId = member?.user_id;

    useEffect(() => {
        if (!memberId) return;

        const fetchTransactions = async () => {
            try {
                const res = await requestMemberApi(
                    `/cashback-wallet/transactions?member_id=${memberId}`
                );
                if (res.ok) {
                    setSummary({
                        balance: res.data.balance || 0,
                        total_credit: res.data.total_credit || 0,
                        total_debit: res.data.total_debit || 0,
                        transactions: res.data.transactions || [],
                    });
                }
            } catch (error) {
                console.error(error);
            }
        };

        fetchTransactions();
    }, [memberId]);

    return (
        <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">
            <Sidebar />
            <div className="flex-1 min-w-0 flex flex-col">
                <Navbar />

                <div className="text-center mt-6">
                    <h1 className="text-3xl font-bold text-green-600">
                        Cashback Wallet
                    </h1>
                </div>

                <div className="p-6 space-y-6">

                    <div className="bg-gradient-to-r from-emerald-600 to-green-500 rounded-2xl p-8 text-white shadow-md">
                        <div className="flex items-center gap-4">
                            <div className="bg-white p-4 rounded-2xl shadow">
                                <IndianRupee size={28} className="text-green-600" />
                            </div>
                            <div>
                                <p className="uppercase font-semibold tracking-wide text-sm opacity-90">
                                    Cashback Wallet
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
                               <h3 className="text-3xl font-bold text-emerald-600 mt-2">₹{Number(summary.total_credit).toFixed(2)}</h3>
                            </div>
                          <div className="bg-white rounded-xl p-6 shadow-sm">
                             <p className="uppercase font-semibold text-gray-500 text-xs tracking-wide">
                                    Total Debit
                                </p>
                               <h3 className="text-3xl font-bold text-emerald-600 mt-2">
                                    ₹{Number(summary.total_debit).toFixed(2)}</h3>
                            </div>
                        </div>
                    </div>

                    <div className="bg-white rounded-2xl shadow-sm p-6 overflow-x-auto">
                        <table className="w-full min-w-175 text-sm">
                            <thead>
                                <tr className="bg-linear-to-r from-emerald-700 to-emerald-500 text-white text-center font-semibold">
                                    <th className="py-3 px-4 rounded-l-xl">Sr No</th>
                                    <th className="py-3 px-4">Date</th>
                                    <th className="py-3 px-4">Detail</th>
                                    <th className="py-3 px-4">Credit Amount</th>
                                    <th className="py-3 px-4">Debit Amount</th>
                                    <th className="py-3 px-4 rounded-r-xl">Balance</th>
                                </tr>
                            </thead>
                            <tbody className="font-medium text-center">
                                {summary.transactions.length > 0 ? (
                                    summary.transactions.map((item, index) => (
                                        <tr key={item.id} className="border-b border-gray-200 hover:bg-green-50 transition">
                                            <td className="py-4 px-4">{index + 1}</td>
                                            <td className="py-4 px-4">
                                                {item.date
                                                    ? new Date(item.date).toLocaleDateString("en-IN")
                                                    : "--"}
                                            </td>
                                            <td className="py-4 px-4">

                                                <div className="flex flex-col items-center gap-1">

                                                    <span
                                                        className={`px-3 py-1 rounded-full text-xs font-semibold ${item.credit_amount
                                                            ? "bg-green-100 text-green-700"
                                                            : "bg-red-100 text-red-700"
                                                            }`}
                                                    >

                                                        {item.credit_amount
                                                            ? "Cashback Earned"
                                                            : "Cashback Used"}

                                                    </span>

                                                    <span className="text-gray-600 text-sm">
                                                        {item.detail}
                                                    </span>

                                                </div>

                                            </td>
                                            <td className="py-4 px-4 text-green-600 font-semibold">
                                                {item.credit_amount != null
                                                    ? `₹${Number(item.credit_amount).toFixed(2)}`
                                                    : "--"}
                                            </td>
                                            <td className="py-4 px-4 text-red-500 font-semibold">
                                                {item.debit_amount != null
                                                    ? `₹${Number(item.debit_amount).toFixed(2)}`
                                                    : "--"}
                                            </td>
                                            <td className="py-4 px-4">
                                                <span className="bg-green-100 text-green-700 px-3 py-1 rounded-full font-semibold">
                                                    ₹{Number(item.balance).toFixed(2)}
                                                </span>
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="6" className="py-6 text-gray-400">
                                            No Transactions Found
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    );
}

export default CashbackHistory;