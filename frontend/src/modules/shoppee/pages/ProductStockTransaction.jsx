import { useState } from "react";
import Sidebar from "../components/sidebar";
import Navbar from "../components/navbar";
import { MdOutlineFileDownload } from "react-icons/md";

function ProductStockTransaction() {
  const productStockTransaction = [
    {
      id: 1,
      product: "Product A",
      date: "2023-01-01",
      fromseller: "Seller A",
      toseller: "Seller B",
      quantity: "10",
      status: "Pending",
    },
    {
      id: 2,
      product: "Product B",
      date: "2023-02-15",
      fromseller: "Seller C",
      toseller: "Seller D",
      quantity: "5",
      status: "Approved",
    },
  ];

  const [currentPage, setCurrentPage] = useState(1);
  const itemsPerPage = 10;
  const indexOfLastItem = currentPage * itemsPerPage;
  const indexOfFirstItem = indexOfLastItem - itemsPerPage;
  const currentTransactions = productStockTransaction.slice(
    indexOfFirstItem,
    indexOfLastItem
  );
  const totalPages = Math.ceil(
    productStockTransaction.length / itemsPerPage
  );

  return (
    <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">
      <div className="flex-1 min-w-0 flex flex-col">
        <div className="text-center mt-4 md:mt-6">
          <h1 className="text-2xl md:text-3xl font-bold text-[#B0422E]">
            Product Stock Transaction
          </h1>
        </div>

        <div className="p-3 md:p-6">
          <div className="bg-white rounded-2xl shadow-sm p-3 md:p-6 overflow-x-auto">
            <div className="flex flex-col sm:flex-row gap-2 sm:gap-0 mb-3">
              <label
                htmlFor="table-search"
                className="text-sm md:text-base font-semibold text-gray-500 flex items-center"
              >
                Search:
              </label>
              <input
                type="text"
                id="table-search"
                className="ml-0 sm:ml-2 text-xs md:text-sm outline-0 border border-gray-300 text-gray-900 rounded-lg px-2 py-1 h-8 bg-gray-200"
              />

              <button className="text-xs md:text-sm font-semibold bg-blue-500 text-white px-3 md:px-4 py-2 rounded-lg hover:bg-blue-600 sm:ml-auto flex items-center gap-2">
                <MdOutlineFileDownload className="text-lg shrink-0" />
                <span className="hidden sm:inline">Download CSV</span>
                <span className="sm:hidden">CSV</span>
              </button>
            </div>

            <table className="w-full min-w-max text-xs md:text-sm text-center">
              <thead>
                <tr className="bg-[#B0422E] text-white">
                  <th className="py-2 md:py-3 px-2 md:px-4 rounded-l-xl">Sr</th>
                  <th className="py-2 md:py-3 px-2 md:px-4 whitespace-nowrap">
                    Product
                  </th>
                  <th className="py-2 md:py-3 px-2 md:px-4 whitespace-nowrap">
                    Date
                  </th>
                  <th className="py-2 md:py-3 px-2 md:px-4 whitespace-nowrap">
                    From Seller
                  </th>
                  <th className="py-2 md:py-3 px-2 md:px-4 whitespace-nowrap">
                    To Seller
                  </th>
                  <th className="py-2 md:py-3 px-2 md:px-4 whitespace-nowrap">
                    Qty
                  </th>
                  <th className="py-2 md:py-3 px-2 md:px-4 rounded-r-xl whitespace-nowrap">
                    Status
                  </th>
                </tr>
              </thead>

              <tbody className="font-medium">
                {currentTransactions.map((tran, index) => (
                  <tr className="border-b" key={tran.id}>
                    <td className="py-2 md:py-4 px-2 md:px-4">{index + 1}</td>
                    <td className="py-2 md:py-4 px-2 md:px-4 truncate max-w-xs">
                      {tran.product}
                    </td>
                    <td className="py-2 md:py-4 px-2 md:px-4 whitespace-nowrap">
                      {tran.date}
                    </td>
                    <td className="py-2 md:py-4 px-2 md:px-4 truncate max-xs">
                      {tran.fromseller}
                    </td>
                    <td className="py-2 md:py-4 px-2 md:px-4 truncate max-xs">
                      {tran.toseller}
                    </td>
                    <td className="py-2 md:py-4 px-2 md:px-4 whitespace-nowrap">
                      {tran.quantity}
                    </td>
                    <td
                      className={`py-2 md:py-4 px-2 md:px-4 whitespace-nowrap ${
                        tran.status === "Approved"
                          ? "text-green-600"
                          : "text-orange-500"
                      }`}
                    >
                      {tran.status}
                    </td>
                  </tr>
                ))}
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
                    className={`rounded-full border px-3 py-1 text-sm font-medium ${
                      currentPage === idx + 1
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
    </div>
  );
}

export default ProductStockTransaction;