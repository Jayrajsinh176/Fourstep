import { useState } from "react";
import Sidebar from "../components/sidebar";
import Navbar from "../components/navbar";
import { shoppeeApi as api } from "../api/axios";

function Toast({ toasts, removeToast }) {
  return (
    <div className="fixed top-5 right-5 z-50 flex flex-col gap-3 w-80">
      {toasts.map((t) => (
        <div
          key={t.id}
          className={`flex items-start gap-3 px-4 py-3 rounded-xl shadow-md text-sm font-medium border
            ${t.type === "success" ? "bg-green-50 border-green-200 text-green-800" : ""}
            ${t.type === "error" ? "bg-red-50 border-red-200 text-red-800" : ""}
            ${t.type === "warning" ? "bg-yellow-50 border-yellow-200 text-yellow-800" : ""}
          `}
        >
          <span>
            {t.type === "success" && "✅"}
            {t.type === "error" && "❌"}
            {t.type === "warning" && "⚠️"}
          </span>

          <span className="flex-1">{t.message}</span>

          <button onClick={() => removeToast(t.id)}>
            ✕
          </button>
        </div>
      ))}
    </div>
  );
}
function useToast() {
  const [toasts, setToasts] = useState([]);

  const showToast = (type, message) => {
    const id = Date.now();

    setToasts((prev) => [...prev, { id, type, message }]);

    setTimeout(() => {
      setToasts((prev) => prev.filter((t) => t.id !== id));
    }, 4000);
  };

  const removeToast = (id) => {
    setToasts((prev) => prev.filter((t) => t.id !== id));
  };

  return { toasts, showToast, removeToast };
}

