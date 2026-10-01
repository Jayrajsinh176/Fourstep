import { Route } from "react-router-dom";
import EcommerceLayout from "./EcommerceLayout";

import Home from './pages/home'
import AllProduct from './pages/allproduct'
import Product from './pages/product'
import Helpform from './components/helpform'
import Checkout from './components/checkout'
import CheckoutFinal from './components/checkoutfinal'
import LoginPage from './pages/Login'
import Signup from './pages/Signup'
import Terms from './pages/Termscondition'
import ReturnPolicy from './pages/returnpolicy'
import CompliancePolicy from './pages/compliancepolicy'
import PrivacyPolicy from './pages/privacypolicy'
import ShippingPolicy from './pages/shippingpolicy'
import ProfilePage from './pages/profilepage'
import CategoryProducts from './pages/categoryproduct'
import DisclaimerPolicy from './pages/sellerdisclamir'
import Orders from './pages/Orders'
import Trackorder from './pages/Trackorder'
import Mediaroom from './pages/mediaroom'
import Aboutus from './pages/aboutus'
import Gallery from './pages/gallery'
import Corepromoter from './pages/corepromoters'
import Legaldocument from './pages/legaldocument'
import Awards from './pages/awards'
import Teammanagement from './pages/teammanagement'
import Opportunity from "./pages/opportunity";
import ComingSoon from "./pages/ComingSoon";
import ComingSoonDetails from "./pages/ComingSoonDetails";
import ForgotPasswordCommon  from "./pages/ForgotPassword";


export default function EcommerceRoutes() {
  return (
    <Route path="/" element={<EcommerceLayout />}>
      <Route index element={<Home />} />
      <Route path="login" element={<LoginPage />} />
      <Route path="signup" element={<Signup />} />
      <Route path="home" element={<Home />} />
      <Route path="allproduct" element={<AllProduct />} />
      <Route path="product" element={<Product />} />
      <Route path="product/:id" element={<Product />} />
      <Route path="helpform" element={<Helpform />} />
      <Route path="checkout" element={<Checkout />} />
      <Route path="checkoutfinal" element={<CheckoutFinal />} />

      <Route path="terms" element={<Terms />} />
      <Route path="returnpolicy" element={<ReturnPolicy />} />
      <Route path="cancellationpolicy" element={<CompliancePolicy />} />
      <Route path="privacypolicy" element={<PrivacyPolicy />} />
      <Route path="shippingpolicy" element={<ShippingPolicy />} />
      <Route path="profile" element={<ProfilePage />} />
      <Route path="category/:id" element={<CategoryProducts />} />
      <Route path="comingsoon" element={<ComingSoon />} />
      <Route
  path="coming-soon/:id"
  element={<ComingSoonDetails />}
/>
      <Route path="orders" element={<Orders />} />
      <Route path="trackorder" element={<Trackorder />} />
      <Route path="sellerdisclaimer" element={<DisclaimerPolicy />} />
   

      <Route path="gallery" element={<Gallery />} />
      
      <Route path="mediaroom" element={<Mediaroom />} />
      <Route path="corepromoters" element={<Corepromoter />} />

      <Route path="aboutus" element={<Aboutus />} />
      <Route path="teammanagement" element={<Teammanagement />} />
      <Route path="legaldocument" element={<Legaldocument />} />
      <Route path="awards" element={<Awards />} />
      <Route path="opportunity" element={<Opportunity />} />
      <Route path="forgotpassword" element={<ForgotPasswordCommon  />} />

    </Route>
  );
}
