import React from "react";
import ReactDOM from "react-dom/client";
import { BrowserRouter } from "react-router-dom";
import App from "./App";
import "./index.css";

const EXTENSION_ASYNC_CHANNEL_ERROR =
  "A listener indicated an asynchronous response by returning true, but the message channel closed before a response was received";

window.addEventListener("unhandledrejection", (event) => {
  const reasonMessage =
    typeof event?.reason === "string"
      ? event.reason
      : event?.reason?.message || "";

  if (reasonMessage.includes(EXTENSION_ASYNC_CHANNEL_ERROR)) {
    event.preventDefault();
  }
});

ReactDOM.createRoot(document.getElementById("root")).render(
  <BrowserRouter>
    <App />
  </BrowserRouter>
);
