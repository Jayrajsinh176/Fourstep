import React, { useState } from "react";
import { useNavigate } from "react-router-dom";

function MemberModal({ isOpen, onClose, selectedProduct }) {
    const navigate = useNavigate();

    const [showMemberInput, setShowMemberInput] = useState(false);
    const [memberId, setMemberId] = useState("");
    const [error, setError] = useState("");

    const validMemberIds = ["MEM123", "VIP999"];

    if (!isOpen) return null;

    const handleContinue = () => {
        if (!memberId.trim()) {
            setError("Please enter Member ID");
            return;
        }

        if (!validMemberIds.includes(memberId.trim())) {
            setError("Invalid Member ID");
            return;
        }

        localStorage.setItem("cartProduct", JSON.stringify(selectedProduct));
        navigate("/checkout", { state: { memberId } });

        resetAndClose();
    };

    const resetAndClose = () => {
        setShowMemberInput(false);
        setMemberId("");
        setError("");
        onClose();
    };

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center">

            <div
                className="absolute inset-0 bg-black/40 backdrop-blur-md"
                onClick={resetAndClose}
            ></div>

            <div className="relative bg-white p-6 rounded-xl w-80 text-center shadow-2xl">

                {!showMemberInput ? (
                    <>
                        <h2 className="text-lg font-semibold mb-4">
                            Are you a Member?
                        </h2>

                        <div className="flex justify-center gap-4">
                            <button
                                onClick={() => setShowMemberInput(true)}
                                className="bg-black text-white px-4 py-2 rounded"
                            >
                                Yes
                            </button>

                            <button
                                onClick={() => {
                                    localStorage.setItem("cartProduct", JSON.stringify(selectedProduct));
                                    navigate("/product");
                                    resetAndClose();
                                }}
                                className="bg-gray-200 px-4 py-2 rounded"
                            >
                                No
                            </button>
                        </div>
                    </>
                ) : (
                    <>
                        <h2 className="text-lg font-semibold mb-2">
                            Enter Member ID
                        </h2>

                        <p className="text-xs text-gray-500 mb-3">
                            Try: MEM123 or VIP999
                        </p>

                        <input
                            type="text"
                            value={memberId}
                            onChange={(e) => {
                                setMemberId(e.target.value);
                                setError("");
                            }}
                            placeholder="Enter your ID"
                            className="border w-full px-3 py-2 mb-2 rounded focus:outline-none focus:ring-2 focus:ring-black"
                        />

                        {error && (
                            <p className="text-red-500 text-xs mb-2">
                                {error}
                            </p>
                        )}

                        <button
                            onClick={handleContinue}
                            className="bg-black text-white w-full py-2 rounded"
                        >
                            Continue
                        </button>
                    </>
                )}

                <button
                    onClick={resetAndClose}
                    className="text-sm text-gray-500 mt-4"
                >
                    Cancel
                </button>
            </div>
        </div>
    );
}

export default MemberModal;
