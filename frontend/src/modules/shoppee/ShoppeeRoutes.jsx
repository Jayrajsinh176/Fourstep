import { Route, Navigate } from "react-router-dom";
import ShoppeeLayout from "./ShoppeeLayout";

// Auth pages
import SignIn from "./pages/SignIn";
import Signup from "./pages/SignUp";

// Main pages
import Dashboard from "./pages/dashboard";
import MyProfile from "./pages/myprofile";
import RaiseTicket from "./pages/RaiseTicket";
import Inbox from "./pages/Inbox";
import Outbox from "./pages/Outbox";
import PurchaseBalanceRequest from "./pages/PurchaseBalanceRequest";
import TurnoverBalance from "./pages/TurnoverBalanceRequest";
import BalanceHistory from "./pages/History";
import Transaction from "./pages/Transaction";
import ProductRequest from "./pages/ProductRequest";
import SendProductRequest from "./pages/SendProductRequest";
import CreateBranchSale from "./pages/CreateBranchSale";
import CreatePromoterSale from "./pages/CreatePromoterSale";
import OrderHistory from "./pages/OrderHistory";
import ProductStockReport from "./pages/ProductStockReport";
import ProductStockTransaction from "./pages/ProductStockTransaction";
import AutoLogin from "./pages/AutoLogin";
import MyKYC from "./pages/MyKYC";
import BranchOrderHistory from "./pages/BranchOrderhistory";
import ForgotPassword from "./pages/ForgotPassword";
import TransactionPassword from "./pages/TransactionPassword";
import InvoicePage from "./pages/DeliveryInvoicePage";
import BVProductList from "./pages/BVProductList";
export default function ShoppeeRoutes() {
  return (
    <Route path="/shoppee" element={<ShoppeeLayout />}>

      {/* ✅ Auth Routes (NOW INSIDE) */}
      <Route path="signin" element={<SignIn />} />
      <Route path="auto-login" element={<AutoLogin />} />
      {/* <Route path="signup" element={<Signup />} /> */}

      {/* ✅ Default Redirect */}
      <Route index element={<Navigate to="signin" />} />

      {/* ✅ Protected Pages */}
      <Route path="dashboard" element={<Dashboard />} />
      <Route path="profile" element={<MyProfile />} />
      <Route path="raiseticket" element={<RaiseTicket />} />
      <Route path="inbox" element={<Inbox />} />
      <Route path="outbox" element={<Outbox />} />
      <Route path="PurchaseRequest" element={<PurchaseBalanceRequest />} />
      <Route path="TurnoverRequest" element={<TurnoverBalance />} />
      <Route path="BalanceHistory" element={<BalanceHistory />} />
      <Route path="Transaction" element={<Transaction />} />
      <Route path="ProductRequest" element={<ProductRequest />} />
      <Route path="SendProductRequest" element={<SendProductRequest />} />
      <Route path="IboBranchSale" element={<CreateBranchSale />} />
      <Route path="IboDeliverySale" element={<CreatePromoterSale />} />
      <Route path="DeliveryOrderHistory" element={<OrderHistory />} />
      <Route path="ProductStockReport" element={<ProductStockReport />} />
      <Route path="ProductStockTransaction" element={<ProductStockTransaction />} />
      <Route path="BvProductList" element={<BVProductList />} />
      <Route path="MyKyc" element={<MyKYC />} />
      <Route path="BranchOrderHistory" element={<BranchOrderHistory />} />
      <Route path="forgot-password" element={<ForgotPassword />} />
      <Route path="transaction-password" element={<TransactionPassword />} />
    <Route
  path="invoice/:id"
  element={<InvoicePage />}
/>


    </Route>
  );
}
