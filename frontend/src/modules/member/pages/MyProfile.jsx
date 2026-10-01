import { useEffect, useRef, useState } from "react";
import Sidebar from "../components/Sidebar";
import Navbar from "../components/Navbar";

const API_BASE_URL =
  import.meta.env.VITE_API_BASE_URL || "https://fourstepretail.com/api";

function getStoredMember() {
  try {
    return JSON.parse(localStorage.getItem("memberData") || "{}");
  } catch {
    return {};
  }
}

function getProfilePhotoUrl(path) {
  if (!path) return "";

  if (path.startsWith("http://") || path.startsWith("https://")) {
    return path;
  }

  const apiOrigin = API_BASE_URL.replace(/\/api\/?$/, "");

  if (
    path.startsWith("/profile_photos/") ||
    path.startsWith("/storage/")
  ) {
    return `${apiOrigin}${path}`;
  }

  return path;
}

function formatDate(value) {
  if (!value) return "-";

  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return "-";

  const day = String(date.getDate()).padStart(2, "0");
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const year = date.getFullYear();

  return `${day}-${month}-${year}`;
}

function getAddressFromMember(member) {
  return {
    address: member?.address || "",
    state: member?.state || "",
    city: member?.city || "",
    district: member?.district || "",
    pincode: member?.pin_code || "",
  };
}

function getShippingAddressFromMember(member) {
  return {
    address: member?.shipping_address || "",
    state: member?.shipping_state || "",
    city: member?.shipping_city || "",
    district: member?.shipping_district || "",
    pincode: member?.shipping_pin_code || "",
  };
}

function getNomineeFromMember(member) {
  return {
    nomineeName: member?.nominee_name || "",
    nomineeRelation: member?.nominee_relation || "",
    nomineeMobile: member?.nominee_mobile_no || "",
    nomineeAddress: member?.nominee_address || "",
    nomineeState: member?.nominee_state || "",
    nomineeCity: member?.nominee_city || "",
    nomineeDistrict: member?.nominee_district || "",
    nomineePincode: member?.nominee_pin_code || "",
  };
}

function composeFullAddressText(address) {
  if (!address) {
    return "-";
  }

  const parts = [
    address.address,
    address.city,
    address.district,
    address.state,
    address.pincode,
  ]
    .map((item) => String(item || "").trim())
    .filter(Boolean);

  return parts.length > 0 ? parts.join(", ") : "-";
}

