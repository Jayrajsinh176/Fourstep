import { useEffect, useMemo, useState } from "react";
import { PackageCheck } from "lucide-react";
import Sidebar from "../components/Sidebar";
import Navbar from "../components/Navbar";
import { requestMemberApi } from "../utils/apiClient";

const EMPTY_PRODUCTS = {
  repurchase_products: [],
  consistency_products: [],
  repurchase_wallet_balance: 0,
  consistency_wallet_balance: 0,
};

const toNumber = (value) => {
  const normalized = Number(value);
  return Number.isFinite(normalized) ? normalized : 0;
};

const formatCurrency = (value) => {
  return new Intl.NumberFormat("en-IN", {
    style: "currency",
    currency: "INR",
    maximumFractionDigits: 2,
  }).format(toNumber(value));
};

const PRODUCT_FALLBACK_IMAGE =
  "https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=600&q=80";

export default function Products() {
  const [data, setData] = useState(EMPTY_PRODUCTS);
  const [loading, setLoading] = useState(true);
  const [processingKey, setProcessingKey] = useState("");
  const [activeWallet, setActiveWallet] = useState("repurchase");
  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");

  const memberUserId = useMemo(() => {
    try {
      const member = JSON.parse(localStorage.getItem("memberData") || "{}");
      return member?.user_id || "";
    } catch {
      return "";
    }
  }, []);

  const fetchProducts = async () => {
    const response = await requestMemberApi("/member/products", {
      method: "GET",
      headers: {
        Accept: "application/json",
        ...(memberUserId ? { "X-Auth-Member": memberUserId } : {}),
      },
    });

    if (!response.ok) {
      throw new Error(response?.data?.message || "Unable to fetch products.");
    }

    const payload = response?.data?.data || {};

    setData({
      repurchase_products: Array.isArray(payload.repurchase_products)
        ? payload.repurchase_products
        : [],
      consistency_products: Array.isArray(payload.consistency_products)
        ? payload.consistency_products
        : [],
      repurchase_wallet_balance: toNumber(payload.repurchase_wallet_balance),
      consistency_wallet_balance: toNumber(payload.consistency_wallet_balance),
    });
  };

  useEffect(() => {
    let isMounted = true;

    const loadPage = async () => {
      try {
        if (isMounted) {
          setLoading(true);
          setError("");
        }

        await fetchProducts();
      } catch (fetchError) {
        if (isMounted) {
          setError(fetchError.message || "Unable to fetch products.");
          setData(EMPTY_PRODUCTS);
        }
      } finally {
        if (isMounted) {
          setLoading(false);
        }
      }
    };

    loadPage();

    return () => {
      isMounted = false;
    };
  }, [memberUserId]);

  const handlePurchase = async (walletType, productId) => {
    const currentKey = `${walletType}:${productId}`;

    if (processingKey) {
      return;
    }

    try {
      setProcessingKey(currentKey);
      setError("");
      setSuccess("");

      const response = await requestMemberApi("/member/products/purchase", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
          ...(memberUserId ? { "X-Auth-Member": memberUserId } : {}),
        },
        body: JSON.stringify({
          wallet_type: walletType,
          product_id: productId,
        }),
      });

      if (!response.ok) {
        throw new Error(response?.data?.message || "Purchase failed.");
      }

      setSuccess(response?.data?.message || "Product purchased successfully.");
      await fetchProducts();
    } catch (purchaseError) {
      setError(purchaseError.message || "Purchase failed.");
    } finally {
      setProcessingKey("");
    }
  };

  const isRepurchaseView = activeWallet === "repurchase";
  const visibleProducts = isRepurchaseView
    ? data.repurchase_products
    : data.consistency_products;
  const sectionTitle = isRepurchaseView
    ? "Repurchase Wallet Amount"
    : "Consistency Wallet Amount";

  return (
    <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">
      <Sidebar />

      <div className="flex-1 min-w-0 flex flex-col">
        <Navbar pageTitle="Products" />

        <div className="p-6 space-y-6">
          <div className="bg-linear-to-r from-blue-700 to-blue-500 rounded-2xl p-6 text-white shadow-sm flex items-center gap-4">
            <div className="bg-white/20 p-3 rounded-xl">
              <PackageCheck className="w-7 h-7" />
            </div>
            <div>
              <h1 className="text-2xl font-bold">Products</h1>
              <p className="text-white/90 text-sm">Wallet-wise product list</p>
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <button
              type="button"
              onClick={() => setActiveWallet("repurchase")}
              className={`bg-white rounded-2xl shadow-sm p-5 border-l-4 text-left ${
                isRepurchaseView ? "border-blue-600" : "border-gray-300"
              }`}
            >
              <p className="text-gray-500 text-sm">Repurchase Wallet Balance</p>
              <h3 className="text-2xl font-bold text-blue-700 mt-1">
                {formatCurrency(data.repurchase_wallet_balance)}
              </h3>
            </button>

            <button
              type="button"
              onClick={() => setActiveWallet("consistency")}
              className={`bg-white rounded-2xl shadow-sm p-5 border-l-4 text-left ${
                !isRepurchaseView ? "border-blue-600" : "border-gray-300"
              }`}
            >
              <p className="text-gray-500 text-sm">Consistency Wallet Balance</p>
              <h3 className="text-2xl font-bold text-blue-700 mt-1">
                {formatCurrency(data.consistency_wallet_balance)}
              </h3>
            </button>
          </div>

          {loading && <p className="text-gray-500">Loading products...</p>}
          {!loading && error && <p className="text-red-500">{error}</p>}
          {!loading && success && <p className="text-green-600">{success}</p>}

          <section className="bg-white rounded-2xl shadow-sm p-6">
            <h2 className="text-xl font-bold text-blue-700 mb-4">{sectionTitle}</h2>
            {!loading && visibleProducts.length === 0 && (
              <p className="text-center text-gray-500 py-5">
                {isRepurchaseView
                  ? "No products found in repurchase wallet."
                  : "No products found in consistency wallet."}
              </p>
            )}

            <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
              {visibleProducts.map((item) => (
                <article key={item.id} className="rounded-2xl border border-gray-200 overflow-hidden shadow-sm bg-white">
                  <img
                    src={item.image_url || PRODUCT_FALLBACK_IMAGE}
                    alt={item.product_name}
                    className="w-full h-44 object-cover"
                  />
                  <div className="p-4">
                    <p className="text-base font-semibold text-gray-800">{item.product_name}</p>
                    <p className="text-lg font-bold text-blue-700 mt-1">{formatCurrency(item.amount)}</p>
                    <button
                      type="button"
                      disabled={!!processingKey}
                      onClick={() => handlePurchase(isRepurchaseView ? "repurchase" : "consistency", item.id)}
                      className="mt-4 w-full px-3 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold disabled:opacity-60"
                    >
                      {processingKey === `${isRepurchaseView ? "repurchase" : "consistency"}:${item.id}`
                        ? "Processing..."
                        : isRepurchaseView
                          ? "Buy from Repurchase Wallet"
                          : "Buy from Consistency Wallet"}
                    </button>
                  </div>
                </article>
              ))}
            </div>
          </section>
        </div>
      </div>
    </div>
  );
}
