  import React from "react";

  export default function Card({ title, value, subtitle, color }) {
    return (
      <div className={`rounded-2xl shadow-md p-6 text-white ${color}`}>
        <div className="flex justify-between items-start">
          <div className="text-sm opacity-90">{title}</div>
          <span className="text-xs bg-white/20 px-2 py-1 rounded-md">
            +2.06%
          </span>
        </div>

        <div className="text-3xl font-bold mt-4">{value}</div>
        <div className="text-sm opacity-80 mt-1">{subtitle}</div>
      </div>
    );
  }
