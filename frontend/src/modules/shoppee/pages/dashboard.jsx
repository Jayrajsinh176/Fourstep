import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { LuWallet } from "react-icons/lu";
import { FaIndianRupeeSign } from "react-icons/fa6";
import { BsCart } from "react-icons/bs";
import { CiMedal } from "react-icons/ci";
import { HiOutlineDocumentText } from "react-icons/hi";
import { GoArrowUpRight } from "react-icons/go";
import { shoppeeApi as api } from "../api/axios";

function Card({ title, amount, note, color, icon }) {
  return (
    <div
      className={`p-4 md:p-6 rounded-xl text-white shadow hover:scale-[1.02] transition-all duration-300 ${color}`}
    >
      <div className="flex justify-between items-start">
        <div className="bg-white/20 w-12 md:w-14 h-12 md:h-14 rounded-xl flex items-center justify-center">
          <span className="text-white text-xl md:text-2xl">{icon}</span>
        </div>
      </div>

      <h3 className="text-sm md:text-lg opacity-90 mt-4">{title}</h3>
      <h2 className="text-2xl md:text-3xl lg:text-4xl font-bold mt-1">
        {amount}
      </h2>
      <p className="text-xs md:text-sm opacity-80 mt-1">{note}</p>
    </div>
  );
}

export default function Dashboard() {
  const navigate = useNavigate();

  const user = JSON.parse(localStorage.getItem("user"));

  const [dashboardData, setDashboardData] = useState({
    member_name: "",
    purchase_balance: "0.00",
    turnover_balance: "0.00",
    purchase_orders: 0,
    sales_orders: 0,
    sales_turnover: "0.00",
    commission_amount: "0.00",
  });

  useEffect(() => {
    const storedUser = localStorage.getItem("user");

    if (!storedUser) {
      navigate("/shoppee/signin");
      return;
    }

    const fetchDashboard = async () => {
      try {
        const userData = JSON.parse(storedUser);

        const response = await api.get(
          `/dashboard-summary/${userData.member_id}`
        );

        setDashboardData(response.data);

      } catch (error) {
        console.log(error);
      }
    };

    fetchDashboard();

  }, [navigate]);

  return (
    <div className="p-3 md:p-4 lg:p-6 bg-gray-100 min-h-screen">
      {/* Welcome */}
      <div className="bg-linear-to-r from-blue-600 to-blue-500 text-white px-4 md:px-6 py-4 rounded-xl shadow">
        <h1 className="text-lg md:text-2xl font-semibold">
          Welcome back, {dashboardData.member_name}
        </h1>
        <p className="text-xs md:text-sm opacity-90 mt-1">
          Here's your latest dashboard overview.
        </p>
      </div>

      {/* Cards */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 md:gap-5 mt-4 md:mt-6">
        {/* Purchase Balance */}
        <Card
          title="Purchase Balance"
          amount={`₹${dashboardData.purchase_balance}`}
          note="Current Purchase Balance"
          icon={<LuWallet />}
          color="bg-[linear-gradient(90deg,#3483D2,#2262A1)]"
        />

        {/* Turnover Balance */}
        <Card
          title="Turnover Balance"
          amount={`₹${dashboardData.turnover_balance}`}
          note="Current Turnover Balance"
          icon={<GoArrowUpRight />}
          color="bg-[linear-gradient(90deg,#45B0D7,#268CB1)]"
        />

        {/* Purchase Orders */}
        <Card
          title="Purchase Orders"
          amount={dashboardData.purchase_orders}
          note="Approved Purchase Orders"
          icon={<BsCart />}
          color="bg-[linear-gradient(90deg,#2DA5D2,#2874BE)]"
        />

        {/* Sales Orders */}
        <Card
          title="No. of Sales Orders"
          amount={dashboardData.sales_orders}
          note="Delivered Sales Orders"
          icon={<HiOutlineDocumentText />}
          color="bg-[linear-gradient(90deg,#B74331,#8A3225)]"
        />

        {/* Sales Turnover */}
        <Card
          title="Sales Turnover Amount"
          amount={`₹${dashboardData.sales_turnover}`}
          note="Total Sales Turnover"
          icon={<FaIndianRupeeSign />}
          color="bg-[linear-gradient(90deg,#2A9EC9,#266DB2)]"
        />

        {/* Commission */}
        <Card
          title="Commission Amount"
          amount={`₹${dashboardData.commission_amount}`}
          note="Available Commission"
          icon={<CiMedal />}
          color="bg-[linear-gradient(90deg,#2F80ED,#1E5DB8)]"
        />
      </div>
    </div>
  );
}