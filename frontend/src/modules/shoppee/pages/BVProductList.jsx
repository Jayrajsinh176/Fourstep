import { useEffect, useState } from "react";
import jsPDF from "jspdf";
import autoTable from "jspdf-autotable";
import { MdOutlineFileDownload } from "react-icons/md";
import api from "../api/axios";

const ROWS_PER_PAGE = 10;

const formatCurrency = (value) => {
  const amount = Number(value) || 0;
  return `₹${amount.toLocaleString("en-IN", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })}`;
};

// jsPDF's default font has no ₹ glyph, so PDF cells use "Rs." instead
const formatCurrencyForPdf = (value) => {
  const amount = Number(value) || 0;
  return `Rs. ${amount.toLocaleString("en-IN", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })}`;
};

const loadImageAsDataURL = (src) =>
  new Promise((resolve, reject) => {
    const img = new Image();
    img.onload = () => {
      const canvas = document.createElement("canvas");
      canvas.width = img.naturalWidth;
      canvas.height = img.naturalHeight;
      canvas.getContext("2d").drawImage(img, 0, 0);
      resolve(canvas.toDataURL("image/png"));
    };
    img.onerror = reject;
    img.src = src;
  });

export default function BVProductList() {
  const [rows, setRows] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState("");
  const [currentPage, setCurrentPage] = useState(1);

  useEffect(() => {
    let isMounted = true;

    const fetchProducts = async () => {
      try {
        if (isMounted) {
          setIsLoading(true);
          setError("");
        }

        const response = await api.get("/products");
        const products = Array.isArray(response.data) ? response.data : [];

        const flattened = products.flatMap((product) =>
          (Array.isArray(product.variants) ? product.variants : []).map(
            (variant) => ({
              _row_key: `p${product.id}-v${variant.id}`,
              product_name: product.name,
              packing_size: variant.packing_size,
              mrp: variant.price,
              offer_price: variant.offer_price,
              bv: variant.bv,
              cashback: variant.cashback,
            }),
          ),
        );

        if (isMounted) {
          setRows(flattened);
          setCurrentPage(1);
        }
      } catch (fetchError) {
        if (isMounted) {
          setError(
            fetchError?.response?.data?.message ||
              "Unable to fetch product list.",
          );
          setRows([]);
        }
      } finally {
        if (isMounted) {
          setIsLoading(false);
        }
      }
    };

    fetchProducts();

    return () => {
      isMounted = false;
    };
  }, []);

  const totalPages = Math.ceil(rows.length / ROWS_PER_PAGE);
  const indexOfFirstItem = (currentPage - 1) * ROWS_PER_PAGE;
  const paginatedRows = rows.slice(
    indexOfFirstItem,
    indexOfFirstItem + ROWS_PER_PAGE,
  );

  const handleDownloadPDF = async () => {
    if (!rows.length) return;

    const pdf = new jsPDF("p", "mm", "a4");
    const pageWidth = pdf.internal.pageSize.getWidth();
    let y = 15;

    try {
      const logoDataUrl = await loadImageAsDataURL("/images/fourstep_logo.png");
      const logoWidth = 30;
      const logoHeight = 16;
      pdf.addImage(
        logoDataUrl,
        "PNG",
        (pageWidth - logoWidth) / 2,
        y,
        logoWidth,
        logoHeight,
      );
      y += logoHeight + 6;
    } catch {
      // Logo failed to load — continue without it
    }

    pdf.setFontSize(20);
    pdf.setTextColor(176, 66, 46);
    pdf.text("FourStep Retail", 105, y, { align: "center" });

    y += 8;
    pdf.setFontSize(16);
    pdf.setTextColor(0, 0, 0);
    pdf.text("BV Product List", 105, y, { align: "center" });

    y += 6;
    pdf.setDrawColor(176, 66, 46);
    pdf.line(14, y, 196, y);

    y += 8;

    const tableData = rows.map((row, index) => [
      index + 1,
      row.product_name || "--",
      row.packing_size || "--",
      formatCurrencyForPdf(row.mrp),
      formatCurrencyForPdf(row.offer_price),
      row.bv ?? "--",
      `${Number(row.cashback || 0)}%`,
    ]);

    autoTable(pdf, {
      startY: y,
      head: [
        [
          "Sr.",
          "Product Name",
          "Packing Size",
          "MRP",
          "Offer Price",
          "BV",
          "Cashback",
        ],
      ],
      body: tableData,
      theme: "grid",
      headStyles: {
        fillColor: [176, 66, 46],
        textColor: [255, 255, 255],
        fontStyle: "bold",
      },
      styles: {
        fontSize: 9,
        cellPadding: 3,
      },
    });

    pdf.save("BV-Product-List.pdf");
  };

  return (
    <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">
      <div className="flex-1 min-w-0 flex flex-col">
        <div className="text-center mt-4 md:mt-6">
          <h1 className="text-2xl md:text-3xl font-bold text-[#B0422E]">
            BV Product List
          </h1>
          <p className="text-gray-500 mt-1 text-sm">
            View packing size, MRP, offer price, BV and cashback for all
            products
          </p>
        </div>

        <div className="p-3 md:p-6">
          <div className="bg-white rounded-2xl shadow-sm p-3 md:p-6">
            <div className="sticky top-0 z-10 bg-white flex justify-end mb-3 pb-2">
              <button
                type="button"
                onClick={handleDownloadPDF}
                disabled={isLoading || !rows.length}
                className="text-xs md:text-sm font-semibold bg-[#B0422E] text-white px-3 md:px-4 py-2 rounded-lg hover:bg-[#963823] flex items-center gap-2 shrink-0 disabled:opacity-50"
              >
                <MdOutlineFileDownload className="text-lg" />
                <span>Download PDF</span>
              </button>
            </div>

            {isLoading && (
              <p className="text-center text-gray-500 py-6">Loading...</p>
            )}
            {!isLoading && error && (
              <p className="text-center text-red-500 py-4">{error}</p>
            )}

            {!isLoading && !error && (
              <div className="overflow-x-auto">
              <table className="w-full min-w-max text-xs md:text-sm">
                <thead>
                  <tr className="bg-[#B0422E] text-white font-semibold">
                    <th className="py-2 md:py-3 px-2 md:px-4 rounded-l-xl">
                      Sr.No
                    </th>
                    <th className="py-2 md:py-3 px-2 md:px-4 whitespace-nowrap text-left max-w-70">
                      Product Name
                    </th>
                    <th className="py-2 md:py-3 px-2 md:px-4 whitespace-nowrap">
                      Packing Size
                    </th>
                    <th className="py-2 md:py-3 px-2 md:px-4 whitespace-nowrap">
                      MRP
                    </th>
                    <th className="py-2 md:py-3 px-2 md:px-4 whitespace-nowrap">
                      Offer Price
                    </th>
                    <th className="py-2 md:py-3 px-2 md:px-4">BV</th>
                    <th className="py-2 md:py-3 px-2 md:px-4 rounded-r-xl whitespace-nowrap">
                      Cashback
                    </th>
                  </tr>
                </thead>

                <tbody className="text-center font-medium">
                  {paginatedRows.length > 0 ? (
                    paginatedRows.map((row, index) => (
                      <tr
                        className="border-b border-gray-200"
                        key={row._row_key}
                      >
                        <td className="py-2 md:py-4 px-2 md:px-4">
                          {indexOfFirstItem + index + 1}
                        </td>
                        <td className="py-2 md:py-4 px-2 md:px-4 text-left max-w-70 wrap-break-word">
                          {row.product_name || "--"}
                        </td>
                        <td className="py-2 md:py-4 px-2 md:px-4 whitespace-nowrap">
                          {row.packing_size || "--"}
                        </td>
                        <td className="py-2 md:py-4 px-2 md:px-4 whitespace-nowrap">
                          {formatCurrency(row.mrp)}
                        </td>
                        <td className="py-2 md:py-4 px-2 md:px-4 whitespace-nowrap">
                          {formatCurrency(row.offer_price)}
                        </td>
                        <td className="py-2 md:py-4 px-2 md:px-4">
                          {row.bv ?? "--"}
                        </td>
                        <td className="py-2 md:py-4 px-2 md:px-4 whitespace-nowrap">
                          {Number(row.cashback || 0)}%
                        </td>
                      </tr>
                    ))
                  ) : (
                    <tr>
                      <td colSpan="7" className="py-6 text-center text-gray-500">
                        No products found
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
              </div>
            )}

            {totalPages > 1 && (
              <div className="mt-4 flex flex-wrap items-center justify-center gap-2">
                <button
                  type="button"
                  onClick={() =>
                    setCurrentPage((prev) => Math.max(prev - 1, 1))
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
                  onClick={() =>
                    setCurrentPage((prev) => Math.min(prev + 1, totalPages))
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
