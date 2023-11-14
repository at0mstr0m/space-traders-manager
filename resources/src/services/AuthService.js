import axios from "axios";
import useUserStore from "@/store/user";
import csrf from "@/services/CSRFService";

const prefix = "/auth";

const api = axios.create({
  baseURL: import.meta.env.VITE_APP_URL + prefix,
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

export default {
  async login(payload) {
    await csrf();
    return api.post("login", payload);
  },
  async currentUser() {
    await csrf();
    // avoid interceptors
    const Axios = axios.create({
      baseURL: import.meta.env.VITE_APP_URL + prefix,
      withCredentials: true, // required to handle the CSRF token
    });
    return Axios.get(import.meta.env.VITE_APP_URL + prefix + "/current-user");
  },
  logout() {
    // avoid interceptors
    const Axios = axios.create({
      baseURL: import.meta.env.VITE_APP_URL + prefix,
      withCredentials: true, // required to handle the CSRF token
    });
    return Axios.post("/logout");
  },
  async forgotPassword(payload) {
    await csrf();
    return api.post("/forgot-password", payload);
  },
  async resetPassword(payload) {
    await csrf();
    return api.post("/reset-password", payload);
  },
  updatePassword(payload) {
    return api.put("/user/password", payload);
  },
  async registerUser(payload) {
    await csrf();
    return api.post("/register", payload);
  },
  sendVerification(payload) {
    return api.post("/email/verification-notification", payload);
  },
  updateUser(payload) {
    return api.put("/user/profile-information", payload);
  },
};
