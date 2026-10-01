import React, { useEffect } from "react";
import { LuPanelLeftDashed } from "react-icons/lu";
import { HiOutlineMenu, HiOutlineX } from "react-icons/hi";
import { useLocation, Link, useNavigate } from "react-router-dom";

export default function Navbar({ onToggleSidebar, isSidebarOpen }) {
  const location = useLocation();
  const navigate = useNavigate();
  const path = location.pathname;

  const user = JSON.parse(localStorage.getItem("user"));

  // Redirect if not logged in
  useEffect(() => {
    if (!user) {
      navigate("/shoppee/signin");
    }
  }, [user, navigate]);

  const formatTitle = (pathname) => {
    const cleaned = pathname.replace(/^\/shoppee\/?/, "");
    if (!cleaned || cleaned === "dashboard") return "Dashboard";

    const name = cleaned
      .replace(/[-\/]/g, " ")
      .replace(/([A-Z])/g, " $1")
      .trim();

    return name
      .split(" ")
      .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
      .join(" ");
  };

  const pageName = formatTitle(path);

  return (
    <div className="bg-white border-b border-gray-200">
      <div className="h-16 px-4 md:px-6 flex justify-between items-center">
        {/* LEFT SIDE */}
        <div className="flex items-center gap-3 min-w-0 flex-1">
          {/* Mobile Menu Button */}
          <button
            onClick={onToggleSidebar}
            className="lg:hidden p-2 hover:bg-gray-100 rounded-lg transition"
            aria-label="Toggle sidebar"
          >
            {isSidebarOpen ? (
              <HiOutlineX className="w-6 h-6" />
            ) : (
              <HiOutlineMenu className="w-6 h-6" />
            )}
          </button>

          <div className="min-w-0">
            <h1 className="text-sm md:text-lg font-semibold text-gray-700 flex items-center truncate">
              <LuPanelLeftDashed className="mr-1 md:mr-2 flex-shrink-0" />

              <Link
                to="/shoppee/dashboard"
                className="hover:text-blue-600 truncate hidden sm:inline"
              >
                Dashboard
              </Link>

              {path !== "/dashboard" && (
                <span className="ml-1 text-gray-500 truncate">
                  {" / "}
                  <span className="text-gray-700 font-medium text-xs md:text-sm truncate">
                    {pageName}
                  </span>
                </span>
              )}
            </h1>

            <p className="text-xs text-gray-400 truncate">
              CUSTOMER ID: {user?.member_id}
            </p>
          </div>
        </div>

        {/* RIGHT SIDE */}
        <div className="flex items-center gap-2 md:gap-6 flex-shrink-0">
          <div className="flex items-center gap-2">
            <img
              src="https://i.pravatar.cc/40"
              alt="profile"
              className="w-8 md:w-9 h-8 md:h-9 rounded-full border flex-shrink-0"
            />
            <div className="text-xs md:text-sm leading-tight hidden sm:block">
              <p className="font-semibold text-gray-700 truncate">
                {user?.fullname}
              </p>
              <p className="text-xs text-gray-400">Member</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
