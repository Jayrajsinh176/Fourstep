import { useCallback, useState } from "react";

export function ToastContainer({ toasts, removeToast }) {
  return (
    <div className="fixed top-5 right-5 z-50 flex flex-col gap-3 w-80">
      {toasts.map((toast) => (
        <div
          key={toast.id}
          className={`flex items-start gap-3 px-4 py-3 rounded-xl shadow-md text-sm font-medium border transition-all
            ${toast.type === "success" ? "bg-green-50 border-green-200 text-green-800" : ""}
            ${toast.type === "error" ? "bg-red-50 border-red-200 text-red-800" : ""}
            ${toast.type === "warning" ? "bg-yellow-50 border-yellow-200 text-yellow-800" : ""}
            ${toast.type === "info" ? "bg-blue-50 border-blue-200 text-blue-800" : ""}
          `}
        >
          <span className="text-lg leading-none mt-0.5">
            {toast.type === "success" && "✅"}
            {toast.type === "error" && "❌"}
            {toast.type === "warning" && "⚠️"}
            {toast.type === "info" && "ℹ️"}
          </span>
          <span className="flex-1 leading-snug">{toast.message}</span>
          <button
            onClick={() => removeToast(toast.id)}
            className="text-gray-400 hover:text-gray-600 leading-none text-base"
          >
            ✕
          </button>
        </div>
      ))}
    </div>
  );
}

export function useToast() {
  const [toasts, setToasts] = useState([]);

  const showToast = useCallback((type, message) => {
    const id = Date.now();
    setToasts((prev) => [...prev, { id, type, message }]);
    setTimeout(() => {
      setToasts((prev) => prev.filter((t) => t.id !== id));
    }, 4000);
  }, []);

  const removeToast = useCallback((id) => {
    setToasts((prev) => prev.filter((t) => t.id !== id));
  }, []);

  return { toasts, showToast, removeToast };
}