export default function MyProfileView() {
  const [member, setMember] = useState(null);
  const [addressForm, setAddressForm] = useState(getAddressFromMember(null));
  const [shippingForm, setShippingForm] = useState(
    getShippingAddressFromMember(null),
  );
  const [nomineeForm, setNomineeForm] = useState(getNomineeFromMember(null));
  const [isLoading, setIsLoading] = useState(true);
  const [isSaving, setIsSaving] = useState(false);
  const [isEditingAddress, setIsEditingAddress] = useState(false);
  const [isEditingShipping, setIsEditingShipping] = useState(false);
  const [isEditingNominee, setIsEditingNominee] = useState(false);
  const [errorMessage, setErrorMessage] = useState("");
  const [successMessage, setSuccessMessage] = useState("");

  const [profilePhoto, setProfilePhoto] = useState("");
  const [selectedPhoto, setSelectedPhoto] = useState(null);
  const [photoPreview, setPhotoPreview] = useState("");
  const [isSavingPhoto, setIsSavingPhoto] = useState(false);

  const photoInputRef = useRef(null);

  useEffect(() => {
    loadProfile();
  }, []);

  async function loadProfile() {
    setIsLoading(true);
    setErrorMessage("");

    const localMember = getStoredMember();

    if (!localMember?.user_id) {
      setMember(localMember || null);
      setAddressForm(getAddressFromMember(localMember));
      setShippingForm(getShippingAddressFromMember(localMember));
      setNomineeForm(getNomineeFromMember(localMember));
      setIsLoading(false);
      return;
    }

    try {
      const response = await fetch(
        `${API_BASE_URL}/member/profile?user_id=${encodeURIComponent(localMember.user_id)}`,
        {
          method: "GET",
          headers: { Accept: "application/json" },
        },
      );

      const data = await response.json();

      if (!response.ok) {
        setErrorMessage(data?.message || "Unable to load profile");
        setMember(localMember);
        setAddressForm(getAddressFromMember(localMember));
        setShippingForm(getShippingAddressFromMember(localMember));
        setNomineeForm(getNomineeFromMember(localMember));
        return;
      }

      const serverMember = data?.member || data || localMember;
      setMember(serverMember);
      setProfilePhoto(serverMember?.profile_photo || "");
      setAddressForm(getAddressFromMember(serverMember));
      setShippingForm(getShippingAddressFromMember(serverMember));
      setNomineeForm(getNomineeFromMember(serverMember));
      localStorage.setItem("memberData", JSON.stringify(serverMember));
    } catch {
      setErrorMessage("Unable to load profile from server");
      setMember(localMember);
      setAddressForm(getAddressFromMember(localMember));
      setShippingForm(getShippingAddressFromMember(localMember));
      setNomineeForm(getNomineeFromMember(localMember));
    } finally {
      setIsLoading(false);
    }
  }
  async function onSavePhoto() {
    if (!member?.user_id) {
      setErrorMessage("User session not found. Please sign in again.");
      return;
    }

    if (!selectedPhoto) {
      setErrorMessage("Please select a profile photo first.");
      return;
    }

    setIsSavingPhoto(true);
    setErrorMessage("");
    setSuccessMessage("");

    try {
      const formData = new FormData();

      formData.append("user_id", member.user_id);
      formData.append("profile_photo", selectedPhoto);

      const response = await fetch(
        `${API_BASE_URL}/member/profile-photo`,
        {
          method: "POST",
          headers: {
            Accept: "application/json",
          },
          body: formData,
        },
      );

      const data = await response.json();

      if (!response.ok) {
        setErrorMessage(
          data?.message || "Unable to save profile photo",
        );
        return;
      }

      const updatedMember = data?.member || {
        ...member,
        profile_photo:
          data?.profile_photo || member.profile_photo,
      };

      setMember(updatedMember);
      setProfilePhoto(updatedMember.profile_photo || "");
      setSelectedPhoto(null);
      setPhotoPreview("");

      localStorage.setItem(
        "memberData",
        JSON.stringify(updatedMember),
      );

      setSuccessMessage("Profile photo updated successfully");

      if (photoInputRef.current) {
        photoInputRef.current.value = "";
      }
    } catch (error) {
      setErrorMessage("Unable to save profile photo to server");
    } finally {
      setIsSavingPhoto(false);
    }
  }


  function onPhotoChange(event) {
    const file = event.target.files?.[0];

    if (!file) {
      return;
    }

    if (!file.type.startsWith("image/")) {
      setErrorMessage("Please select a valid image.");
      return;
    }

    if (file.size > 2 * 1024 * 1024) {
      setErrorMessage("Profile photo must be less than 2MB.");
      return;
    }

    setErrorMessage("");
    setSuccessMessage("");
    setSelectedPhoto(file);

    const previewUrl = URL.createObjectURL(file);
    setPhotoPreview(previewUrl);
  }

  function onEditAddress() {
    setSuccessMessage("");
    setErrorMessage("");
    setAddressForm(getAddressFromMember(member));
    setIsEditingAddress(true);
  }

  function onCancelAddress() {
    setAddressForm(getAddressFromMember(member));
    setIsEditingAddress(false);
  }

  function onAddressChange(event) {
    const { name, value } = event.target;
    setAddressForm((prev) => ({ ...prev, [name]: value }));
  }

  function onEditShipping() {
    setSuccessMessage("");
    setErrorMessage("");
    setShippingForm(getShippingAddressFromMember(member));
    setIsEditingShipping(true);
  }

  function onCancelShipping() {
    setShippingForm(getShippingAddressFromMember(member));
    setIsEditingShipping(false);
  }

  function onShippingChange(event) {
    const { name, value } = event.target;
    setShippingForm((prev) => ({ ...prev, [name]: value }));
  }

  function onEditNominee() {
    setSuccessMessage("");
    setErrorMessage("");
    setNomineeForm(getNomineeFromMember(member));
    setIsEditingNominee(true);
  }

  function onCancelNominee() {
    setNomineeForm(getNomineeFromMember(member));
    setIsEditingNominee(false);
  }

  function onNomineeChange(event) {
    const { name, value } = event.target;
    setNomineeForm((prev) => ({ ...prev, [name]: value }));
  }

  async function onSaveAddress() {
    if (!member?.user_id) {
      setErrorMessage("User session not found. Please sign in again.");
      return;
    }

    setIsSaving(true);
    setErrorMessage("");
    setSuccessMessage("");

    try {
      const response = await fetch(`${API_BASE_URL}/member/profile`, {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify({
          user_id: member.user_id,
          address: addressForm.address,
          state: addressForm.state,
          city: addressForm.city,
          district: addressForm.district,
          pin_code: addressForm.pincode,
        }),
      });

      const data = await response.json();

      if (!response.ok) {
        setErrorMessage(data?.message || "Unable to save profile");
        return;
      }

      const updatedMember = data?.member || {
        ...member,
        address: addressForm.address,
        state: addressForm.state,
        city: addressForm.city,
        district: addressForm.district,
        pin_code: addressForm.pincode,
      };

      setMember(updatedMember);
      setAddressForm(getAddressFromMember(updatedMember));
      setShippingForm(getShippingAddressFromMember(updatedMember));
      setNomineeForm(getNomineeFromMember(updatedMember));
      setIsEditingAddress(false);
      setSuccessMessage("Profile updated successfully");
      localStorage.setItem("memberData", JSON.stringify(updatedMember));
    } catch {
      setErrorMessage("Unable to save profile to server");
    } finally {
      setIsSaving(false);
    }
  }

  async function onSaveShipping() {
    if (!member?.user_id) {
      setErrorMessage("User session not found. Please sign in again.");
      return;
    }

    setIsSaving(true);
    setErrorMessage("");
    setSuccessMessage("");

    try {
      const response = await fetch(`${API_BASE_URL}/member/profile`, {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify({
          user_id: member.user_id,
          shipping_address: shippingForm.address,
          shipping_state: shippingForm.state,
          shipping_city: shippingForm.city,
          shipping_district: shippingForm.district,
          shipping_pin_code: shippingForm.pincode,
        }),
      });

      const data = await response.json();

      if (!response.ok) {
        setErrorMessage(data?.message || "Unable to save shipping address");
        return;
      }

      const updatedMember = data?.member || {
        ...member,
        shipping_address: shippingForm.address,
        shipping_state: shippingForm.state,
        shipping_city: shippingForm.city,
        shipping_district: shippingForm.district,
        shipping_pin_code: shippingForm.pincode,
      };

      setMember(updatedMember);
      setAddressForm(getAddressFromMember(updatedMember));
      setShippingForm(getShippingAddressFromMember(updatedMember));
      setNomineeForm(getNomineeFromMember(updatedMember));
      setIsEditingShipping(false);
      setSuccessMessage("Shipping address updated successfully");
      localStorage.setItem("memberData", JSON.stringify(updatedMember));
    } catch {
      setErrorMessage("Unable to save shipping address to server");
    } finally {
      setIsSaving(false);
    }
  }

  async function onSaveNominee() {
    if (!member?.user_id) {
      setErrorMessage("User session not found. Please sign in again.");
      return;
    }

    setIsSaving(true);
    setErrorMessage("");
    setSuccessMessage("");

    try {
      const response = await fetch(`${API_BASE_URL}/member/profile`, {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify({
          user_id: member.user_id,
          nominee_name: nomineeForm.nomineeName,
          nominee_relation: nomineeForm.nomineeRelation,
          nominee_mobile_no: nomineeForm.nomineeMobile,
          nominee_address: nomineeForm.nomineeAddress,
          nominee_state: nomineeForm.nomineeState,
          nominee_city: nomineeForm.nomineeCity,
          nominee_district: nomineeForm.nomineeDistrict,
          nominee_pin_code: nomineeForm.nomineePincode,
        }),
      });

      const data = await response.json();

      if (!response.ok) {
        setErrorMessage(data?.message || "Unable to save nominee details");
        return;
      }

      const updatedMember = data?.member || {
        ...member,
        nominee_name: nomineeForm.nomineeName,
        nominee_relation: nomineeForm.nomineeRelation,
        nominee_mobile_no: nomineeForm.nomineeMobile,
        nominee_address: nomineeForm.nomineeAddress,
        nominee_state: nomineeForm.nomineeState,
        nominee_city: nomineeForm.nomineeCity,
        nominee_district: nomineeForm.nomineeDistrict,
        nominee_pin_code: nomineeForm.nomineePincode,
      };

      setMember(updatedMember);
      setAddressForm(getAddressFromMember(updatedMember));
      setShippingForm(getShippingAddressFromMember(updatedMember));
      setNomineeForm(getNomineeFromMember(updatedMember));
      setIsEditingNominee(false);
      setSuccessMessage("Nominee details updated successfully");
      localStorage.setItem("memberData", JSON.stringify(updatedMember));
    } catch {
      setErrorMessage("Unable to save nominee details to server");
    } finally {
      setIsSaving(false);
    }
  }

  const fullName = (member?.fullname || "").trim();
  const nameParts = fullName ? fullName.split(/\s+/) : [];
  const firstName = nameParts[0] || "-";
  const lastName = nameParts.slice(1).join(" ") || "-";
  const location =
    [member?.state, member?.city].filter(Boolean).join(", ") || "-";

  return (
    <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">
      <Sidebar />

      <div className="flex-1 min-w-0 flex flex-col">
        <Navbar />

        <div className="text-center mt-6">
          <h1 className="text-2xl font-bold text-[#B0422E]">My Profile</h1>
          {isLoading && (
            <p className="text-sm text-gray-500 mt-2">Loading profile...</p>
          )}
          {isSaving && (
            <p className="text-sm text-gray-500 mt-2">Saving profile...</p>
          )}
          {successMessage && (
            <p className="text-sm text-green-600 mt-2">{successMessage}</p>
          )}
          {errorMessage && (
            <p className="text-sm text-red-500 mt-2">{errorMessage}</p>
          )}
        </div>

        <div className="p-6 space-y-6">
          <div className="bg-white rounded-2xl shadow-sm p-6">
            <h2 className="text-blue-700 text-lg font-bold">Main Sponsor</h2>
            <p className="text-gray-500 text-sm mt-1">{location}</p>
          </div>
          <div className="bg-white rounded-2xl shadow-sm p-6">
            <div className="flex justify-between items-center mb-6">
              <h2 className="text-blue-700 text-lg font-bold">
                Profile Photo
              </h2>
            </div>

            <div className="flex flex-col sm:flex-row items-center gap-6">
              <div className="w-28 h-28 rounded-full overflow-hidden bg-gray-100 border">
                {(photoPreview || profilePhoto) ? (
                  <img
                    src={photoPreview || getProfilePhotoUrl(profilePhoto)}
                    alt="Profile"
                    className="w-full h-full object-cover"
                  />
                ) : (
                  <div className="w-full h-full flex items-center justify-center text-gray-400 text-sm">
                    No Photo
                  </div>
                )}
              </div>

              <div className="flex flex-col gap-3">
                <input
                  ref={photoInputRef}
                  type="file"
                  accept="image/jpeg,image/png,image/webp"
                  onChange={onPhotoChange}
                  className="hidden"
                />

                <button
                  type="button"
                  onClick={() => photoInputRef.current?.click()}
                  className="bg-[#B0422E] hover:bg-[#963925] text-white px-5 py-2 rounded-md text-sm"
                >
                  {profilePhoto ? "Change Photo" : "Choose Photo"}
                </button>

                {selectedPhoto && (
                  <button
                    type="button"
                    onClick={onSavePhoto}
                    disabled={isSavingPhoto}
                    className="bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white px-5 py-2 rounded-md text-sm"
                  >
                    {isSavingPhoto ? "Saving..." : "Save Photo"}
                  </button>
                )}

                <p className="text-xs text-gray-500">
                  JPG, PNG or WebP. Maximum 2MB.
                </p>
              </div>
            </div>
          </div>
          <div className="bg-white rounded-2xl shadow-sm p-6">
            <h2 className="text-blue-700 text-lg font-bold mb-6">
              Personal Information
            </h2>

            <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-y-6 gap-x-6">
              <InfoRow label="First Name" value={firstName} />
              <InfoRow label="Last Name" value={lastName} />
              <InfoRow label="Date of Birth" value={formatDate(member?.dob)} />
              <InfoRow label="Email Address" value={member?.email || "-"} />
              <InfoRow
                label="Whatsapp Number"
                value={member?.mobile_no || "-"}
              />
              <InfoRow label="Gender" value={member?.gender || "-"} />
            </div>
          </div>

          <AddressCard
            title="Address"
            isEditing={isEditingAddress}
            form={addressForm}
            fullAddress={composeFullAddressText(addressForm)}
            onEdit={onEditAddress}
            onCancel={onCancelAddress}
            onSave={onSaveAddress}
            onChange={onAddressChange}
          />

          <AddressCard
            title="Shipping Address"
            isEditing={isEditingShipping}
            form={shippingForm}
            fullAddress={composeFullAddressText(shippingForm)}
            onEdit={onEditShipping}
            onCancel={onCancelShipping}
            onSave={onSaveShipping}
            onChange={onShippingChange}
          />

          <NomineeCard
            nominee={nomineeForm}
            isEditing={isEditingNominee}
            onEdit={onEditNominee}
            onCancel={onCancelNominee}
            onSave={onSaveNominee}
            onChange={onNomineeChange}
          />
        </div>
      </div>
    </div>
  );
}

