import axios from "axios";

const api = axios.create({
  baseURL: import.meta.env.VITE_APP_URL,
});

export default function csrf() {
  return api.get("/sanctum/csrf-cookie");
}
