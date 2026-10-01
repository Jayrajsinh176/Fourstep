import { useEffect, useState } from "react";
import Sidebar from "../components/Sidebar";
import Navbar from "../components/Navbar";
import { shoppeeApi as api } from "../api/axios";


const normalizeReviewStatus = (status) => {
    const normalized = String(status || "process").toLowerCase().trim();

    if (["success", "approved", "approve", "accepted"].includes(normalized)) {
        return "success";
    }

    if (["reject", "rejected", "failed", "deny", "denied"].includes(normalized)) {
        return "reject";
    }

    return "process";
};

const formatReviewStatusLabel = (status) => {
    const normalized = normalizeReviewStatus(status);

    if (normalized === "success") return "Approved";
    if (normalized === "reject") return "Rejected";
    return "Processing";
};

const getReviewStatusCard = (status) => {
    const normalized = normalizeReviewStatus(status);

    if (normalized === "success") {
        return {
            title: "KYC Approved Successfully",
            subtitle: "Your bank and identity details have been verified successfully.",
            titleClass: "text-green-600",
            badgeClass: "bg-green-100 text-green-700",
        };
    }

    if (normalized === "reject") {
        return {
            title: "KYC Rejected",
            subtitle:
                "Your submitted details were rejected. Please update the required information and resubmit your KYC.",
            titleClass: "text-red-600",
            badgeClass: "bg-red-100 text-red-700",
        };
    }

    return {
        title: "KYC Under Review",
        subtitle:
            "Your bank and identity details have been submitted successfully and are currently being reviewed by admin.",
        titleClass: "text-yellow-600",
        badgeClass: "bg-yellow-100 text-yellow-700",
    };
};

const getStatusMessageClass = (status) => {
    const normalized = normalizeReviewStatus(status);

    if (normalized === "success") return "text-green-600";
    if (normalized === "reject") return "text-red-600";
    return "text-yellow-600";
};

const hasBankDetails = (kycData) => {
    return Boolean(
        kycData?.account_beneficiary_name &&
        kycData?.account_no &&
        kycData?.ifs_code &&
        kycData?.bank_name &&
        kycData?.branch_name
    );
};

const hasIdentityDetails = (kycData) => {
    return Boolean(kycData?.aadhaar_number && kycData?.pan_number);
};

const hasSubmittedKyc = (kycData) => {
    return Boolean(kycData?.id);
};