function InfoRow({ label, value }) {
  return (
    <div>
      <p className="text-gray-500 text-sm">{label}</p>
      <p className="font-medium">{value || "-"}</p>
    </div>
  );
}

function AddressCard({
  title,
  isEditing,
  form,
  fullAddress,
  onEdit,
  onCancel,
  onSave,
  onChange,
}) {
  return (
    <div className="bg-white rounded-2xl shadow-sm p-6">
      <div className="flex justify-between items-center mb-6">
        <h2 className="text-blue-700 text-lg font-bold">{title}</h2>

        {!isEditing ? (
          <button
            onClick={onEdit}
            className="bg-[#B0422E] hover:bg-[#963925] text-white px-5 py-2 rounded-md text-sm"
          >
            Edit
          </button>
        ) : (
          <div className="flex gap-2">
            <button
              onClick={onSave}
              className="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm"
            >
              Save
            </button>
            <button
              onClick={onCancel}
              className="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded-md text-sm"
            >
              Cancel
            </button>
          </div>
        )}
      </div>

      {!isEditing ? (
        <div>
          <p className="text-gray-500 text-sm">Full Address</p>
          <p className="font-medium wrap-break-word">{fullAddress}</p>
        </div>
      ) : (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-y-6 gap-x-6">
          <AddressField
            label="Address"
            name="address"
            value={form.address}
            isEditing={isEditing}
            onChange={onChange}
            colSpan="xl:col-span-2"
          />
          <AddressField
            label="State"
            name="state"
            value={form.state}
            isEditing={isEditing}
            onChange={onChange}
          />
          <AddressField
            label="City"
            name="city"
            value={form.city}
            isEditing={isEditing}
            onChange={onChange}
          />
          <AddressField
            label="District"
            name="district"
            value={form.district}
            isEditing={isEditing}
            onChange={onChange}
          />
          <AddressField
            label="Pincode"
            name="pincode"
            value={form.pincode}
            isEditing={isEditing}
            onChange={onChange}
          />
        </div>
      )}
    </div>
  );
}

