import React, { useState } from "react";
import { MdOutlineDashboard } from "react-icons/md";
import { GoPerson } from "react-icons/go";
import { LuNetwork, LuWallet } from "react-icons/lu";
import { FiChevronDown, FiChevronRight, FiLogOut } from "react-icons/fi";
import { BiSolidOffer } from "react-icons/bi";
import { FaHandsHelping } from "react-icons/fa";
import { MdOutlinePayments } from "react-icons/md";
import { Link, useLocation, useNavigate } from "react-router-dom";

export default function Sidebar({ onClose }) {
  const location = useLocation();
  const navigate = useNavigate();
  const user = JSON.parse(localStorage.getItem("user"));

  const handleLogout = () => {
    // Clear all browser storage
    localStorage.clear();
    sessionStorage.clear();

    // Force redirect
    window.location.href = "/shoppee/signin";
  };

  const handleNavClick = () => {
    onClose?.();
  };

  const getInitials = (name) => {
    if (!name) return "";

    const words = name.split(" ");
    const initials = words
      .slice(0, 2)
      .map((word) => word[0])
      .join("");
    return initials.toUpperCase();
  };

  // Dashboard
  const isDashboard =
    location.pathname === "/shoppee/dashboard" ||
    location.pathname === "/shoppee";

  // Account Routes
  const isProfile = location.pathname === "/shoppee/profile";
  const isTransactionPassword = location.pathname === "/shoppee/transaction-password";
  const isKyc = location.pathname === "/shoppee/MyKyc";
  const isWelcomeLetter = location.pathname === "/shoppee/welcome-letter";
  const isBrachCertificate =
    location.pathname === "/shoppee/branch-certificate";
  const isAccountSection =
    isProfile || isKyc || isWelcomeLetter || isBrachCertificate || isTransactionPassword;

  // Portfolio Routes
  const isPurchaseBalance = location.pathname === "/shoppee/PurchaseRequest";
  const isTurnoverBalance = location.pathname === "/shoppee/TurnoverRequest";
  const isHistory = location.pathname === "/shoppee/BalanceHistory";
  const isTransaction = location.pathname === "/shoppee/Transaction";
  const isPaymnet =
    isPurchaseBalance || isTurnoverBalance || isHistory || isTransaction;

  // Product Section Routes
  const isProductRequest = location.pathname === "/shoppee/ProductRequest";
  const isSendProductRequest =
    location.pathname === "/shoppee/SendProductRequest";
  const isProductStockReport =
    location.pathname === "/shoppee/ProductStockReport";
  const isProductStockTransaction =
    location.pathname === "/shoppee/ProductStockTransaction";
  const isBvProductList = location.pathname === "/shoppee/BvProductList";

  const isProduct =
    isProductRequest ||
    isSendProductRequest ||
    isProductStockReport ||
    isProductStockTransaction ||
    isBvProductList;

  // Product Sales Section Routes
  const isIbobranchsales = location.pathname === "/shoppee/CreateBranchSale";
  const isIBODeliverySale = location.pathname === "/shoppee/CreatePromoterSale";
  const isDeliveryOrderHistory = location.pathname === "/shoppee/DeliveryOrderHistory";
  const isBranchOrderHistory = location.pathname === "/shoppee/BranchOrderHistory";
  const isProductSalesSection =
    isIbobranchsales || isIBODeliverySale || isDeliveryOrderHistory || isBranchOrderHistory;

  // Offer Details Section Routes
  const isOfferDetails = location.pathname === "/shoppee/offerdetails";

  // Help Desk Section Routes
  const isRaiseTicket = location.pathname === "/shoppee/raiseticket";
  const isInbox = location.pathname === "/shoppee/inbox";
  const isOutbox = location.pathname === "/shoppee/outbox";

  const isHelpDeskSection = isRaiseTicket || isInbox || isOutbox;

  const [openAccount, setOpenAccount] = useState(isAccountSection);
  const [openPayment, setOpenPayment] = useState(isPaymnet);
  const [openProduct, setOpenProduct] = useState(isProduct);
  const [openProductSales, setProductSales] = useState(isProductSalesSection);
  const [openHelpDesk, setOpenHelpDesk] = useState(isHelpDeskSection);

  return (
    <div className="w-64 bg-white border-r border-gray-300 flex flex-col h-full">
      {/* Logo */}
      <div className="h-16 flex items-center px-5 border-b border-gray-200 flex-shrink-0">
        <img
          src="/images/fourstep_logo.png"
          alt="logo"
          className="h-15 ml-8 mb-2 mt-2"
        />
      </div>

      {/* Side Bar Menu */}
      <div className="flex-1 overflow-y-auto p-4 text-sm space-y-1">
        {/* Dashboard */}
        <Link
          to="/shoppee/dashboard"
          onClick={handleNavClick}
          className={`px-3 py-2 rounded flex items-center ${isDashboard ? "bg-blue-100 text-blue-600" : "hover:bg-gray-100"
            }`}
        >
          <MdOutlineDashboard className="mr-2" /> Dashboard
        </Link>

        {/* My Account Dropdown */}
        <div>
          <div
            onClick={() => setOpenAccount(!openAccount)}
            className={`px-3 py-2 rounded cursor-pointer flex justify-between items-center ${isAccountSection
                ? "bg-blue-50 text-blue-600"
                : "hover:bg-gray-100"
              }`}
          >
            <span className="flex items-center">
              <GoPerson className="mr-2" /> My Account
            </span>
            {openAccount ? <FiChevronDown /> : <FiChevronRight />}
          </div>

          {openAccount && (
            <div className="ml-7 text-gray-600 space-y-1">
              <Link
                to="/shoppee/profile"
                onClick={handleNavClick}
                className={`block px-2 py-1 rounded ${isProfile ? "bg-blue-100 text-blue-600" : "hover:bg-blue-50"
                  }`}
              >
                My Profile
              </Link>
              <Link
                to="/shoppee/transaction-password"
                onClick={handleNavClick}
                className={`block px-2 py-1 rounded ${isTransactionPassword ? "bg-blue-100 text-blue-600" : "hover:bg-blue-50"
                  }`}
              >
                Transaction Password
              </Link>
              <Link
                to="/shoppee/MyKyc"
                onClick={handleNavClick}
                className={`block px-2 py-1 rounded ${isKyc ? "bg-blue-100 text-blue-600" : "hover:bg-blue-50"
                  }`}
              >
                My KYC
              </Link>

              <Link
                to="/shoppee/welcome-letter"
                onClick={handleNavClick}
                className={`block px-2 py-1 rounded ${isWelcomeLetter
                    ? "bg-blue-100 text-blue-600"
                    : "hover:bg-blue-50"
                  }`}
              >
                Welcome Letter
              </Link>
              <Link
                to="/shoppee/royalty-certificate"
                onClick={handleNavClick}
                className={`block px-2 py-1 rounded ${isBrachCertificate
                    ? "bg-blue-100 text-blue-600"
                    : "hover:bg-blue-50"
                  }`}
              >
                Branch Certificate
              </Link>
            </div>
          )}
        </div>

        {/* Payment Dropdown */}
        <div>
          <div
            onClick={() => setOpenPayment(!openPayment)}
            className={`px-3 py-2 rounded cursor-pointer flex justify-between items-center ${isPaymnet ? "bg-blue-50 text-blue-600" : "hover:bg-gray-100"
              }`}
          >
            <span className="flex items-center">
              <MdOutlinePayments className="mr-2" /> Payment
            </span>
            {openPayment ? <FiChevronDown /> : <FiChevronRight />}
          </div>

          {openPayment && (
            <div className="ml-7 text-gray-600 space-y-1">
              <Link
                to="/shoppee/PurchaseRequest"
                onClick={handleNavClick}
                className={`block px-2 py-1 rounded ${isPurchaseBalance
                    ? "bg-blue-100 text-blue-600"
                    : "hover:bg-blue-50"
                  }`}
              >
                Purchase Balance Request
              </Link>

              <Link
                to="/shoppee/TurnoverRequest"
                onClick={handleNavClick}
                className={`block px-2 py-1 rounded ${isTurnoverBalance
                    ? "bg-blue-100 text-blue-600"
                    : "hover:bg-blue-50"
                  }`}
              >
                Turnover Balance Request
              </Link>

              <Link
                to="/shoppee/BalanceHistory"
                onClick={handleNavClick}
                className={`block px-2 py-1 rounded ${isHistory ? "bg-blue-100 text-blue-600" : "hover:bg-blue-50"
                  }`}
              >
                History
              </Link>
              <Link
                to="/shoppee/Transaction"
                onClick={handleNavClick}
                className={`block px-2 py-1 rounded ${isTransaction
                    ? "bg-blue-100 text-blue-600"
                    : "hover:bg-blue-50"
                  }`}
              >
                Transcation
              </Link>
            </div>
          )}
        </div>

        {/* Product Dropdown */}
        <div>
          <div
            onClick={() => setOpenProduct(!openProduct)}
            className={`px-3 py-2 rounded cursor-pointer flex justify-between items-center ${isProduct ? "bg-blue-50 text-blue-600" : "hover:bg-gray-100"
              }`}
          >
            <span className="flex items-center">
              <LuNetwork className="mr-2" /> Product
            </span>
            {openProduct ? <FiChevronDown /> : <FiChevronRight />}
          </div>
          {openProduct && (
            <div className="ml-7 text-gray-600 space-y-1">
              <Link
                to="/shoppee/ProductRequest"
                onClick={handleNavClick}
                className={`block px-2 py-1 rounded ${isProductRequest
                    ? "bg-blue-100 text-blue-600"
                    : "hover:bg-blue-50"
                  }`}
              >
                Product Request
              </Link>

              <Link
                to="/shoppee/SendProductRequest"
                onClick={handleNavClick}
                className={`block px-2 py-1 rounded ${isSendProductRequest
                    ? "bg-blue-100 text-blue-600"
                    : "hover:bg-blue-50"
                  }`}
              >
                Send Product Request
              </Link>

              <Link
                to="/shoppee/ProductStockReport"
                onClick={handleNavClick}
                className={`block px-2 py-1 rounded ${isProductStockReport
                    ? "bg-blue-100 text-blue-600"
                    : "hover:bg-blue-50"
                  }`}
              >
                Product Stock Report
              </Link>
              <Link
                to="/shoppee/ProductStockTransaction"
                onClick={handleNavClick}
                className={`block px-2 py-1 rounded ${isProductStockTransaction
                    ? "bg-blue-100 text-blue-600"
                    : "hover:bg-blue-50"
                  }`}
              >
                Product Stock Transaction
              </Link>
              <Link
                to="/shoppee/BvProductList"
                onClick={handleNavClick}
                className={`block px-2 py-1 rounded ${isBvProductList
                    ? "bg-blue-100 text-blue-600"
                    : "hover:bg-blue-50"
                  }`}
              >
                BV Product List
              </Link>
            </div>
          )}
        </div>

        {/* Product Sales Dropdown */}
        <div>
          <div
            onClick={() => setProductSales(!openProductSales)}
            className={`px-3 py-2 rounded cursor-pointer flex justify-between items-center ${isProductSalesSection
                ? "bg-blue-50 text-blue-600"
                : "hover:bg-gray-100"
              }`}
          >
            <span className="flex items-center">
              <LuWallet className="mr-2" /> Product Sales
            </span>
            {openProductSales ? <FiChevronDown /> : <FiChevronRight />}
          </div>
          {openProductSales && (
            <div className="ml-7 text-gray-600 space-y-1">
              <Link
                to="/shoppee/IboBranchSale"
                onClick={handleNavClick}
                className={`block px-2 py-1 rounded ${isIbobranchsales
                    ? "bg-blue-100 text-blue-600"
                    : "hover:bg-blue-50"
                  }`}
              >
                IBO Branch Sale
              </Link>

              <Link
                to="/shoppee/IboDeliverySale"
                onClick={handleNavClick}
                className={`block px-2 py-1 rounded ${isIBODeliverySale
                    ? "bg-blue-100 text-blue-600"
                    : "hover:bg-blue-50"
                  }`}
              >
                IBO Delivery Sale
              </Link>

              <Link
                to="/shoppee/DeliveryOrderHistory"
                onClick={handleNavClick}
                className={`block px-2 py-1 rounded ${isDeliveryOrderHistory
                    ? "bg-blue-100 text-blue-600"
                    : "hover:bg-blue-50"
                  }`}
              >
                Delivery Order History
              </Link>

              <Link
                to="/shoppee/BranchOrderHistory"
                onClick={handleNavClick}
                className={`block px-2 py-1 rounded ${isBranchOrderHistory
                    ? "bg-blue-100 text-blue-600"
                    : "hover:bg-blue-50"
                  }`}
              >
                Branch Order History
              </Link>
            </div>
          )}
        </div>

        {/* Offer Details  */}
        <Link
          to="/shoppee/dashboard"
          onClick={handleNavClick}
          className={`px-3 py-2 rounded flex items-center ${isOfferDetails ? "bg-blue-100 text-blue-600" : "hover:bg-gray-100"
            }`}
        >
          <BiSolidOffer className="mr-2" /> Offer Details
        </Link>

        {/* Help Desk Dropdown */}
        <div>
          <div
            onClick={() => setOpenHelpDesk(!openHelpDesk)}
            className={`px-3 py-2 rounded cursor-pointer flex justify-between items-center ${isHelpDeskSection
                ? "bg-blue-50 text-blue-600"
                : "hover:bg-gray-100"
              }`}
          >
            <span className="flex items-center">
              <FaHandsHelping className="mr-2" /> HelpDesk
            </span>
            {openHelpDesk ? <FiChevronDown /> : <FiChevronRight />}
          </div>

          {openHelpDesk && (
            <div className="ml-7 text-gray-600 space-y-1">
              <Link
                to="/shoppee/raiseticket"
                onClick={handleNavClick}
                className={`block px-2 py-1 rounded ${isRaiseTicket
                    ? "bg-blue-100 text-blue-600"
                    : "hover:bg-blue-50"
                  }`}
              >
                Raise Ticket
              </Link>

              <Link
                to="/shoppee/inbox"
                onClick={handleNavClick}
                className={`block px-2 py-1 rounded ${isInbox ? "bg-blue-100 text-blue-600" : "hover:bg-blue-50"
                  }`}
              >
                Inbox
              </Link>

              <Link
                to="/shoppee/outbox"
                onClick={handleNavClick}
                className={`block px-2 py-1 rounded ${isOutbox ? "bg-blue-100 text-blue-600" : "hover:bg-blue-50"
                  }`}
              >
                Outbox
              </Link>
            </div>
          )}
        </div>
      </div>

      {/* Logout Section */}
      <div className="border-t border-gray-200 bg-gray-100 px-4 py-3 flex-shrink-0">
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-3 min-w-0">
            <div className="w-9 h-9 rounded-full bg-gray-200 flex items-center justify-center text-sm font-medium text-gray-600 flex-shrink-0">
              {getInitials(user?.fullname)}
            </div>

            <div className="leading-tight min-w-0">
              <p className="text-sm font-medium text-gray-800 truncate">
                {user?.fullname}
              </p>
              <p className="text-xs text-gray-500 truncate">{user?.member_id}</p>
            </div>
          </div>

          <button
            className="p-2 rounded-md hover:bg-gray-200 transition-all duration-200 flex-shrink-0"
            onClick={handleLogout}
          >
            <FiLogOut className="w-5 h-5 text-gray-600" />
          </button>
        </div>
      </div>
    </div>
  );
}