function MyKYC() {
    const [step, setStep] = useState(1);
    const [isBankReadOnly, setIsBankReadOnly] = useState(false);
    const [isIdentityReadOnly, setIsIdentityReadOnly] = useState(false);
    const [bankPassbookFile, setBankPassbookFile] = useState(null);
    const [aadhaarCardFile, setAadhaarCardFile] = useState(null);
    const [panCardFile, setPanCardFile] = useState(null);
    const [transactionPasswordStatus, setTransactionPasswordStatus] =
        useState("process");
    const [isLoading, setIsLoading] = useState(true);
    const [isSaving, setIsSaving] = useState(false);
    const [message, setMessage] = useState("");
    const [error, setError] = useState("");
    const [kycId, setKycId] = useState(null);

    const [form, setForm] = useState({
        account_beneficiary_name: "",
        account_no: "",
        re_account_no: "",
        ifs_code: "",
        bank_name: "",
        branch_name: "",
        aadhaar_number: "",
        pan_number: "",
        transaction_password: "",
    });

    useEffect(() => {
        const loadKyc = async () => {
            setIsLoading(true);
            setError("");
            setMessage("");

            const user = JSON.parse(localStorage.getItem("user") || "{}");

            if (!user?.member_id) {
                setIsLoading(false);
                setError("Please sign in first");
                return;
            }

            try {
                const response = await api.get(
                    `/member-kyc/latest/${user.member_id}`
                );

                const data = response.data;



                const kycData = data?.data || {};
                const reviewStatus = normalizeReviewStatus(
                    kycData?.status || "process"
                );

                setTransactionPasswordStatus(reviewStatus);
                setKycId(kycData?.id || null);

                setForm({
                    account_beneficiary_name: kycData?.account_beneficiary_name || "",
                    account_no: kycData?.account_no || "",
                    re_account_no: kycData?.account_no || "",
                    ifs_code: kycData?.ifs_code || "",
                    bank_name: kycData?.bank_name || "",
                    branch_name: kycData?.branch_name || "",
                    aadhaar_number: kycData?.aadhaar_number || "",
                    pan_number: kycData?.pan_number || "",
                    transaction_password: "",
                });

                const bankCompleted = hasBankDetails(kycData);
                const identityCompleted = hasIdentityDetails(kycData);
                const submitted = hasSubmittedKyc(kycData);

                if (submitted && reviewStatus !== "reject") {
                    setStep(3);
                    setIsBankReadOnly(true);
                    setIsIdentityReadOnly(true);
                } else if (submitted && reviewStatus === "reject") {

                    setStep(2);

                    setIsBankReadOnly(false);
                    setIsIdentityReadOnly(false);

                    setBankPassbookFile(null);
                    setAadhaarCardFile(null);
                    setPanCardFile(null);

                    setMessage("Your KYC was rejected. Please upload all documents again and resubmit.");
                } else if (bankCompleted && !identityCompleted) {
                    setStep(2);
                    setIsBankReadOnly(true);
                    setIsIdentityReadOnly(false);
                } else {
                    setStep(1);
                    setIsBankReadOnly(bankCompleted);
                    setIsIdentityReadOnly(false);
                }
            } catch {
                setError("Unable to connect to backend");
            } finally {
                setIsLoading(false);
            }
        };

        loadKyc();
    }, []);

    const handleChange = (e) => {
        const { name, value } = e.target;
        setForm((prev) => ({ ...prev, [name]: value }));
    };

    const handlePassbookChange = (e) => {
        const file = e.target.files?.[0] || null;
        setBankPassbookFile(file);
    };

    const handleAadhaarCardChange = (e) => {
        const file = e.target.files?.[0] || null;
        setAadhaarCardFile(file);
    };

    const handlePanCardChange = (e) => {
        const file = e.target.files?.[0] || null;
        setPanCardFile(file);
    };

    const renderUploadContainer = ({ id, file, onChange, disabled, existingText }) => (
        <div
            className={`flex items-center gap-3 rounded-md border border-gray-200 bg-gray-50 px-3 py-2 ${disabled ? "opacity-80" : ""
                }`}
        >
            <input
                id={id}
                type="file"
                accept=".jpg,.jpeg,.png,.pdf"
                onChange={onChange}
                disabled={disabled}
                className="hidden"
            />
            <label
                htmlFor={id}
                className={`inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium ${disabled
                    ? "cursor-not-allowed bg-gray-100 text-gray-400"
                    : "cursor-pointer bg-white text-gray-700 hover:bg-gray-100"
                    }`}
            >
                Choose File
            </label>
            <span className="text-sm text-gray-500 truncate">
                {file ? file.name : existingText || "No file chosen"}
            </span>
        </div>
    );

    const validateStep1 = () => {
        if (
            !form.account_beneficiary_name ||
            !form.account_no ||
            !form.re_account_no ||
            !form.ifs_code ||
            !form.bank_name ||
            !form.branch_name
        ) {
            setError("Please fill all bank info fields");
            return false;
        }

        if (form.account_no !== form.re_account_no) {
            setError("Account numbers do not match");
            return false;
        }

        if (!bankPassbookFile) {
            setError("Please upload your bank passbook or cancelled cheque photo");
            return false;
        }

        return true;
    };

    const goToStep2 = () => {
        setError("");
        setMessage("");

        if (!validateStep1()) return;

        setStep(2);
    };

    const goBackToStep1 = () => {
        if (transactionPasswordStatus === "success") return;
        setError("");
        setMessage("");
        setStep(1);
    };

    const submitKyc = async () => {
        setError("");
        setMessage("");

        const user = JSON.parse(localStorage.getItem("user") || "{}");

        if (!user?.id) {
            setError("Please sign in first");
            return;
        }

        if (!form.aadhaar_number || !form.pan_number || !form.transaction_password) {
            setError("Please fill all KYC details and transaction password");
            return;
        }

        if (!aadhaarCardFile) {
            setError("Please upload Aadhaar card image");
            return;
        }

        if (!panCardFile) {
            setError("Please upload PAN card image");
            return;
        }

        const formData = new FormData();
        formData.append("member_id", user.id);
        formData.append("account_beneficiary_name", form.account_beneficiary_name);
        formData.append("account_no", form.account_no);
        formData.append("re_account_no", form.re_account_no);
        formData.append("ifs_code", form.ifs_code);
        formData.append("bank_name", form.bank_name);
        formData.append("branch_name", form.branch_name);
        formData.append("aadhaar_number", form.aadhaar_number);
        formData.append("pan_number", form.pan_number);
        formData.append("transaction_password", form.transaction_password);

        if (bankPassbookFile) {
            formData.append("bank_passbook_image", bankPassbookFile);
        }

        if (aadhaarCardFile) {
            formData.append("aadhaar_image", aadhaarCardFile);
        }

        if (panCardFile) {
            formData.append("pan_image", panCardFile);
        }

        try {
            setIsSaving(true);

            const response = await api.post(
                "/member-kyc",
                formData,
                {
                    headers: {
                        "Content-Type": "multipart/form-data",
                    },
                }
            );

            const data = response.data;



            const latestStatus = normalizeReviewStatus(
                data?.data?.status || "process"
            );

            setTransactionPasswordStatus(latestStatus);
            setIsBankReadOnly(true);
            setIsIdentityReadOnly(true);
            setStep(3);
            setForm((prev) => ({
                ...prev,
                transaction_password: "",
            }));
            setMessage("KYC submitted successfully");
        } catch (err) {

            console.log(err.response?.data);

            setError(
                err.response?.data?.message ||
                "Upload error"
            );
        } finally {
            setIsSaving(false);
        }
    };

    const reviewCard = getReviewStatusCard(transactionPasswordStatus);

    return (
        <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">
            {/* <Sidebar /> */}

            <div className="flex-1 min-w-0 flex flex-col">
                {/* <Navbar /> */}

                <div className="text-center mt-6">
                    <h1 className="text-2xl font-bold text-[#B0422E]">My KYC with Bank Info</h1>

                    {isLoading && <p className="text-sm text-gray-500 mt-2">Loading KYC...</p>}

                    {error && <p className="text-sm text-red-500 mt-2">{error}</p>}

                    {message && (
                        <p className={`text-sm mt-2 ${getStatusMessageClass(transactionPasswordStatus)}`}>
                            {message}
                        </p>
                    )}

                    <div className="flex justify-center items-center mt-6 px-4">
                        <div className="flex items-center text-xs sm:text-sm overflow-x-auto max-w-full pb-2">
                            <div className="flex flex-col items-center">
                                <div
                                    className={`w-7 h-7 rounded-full flex items-center justify-center text-white text-xs ${step >= 1 ? "bg-blue-600" : "bg-gray-300"
                                        }`}
                                >
                                    1
                                </div>
                                <span className="mt-1 text-gray-600">Bank Info</span>
                            </div>

                            <div className={`w-24 h-0.5 mx-2 ${step >= 2 ? "bg-blue-600" : "bg-gray-300"}`} />

                            <div className="flex flex-col items-center">
                                <div
                                    className={`w-7 h-7 rounded-full flex items-center justify-center text-white text-xs ${step >= 2 ? "bg-blue-600" : "bg-gray-300"
                                        }`}
                                >
                                    2
                                </div>
                                <span className="mt-1 text-gray-600">KYC Details</span>
                            </div>

                            <div className={`w-24 h-0.5 mx-2 ${step >= 3 ? "bg-blue-600" : "bg-gray-300"}`} />

                            <div className="flex flex-col items-center">
                                <div
                                    className={`w-7 h-7 rounded-full flex items-center justify-center text-white text-xs ${step >= 3 ? "bg-blue-600" : "bg-gray-300"
                                        }`}
                                >
                                    3
                                </div>
                                <span className="mt-1 text-gray-600">Review Status</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="p-6">
                    {step === 1 && (
                        <div className="bg-white rounded-2xl shadow-sm p-8">
                            <h2 className="text-[#AE4329] font-bold mb-8">Bank Info</h2>

                            <div className="space-y-8">
                                {[
                                    { label: "Account Beneficiary Name*", name: "account_beneficiary_name" },
                                    { label: "Account No*", name: "account_no" },
                                    { label: "Re Enter Account No*", name: "re_account_no" },
                                    { label: "IFS Code*", name: "ifs_code" },
                                    { label: "Bank Name*", name: "bank_name" },
                                    { label: "Branch Name*", name: "branch_name" },
                                ].map((field) => (
                                    <div key={field.name} className="flex flex-col sm:flex-row sm:items-center gap-2">
                                        <label className="sm:w-64 font-bold text-gray-600">{field.label}</label>
                                        <input
                                            name={field.name}
                                            value={form[field.name]}
                                            onChange={(e) => {

                                                let value = e.target.value;

                                                if (
                                                    field.name === "account_no" ||
                                                    field.name === "re_account_no"
                                                ) {
                                                    value = value.replace(/\D/g, "");
                                                }

                                                setForm((prev) => ({
                                                    ...prev,
                                                    [field.name]: value,
                                                }));
                                            }}
                                            readOnly={isBankReadOnly}
                                            className={`flex-1 border-b border-gray-300 outline-none py-1 ${isBankReadOnly
                                                ? "bg-gray-100 text-gray-500 cursor-not-allowed"
                                                : "focus:border-blue-600"
                                                }`}
                                        />
                                    </div>
                                ))}

                                <div className="flex flex-col sm:flex-row sm:items-center gap-2">
                                    <label className="sm:w-64 font-bold text-gray-600">
                                        Upload Bank Passbook Front Page Or Cancelled Cheque Photo*
                                    </label>
                                    <div className="flex-1">
                                        {renderUploadContainer({
                                            id: "bank-passbook-photo",
                                            file: bankPassbookFile,
                                            onChange: handlePassbookChange,
                                            disabled: isBankReadOnly,
                                            existingText: isBankReadOnly ? "Already uploaded" : "",
                                        })}
                                    </div>
                                </div>
                            </div>

                            <div className="text-center mt-10">
                                <button
                                    onClick={goToStep2}
                                    disabled={isLoading}
                                    className="bg-[#B0422E] hover:bg-[#943825] text-white px-8 py-2 rounded-md disabled:opacity-60"
                                >
                                    Continue
                                </button>
                            </div>
                        </div>
                    )}

                    {step === 2 && (
                        <div className="bg-white rounded-2xl shadow-sm p-8">
                            <h2 className="text-[#AE4329] font-bold mb-8">KYC Details</h2>

                            <div className="space-y-8">
                                <div className="flex flex-col sm:flex-row sm:items-center gap-2">
                                    <label className="sm:w-64 font-bold text-gray-600">Aadhaar Number*</label>
                                    <input
                                        name="aadhaar_number"
                                        value={form.aadhaar_number}
                                        maxLength={12}
                                        onChange={(e) =>
                                            setForm((prev) => ({
                                                ...prev,
                                                aadhaar_number: e.target.value.replace(/\D/g, ""),
                                            }))
                                        }
                                        readOnly={isIdentityReadOnly}
                                        className={`flex-1 border-b border-gray-300 outline-none py-1 ${isIdentityReadOnly
                                            ? "bg-gray-100 text-gray-500 cursor-not-allowed"
                                            : "focus:border-blue-600"
                                            }`}
                                    />
                                </div>

                                <div className="flex flex-col sm:flex-row sm:items-center gap-2">
                                    <label className="sm:w-64 font-bold text-gray-600">
                                        Upload Aadhaar Card Front And Back Page Photo*
                                    </label>
                                    <div className="flex-1">
                                        {renderUploadContainer({
                                            id: "aadhaar-card-photo",
                                            file: aadhaarCardFile,
                                            onChange: handleAadhaarCardChange,
                                            disabled: isIdentityReadOnly,
                                            existingText: isIdentityReadOnly ? "Already uploaded" : "",
                                        })}
                                    </div>
                                </div>

                                <div className="flex flex-col sm:flex-row sm:items-center gap-2">
                                    <label className="sm:w-64 text-gray-600 font-bold">PAN Number*</label>
                                    <input
                                        name="pan_number"
                                        value={form.pan_number}
                                        onChange={(e) =>
                                            setForm((prev) => ({
                                                ...prev,
                                                pan_number: e.target.value.toUpperCase(),
                                            }))
                                        }
                                        readOnly={isIdentityReadOnly}
                                        className={`flex-1 border-b border-gray-300 outline-none py-1 ${isIdentityReadOnly
                                            ? "bg-gray-100 text-gray-500 cursor-not-allowed"
                                            : "focus:border-blue-600"
                                            }`}
                                    />
                                </div>

                                <div className="flex flex-col sm:flex-row sm:items-center gap-2">
                                    <label className="sm:w-64 text-gray-600 font-bold">
                                        Upload PAN Card Front Page Photo*
                                    </label>
                                    <div className="flex-1">
                                        {renderUploadContainer({
                                            id: "pan-card-photo",
                                            file: panCardFile,
                                            onChange: handlePanCardChange,
                                            disabled: isIdentityReadOnly,
                                            existingText: isIdentityReadOnly ? "Already uploaded" : "",
                                        })}
                                    </div>
                                </div>
                            </div>

                            <div className="flex flex-col sm:flex-row sm:items-center gap-2 mt-8">
                                <label className="sm:w-64 text-gray-600 font-bold">Transaction Password*</label>
                                <input
                                    type="password"
                                    name="transaction_password"
                                    value={form.transaction_password}
                                    onChange={handleChange}
                                    placeholder="Enter transaction password"
                                    className="flex-1 border-b border-gray-300 outline-none py-1 focus:border-blue-600"
                                />
                            </div>

                            <div className="mt-4 text-center">
                                <span
                                    className={`inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold ${reviewCard.badgeClass}`}
                                >
                                    Current Review Status: {formatReviewStatusLabel(transactionPasswordStatus)}
                                </span>
                            </div>

                            <div className="text-center mt-8 flex justify-center gap-3">
                                <button
                                    onClick={goBackToStep1}
                                    className="bg-gray-200 hover:bg-gray-300 text-gray-800 px-8 py-2 rounded-md"
                                >
                                    Back
                                </button>

                                <button
                                    onClick={submitKyc}
                                    disabled={isSaving}
                                    className="bg-[#B0422E] hover:bg-[#943825] text-white px-8 py-2 rounded-md disabled:opacity-60"
                                >
                                    {isSaving ? "Submitting..." : "Submit KYC"}
                                </button>
                            </div>
                        </div>
                    )}

                    {step === 3 && (
                        <div className="bg-white rounded-2xl shadow-sm p-12 text-center">
                            <div className="max-w-xl mx-auto">
                                <div className="mb-4">
                                    <span
                                        className={`inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold ${reviewCard.badgeClass}`}
                                    >
                                        {formatReviewStatusLabel(transactionPasswordStatus)}
                                    </span>
                                </div>

                                <h2 className={`text-xl font-semibold ${reviewCard.titleClass}`}>
                                    {reviewCard.title}
                                </h2>

                                <p className="mt-3 text-sm text-gray-500">{reviewCard.subtitle}</p>

                                {transactionPasswordStatus === "reject" && (
                                    <div className="mt-8">
                                        <button
                                            onClick={() => {

                                                setBankPassbookFile(null);
                                                setAadhaarCardFile(null);
                                                setPanCardFile(null);

                                                setIsBankReadOnly(false);
                                                setIsIdentityReadOnly(false);

                                                setStep(1);
                                            }}
                                            className="bg-[#B0422E] hover:bg-[#943825] text-white px-8 py-2 rounded-md"
                                        >
                                            Edit & Resubmit
                                        </button>
                                    </div>
                                )}

                                {transactionPasswordStatus === "process" && (
                                    <p className="mt-6 text-sm text-gray-500">
                                        Please wait for admin verification. You cannot edit the KYC while it is under review.
                                    </p>
                                )}

                                {transactionPasswordStatus === "success" && (
                                    <p className="mt-6 text-sm text-green-600">
                                        Your KYC is fully verified and approved.
                                    </p>
                                )}
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}

export default MyKYC;