function AddressField({
  label,
  name,
  value,
  isEditing,
  onChange,
  colSpan = "",
}) {
  return (
    <div className={colSpan}>
      <p className="text-gray-500 text-sm">{label}</p>

      {isEditing ? (
        <input
          name={name}
          value={value}
          onChange={onChange}
          className="w-full border rounded px-3 py-2 mt-1 outline-none focus:ring-2 focus:ring-blue-200"
        />
      ) : (
        <p className="font-medium">{value || "-"}</p>
      )}
    </div>
  );
}

function NomineeCard({
  nominee,
  isEditing,
  onEdit,
  onCancel,
  onSave,
  onChange,
}) {
  const nomineeAddress = composeFullAddressText({
    address: nominee.nomineeAddress,
    state: nominee.nomineeState,
    city: nominee.nomineeCity,
    district: nominee.nomineeDistrict,
    pincode: nominee.nomineePincode,
  });

  return (
    <div className="bg-white rounded-2xl shadow-sm p-6">
      <div className="flex justify-between items-center mb-6">
        <h2 className="text-blue-700 text-lg font-bold">Nominee Details</h2>

        {!isEditing ? (
          <button
            onClick={onEdit}
            className="bg-[#B0422E] hover:bg-[#963925] text-white px-5 py-2 rounded-md text-sm"
          >
            Edit
          </button>
        ) : (
          <div className="flex gap-2">
            <button
              onClick={onSave}
              className="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm"
            >
              Save
            </button>
            <button
              onClick={onCancel}
              className="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded-md text-sm"
            >
              Cancel
            </button>
          </div>
        )}
      </div>

      {!isEditing ? (
        <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-y-6 gap-x-6">
          <InfoRow label="Nominee Name" value={nominee.nomineeName || "-"} />
          <InfoRow label="Relation" value={nominee.nomineeRelation || "-"} />
          <InfoRow label="Mobile Number" value={nominee.nomineeMobile || "-"} />
          <div className="sm:col-span-2 xl:col-span-3">
            <p className="text-gray-500 text-sm">Full Address</p>
            <p className="font-medium wrap-break-word">{nomineeAddress}</p>
          </div>
        </div>
      ) : (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-y-6 gap-x-6">
          <AddressField
            label="Nominee Name"
            name="nomineeName"
            value={nominee.nomineeName}
            isEditing
            onChange={onChange}
            colSpan="xl:col-span-2"
          />
          <AddressField
            label="Relation"
            name="nomineeRelation"
            value={nominee.nomineeRelation}
            isEditing
            onChange={onChange}
          />
          <AddressField
            label="Mobile Number"
            name="nomineeMobile"
            value={nominee.nomineeMobile}
            isEditing
            onChange={onChange}
          />
          <AddressField
            label="Address"
            name="nomineeAddress"
            value={nominee.nomineeAddress}
            isEditing
            onChange={onChange}
            colSpan="xl:col-span-2"
          />
          <AddressField
            label="State"
            name="nomineeState"
            value={nominee.nomineeState}
            isEditing
            onChange={onChange}
          />
          <AddressField
            label="City"
            name="nomineeCity"
            value={nominee.nomineeCity}
            isEditing
            onChange={onChange}
          />
          <AddressField
            label="District"
            name="nomineeDistrict"
            value={nominee.nomineeDistrict}
            isEditing
            onChange={onChange}
          />
          <AddressField
            label="Pincode"
            name="nomineePincode"
            value={nominee.nomineePincode}
            isEditing
            onChange={onChange}
          />
        </div>
      )}
    </div>
  );
}
