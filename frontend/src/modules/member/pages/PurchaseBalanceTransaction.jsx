import { IndianRupee } from "lucide-react";
import { useEffect, useState } from "react";
import Sidebar from "../components/Sidebar";
import Navbar from "../components/Navbar";
import { requestMemberApi } from "../utils/apiClient";

function PurchaseBalanceTransaction() {
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
        const res = await requestMemberApi(`/balance-transactions?member_id=${memberId}`);
        if (res.ok) {
          setSummary({
            balance:      res.data.balance      || 0,
            total_credit: res.data.total_credit || 0,
            total_debit:  res.data.total_debit  || 0,
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
          <h1 className="text-3xl font-bold text-[#B0422E]">
            Transactions
          </h1>
        </div>

        <div className="p-6 space-y-6">

          <div className="bg-[#B0422E] rounded-2xl p-8 text-white shadow-md">
            <div className="flex items-center gap-4">
              <div className="bg-white p-4 rounded-xl">
                <IndianRupee size={28} className="text-[#B0422E]" />
              </div>
              <div>
                <p className="uppercase font-semibold tracking-wide text-sm opacity-90">
                  Purchase Balance
                </p>
                <h2 className="text-3xl font-bold">₹{summary.balance}</h2>
              </div>
            </div>

            <hr className="border-white/30 my-6" />

            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div className="bg-[#CF9D94A1] rounded-xl p-6">
                <p className="uppercase font-semibold text-[#FFFFFFAD] text-xs tracking-wide">
                  Total Credit
                </p>
                <h3 className="text-2xl font-semibold mt-2">₹{summary.total_credit}</h3>
              </div>
              <div className="bg-[#CF9D94A1] rounded-xl p-6">
                <p className="uppercase font-semibold text-[#FFFFFFAD] text-xs tracking-wide">
                  Total Debit
                </p>
                <h3 className="text-2xl font-semibold mt-2">₹{summary.total_debit}</h3>
              </div>
            </div>
          </div>

          <div className="bg-white rounded-2xl shadow-sm p-6 overflow-x-auto">
            <table className="w-full min-w-175 text-sm">
              <thead>
                <tr className="bg-[#B0422E] text-white text-center font-semibold">
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
                    <tr key={item.id} className="border-b border-gray-200 hover:bg-gray-50">
                      <td className="py-4 px-4">{index + 1}</td>
                      <td className="py-4 px-4">
                        {item.date
                          ? new Date(item.date).toLocaleDateString("en-IN")
                          : "--"}
                      </td>
                      <td className="py-4 px-4">{item.detail || "--"}</td>
                      <td className="py-4 px-4 text-green-600 font-semibold">
                        {item.credit_amount != null ? `₹${item.credit_amount}` : "--"}
                      </td>
                      <td className="py-4 px-4 text-red-500 font-semibold">
                        {item.debit_amount != null ? `₹${item.debit_amount}` : "--"}
                      </td>
                      <td className="py-4 px-4">₹{item.balance}</td>
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

export default PurchaseBalanceTransaction;