export default function MyProfileView() {
  const user = JSON.parse(localStorage.getItem("user"));

  const [editMode, setEditMode] = useState(false);
const { toasts, showToast, removeToast } = useToast();
  const [formData, setFormData] = useState({
    address: user?.address || "",
    state: user?.state || "",
    city: user?.city || "",
    district: user?.district || "",
    pin_code: user?.pin_code || "",
  });

  const fullName = user?.fullname?.trim() || "";
  const nameParts = fullName.split(" ");
  const firstName = nameParts[0] || "";
  const lastName = nameParts.slice(1).join(" ") || "";

  const handleEdit = () => {
    setEditMode(true);
  };

  const handleCancel = () => {
    setFormData({
      address: user?.address || "",
      state: user?.state || "",
      city: user?.city || "",
      district: user?.district || "",
      pin_code: user?.pin_code || "",
    });
    setEditMode(false);
  };

  const handleSave = () => {
    setEditMode(false);
  };

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value,
    });
  };

  const handleUpdate = async () => {
    if (!formData.pin_code) {
     showToast("warning", "Pincode is required");
      return;
    }

    try {
      const response = await api.put(`/update-profile/${user.id}`, formData);
   showToast("success", "Profile updated successfully!");

      localStorage.setItem("user", JSON.stringify(response.data.data));

      setEditMode(false);
    } catch (error) {
      console.log(error.response?.data || error.message);
   showToast(
  "error",
  error.response?.data?.message || "Update failed"
);
    }
    window.location.reload();
  };

  return (
    <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">
      <Toast toasts={toasts} removeToast={removeToast} />
      <div className="flex-1 flex flex-col">
        <div className="text-center mt-4 md:mt-6">
          <h1 className="text-2xl md:text-3xl font-bold text-[#B0422E]">
            My Profile
          </h1>
        </div>

        <div className="p-3 md:p-6 space-y-4 md:space-y-6">
          {/* USER CARD */}
          <div className="bg-white rounded-2xl shadow-sm p-4 md:p-6">
            <h2 className="text-blue-700 text-lg md:text-xl font-bold">
              {user?.fullname}
            </h2>
            <p className="text-gray-500 text-xs md:text-sm mt-1">
              {user?.state}, India
            </p>
          </div>

          {/* PERSONAL INFO */}
          <div className="bg-white rounded-2xl shadow-sm p-4 md:p-6">
            <h2 className="text-blue-700 text-lg md:text-xl font-bold mb-4 md:mb-6">
              Personal Information
            </h2>
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-y-4 md:gap-y-6">
              <Info label="First Name" value={firstName} />
              <Info label="Last Name" value={lastName} />
              <Info label="Date of Birth" value={user?.dob} />
              <Info label="Email Address" value={user?.email} />
              <Info label="Phone Number" value={`+91 ${user?.mobile_no}`} />
              <Info label="PAN Number" value={user?.user_pan} />
              <Info label="Aadhaar Number" value={user?.aadhaar_no} />
              <Info label="Address" value={user?.user_address} />


            </div>
          </div>

          {/*  Branch Information*/}
          <div className="bg-white rounded-2xl shadow-sm p-4 md:p-6">
            <h2 className="text-blue-700 text-lg md:text-xl font-bold mb-4">
              Branch Information
            </h2>

            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-y-4 md:gap-y-6">
              <Info label="Branch Name" value={user?.branch_name} />
              <Info label="Branch Type" value={user?.branch_type} />
              <Info label="Branch PAN" value={user?.branch_pan} />
              <Info label="GST NO" value={user?.gst_no} />
            </div>
          </div>

          {/* SHIPPING ADDRESS */}
          <div className="bg-white rounded-2xl shadow-sm p-4 md:p-6">
            <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 mb-4 md:mb-6">
              <h2 className="text-blue-700 text-lg md:text-xl font-bold">
                Branch Address
              </h2>

              {!editMode ? (
                <button
                  onClick={handleEdit}
                  className="bg-[#B0422E] text-white px-4 md:px-5 py-2 rounded-md text-xs md:text-sm whitespace-nowrap"
                >
                  ✎ Edit
                </button>
              ) : (
                <div className="flex gap-2">
                  <button
                    onClick={handleSave}
                    className="bg-green-600 text-white px-3 md:px-4 py-2 rounded-md text-xs md:text-sm whitespace-nowrap"
                  >
                    Save
                  </button>
                  <button
                    onClick={handleCancel}
                    className="bg-gray-400 text-white px-3 md:px-4 py-2 rounded-md text-xs md:text-sm whitespace-nowrap"
                  >
                    Cancel
                  </button>
                </div>
              )}
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-y-4 md:gap-y-6">
              <Field
                label="Address"
                name="address"
                value={editMode ? formData.address : user?.address}
                editMode={editMode}
                onChange={handleChange}
                colSpan="sm:col-span-2 lg:col-span-2 xl:col-span-2"
              />

              <Field
                label="State"
                name="state"
                value={editMode ? formData.state : user?.state}
                editMode={editMode}
                onChange={handleChange}
              />

              <Field
                label="City"
                name="city"
                value={editMode ? formData.city : user?.city}
                editMode={editMode}
                onChange={handleChange}
              />

              <Field
                label="District"
                name="district"
                value={editMode ? formData.district : user?.district}
                editMode={editMode}
                onChange={handleChange}
              />

              <Field
                label="Pincode"
                name="pin_code"
                value={editMode ? formData.pin_code : user?.pin_code}
                editMode={editMode}
                onChange={handleChange}
              />
            </div>
          </div>

          <div className="flex justify-center mt-4 md:mt-6">
            <button
              onClick={handleUpdate}
              className="bg-[#B0422E] hover:bg-red-800 text-white px-6 md:px-8 py-2 rounded-md text-sm transition"
            >
              Update
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}

function Info({ label, value }) {
  return (
    <div>
      <p className="text-gray-500 text-xs md:text-sm">{label}</p>
      <p className="font-medium text-sm md:text-base break-words">{value}</p>
    </div>
  );
}

function Field({ label, name, value, editMode, onChange, colSpan }) {
  return (
    <div className={colSpan}>
      <p className="text-gray-500 text-xs md:text-sm">{label}</p>

      {editMode ? (
        <input
          name={name}
          value={value}
          onChange={onChange}
          className="w-full border rounded px-2 md:px-3 py-1 mt-1 text-sm"
        />
      ) : (
        <p className="font-medium text-sm md:text-base break-words">{value}</p>
      )}
    </div>
  );
}