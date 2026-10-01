import { useEffect, useState } from "react";
import jsPDF from "jspdf";
import autoTable from "jspdf-autotable";
import { FiDownload } from "react-icons/fi";
import Sidebar from "../components/Sidebar";
import Navbar from "../components/Navbar";
import ReferralTableCard from "../components/ReferralTableCard";

const API_BASE_URL =
  import.meta.env.VITE_API_BASE_URL || "https://fourstepretail.com/api";

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

        const response = await fetch(`${API_BASE_URL}/products`, {
          headers: { Accept: "application/json" },
        });

        const data = await response.json();

        if (!response.ok) {
          throw new Error(data?.message || "Unable to fetch product list.");
        }

        const products = Array.isArray(data) ? data : [];

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
          setError(fetchError.message || "Unable to fetch product list.");
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

  const columns = [
    {
      key: "sr_no",
      header: "Sr.No",
      render: (row, rowIndex) =>
        (currentPage - 1) * ROWS_PER_PAGE + rowIndex + 1,
    },
    {
      key: "product_name",
      header: "Product Name",
      render: (row) => row.product_name || "--",
    },
    {
      key: "packing_size",
      header: "Packing Size",
      render: (row) => row.packing_size || "--",
    },
    {
      key: "mrp",
      header: "MRP",
      render: (row) => formatCurrency(row.mrp),
    },
    {
      key: "offer_price",
      header: "Offer Price",
      render: (row) => formatCurrency(row.offer_price),
    },
    {
      key: "bv",
      header: "BV",
      render: (row) => row.bv ?? "--",
    },
    {
      key: "cashback",
      header: "Cashback",
      render: (row) => `${Number(row.cashback || 0)}%`,
    },
  ];

  const totalPages = Math.ceil(rows.length / ROWS_PER_PAGE);
  const paginatedRows = rows.slice(
    (currentPage - 1) * ROWS_PER_PAGE,
    currentPage * ROWS_PER_PAGE,
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
      <Sidebar />

      <div className="flex-1 min-w-0 flex flex-col">
        <Navbar />

        <div className="p-6">
          <div className="flex flex-wrap items-center justify-between gap-3 mb-6">
            <div>
              <h1 className="text-2xl md:text-3xl font-bold text-[#B0422E]">
                BV Product List
              </h1>
              <p className="text-gray-500 mt-1 text-sm">
                View packing size, MRP, offer price, BV and cashback for all products
              </p>
            </div>

            <button
              type="button"
              onClick={handleDownloadPDF}
              disabled={isLoading || !rows.length}
              className="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-[#B0422E] text-white text-sm font-medium disabled:opacity-50"
            >
              <FiDownload /> Download PDF
            </button>
          </div>

          <div className="bg-white rounded-xl shadow-sm">
            <ReferralTableCard
              title=""
              columns={columns}
              rows={paginatedRows}
              isLoading={isLoading}
              error={error}
              emptyMessage="No products found"
            />

            {!isLoading && totalPages > 1 && (
              <div className="px-6 pb-6 -mt-4 flex items-center justify-between flex-wrap gap-3">
                <p className="text-sm text-gray-500">
                  Showing{" "}
                  <span className="font-semibold text-gray-700">
                    {(currentPage - 1) * ROWS_PER_PAGE + 1}
                  </span>
                  –
                  <span className="font-semibold text-gray-700">
                    {Math.min(currentPage * ROWS_PER_PAGE, rows.length)}
                  </span>{" "}
                  of{" "}
                  <span className="font-semibold text-gray-700">
                    {rows.length}
                  </span>{" "}
                  products
                </p>
                <div className="flex items-center gap-1.5">
                  <button
                    onClick={() => setCurrentPage((p) => Math.max(1, p - 1))}
                    disabled={currentPage === 1}
                    className="px-3 py-1.5 rounded-lg border text-sm font-medium disabled:opacity-40 disabled:cursor-not-allowed bg-white border-gray-200 hover:border-[#B0422E] hover:text-[#B0422E] transition-colors"
                  >
                    ← Prev
                  </button>
                  {Array.from({ length: totalPages }, (_, i) => i + 1).map(
                    (page) => (
                      <button
                        key={page}
                        onClick={() => setCurrentPage(page)}
                        className={`w-8 h-8 rounded-lg text-sm font-semibold transition-colors ${
                          page === currentPage
                            ? "bg-[#B0422E] text-white shadow-sm"
                            : "bg-white border border-gray-200 text-gray-600 hover:border-[#B0422E] hover:text-[#B0422E]"
                        }`}
                      >
                        {page}
                      </button>
                    ),
                  )}
                  <button
                    onClick={() =>
                      setCurrentPage((p) => Math.min(totalPages, p + 1))
                    }
                    disabled={currentPage === totalPages}
                    className="px-3 py-1.5 rounded-lg border text-sm font-medium disabled:opacity-40 disabled:cursor-not-allowed bg-white border-gray-200 hover:border-[#B0422E] hover:text-[#B0422E] transition-colors"
                  >
                    Next →
                  </button>
                </div>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
