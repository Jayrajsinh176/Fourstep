import { Outlet, useLocation } from "react-router-dom";
import { useState } from "react";
import Navbar from "./components/navbar";
import Sidebar from "./components/sidebar";
import SessionManager from "./components/SessionManager";

export default function ShoppeeLayout() {
  const location = useLocation();
  const [sidebarOpen, setSidebarOpen] = useState(false);

  const hideLayout =
    location.pathname.includes("/shoppee/signin") ||
    location.pathname.includes("/shoppee/forgot-password") ||
    location.pathname.includes("/shoppee/invoice/");

  if (hideLayout) {
    return <Outlet />;
  }

  const toggleSidebar = () => setSidebarOpen(!sidebarOpen);
  const closeSidebar = () => setSidebarOpen(false);

  return (
    <>
  <SessionManager />
    <div className="flex bg-gray-100 min-h-screen">
      {/* Mobile sidebar overlay */}
      {sidebarOpen && (
        <div
          className="fixed inset-0 bg-black/50 z-30 lg:hidden"
          onClick={closeSidebar}
        />
      )}

      {/* Sidebar */}
      <div
        className={`fixed inset-y-0 left-0 z-40 transform transition-transform duration-300 ${
          sidebarOpen ? "translate-x-0" : "-translate-x-full"
        } lg:translate-x-0`}
      >
        <Sidebar onClose={closeSidebar} />
      </div>

      {/* Main content */}
      <div className="flex-1 flex flex-col w-full lg:w-auto min-w-0 lg:ml-64 h-screen overflow-auto">
        <Navbar onToggleSidebar={toggleSidebar} isSidebarOpen={sidebarOpen} />
        <Outlet />
      </div>
    </div>
      </>
  );
}