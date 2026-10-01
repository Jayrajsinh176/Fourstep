import { useEffect, useState } from "react";
import Sidebar from "../components/sidebar";
import Navbar from "../components/navbar";
import { MdOutlineFileDownload } from "react-icons/md";
import { shoppeeApi as api } from "../api/axios";

function ProductStockReport() {
  const [stockReport, setStockReport] = useState([]);
  const [currentPage, setCurrentPage] = useState(1);
  const [selectedCategory, setSelectedCategory] = useState("All");
  const [categories, setCategories] = useState([]);
  const itemsPerPage = 10;

  useEffect(() => {
    const fetchStock = async () => {
      try {
        const user = JSON.parse(localStorage.getItem("user"));

        const res = await api.get("/stock-report", {
          params: {
            member_id: user?.id,
          },
        });

        setStockReport(res.data);
      } catch (error) {
        console.log(error);
      }
    };

    fetchStock();
  }, []);

  useEffect(() => {
    const fetchCategories = async () => {
      const res = await api.get("/categories");
      setCategories([
        { category: "All" },
        ...res.data,
      ]);
    };

    fetchCategories();
  }, []);

  const filteredStockReport =
    selectedCategory === "All"
      ? stockReport
      : stockReport.filter(
        (item) => item.category === selectedCategory
      );

  const indexOfLastItem = currentPage * itemsPerPage;
  const indexOfFirstItem = indexOfLastItem - itemsPerPage;

  const currentStockReport = filteredStockReport.slice(
    indexOfFirstItem,
    indexOfLastItem
  );

  const totalPages = Math.ceil(
    filteredStockReport.length / itemsPerPage
  );

  useEffect(() => {
    setCurrentPage(1);
  }, [selectedCategory]);

const handleDownloadPDF = async () => {
  const { jsPDF } = await import("jspdf");
  const autoTable = (await import("jspdf-autotable")).default;

  const doc = new jsPDF({
    orientation: "landscape",
  });

  const tableColumn = [
    "Sr",
    "Product",
    "MRP",
    "Offer",
    "PV",
    "BV",
    "Received",
    "Used",
    "Balance",
    "Amount",
  ];

  const tableRows = filteredStockReport.map((item, index) => [
    index + 1,
    item.product,
    item.mrp,
    item.offerPrice,
    item.pv,
    item.bv,
    item.productReceived,
    item.productUsed,
    item.productBalance,
    Number(item.balanceAmount || 0).toFixed(2),
  ]);

  autoTable(doc, {
    head: [tableColumn],
    body: tableRows,
    startY: 20,
    theme: "grid",
    styles: {
      fontSize: 8,
    },
  });

  doc.save("Product_Stock_Report.pdf");
};

  return (
    <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">
      <div className="flex-1 min-w-0 flex flex-col">
        <div className="text-center mt-4 md:mt-6">
          <h1 className="text-2xl md:text-3xl font-bold text-[#B0422E]">
            Product Stock Report
          </h1>
        </div>

        <div className="p-3 md:p-6">
          <div className="bg-white rounded-2xl shadow-sm p-3 md:p-6 overflow-x-auto">

            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
              <select
                value={selectedCategory}
                onChange={(e) => setSelectedCategory(e.target.value)}
                className="border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none"
              >
                {categories.map((cat, index) => (
                  <option
                    key={index}
                    value={cat.category}
                  >
                    {cat.category}
                  </option>
                ))}
              </select>

              <button
                onClick={handleDownloadPDF}
                className="text-xs md:text-sm font-semibold bg-blue-500 text-white px-3 md:px-4 py-2 rounded-lg hover:bg-blue-600 flex items-center gap-2 shrink-0"
              >
                <MdOutlineFileDownload className="text-lg" />

                <span className="hidden sm:inline">
                  Download
                </span>

              </button>
            </div>

            <table className="w-full min-w-max text-xs md:text-sm">
              <thead>
                <tr className="bg-[#B0422E] text-white font-semibold">
                  <th className="py-2 md:py-3 px-2 md:px-4 rounded-l-xl">
                    Sr
                  </th>

                  <th className="py-2 md:py-3 px-2 md:px-4 whitespace-nowrap">
                    Product
                  </th>

                  <th className="py-2 md:py-3 px-2 md:px-4 whitespace-nowrap">
                    MRP
                  </th>

                  <th className="py-2 md:py-3 px-2 md:px-4 whitespace-nowrap">
                    Offer Price
                  </th>

                  <th className="py-2 md:py-3 px-2 md:px-4">
                    PV
                  </th>

                  <th className="py-2 md:py-3 px-2 md:px-4">
                    BV
                  </th>

                  <th className="py-2 md:py-3 px-2 md:px-4 whitespace-nowrap">
                    Received
                  </th>

                  <th className="py-2 md:py-3 px-2 md:px-4 whitespace-nowrap">
                    Used
                  </th>

                  <th className="py-2 md:py-3 px-2 md:px-4 whitespace-nowrap">
                    Balance
                  </th>

                  <th className="py-2 md:py-3 px-2 md:px-4 rounded-r-xl whitespace-nowrap">
                    Amount
                  </th>
                </tr>
              </thead>

              <tbody className="text-center font-medium">
                {currentStockReport.length > 0 ? (
                  currentStockReport.map((item, index) => (
                    <tr
                      className="border-b border-gray-400"
                      key={item.id}
                    >
                      <td className="py-2 md:py-4 px-2 md:px-4">
                        {indexOfFirstItem + index + 1}
                      </td>

                      <td className="py-2 md:py-4 px-2 md:px-4 truncate">
                        {item.product}
                      </td>

                      <td className="py-2 md:py-4 px-2 md:px-4 whitespace-nowrap">
                        {item.mrp}
                      </td>

                      <td className="py-2 md:py-4 px-2 md:px-4 whitespace-nowrap">
                        {item.offerPrice}
                      </td>

                      <td className="py-2 md:py-4 px-2 md:px-4">
                        {item.pv}
                      </td>

                      <td className="py-2 md:py-4 px-2 md:px-4">
                        {item.bv}
                      </td>

                      <td className="py-2 md:py-4 px-2 md:px-4 whitespace-nowrap">
                        {item.productReceived}
                      </td>

                      <td className="py-2 md:py-4 px-2 md:px-4 whitespace-nowrap">
                        {item.productUsed}
                      </td>

                      <td className="py-2 md:py-4 px-2 md:px-4 whitespace-nowrap">
                        {item.productBalance}
                      </td>

                      <td className="py-2 md:py-4 px-2 md:px-4 whitespace-nowrap">
                        {Number(item.balanceAmount || 0).toFixed(2)}
                      </td>
                    </tr>
                  ))
                ) : (
                  <tr>
                    <td
                      colSpan="10"
                      className="py-6 text-center text-gray-500"
                    >
                      No products found
                    </td>
                  </tr>
                )}
              </tbody>
            </table>

            {totalPages > 1 && (
              <div className="mt-4 flex flex-wrap items-center justify-center gap-2">
                <button
                  type="button"
                  onClick={() =>
                    setCurrentPage((prev) =>
                      Math.max(prev - 1, 1)
                    )
                  }
                  disabled={currentPage === 1}
                  className="rounded-full border border-gray-300 bg-white px-3 py-1 text-sm font-medium disabled:cursor-not-allowed disabled:opacity-50"
                >
                  Prev
                </button>

                {[...Array(totalPages)].map((_, idx) => (
                  <button
                    key={idx}
                    type="button"
                    onClick={() =>
                      setCurrentPage(idx + 1)
                    }
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
                  onClick={() =>
                    setCurrentPage((prev) =>
                      Math.min(prev + 1, totalPages)
                    )
                  }
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

export default ProductStockReport;