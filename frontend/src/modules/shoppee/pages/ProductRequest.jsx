import React, { useEffect, useState } from "react";
import { shoppeeApi as api } from "../api/axios";
import { ToastContainer, useToast } from "../components/Toast";

function ProductRequest() {

  const [products, setProducts] = useState([]);
  const [categories, setCategories] = useState([]);
  const [selectedCategory, setSelectedCategory] = useState("");
  const [currentPage, setCurrentPage] = useState(1);
  const [purchaseBalance, setPurchaseBalance] = useState(0);
  const [showPasswordModal, setShowPasswordModal] = useState(false);
  const [transactionPassword, setTransactionPassword] = useState("");
  const { toasts, showToast, removeToast } = useToast();

  // FETCH CATEGORIES
  useEffect(() => {

    const fetchCategories = async () => {

      try {

        const res = await api.get("/categories");

        const uniqueCategories = [
          ...new Map(
            res.data.map((item) => [item.category, item])
          ).values()
        ];

        setCategories(uniqueCategories);

      } catch (error) {

        console.error(error);
      }
    };

    fetchCategories();

  }, []);

  useEffect(() => {

    const fetchBalance = async () => {

      try {

        const user = JSON.parse(
          localStorage.getItem("user")
        );

        const res = await api.get(
          "/transactions",
          {
            params: {
              member_id: user?.id
            }
          }
        );

        setPurchaseBalance(
          res.data.purchase_balance || 0
        );

      } catch (error) {

        console.error(error);
      }
    };

    fetchBalance();

  }, []);

  // FETCH PRODUCTS
  useEffect(() => {

    const fetchProducts = async () => {

      try {

        const res = await api.get("/products", {
          params: {
            category: selectedCategory
          }
        });

        const data = res.data
          .filter(
            (p) =>
              p.stock > 0 &&
              p.product_status === "order_now"
          )
          .map((p) => ({
            ...p,
            quantity: 1,
            selected: false
          }));

        setProducts(data);

      } catch (error) {

        console.error(error);
      }
    };

    fetchProducts();

  }, [selectedCategory]);

  // CATEGORY CHANGE
  const handleCategoryChange = (e) => {

    setSelectedCategory(e.target.value);

    setCurrentPage(1);
  };



  // PAGINATION
  const productsPerPage = 10;

  const indexOfLastProduct =
    currentPage * productsPerPage;

  const indexOfFirstProduct =
    indexOfLastProduct - productsPerPage;

  const currentProducts =
    products.slice(
      indexOfFirstProduct,
      indexOfLastProduct
    );

  const totalPages = Math.ceil(
    products.length / productsPerPage
  );

  // QUANTITY CHANGE
  const handleQuantityChange = (index, value) => {

    const updated = [...products];

    const actualIndex =
      indexOfFirstProduct + index;

    updated[actualIndex].quantity =
      Number(value);

    setProducts(updated);
  };

  // CHECKBOX CHANGE
  const handleCheckboxChange = (index) => {

    const updated = [...products];

    const actualIndex =
      indexOfFirstProduct + index;

    updated[actualIndex].selected =
      !updated[actualIndex].selected;

    setProducts(updated);
  };

  // MULTIPLE SUBMIT
  const handleSubmit = async () => {

    try {

      const selectedProducts =
        products.filter((p) => p.selected);

      const user =
        JSON.parse(localStorage.getItem("user"));

      await api.post("/product-request", {

        member_id: user?.id,
        transaction_password:
          transactionPassword,
        products: selectedProducts.map((pro) => ({

          id: pro.id,

          variant_id: pro.variant_id,

          quantity:
            pro.minimum_quantity * pro.quantity
        }))
      });

      showToast(
        "success",
        "Product requests submitted successfully"
      );
      setShowPasswordModal(false);

      setTransactionPassword("");

      // RESET SELECTION
      const resetProducts = products.map((p) => ({
        ...p,
        selected: false,
        quantity: 1
      }));

      setProducts(resetProducts);

    } catch (error) {

      console.error(error);

      console.log(error.response);

      showToast(
        "error",
        error.response?.data?.message ||
        "Something went wrong"
      );
    }
  };
  const selectedAmount = products
    .filter((p) => p.selected)
    .reduce((sum, p) => {

      const qty =
        p.minimum_quantity * p.quantity;

      return (
        sum +
        Number(p.offerprice || 0) * qty
      );

    }, 0);

  // const remainingBalance =
  //   purchaseBalance - selectedAmount;
  return (

    <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">

      <div className="flex-1 min-w-0 flex flex-col">

        <div className="text-center mt-6">

          <h1 className="text-3xl font-bold text-[#B0422E]">
            Product Request
          </h1>
          <div className="px-10 mt-4">
            <div className="flex gap-3 flex-wrap">

              <div className="bg-white border border-[#B0422E] text-[#B0422E] px-4 py-2 rounded-lg font-semibold text-sm shadow-sm">
                Available Balance : ₹{purchaseBalance.toFixed(2)}
              </div>

              <div className="bg-white border border-[#B0422E] text-[#B0422E] px-4 py-2 rounded-lg font-semibold text-sm shadow-sm">
                Selected Amount : ₹{selectedAmount.toFixed(2)}
              </div>

            </div>
          </div>
        </div>

        <div className="p-6">

          <div className="bg-white rounded-2xl shadow-sm p-6 overflow-x-auto">

            {/* TOP BAR */}
            <div className="flex justify-between items-center mb-4">

              <button
                onClick={() => {

                  const selectedProducts =
                    products.filter((p) => p.selected);

                  if (selectedProducts.length === 0) {

                    showToast(
                      "warning",
                      "Please select at least one product"
                    );

                    return;
                  }

                  setShowPasswordModal(true);

                }}
                className="bg-[#B0422E] text-white px-5 py-2 rounded-lg hover:bg-[#8c3222]"
              >
                Submit Selected
              </button>

              <select
                value={selectedCategory}
                onChange={handleCategoryChange}
                className="border border-gray-200 rounded-lg px-2 py-2 bg-gray-200 focus:outline-none"
              >

                <option value="">
                  Category
                </option>

                {categories.map((cat) => (

                  <option
                    key={cat.id}
                    value={cat.category}
                  >
                    {cat.category}
                  </option>

                ))}

              </select>

            </div>

            <table className="w-full min-w-full text-sm text-center">

              <thead>

                <tr className="bg-[#B0422E] text-white">

                  <th className="py-3 px-4 rounded-l-xl">
                    Select
                  </th>

                  <th className="py-3 px-4">
                    Sr No
                  </th>

                  <th className="py-3 px-4">
                    Category
                  </th>

                  <th className="py-3 px-4">
                    Product Name
                  </th>

                  <th className="py-3 px-4">
                    Packing Size
                  </th>

                  <th className="py-3 px-4">
                    PV
                  </th>

                  <th className="py-3 px-4">
                    BV
                  </th>

                  <th className="py-3 px-4">
                    MRP
                  </th>

                  <th className="py-3 px-4">
                    Offer Price
                  </th>

                  <th className="py-3 px-4">
                    Min Qty
                  </th>

                  <th className="py-3 px-4">
                    Quantity
                  </th>
                  <th className="py-3 px-4">
                    Final Quantity
                  </th>

                  <th className="py-3 px-4">
                    Total Amount
                  </th>

                  <th className="py-3 px-4">
                    Total PV
                  </th>

                  <th className="py-3 px-4 rounded-r-xl">
                    Total BV
                  </th>

                </tr>

              </thead>

              <tbody className="font-medium">

                {currentProducts.map((pro, index) => {

                  const finalQuantity =
                    pro.minimum_quantity *
                    pro.quantity;

                  const totalamount =
                    (Number(pro.offerprice || 0) *
                      finalQuantity).toFixed(2);

                  const totalpv =
                    Number(pro.pv || 0) *
                    finalQuantity;

                  const totalbv =
                    Number(pro.bv || 0) *
                    finalQuantity;


                  return (

                    <tr
                      className="border-b border-gray-300"
                      key={pro.variant_id}
                    >

                      <td className="py-4 px-4">

                        <input
                          type="checkbox"
                          checked={pro.selected}
                          onChange={() =>
                            handleCheckboxChange(index)
                          }
                        />

                      </td>

                      <td className="py-4 px-4">
                        {indexOfFirstProduct + index + 1}
                      </td>

                      <td className="py-4 px-4">
                        {pro.category}
                      </td>

                      <td className="py-4 px-4">
                        {pro.productname}
                      </td>

                      <td className="py-4 px-4">
                        {pro.packing_size}
                      </td>

                      <td className="py-4 px-4">
                        {pro.pv || 0}
                      </td>

                      <td className="py-4 px-4">
                        {pro.bv || 0}
                      </td>

                      <td className="py-4 px-4">
                        {pro.mrp}
                      </td>

                      <td className="py-4 px-4">
                        {pro.offerprice}
                      </td>

                      <td className="py-4 px-4">
                        {pro.minimum_quantity}
                      </td>

                      <td className="py-4 px-4">

                        <input
                          type="number"
                          min={1}
                          value={pro.quantity}
                          onChange={(e) =>
                            handleQuantityChange(
                              index,
                              e.target.value
                            )
                          }
                          className="border border-gray-300 rounded-lg px-2 py-1 w-16 text-center focus:outline-none"
                        />

                      </td>
                      <td className="py-4 px-4 font-semibold">
                        {finalQuantity}
                      </td>


                      <td className="py-4 px-4">
                        {Number(totalamount).toFixed(2)}
                      </td>



                      <td className="py-4 px-4">
                        {totalpv}
                      </td>

                      <td className="py-4 px-4">
                        {totalbv}
                      </td>

                    </tr>
                  );
                })}

              </tbody>

            </table>

            {/* PAGINATION */}
            <div className="flex justify-center items-center gap-2 mt-6 flex-wrap">

              <button
                disabled={currentPage === 1}
                onClick={() =>
                  setCurrentPage((prev) => prev - 1)
                }
                className="px-4 py-2 rounded-lg bg-gray-200 disabled:opacity-50"
              >
                Prev
              </button>

              {[...Array(totalPages)].map((_, i) => (

                <button
                  key={i}
                  onClick={() =>
                    setCurrentPage(i + 1)
                  }
                  className={`px-4 py-2 rounded-lg ${currentPage === i + 1
                    ? "bg-[#B0422E] text-white"
                    : "bg-gray-200"
                    }`}
                >
                  {i + 1}
                </button>

              ))}

              <button
                disabled={currentPage === totalPages}
                onClick={() =>
                  setCurrentPage((prev) => prev + 1)
                }
                className="px-4 py-2 rounded-lg bg-gray-200 disabled:opacity-50"
              >
                Next
              </button>

            </div>

          </div>

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

                  handleSubmit();
                }}
                className="px-4 py-2 bg-[#B0422E] text-white rounded-lg"
              >
                Verify & Submit
              </button>

            </div>

          </div>

        </div>
      )}
      <ToastContainer toasts={toasts} removeToast={removeToast} />
    </div>
  );
}

export default ProductRequest;