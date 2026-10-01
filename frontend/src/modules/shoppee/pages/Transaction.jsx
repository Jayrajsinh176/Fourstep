import { IndianRupee } from "lucide-react";
import { useEffect, useState } from "react";
import { shoppeeApi as api } from "../api/axios";

function Transaction() {
  const [summary, setSummary] = useState({
    purchase_balance: 0,
    purchase_credit: 0,
    purchase_debit: 0,

    turnover_balance: 0,
    turnover_credit: 0,
    turnover_debit: 0,

    commission_balance: 0,
    commission_credit: 0,
    commission_debit: 0,

    transactions: [],
  });
  const [currentPage, setCurrentPage] = useState(1);
  const itemsPerPage = 10;

  useEffect(() => {
    const fetchTransactions = async () => {
      try {
        const user = JSON.parse(localStorage.getItem("user"));

        const res = await api.get(`/transactions?member_id=${user.id}`);
        setSummary({
          purchase_balance: res.data.purchase_balance,
          purchase_credit: res.data.purchase_credit,
          purchase_debit: res.data.purchase_debit,

          turnover_balance: res.data.turnover_balance,
          turnover_credit: res.data.turnover_credit,
          turnover_debit: res.data.turnover_debit,

          commission_balance: res.data.commission_balance,
          commission_credit: res.data.commission_credit,
          commission_debit: res.data.commission_debit,

          transactions: res.data.transactions,
        });
      } catch (error) {
        console.error(error);
      }
    };

    fetchTransactions();
  }, []);

  const indexOfLastTransaction = currentPage * itemsPerPage;
  const indexOfFirstTransaction = indexOfLastTransaction - itemsPerPage;
  const currentTransactions = summary.transactions.slice(
    indexOfFirstTransaction,
    indexOfLastTransaction
  );
  const totalPages = Math.ceil(summary.transactions.length / itemsPerPage);

  useEffect(() => {
    setCurrentPage(1);
  }, [summary.transactions]);

  return (
    <div className="flex flex-col bg-gray-100 min-h-screen">
      <div className="text-center mt-4 md:mt-6">
        <h1 className="text-2xl md:text-3xl font-bold text-[#B0422E]">
          Transactions
        </h1>
      </div>

      <div className="p-3 md:p-6 space-y-4 md:space-y-6">
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">

          {/* PURCHASE BALANCE */}
          <div className="bg-[#B0422E] rounded-2xl p-4 md:p-8 text-white shadow-md">
            <div className="flex items-center gap-4">
              <div className="bg-white/20 p-3 md:p-4 rounded-xl">
                <IndianRupee size={24} className="md:w-7 md:h-7" />
              </div>

              <div>
                <p className="uppercase text-xs md:text-sm font-semibold tracking-wide">
                  Purchase Balance
                </p>

                <h2 className="text-2xl md:text-3xl font-bold">
                  ₹{summary.purchase_balance}
                </h2>
              </div>
            </div>

            <div className="grid grid-cols-2 gap-4 mt-6">
              <div className="bg-[#CF9D94A1] rounded-xl p-4">
                <p className="uppercase text-xs text-[#FFFFFFAD]">
                  Total Credit
                </p>

                <h3 className="text-xl font-semibold mt-2">
                  ₹ {summary.purchase_credit}
                </h3>
              </div>

              <div className="bg-[#CF9D94A1] rounded-xl p-4">
                <p className="uppercase text-xs text-[#FFFFFFAD]">
                  Total Debit
                </p>

                <h3 className="text-xl font-semibold mt-2">
                  ₹ {summary.purchase_debit}
                </h3>
              </div>
            </div>
          </div>

          {/* TURNOVER BALANCE */}
          <div className="bg-[#1F7A5C] rounded-2xl p-4 md:p-8 text-white shadow-md">
            <div className="flex items-center gap-4">
              <div className="bg-white/20 p-3 md:p-4 rounded-xl">
                <IndianRupee size={24} className="md:w-7 md:h-7" />
              </div>

              <div>
                <p className="uppercase text-xs md:text-sm font-semibold tracking-wide">
                  Turnover Balance
                </p>

                <h2 className="text-2xl md:text-3xl font-bold">
                  ₹{summary.turnover_balance}
                </h2>
              </div>
            </div>

            <div className="grid grid-cols-2 gap-4 mt-6">
              <div className="bg-[#ffffff26] rounded-xl p-4">
                <p className="uppercase text-xs text-[#FFFFFFAD]">
                  Total Credit
                </p>

                <h3 className="text-xl font-semibold mt-2">
                  ₹ {summary.turnover_credit}
                </h3>
              </div>

              <div className="bg-[#ffffff26] rounded-xl p-4">
                <p className="uppercase text-xs text-[#FFFFFFAD]">
                  Total Debit
                </p>

                <h3 className="text-xl font-semibold mt-2">
                  ₹ {summary.turnover_debit}
                </h3>
              </div>
            </div>
          </div>
          {/* COMMISSION BALANCE */}
          <div className="bg-linear-to-r from-[#2563EB] to-[#3B82F6] rounded-2xl p-4 md:p-8 text-white shadow-md">
            <div className="flex items-center gap-4">
              <div className="bg-white/20 p-3 md:p-4 rounded-xl">
                <IndianRupee size={24} className="md:w-7 md:h-7" />
              </div>

              <div>
                <p className="uppercase text-xs md:text-sm font-semibold tracking-wide">
                  Commission Balance
                </p>

                <h2 className="text-2xl md:text-3xl font-bold">
                  ₹{summary.commission_balance}
                </h2>
              </div>
            </div>

            <div className="grid grid-cols-2 gap-4 mt-6">
              <div className="bg-[#ffffff26] rounded-xl p-4">
                <p className="uppercase text-xs text-[#FFFFFFAD]">
                  Total Credit
                </p>

                <h3 className="text-xl font-semibold mt-2">
                  ₹ {summary.commission_credit}
                </h3>
              </div>

              <div className="bg-[#ffffff26] rounded-xl p-4">
                <p className="uppercase text-xs text-[#FFFFFFAD]">
                  Total Debit
                </p>

                <h3 className="text-xl font-semibold mt-2">
                  ₹ {summary.commission_debit}
                </h3>
              </div>
            </div>
          </div>

        </div>

        <div className="bg-white rounded-2xl shadow-sm p-3 md:p-6 overflow-x-auto">
          <table className="w-full min-w-max text-xs md:text-sm">
            <thead>
              <tr className="bg-[#B0422E] text-white text-center font-semibold">
                <th className="py-2 md:py-3 px-2 md:px-4 rounded-l-xl">Sr</th>
                <th className="py-2 md:py-3 px-2 md:px-4 whitespace-nowrap">
                  Date
                </th>
                <th className="py-2 md:py-3 px-2 md:px-4 whitespace-nowrap">
                  Type
                </th>
                <th className="py-2 md:py-3 px-2 md:px-4 whitespace-nowrap">
                  Detail
                </th>
                <th className="py-2 md:py-3 px-2 md:px-4 whitespace-nowrap">
                  Credit
                </th>
                <th className="py-2 md:py-3 px-2 md:px-4 whitespace-nowrap">
                  Debit
                </th>
                <th className="py-2 md:py-3 px-2 md:px-4 rounded-r-xl whitespace-nowrap">
                  Balance
                </th>
              </tr>
            </thead>
            <tbody className="font-medium text-center">
              {summary.transactions.length > 0 ? (
                currentTransactions.map((item, index) => (
                  <tr key={item.id} className="border-b border-gray-400">
                    <td className="py-2 md:py-4 px-2 md:px-4">{index + 1}</td>
                    <td className="py-2 md:py-4 px-2 md:px-4 whitespace-nowrap">
                      {item.date}
                    </td>
                    <td className="py-2 md:py-4 px-2 md:px-4">
                      <span
                        className={`px-3 py-1 rounded-full text-white text-xs ${item.balance_type === "purchase"
                            ? "bg-[#B0422E]"
                            : item.balance_type === "turnover"
                              ? "bg-[#1F7A5C]"
                              : "bg-[#2563EB]"
                          }`}
                      >
                        {item.balance_type}
                      </span>
                    </td>
                    <td className="py-2 md:py-4 px-2 md:px-4 truncate max-w-xs">
                      {item.detail}
                    </td>
                    <td className="py-2 md:py-4 px-2 md:px-4 whitespace-nowrap">
                      {item.credit_amount ?? "--"}
                    </td>
                    <td className="py-2 md:py-4 px-2 md:px-4 whitespace-nowrap">
                      {item.debit_amount ?? "--"}
                    </td>
                    <td className="py-2 md:py-4 px-2 md:px-4 whitespace-nowrap">
                      {item.balance}
                    </td>
                  </tr>
                ))
              ) : (
                <tr>
                  <td colSpan="7" className="py-4 px-4 text-center">
                    No Approved Transactions Found
                  </td>
                </tr>
              )}
            </tbody>
          </table>

          {totalPages > 1 && (
            <div className="mt-4 flex flex-wrap items-center justify-center gap-2">
              <button
                type="button"
                onClick={() => setCurrentPage((prev) => Math.max(prev - 1, 1))}
                disabled={currentPage === 1}
                className="rounded-full border border-gray-300 bg-white px-3 py-1 text-sm font-medium disabled:cursor-not-allowed disabled:opacity-50"
              >
                Prev
              </button>

              {[...Array(totalPages)].map((_, idx) => (
                <button
                  key={idx}
                  type="button"
                  onClick={() => setCurrentPage(idx + 1)}
                  className={`rounded-full border px-3 py-1 text-sm font-medium ${currentPage === idx + 1
                    ? "border-[#B0422E] bg-[#B0422E] text-white"
                    : "border-gray-300 bg-white text-gray-700"
                    }`}
                >
                  {idx + 1}
                </button>
              ))}

              <button
                type="button"
                onClick={() => setCurrentPage((prev) => Math.min(prev + 1, totalPages))}
                disabled={currentPage === totalPages}
                className="rounded-full border border-gray-300 bg-white px-3 py-1 text-sm font-medium disabled:cursor-not-allowed disabled:opacity-50"
              >
                Next
              </button>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

export default Transaction;
