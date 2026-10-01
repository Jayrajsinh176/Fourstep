import axios from "axios";

const liveApi = axios.create({
  baseURL: "https://fourstepretail.com/api",
});

export default liveApi;