import React from "react";

export default function Topbar() {

  const user = JSON.parse(localStorage.getItem("user"));

  return (
    <div className="bg-blue-600 text-white rounded-xl p-4 shadow">
      Welcome back, {user?.fullname}
    </div>
  );
} 