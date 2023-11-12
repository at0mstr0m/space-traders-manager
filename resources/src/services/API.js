import axios from "axios";
import useUserStore from "@/store/user";

const api = axios.create({
  baseURL: import.meta.env.VITE_APP_URL + "/api",
  withCredentials: true, // required to handle the CSRF token
});

api.interceptors.response.use(
  (response) => {
    return response;
  },
  function (error) {
    const userStore = useUserStore();
    if (
      error.response &&
      [401, 419].includes(error.response.status) &&
      userStore.getUser()
    ) {
      userStore.logout();
    }
    return Promise.reject(error);
  }
);

export default api;
