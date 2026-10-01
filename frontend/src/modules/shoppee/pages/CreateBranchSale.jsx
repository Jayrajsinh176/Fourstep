import React, { useEffect, useState, useCallback } from "react";
import Sidebar from "../components/sidebar";
import Navbar from "../components/navbar";
import { shoppeeApi as api } from "../api/axios";

function Toast({ toasts, removeToast }) {
  return (
    <div className="fixed top-5 right-5 z-50 flex flex-col gap-3 w-80">
      {toasts.map((t) => (
        <div
          key={t.id}
          className={`flex items-start gap-3 px-4 py-3 rounded-xl shadow-md text-sm font-medium border transition-all
            ${t.type === "success" ? "bg-green-50 border-green-200 text-green-800" : ""}
            ${t.type === "error" ? "bg-red-50 border-red-200 text-red-800" : ""}
            ${t.type === "warning" ? "bg-yellow-50 border-yellow-200 text-yellow-800" : ""}
          `}
        >
          <span className="text-lg leading-none mt-0.5">
            {t.type === "success" && "✅"}
            {t.type === "error" && "❌"}
            {t.type === "warning" && "⚠️"}
          </span>
          <span className="flex-1 leading-snug">{t.message}</span>
          <button
            onClick={() => removeToast(t.id)}
            className="text-gray-400 hover:text-gray-600 leading-none text-base"
          >
            ✕
          </button>
        </div>
      ))}
    </div>
  );
}

function useToast() {
  const [toasts, setToasts] = useState([]);

  const showToast = useCallback((type, message) => {
    const id = Date.now();
    setToasts((prev) => [...prev, { id, type, message }]);
    setTimeout(() => {
      setToasts((prev) => prev.filter((t) => t.id !== id));
    }, 4000);
  }, []);

  const removeToast = useCallback((id) => {
    setToasts((prev) => prev.filter((t) => t.id !== id));
  }, []);

  return { toasts, showToast, removeToast };
}

