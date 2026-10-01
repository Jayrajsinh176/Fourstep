import { Outlet, useLocation } from "react-router-dom";
import Header from "./components/header";
import UpFooter from "./components/upfooter";
import Footer from "./components/footer";
import ScrollToTop from "./components/scrolltop";

export default function EcommerceLayout() {
  const location = useLocation();

  const hideLayout =
    location.pathname === "/login" ||
    location.pathname === "/signup" ||
    location.pathname === "/forgotpassword";

  return (
    <>
      <ScrollToTop />

      {!hideLayout && <Header />}

      <Outlet />

      {!hideLayout && (
        <>
          <UpFooter />
          <Footer />
        </>
      )}
    </>
  );
}