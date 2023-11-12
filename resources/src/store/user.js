import { defineStore } from "pinia";
import { ref } from "vue";
import AuthService from "@/services/AuthService";

const useUserStore = defineStore("user", () => {
  const _user = ref(null);

  function getUser() {
    return _user.value;
  }

  async function init() {
    try {
      const response = await AuthService.currentUser();
      _user.value = response.data.data;
    } catch (error) {
      console.error(error);
      _user.value = null;
    }
    return getUser();
  }

  async function login(email, password) {
    const response = await AuthService.login({
      email: email,
      password: password,
    });
    _user.value = response.data.data;
    return getUser();
  }

  async function logout() {
    return await AuthService.logout();
  }

  function isAuthenticated() {
    return !!_user.value;
  }

  function getAgent() {
    return _user.value?.agent;
  }

  return {
    init,
    getUser,
    login,
    logout,
    isAuthenticated,
    getAgent,
  };
});

export default useUserStore;
