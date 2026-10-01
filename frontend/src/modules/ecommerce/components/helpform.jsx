import React, { useState, useRef, useEffect } from 'react'
import { LiaInfoCircleSolid } from "react-icons/lia";
import api from "../api/axios";

function Helpform() {

  const [imagePreview, setImagePreview] = useState(null);
  const [error, setError] = useState('');
  const fileInputRef = useRef(null);

  const [formData, setFormData] = useState({

    mobile_no: '',
    email: '',
    category: '',
    subject: '',
    details: ''
  });

  const [formErrors, setFormErrors] = useState({});


  const MAX_FILE_SIZE = 2 * 1024 * 1024;

  // 🔥 AUTO FILL USER DATA
  useEffect(() => {
    window.scrollTo(0, 0);

    const user = JSON.parse(localStorage.getItem("user"));

    if (user) {
      setFormData(prev => ({
        ...prev,
        mobile_no: user.mobile_no || '',
        email: user.email || ''
      }));
    }
  }, []);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  const handleImageChange = (e) => {
    const file = e.target.files[0];
    setError('');

    if (!file) return;

    if (!file.type.startsWith('image/')) {
      setError('Please upload an image file');
      return;
    }

    if (file.size > MAX_FILE_SIZE) {
      setError('File size must be less than 2MB');
      return;
    }

    const reader = new FileReader();
    reader.onloadend = () => {
      setImagePreview(reader.result);
    };
    reader.readAsDataURL(file);
  };

  const handleFileClick = () => {
    fileInputRef.current?.click();
  };

  // 🔥 SUBMIT
  const handleSubmit = async (e) => {
    e.preventDefault();

    let errors = {};

    if (!formData.category || formData.category === "Select Category") {
      errors.category = "Please select a category";
    }

    if (!formData.subject.trim()) {
      errors.subject = "Subject is required";
    }

    if (!formData.details.trim()) {
      errors.details = "Details are required";
    }

    if (Object.keys(errors).length > 0) {
      setFormErrors(errors);
      return;
    }

    try {
      const user = JSON.parse(localStorage.getItem("user"));

      const form = new FormData();

      form.append("member_id", user.id); // ✅ MAIN
      form.append("category", formData.category);
      form.append("subject", formData.subject);
      form.append("details", formData.details);

      if (fileInputRef.current.files[0]) {
        form.append("image", fileInputRef.current.files[0]);
      }

      await api.post("/help-ticket", form, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });

      alert("We have received your query. We will contact you soon.");

      setFormData(prev => ({
        ...prev,
        category: '',
        subject: '',
        details: ''
      }));

      setImagePreview(null);



    } catch (error) {
      console.log("FULL ERROR:", error);
      console.log("RESPONSE:", error?.response);
      console.log("DATA:", error?.response?.data);

      alert(error?.response?.data?.message || "Something went wrong!");
    }

  };

  // 🔥 FETCH TICKETS


  return (
    <div className="min-h-screen bg-white px-3 sm:px-4 md:px-6 py-4 sm:py-10">
      <div className="max-w-5xl mx-auto">

        <p className="text-xs sm:text-sm text-gray-500 mb-4">
          Home &gt; Help &gt; New Ticket
        </p>

        <h1 className="text-xl sm:text-2xl font-semibold mb-2">How can we help?</h1>
        <p className="text-xs sm:text-sm text-gray-700 font-semibold mb-6 sm:mb-8">
          Need assistance with your order or interested in joining Team 4step?
          Submit a ticket here. Have you looked at our{" "}
          <span className="text-red-500 cursor-pointer">FAQs</span>?
        </p>

        <form className="space-y-4 sm:space-y-6" onSubmit={handleSubmit}>

          {/* ✅ AUTO-FILLED + DISABLED */}
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
            <div>
              <label className="block text-xs sm:text-sm font-medium mb-1">
                Mobile Number<span className="text-red-500">*</span>
              </label>
              <input
                type="text"
                name="mobile"
                value={formData.mobile_no || ''}
                readOnly
                className="w-full border rounded-md px-3 py-2 text-sm bg-gray-100"
              />
            </div>

            <div>
              <label className="block text-xs sm:text-sm font-medium mb-1">
                Email<span className="text-red-500">*</span>
              </label>
              <input
                type="email"
                name="email"
                value={formData.email || ''}
                readOnly
                className="w-full border rounded-md px-3 py-2 text-sm bg-gray-100"
              />
            </div>
          </div>

          <div>
            <label className="block text-xs sm:text-sm font-medium mb-1">
              Category<span className="text-red-500">*</span>
            </label>
            <select
              name="category"
              onChange={handleChange}
              value={formData.category}
              className="w-full border rounded-md px-3 py-2 text-sm"
            >
              <option>Select Category</option>
              <option>Order Issue</option>
              <option>Account Issue</option>
              <option>General Query</option>
            </select>
            {formErrors.category && <p className="text-red-500 text-xs">{formErrors.category}</p>}
          </div>

          <div>
            <label className="block text-xs sm:text-sm font-medium mb-1">
              Subject<span className="text-red-500">*</span>
            </label>
            <input
              type="text"
              name="subject"
              onChange={handleChange}
              value={formData.subject}
              className="w-full border rounded-md px-3 py-2 text-sm"
            />
            {formErrors.subject && <p className="text-red-500 text-xs">{formErrors.subject}</p>}
          </div>

          <div>
            <label className="block text-xs sm:text-sm font-medium mb-1">
              Share details here<span className="text-red-500">*</span>
            </label>
            <textarea
              rows="4"
              name="details"
              onChange={handleChange}
              value={formData.details}
              className="w-full border rounded-md px-3 py-2 resize-none text-sm"
            />
            {formErrors.details && <p className="text-red-500 text-xs">{formErrors.details}</p>}
          </div>

          <div>
            <label className="block text-xs sm:text-sm font-medium mb-2">
              Upload Image (Optional)
            </label>

            <div
              onClick={handleFileClick}
              className="border rounded-md px-3 sm:px-4 py-4 sm:py-6 flex flex-col items-center justify-center cursor-pointer hover:bg-gray-50"
            >
              <span className="text-xs sm:text-sm font-medium text-gray-600 text-center">
                Click to upload or drag and drop
              </span>
              <span className="text-xs text-gray-500 mt-1">PNG, JPG, JPEG (Max 2MB)</span>
              <input
                ref={fileInputRef}
                type="file"
                className="hidden"
                onChange={handleImageChange}
                accept="image/png,image/jpeg,image/jpg"
              />
            </div>

            {error && (
              <div className="mt-2 bg-red-100 text-red-700 text-xs px-3 py-2 rounded text-center">
                {error}
              </div>
            )}

            {imagePreview && (
              <img
                src={imagePreview}
                alt="Preview"
                className="mt-4 max-h-40 sm:max-h-48 object-contain border rounded"
              />
            )}

            <div className="mt-2 bg-yellow-100 text-xs px-3 py-2 rounded text-center">
              <LiaInfoCircleSolid className="inline text-lg text-red-600 mr-2" />
              <span>Upload clear images of Product & Packaging Box in PNG, JPG, JPEG format only</span>
            </div>
          </div>

          <button
            type="submit"
            className="w-full sm:w-auto bg-gray-700 text-white px-6 py-2 rounded-md hover:bg-gray-600 text-sm"
          >
            Submit
          </button>

        </form>



      </div>
    </div>
  );
}

export default Helpform;