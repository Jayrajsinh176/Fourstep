
import { Outlet } from "react-router-dom";
import SessionManager from "./components/SessionManager";

export default function MemberLayout() {
return (
  <>
    <SessionManager />

    <div className="member-wrapper">
      <div className="member-main">
        <Outlet />
      </div>
    </div>
  </>
);
}
