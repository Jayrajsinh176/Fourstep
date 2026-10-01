import { Routes } from "react-router-dom";
import MemberRoutes from "../modules/member/MemberRoutes";
import EcommerceRoutes from "../modules/ecommerce/EcommerceRoutes";
import ShoppeeRoutes from "../modules/shoppee/ShoppeeRoutes"; // ✅ ADD THIS

export default function AppRoutes() {
  return (
    <Routes>
      {EcommerceRoutes()}
      {MemberRoutes()}
      {ShoppeeRoutes()} {/* ✅ ADD THIS */}
    </Routes>
  );
}