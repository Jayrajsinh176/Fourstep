import Sidebar from "../components/Sidebar";
import Navbar from "../components/Navbar";
import { useState } from "react";

const API_BASE_URL =
  import.meta.env.VITE_API_BASE_URL || "https://fourstepretail.com/api";

export default function CreateIDCard() {
  const [selectedFile, setSelectedFile] = useState(null);
  const [fileName, setFileName] = useState("");
  const [error, setError] = useState("");
  const [message, setMessage] = useState("");
  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleFileChange = (e) => {
    const file = e.target.files[0];

    setError("");
    setMessage("");

    if (!file) {
      setSelectedFile(null);
      setFileName("");
      return;
    }

    if (file.size > 2 * 1024 * 1024) {
      setSelectedFile(null);
      setFileName("");
      setError("File size must be 2 MB or less");
      return;
    }

    const allowedTypes = ["image/jpeg", "image/png", "image/gif"];
    if (!allowedTypes.includes(file.type)) {
      setSelectedFile(null);
      setFileName("");
      setError("Only JPG, PNG, or GIF images are allowed");
      return;
    }

    setSelectedFile(file);
    setFileName(file.name);
  };

  const handleSubmit = async () => {
    setError("");
    setMessage("");

    let memberData = {};
    try {
      memberData = JSON.parse(localStorage.getItem("memberData") || "{}") || {};
    } catch {
      memberData = {};
    }

    if (!memberData?.user_id) {
      setError("Please sign in first");
      return;
    }

    if (!selectedFile) {
      setError("Please choose a photo before submit");
      return;
    }

    try {
      setIsSubmitting(true);

      const formData = new FormData();
      formData.append("user_id", memberData.user_id);
      formData.append("photo", selectedFile);

      const response = await fetch(`${API_BASE_URL}/member/id-card`, {
        method: "POST",
        headers: {
          Accept: "application/json",
        },
        body: formData,
      });

      const data = await response.json();

      if (!response.ok) {
        setError(data?.message || "Unable to upload photo");
        return;
      }

      setMessage("Photo uploaded successfully");
    } catch {
      setError("Unable to connect to backend");
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">
      <Sidebar />

      <div className="flex-1 min-w-0 flex flex-col">
        <Navbar />

        <div className="text-center mt-6">
          <h1 className="text-2xl font-bold text-[#B0422E]">Create ID Card</h1>
          {error && <p className="text-sm text-red-500 mt-2">{error}</p>}
          {message && <p className="text-sm text-green-600 mt-2">{message}</p>}
        </div>

        <div className="p-6">
          <div className="bg-white rounded-2xl border-2 border-white p-8">
            <div>
              <p className="text-gray-700 font-medium mb-3">Upload Photo :</p>

              <div className="flex flex-col sm:flex-row sm:items-center gap-4 sm:gap-6">
                <label className="cursor-pointer bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-md text-sm flex items-center gap-2 shadow-sm">
                  <svg
                    className="w-4 h-4 text-gray-600"
                    fill="none"
                    stroke="currentColor"
                    strokeWidth="2"
                    viewBox="0 0 24 24"
                  >
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M12 4v12m0 0l-4-4m4 4l4-4"
                    />
                  </svg>
                  Choose File
                  <input
                    type="file"
                    accept="image/*"
                    onChange={handleFileChange}
                    className="hidden"
                  />
                </label>

                <p className="text-xs text-gray-400">
                  Max. file size allowed : 2 MB | Only upload image JPG/PNG/GIF
                </p>
              </div>

              {fileName && (
                <p className="text-sm text-gray-600 mt-3">
                  Selected: {fileName}
                </p>
              )}

              <div className="mt-6">
                <button
                  type="button"
                  onClick={handleSubmit}
                  disabled={isSubmitting}
                  className="bg-red-700 hover:bg-red-800 disabled:opacity-60 text-white px-8 py-2 rounded-md"
                >
                  {isSubmitting ? "Submitting..." : "Submit"}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
