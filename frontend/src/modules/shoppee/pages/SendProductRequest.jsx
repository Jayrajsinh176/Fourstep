import React, { useEffect, useState } from "react";
import Sidebar from "../components/sidebar";
import Navbar from "../components/navbar";
import { FaSearch } from "react-icons/fa";
import { shoppeeApi as api } from "../api/axios";

function SendProductRequest() {

  const [sendproductrequest, setSendProductRequest] = useState([]);
  const [statusFilter, setStatusFilter] = useState("all");
  const [searchTerm, setSearchTerm] = useState("");
  const [activeSearch, setActiveSearch] = useState("");
  const [searchError, setSearchError] = useState("");
  const [selectedRequest, setSelectedRequest] = useState(null);
  const [currentPage, setCurrentPage] = useState(1);
  const requestsPerPage = 10;

  const handleSearch = () => {
    if (!searchTerm.trim()) {
      setSearchError("Please enter a search term.");
      return;
    }

    setSearchError("");
    setActiveSearch(searchTerm.trim());
    setCurrentPage(1);
  };

  // FETCH REQUESTS
  useEffect(() => {

    const fetchRequests = async (status = "all") => {
      try {
        const user = JSON.parse(localStorage.getItem("user"));

        const res = await api.get("/product-requests", {
          params: {
            status,
            member_id: user?.id
          }
        });
        setSendProductRequest(res.data);
        setCurrentPage(1);

      } catch (error) {
        console.error(error);
      }
    };

    fetchRequests(statusFilter);
  }, [statusFilter]);

  const filteredRequests = sendproductrequest.filter((req) => {
    if (!activeSearch) return true;
    return req.productname?.toLowerCase().includes(activeSearch.toLowerCase());
  });

  const indexOfLastRequest = currentPage * requestsPerPage;
  const indexOfFirstRequest = indexOfLastRequest - requestsPerPage;
  const currentRequests = filteredRequests.slice(
    indexOfFirstRequest,
    indexOfLastRequest
  );
  const totalPages = Math.ceil(filteredRequests.length / requestsPerPage);


  return (
    <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">


      <div className="flex-1 min-w-0 flex flex-col">


        <div className="text-center mt-6">
          <h1 className="text-3xl font-bold text-[#B0422E]">
            Send Product Request
          </h1>
        </div>

        <div className="p-6">
          <div className="bg-white rounded-2xl shadow-sm p-6 overflow-x-auto">

            <div className="flex mb-3 items-center">

              <button
                onClick={() => setStatusFilter("all")}
                className="border border-gray-200 rounded px-4 py-1 mr-2 bg-gray-100 hover:bg-gray-300"
              >
                All
              </button>

              <button
                onClick={() => setStatusFilter("Pending")}
                className="border border-gray-200 rounded px-4 py-1 mr-2 bg-gray-100 hover:bg-gray-300"
              >
                Pending
              </button>

              <button
                onClick={() => setStatusFilter("Stock Inward")}
                className="border border-gray-200 rounded px-4 py-1 mr-2 bg-gray-100 hover:bg-gray-300"
              >
                Stock Inward
              </button>

              <button
                onClick={() => setStatusFilter("Approved")}
                className="border border-gray-200 rounded px-4 py-1 mr-2 bg-gray-100 hover:bg-gray-300"
              >
                Approved
              </button>

              <button
                onClick={() => setStatusFilter("Cancelled")}
                className="border border-gray-200 rounded px-4 py-1 mr-2 bg-gray-100 hover:bg-gray-300"
              >
                Cancelled
              </button>

              <div className="ml-auto flex flex-col items-end">
                <div className="flex items-center justify-end border-gray-300 bg-gray-100 rounded-lg px-3 py-1">

                  <input
                    value={searchTerm}
                    onChange={(e) => {
                      const value = e.target.value;
                      setSearchTerm(value);
                      if (!value.trim()) {
                        setActiveSearch("");
                        setSearchError("");
                        setCurrentPage(1);
                      }
                    }}
                    type="text"
                    placeholder="Search..."
                    className="outline-none text-sm px-2 py-1 bg-transparent"
                  />
                  <button
                    type="button"
                    onClick={handleSearch}
                    disabled={!searchTerm.trim()}
                    className="text-sm font-semibold disabled:cursor-not-allowed disabled:opacity-50"
                  >
                    <FaSearch />
                  </button>
                </div>
                {searchError ? (
                  <span className="text-xs text-red-600 mt-1">{searchError}</span>
                ) : null}
              </div>

            </div>

            <table className="w-full min-w-190 text-sm text-center">

              <thead>


                <tr className="bg-[#B0422E] text-white">
                  <th className="py-3 px-4 rounded-l-xl">Request No</th>
                  <th className="py-3 px-4">Products</th>
                  <th className="py-3 px-4">Date</th>
                  <th className="py-3 px-4">Total Products</th>
                  <th className="py-3 px-4">Total Amount</th>
                  <th className="py-3 px-4">Total PV</th>
                  <th className="py-3 px-4">Total BV</th>
                  <th className="py-3 px-4">Status</th>
                  <th className="py-3 px-4 rounded-r-xl">Action</th>
                </tr>
              </thead>

              <tbody className="font-medium">

                {currentRequests.map((send, index) => (

                  <tr className="border-b border-gray-400" key={send.id}>

                    {/* REQUEST NO */}
                    <td className="py-4 px-4">
                      {index + 1}
                    </td>

                    {/* PRODUCTS */}
                    <td className="py-4 px-4 text-left">

                      {send.products?.length > 0 ? (

                        <div className="space-y-2">

                          {send.products.map((pro, i) => (

                            <div key={i}>

                              <div className="font-medium">
                                {pro.productname} ({pro.packing_size})
                              </div>

                              <div className="text-xs text-gray-500">
                                Qty: {pro.quantity}
                              </div>

                            </div>

                          ))}

                        </div>

                      ) : (

                        <span>No Products</span>

                      )}

                    </td>

                    {/* DATE */}
                    <td className="py-4 px-4">
                      {new Date(send.date).toLocaleDateString()}
                    </td>

                    {/* TOTAL PRODUCTS */}
                    <td className="py-4 px-4">
                      {send.total_products}
                    </td>

                    {/* TOTAL AMOUNT */}
                    <td className="py-4 px-4">
                      {send.total_amount}
                    </td>

                    {/* TOTAL PV */}
                    <td className="py-4 px-4">
                      {send.total_pv}
                    </td>

                    {/* TOTAL BV */}
                    <td className="py-4 px-4">
                      {send.total_bv}
                    </td>

                    {/* STATUS */}
                    <td
                      className={
                        send.status === "Approved"
                          ? "text-green-600 py-4 px-4"
                          : send.status === "Cancelled"
                            ? "text-red-600 py-4 px-4"
                            : "text-orange-500 py-4 px-4"
                      }
                    >
                      {send.status}
                    </td>
                    <td className="py-4 px-4">
                      <button
                        onClick={() => setSelectedRequest(send)}
                        className="bg-[#B0422E] text-white px-4 py-1 rounded-lg hover:bg-[#8f3525]"
                      >
                        View
                      </button>
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

      {/* INVOICE MODAL */}
      {selectedRequest && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">

          <div className="bg-white w-full max-w-5xl rounded-3xl shadow-2xl overflow-hidden">

            {/* HEADER */}
            <div className="bg-[#B0422E] text-white px-8 py-6 flex justify-between items-center">

              <div>
                <h2 className="text-3xl font-bold">
                  Invoice Details
                </h2>

                <p className="text-sm opacity-90 mt-1">
                  Product Request Invoice
                </p>
              </div>

              <button
                onClick={() => setSelectedRequest(null)}
                className="text-white text-3xl hover:scale-110 duration-200"
              >
                ×
              </button>

            </div>

            {/* BODY */}
            <div className="p-8">

              {/* TOP INFO */}
              <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

                <div className="bg-gray-50 rounded-2xl p-5 border border-gray-200">

                  <p className="text-sm text-gray-500 mb-1">
                    Request Date
                  </p>

                  <h3 className="text-lg font-semibold text-gray-800">
                    {new Date(selectedRequest.date).toLocaleDateString()}
                  </h3>

                </div>

                <div className="bg-gray-50 rounded-2xl p-5 border border-gray-200">

                  <p className="text-sm text-gray-500 mb-1">
                    Status
                  </p>

                  <span
                    className={`inline-block px-4 py-1 rounded-full text-sm font-semibold
                ${selectedRequest.status === "Approved"
                        ? "bg-green-100 text-green-700"
                        : selectedRequest.status === "Cancelled"
                          ? "bg-red-100 text-red-700"
                          : "bg-orange-100 text-orange-700"
                      }`}
                  >
                    {selectedRequest.status}
                  </span>

                </div>

                <div className="bg-gray-50 rounded-2xl p-5 border border-gray-200">

                  <p className="text-sm text-gray-500 mb-1">
                    Total Products
                  </p>

                  <h3 className="text-lg font-semibold text-gray-800">
                    {selectedRequest.total_products}
                  </h3>

                </div>

              </div>

              {/* TABLE */}
              <div className="overflow-x-auto rounded-2xl border border-gray-200">

                <table className="w-full text-sm">

                  <thead className="bg-[#B0422E] text-white">

                    <tr>

                      <th className="py-4 px-4 text-left">
                        Product
                      </th>

                      <th className="py-4 px-4 text-center">
                        Quantity
                      </th>

                      <th className="py-4 px-4 text-center">
                        Total Amount
                      </th>

                      <th className="py-4 px-4 text-center">
                        Total PV
                      </th>
                      <th className="py-4 px-4 text-center rounded-r-xl">
                        Total BV
                      </th>
                      

                    </tr>

                  </thead>

                  <tbody>

                    {selectedRequest.products?.map((item, index) => {

                      return (

                        <tr
                          key={index}
                          className="border-b border-gray-200 hover:bg-gray-50"
                        >

                          <td className="py-4 px-4 font-medium text-gray-800">
                            {item.productname} ({item.packing_size})
                          </td>
                          <td className="py-4 px-4 text-center">
                            {item.quantity}
                          </td>

                          <td className="py-4 px-4 text-center font-semibold text-[#B0422E]">
                            ₹ {selectedRequest.total_amount}
                          </td>

                          <td className="py-4 px-4 text-center font-semibold">
                            {selectedRequest.total_pv}
                          </td>

                          <td className="py-4 px-4 text-center font-semibold">
                            {selectedRequest.total_bv}
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
      )}
    </div>
  );
}

export default SendProductRequest;