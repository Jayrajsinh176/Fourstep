import { LuPanelLeftDashed } from "react-icons/lu";
import { MdSupervisorAccount } from "react-icons/md";
import { useEffect, useState } from "react";
import { FiMenu } from "react-icons/fi";
import { useLocation } from "react-router-dom";
import { requestMemberApi } from "../utils/apiClient";

function toTitleCaseFromPath(pathname) {
  const cleanPath = String(pathname || "")
    .replace(/^\/member\/?/, "")
    .replace(/^\//, "");

  if (!cleanPath || cleanPath === "dashboard") {
    return "Dashboard";
  }

  const words = cleanPath.split("-").filter(Boolean);

  return words
    .map((word) => {
      const lower = word.toLowerCase();

      if (lower === "kyc") return "KYC";
      if (lower === "id") return "ID";

      return lower.charAt(0).toUpperCase() + lower.slice(1);
    })
    .join(" ");
}

function getInitials(name, fallback) {
  const trimmed = String(name || "").trim();

  if (trimmed) {
    const parts = trimmed.split(/\s+/);
    const initials = parts.length > 1
      ? parts[0][0] + parts[parts.length - 1][0]
      : parts[0].slice(0, 2);
    return initials.toUpperCase();
  }

  return String(fallback || "?").slice(0, 2).toUpperCase();
}

function getProfilePhotoUrl(path) {
  if (!path) return "";

  if (path.startsWith("http://") || path.startsWith("https://")) {
    return path;
  }

  if (
    path.startsWith("/profile_photos/") ||
    path.startsWith("/storage/")
  ) {
    const apiOrigin =
      window.location.hostname === "localhost" ||
      window.location.hostname === "127.0.0.1"
        ? "http://127.0.0.1:8000"
        : "https://fourstepretail.com";

    return `${apiOrigin}${path}`;
  }

  return path;
}

export default function Navbar({ pageTitle }) {
  const location = useLocation();
  const resolvedPageTitle = pageTitle || toTitleCaseFromPath(location.pathname);

  const [stats, setStats] = useState({
    left_members: 0,
    right_members: 0,
  });

  const [initials, setInitials] = useState("?");
  const [profilePhoto, setProfilePhoto] = useState("");

  useEffect(() => {
    try {
      const md = JSON.parse(localStorage.getItem("memberData") || "{}") || {};

      setInitials(getInitials(md.fullname, md.user_id));
      setProfilePhoto(md.profile_photo || "");
    } catch {
      setInitials("?");
      setProfilePhoto("");
    }
  }, []);

  useEffect(() => {
    const fetchDashboard = async () => {
      try {
        const md = JSON.parse(localStorage.getItem("memberData") || "{}") || {};

        if (!md.user_id) return;

        const res = await requestMemberApi("/member/dashboard", {
          headers: { "X-Auth-Member": md.user_id },
        });

        if (res?.ok) {
          setStats({
            left_members: res.data.left_members ?? 0,
            right_members: res.data.right_members ?? 0,
          });
        }
      } catch (error) {
        console.error("Dashboard fetch error:", error);
      }
    };

    fetchDashboard();
  }, []);

  return (
    <header className="h-16 bg-white border-b border-gray-300 flex items-center justify-between px-6">
      {/* Left */}
      <div className="flex items-center gap-3">
        <button
          type="button"
          className="lg:hidden inline-flex items-center justify-center w-9 h-9 rounded-md border border-gray-200 text-gray-700 hover:bg-gray-100"
          onClick={() => window.dispatchEvent(new Event("toggle-sidebar"))}
          aria-label="Open sidebar"
        >
          <FiMenu className="text-lg" />
        </button>

        <div>
          <h1 className="text-lg font-semibold text-gray-700 flex items-center gap-2">
            <LuPanelLeftDashed />
            {resolvedPageTitle}
          </h1>

          <p className="text-xs text-gray-400">
            CUSTOMER ID:
            {(() => {
              try {
                const md =
                  JSON.parse(localStorage.getItem("memberData") || "{}") || {};
                return md.user_id ? ` MLM-${md.user_id}` : "-";
              } catch {
                return "-";
              }
            })()}
          </p>
        </div>
      </div>

      {/* Right */}
      <div className="flex items-center gap-6">
        <div className="text-sm text-gray-600 hidden md:block">
          <p>
            <MdSupervisorAccount className="inline mr-1" />
            Left Members: <b>{stats.left_members}</b>
          </p>

          <p>
            <MdSupervisorAccount className="inline mr-1" />
            Right Members: <b>{stats.right_members}</b>
          </p>
        </div>

      <div className="w-9 h-9 rounded-full border overflow-hidden flex items-center justify-center text-sm font-semibold">
          {profilePhoto ? (
            <img
              src={getProfilePhotoUrl(profilePhoto)}
              alt="Profile"
              className="w-full h-full object-cover"
            />
          ) : (
            initials
          )}
        </div>
      </div>
    </header>
  );
}
