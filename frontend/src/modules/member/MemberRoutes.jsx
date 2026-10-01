import { Routes, Route, Navigate } from "react-router-dom";
import MemberLayout from "./MemberLayout";
import SignIn from "./pages/SignIn";
import SignUp from "./pages/SignUp";

import Dashboard from "./pages/Dashboard";
import MyProfile from "./pages/MyProfile";
import KYC from "./pages/KYC";
import TransactionPassword from "./pages/TransactionPassword";
import CreateIDCard from "./pages/CreateIDCard";
import BuiltupTree from "./pages/BuiltupTree";
import ReferralBranch from "./pages/ReferralBranch";
import ReferralPromoters from "./pages/ReferralPromoters";
import RightLeftDownline from "./pages/RightLeftDownline";
import MatchingStatus from "./pages/MatchingStatus";
import RepurchaseStatus from "./pages/RepurchaseStatus";
import ConsistencyStatus from "./pages/ConsistencyStatus";
import MonitoringStatus from "./pages/MonitoringStatus";
import DiwaliCelebration from "./pages/DiwaliCelebration";
import PurchaseBalanceRequest from "./pages/PurchaseBalanceRequest";
import PurchaseBalanceHistory from "./pages/PurchaseBalanceHistory";
import PurchaseBalanceTransaction from "./pages/PurchaseBalanceTransaction";
import RankWithRewardStatus from "./pages/RankWithRewardStatus";
import DiwaliCelebrationBonus from "./pages/DiwaliCelebrationBonus";
import FamilySaverBonus from "./pages/FamilySaverBonus";
import RepurchaseBonus from "./pages/RepurchaseBonus";
import RoyaltyClubBonus from "./pages/RoyaltyClubBonus";
// import BranchTurnoverBonus from "./pages/BranchTurnoverBonus";
import BusinessMonitoringBonus from "./pages/BusinessMonitoringBonus";
import LeadershipRankBonus from "./pages/LeadershipRankBonus";
import EarningHistory from "./pages/EarningHistory";
import EarningBalanceWithdrawal from "./pages/EarningBalanceWithdrawal";
import Inbox from "./pages/Inbox";
import Outbox from "./pages/Outbox";
import Compose from "./pages/Compose";
import IncomeSummary from "./pages/IncomeSummary";
import WelcomeLetter from "./pages/WelcomeLetter";
import RoyaltyCertificate from "./pages/RoyaltyCertificate";
import RoyaltyStatus from "./pages/RoyaltyStatus";
// import TravelclubStatus from "./pages/TravelclubStatus";
import GroupBuiltupBonus from "./pages/GroupBuiltupBonus";
import Downloads from "./pages/Downloads";
import Products from "./pages/Products";
import BVProductList from "./pages/BVProductList";
import IncomeReport from "./pages/IncomeReport";
import AutoLogin from "./pages/autologin";
import ProtectedRoute from "./components/ProtectedRoute";
import OrderHistory from "./pages/OrderHistory";
import ForgotPassword from "./pages/ForgotPassword";
import CashbackHistory from "./pages/CashbackHistory";
import ConsistencyWallet from "./pages/ConsistencyWallet";


export default function MemberRoutes() {
  return (
    <>
      {/* public auth pages */}
      <Route path="/member/signin" element={<SignIn />} />
      <Route path="/member/signup" element={<SignUp />} />
      <Route path="/member/forgot-password" element={<ForgotPassword />} />
          <Route path="/member/auto-login/:id" element={<AutoLogin />} />

      {/* protected member area under layout */}
     <Route
  path="/member"
  element={
    <ProtectedRoute>
      <MemberLayout />
    </ProtectedRoute>
  }
>
        <Route index element={<Navigate to="dashboard" />} />

        <Route path="dashboard" element={<Dashboard />} />
        <Route path="profile" element={<MyProfile />} />
        <Route
  path="transaction-password"
  element={<TransactionPassword />}
/>
        <Route path="kyc" element={<KYC />} />
        <Route path="create-id-card" element={<CreateIDCard />} />
        <Route path="welcome-letter" element={<WelcomeLetter />} />
        <Route path="royalty-certificate" element={<RoyaltyCertificate />} />
        <Route path="builtup-tree" element={<BuiltupTree />} />
        <Route path="referral-branch" element={<ReferralBranch />} />
        <Route path="referral-promoters" element={<ReferralPromoters />} />
        <Route path="left-right-downline" element={<RightLeftDownline />} />
        <Route path="group-turnover-status" element={<MatchingStatus />} />
        <Route path="group-purchase-status" element={<RepurchaseStatus />} />
        <Route path="consistency-status" element={<ConsistencyStatus />} />
        <Route path="monitoring-status" element={<MonitoringStatus />} />
        {/* <Route path="diwali-celebration" element={<DiwaliCelebration />} /> */}
        <Route path="royalty-status" element={<RoyaltyStatus />} />
        {/* <Route path="travelclub-status" element={<TravelclubStatus />} /> */}
        <Route path="rank-reward-status" element={<RankWithRewardStatus />} />
        <Route path="purchase-request" element={<PurchaseBalanceRequest />} />
        <Route path="purchase-history" element={<PurchaseBalanceHistory />} />
        <Route path="purchase-transactions" element={<PurchaseBalanceTransaction />} />
        <Route path="earning-group-builtup" element={<GroupBuiltupBonus />} />
        {/* <Route path="earning-diwali" element={<DiwaliCelebrationBonus />} /> */}
        <Route path="earning-family" element={<FamilySaverBonus />} />
        <Route path="earning-repurchase" element={<RepurchaseBonus />} />
        <Route path="cashback-history" element={<CashbackHistory />} />
        <Route path="consistency-wallet" element={<ConsistencyWallet />} />
        <Route path="earning-royalty" element={<RoyaltyClubBonus />} />
        {/* <Route path="earning-branch" element={<BranchTurnoverBonus />} /> */}
        <Route path="income-report" element={<IncomeReport />} />
        
        <Route path="left-downline" element={<RightLeftDownline side="left" />} />
        <Route path="right-downline" element={<RightLeftDownline side="right" />} />
        <Route
          path="earning-monitoring"
          element={<BusinessMonitoringBonus />}
        />
        <Route path="earning-leadership" element={<LeadershipRankBonus />} />
        <Route path="earning-history" element={<EarningHistory />} />
        <Route
          path="withdrawal-report"
          element={<EarningBalanceWithdrawal />}
        />
        <Route path="income-summary" element={<IncomeSummary />} />
        <Route path="inbox" element={<Inbox />} />
        <Route path="outbox" element={<Outbox />} />
        <Route path="compose" element={<Compose />} />
        <Route path="downloads" element={<Downloads />} />
        <Route path="products" element={<Products />} />
        <Route path="bv-product-list" element={<BVProductList />} />
        <Route path="order-history" element={<OrderHistory />} />
      </Route>
    </>
  );
}
