import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";

function TeamManagement() {

    const navigate = useNavigate();
    const [dots, setDots] = useState("");

    useEffect(() => {
        const interval = setInterval(() => {
            setDots((prev) => (prev.length >= 3 ? "" : prev + "."));
        }, 500);
        return () => clearInterval(interval);
    }, []);

    return (
        <div className="min-h-[80vh] bg-white flex items-center justify-center px-6 py-12">
            <div className="text-center max-w-md w-full">

                {/* Icon */}
                <div className="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-7">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#111" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <polyline points="12 6 12 12 16 14" />
                    </svg>
                </div>

                {/* Tag */}
                <span className="inline-block text-xs font-semibold tracking-widest uppercase text-gray-400 bg-gray-100 rounded-full px-4 py-1.5 mb-6">
                    Coming Soon{dots}
                </span>

                {/* Title */}
                <h1 className="text-4xl sm:text-5xl font-bold text-gray-900 leading-tight mb-5">
                    Team Management is  on its way
                </h1>

                {/* Divider */}
                <div className="w-10 h-0.5 bg-gray-900 rounded-full mx-auto mb-6" />

                {/* Description */}
                <p className="text-gray-400 text-sm sm:text-base leading-relaxed mb-10">
                    We're working hard to bring you something great. <br />
                    This page will be available very soon.
                </p>

                {/* Buttons */}
                <div className="flex items-center justify-center gap-3 flex-wrap">
                    <button
                        onClick={() => navigate(-1)}
                        className="inline-flex items-center gap-2 bg-gray-900 text-white text-sm font-semibold px-6 py-3 rounded-xl hover:bg-gray-700 active:scale-95 transition-all"
                    >
                        ← Go Back
                    </button>
                    <button
                        onClick={() => navigate("/")}
                        className="inline-flex items-center gap-2 border border-gray-200 text-gray-600 text-sm font-semibold px-6 py-3 rounded-xl hover:border-gray-900 hover:text-gray-900 active:scale-95 transition-all"
                    >
                        Home
                    </button>
                </div>

            </div>
        </div>
    );
}

export default TeamManagement;