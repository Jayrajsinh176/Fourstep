import axios from "axios";

const baseURL =
  window.location.hostname === "localhost"
    ? "http://127.0.0.1:8000/api/"
    : "https://fourstepretail.com/api/";

const api = axios.create({
  baseURL,
});

export default api;