function CreateBranchSale() {

  const { toasts, showToast, removeToast } = useToast();

  const [orderTypes, setOrderTypes] = useState([]);

  const [selectedOrderType, setSelectedOrderType] = useState("");

  const [products, setProducts] = useState([]);

  const [currentPage, setCurrentPage] = useState(1);

  const itemsPerPage = 10;

  const [customerId, setCustomerId] = useState("");

  const [customerData, setCustomerData] = useState(null);

  const [showOrderSection, setShowOrderSection] = useState(false);

  const [showPasswordModal, setShowPasswordModal] = useState(false);
  const [transactionPassword, setTransactionPassword] = useState("");

  useEffect(() => {
    fetchOrderTypes();
  }, []);

  const fetchOrderTypes = async () => {
    try {
      const res = await api.get("/order-types");
      if (res.data.success) {
        setOrderTypes(res.data.types);
      }
    } catch (err) {
      console.log(err);
    }
  };

  const verifyCustomer = async () => {
    if (!customerId) {
      showToast("warning", "Please enter User ID / Mobile / Email");
      return;
    }
    try {
      const res = await api.get(`/verify-ecom-member/${customerId}`);
      if (res.data.success) {
        setCustomerData(res.data.member);
        setShowOrderSection(true);
      } else {
        showToast("error", "Member not found");
      }
    } catch (err) {
      console.log(err);
      showToast("error", err.response?.data?.message || "Member not found");
    }
  };

  const handleOrderTypeChange = async (typeId) => {
    setSelectedOrderType(typeId);
    setCurrentPage(1);
    try {
      const res = await api.get(`/products-by-order-type/${typeId}`);
      if (res.data.success) {
        const updatedProducts = res.data.products.map((item) => ({
          ...item,
          quantity: 1,
          checked: false,
        }));
        setProducts(updatedProducts);
      }
    } catch (err) {
      console.log(err);
    }
  };

  const handleCheckbox = (index) => {
    const updated = [...products];
    updated[index].checked = !updated[index].checked;
    setProducts(updated);
  };

  const handleQuantityChange = (index, value) => {
    const updated = [...products];
    updated[index].quantity = value;
    setProducts(updated);
  };

  const resetState = () => {
    setCustomerId("");
    setCustomerData(null);
    setShowOrderSection(false);
    setProducts([]);
    setSelectedOrderType("");
    setShowPasswordModal(false);

    setTransactionPassword("");
  };
  
  const handleSubmitSelected = async () => {
    const selected = products.filter((item) => item.checked);
    if (selected.length === 0) {
      showToast("warning", "Please select products");
      return;
    }
    try {
      const payload = {
        member_id: JSON.parse(localStorage.getItem("user"))?.member_id,
        transaction_password: transactionPassword,
        customer_user_id: customerData.member_id || customerData.user_id,
        order_type_id: selectedOrderType,
        products: selected,
      };
      const res = await api.post("/submit-turnover-order", payload);
      if (res.data.success) {
        showToast("success", "Order placed successfully!");
        resetState();
      } else {
        showToast("error", res.data.message);
      }
    } catch (err) {
      console.log(err.response?.data);
      const backendMessage = err.response?.data?.message;
      if (backendMessage) {
        showToast("error", backendMessage);
      } else {
        showToast("error", "Something went wrong");
      }
      resetState();
    }
  };

  const indexOfLastItem = currentPage * itemsPerPage;
  const indexOfFirstItem = indexOfLastItem - itemsPerPage;
  const currentProducts = products.slice(indexOfFirstItem, indexOfLastItem);
  const totalPages = Math.ceil(products.length / itemsPerPage);

  return (

    <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">

      <Toast toasts={toasts} removeToast={removeToast} />

      <div className="flex-1 min-w-0 flex flex-col">

        <div className="w-full px-3 sm:px-5 lg:px-8 py-5">

          {/* PAGE TITLE */}
          <h1 className="text-center text-2xl sm:text-3xl lg:text-5xl font-bold text-[#B0422E] mb-6">
            IBO Branch Sale
          </h1>


          {/* VERIFY USER SECTION */}
          {!customerData && (
            <div className="bg-white rounded-2xl border border-gray-200 shadow-sm mb-6 p-4 sm:p-6">

              <div className="flex flex-col lg:flex-row gap-4 lg:items-end">

                <div className="flex-1">

                  <label className="block text-gray-700 font-semibold mb-2">
                    Enter User ID / Mobile No / Email
                  </label>

                  <input
                    type="text"
                    value={customerId}
                    onChange={(e) => setCustomerId(e.target.value)}
                    placeholder="Enter User ID / Mobile No / Email"
                    className="w-full h-12 border border-gray-300 rounded-xl px-4 focus:outline-none focus:ring-2 focus:ring-[#B0422E]"
                  />

                </div>

                <button
                  onClick={verifyCustomer}
                  className="bg-[#B0422E] hover:bg-[#933621] text-white px-8 h-12 rounded-xl font-semibold"
                >
                  Verify User
                </button>

              </div>

            </div>
          )}

          {/* USER DETAILS */}
          {customerData && (
            <div className="bg-white rounded-2xl border border-gray-200 shadow-sm mb-6 p-4 sm:p-6">

              <div className="flex justify-end mb-4">
                <button
                  onClick={() => {
                    setCustomerId("");
                    setCustomerData(null);
                    setShowOrderSection(false);
                    setProducts([]);
                    setSelectedOrderType("");
                  }}
                  className="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg"
                >
                  Change User
                </button>
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">

                <div className="bg-gray-100 rounded-xl p-4">
                  <p className="text-sm text-gray-500">User ID</p>
                  <p className="font-semibold">{customerData.member_id}</p>
                </div>

                <div className="bg-gray-100 rounded-xl p-4">
                  <p className="text-sm text-gray-500">Name</p>
                  <p className="font-semibold">{customerData.fullname}</p>
                </div>

                <div className="bg-gray-100 rounded-xl p-4">
                  <p className="text-sm text-gray-500">Mobile</p>
                  <p className="font-semibold">{customerData.mobile_no}</p>
                </div>

                <div className="bg-gray-100 rounded-xl p-4">
                  <p className="text-sm text-gray-500">City</p>
                  <p className="font-semibold">{customerData.city}</p>
                </div>

                <div className="bg-gray-100 rounded-xl p-4">
                  <p className="text-sm text-gray-500">User Type</p>
                  <p className="font-semibold">
                    {customerData.user_id ? "MLM User" : "Normal User"}
                  </p>
                </div>

              </div>

            </div>
          )}

          {/* ORDER TYPE SECTION */}
          {showOrderSection && (

            <div className="bg-white rounded-2xl border border-gray-200 shadow-sm mb-6 p-4 sm:p-6">

              <div className="flex flex-col sm:flex-row sm:items-center gap-3">

                <label className="text-gray-700 font-semibold text-sm sm:text-base">
                  Select Order Type
                </label>

                <select
                  value={selectedOrderType}
                  onChange={(e) => handleOrderTypeChange(e.target.value)}
                  className="w-full sm:w-72 h-12 bg-gray-100 border border-gray-300 rounded-xl px-4 focus:outline-none focus:ring-2 focus:ring-[#B0422E]"
                >
                  <option value="">Category</option>
                  {orderTypes.map((type) => (
                    <option key={type.id} value={type.id}>
                      {type.name}
                    </option>
                  ))}
                </select>

              </div>

            </div>

          )}

          {/* PRODUCT TABLE */}
          {products.length > 0 && (

            <div className="bg-white rounded-3xl border border-gray-200 shadow-sm p-3 sm:p-6 overflow-x-auto">

              {/* SUBMIT BUTTON */}
              <div className="flex justify-center sm:justify-start mb-5">
                <button
                  onClick={() => {

                    const selected =
                      products.filter((item) => item.checked);

                    if (selected.length === 0) {

                      showToast(
                        "warning",
                        "Please select products"
                      );

                      return;
                    }

                    setShowPasswordModal(true);

                  }}
                  className="bg-[#B0422E] hover:bg-[#933621] text-white px-6 sm:px-8 py-3 rounded-xl font-semibold text-sm sm:text-base transition-all duration-200"
                >
                  Submit Selected
                </button>
              </div>

              <table className="w-full min-w-[1400px]">

                <thead>
                  <tr className="bg-[#B0422E] text-white text-center text-sm sm:text-base">
                    <th className="py-4 rounded-l-2xl">Select</th>
                    <th className="py-4">Sr No</th>
                    <th className="py-4">Category</th>
                    <th className="py-4">Product Name</th>
                    <th className="py-4">Packing Size</th>
                    <th className="py-4">PV</th>
                    <th className="py-4">BV</th>
                    <th className="py-4">MRP</th>
                    <th className="py-4">Offer Price</th>
                    <th className="py-4">Quantity</th>
                    <th className="py-4">Total Amount</th>
                    {/* <th className="py-4">Commission</th> */}
                    <th className="py-4">Net Amount</th>
                    <th className="py-4">Total PV</th>
                    <th className="py-4 rounded-r-2xl">Total BV</th>
                  </tr>
                </thead>

                <tbody>
                  {currentProducts.map((item, index) => {

                    const totalAmount = Number(
                      (item.offer_price * item.quantity).toFixed(2)
                    );

                    const totalPV = Number(
                      (item.pv * item.quantity).toFixed(2)
                    );

                    const totalBV = Number(
                      (item.bv * item.quantity).toFixed(2)
                    );

                    return (
                      <tr
                        key={index}
                        className="text-center border-b border-gray-200 text-sm sm:text-base"
                      >

                        <td className="py-5">
                          <input
                            type="checkbox"
                            checked={item.checked}
                            onChange={() => handleCheckbox(indexOfFirstItem + index)}
                            className="w-4 h-4"
                          />
                        </td>

                        <td>{indexOfFirstItem + index + 1}</td>

                        <td>{item.category_name || "Wellness"}</td>

                        <td className="font-medium px-2">{item.name}</td>

                        <td>{item.packing_size}</td>

                        <td>{item.pv}</td>

                        <td>{item.bv}</td>

                        <td>₹{item.price}</td>

                        <td>₹{item.offer_price}</td>

                        <td>
                          <input
                            type="number"
                            min="1"
                            value={item.quantity}
                            onChange={(e) =>
                              handleQuantityChange(indexOfFirstItem + index, e.target.value)
                            }
                            className="w-20 h-10 border border-gray-300 rounded-xl text-center focus:outline-none focus:ring-2 focus:ring-[#B0422E]"
                          />
                        </td>
                        <td className="font-semibold">
                          ₹{totalAmount.toFixed(2)}
                        </td>

                        <td className="font-semibold">
                          ₹{totalAmount.toFixed(2)}
                        </td>

                        <td className="font-semibold">
                          {totalPV.toFixed(2)}
                        </td>

                        <td className="font-semibold">
                          {totalBV.toFixed(2)}
                        </td>

                      </tr>
                    );
                  })}
                </tbody>

              </table>

              {/* PAGINATION */}
              <div className="flex justify-center items-center gap-4 mt-6">

                <button
                  disabled={currentPage === 1}
                  onClick={() => setCurrentPage(currentPage - 1)}
                  className="px-5 py-2 bg-[#B0422E] text-white rounded-xl disabled:opacity-50"
                >
                  Previous
                </button>

                <span className="font-semibold">
                  Page {currentPage} of {totalPages}
                </span>

                <button
                  disabled={currentPage === totalPages}
                  onClick={() => setCurrentPage(currentPage + 1)}
                  className="px-5 py-2 bg-[#B0422E] text-white rounded-xl disabled:opacity-50"
                >
                  Next
                </button>

              </div>

            </div>

          )}

        </div>

      </div>
      {showPasswordModal && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50">

          <div className="bg-white rounded-xl p-6 w-full max-w-md">

            <h2 className="text-xl font-bold text-[#B0422E] mb-4">
              Enter Transaction Password
            </h2>

            <input
              type="password"
              value={transactionPassword}
              onChange={(e) =>
                setTransactionPassword(e.target.value)
              }
              placeholder="Transaction Password"
              className="w-full border rounded-lg px-3 py-2 mb-4"
            />

            <div className="flex justify-end gap-2">

              <button
                onClick={() => {
                  setShowPasswordModal(false);
                  setTransactionPassword("");
                }}
                className="px-4 py-2 bg-gray-300 rounded-lg"
              >
                Cancel
              </button>

              <button
                onClick={() => {

                  if (!transactionPassword) {

                    showToast(
                      "warning",
                      "Please enter transaction password"
                    );

                    return;
                  }

                  handleSubmitSelected();
                }}
                className="px-4 py-2 bg-[#B0422E] text-white rounded-lg"
              >
                Verify & Submit
              </button>

            </div>

          </div>

        </div>
      )}

    </div>

  );
}

export default CreateBranchSale;