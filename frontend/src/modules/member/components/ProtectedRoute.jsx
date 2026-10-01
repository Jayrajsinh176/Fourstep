import { Navigate } from "react-router-dom";

export default function ProtectedRoute({ children }) {
  const memberData = localStorage.getItem("memberData");
  const session = localStorage.getItem("memberSession");

  if (!memberData || session !== "true") {
    return <Navigate to="/member/signin" replace />;
  }

  return children;
}