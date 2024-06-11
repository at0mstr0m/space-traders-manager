import { defineStore } from "pinia";
import { ref } from "vue";
import AuthService from "@/services/AuthService";

const useUserStore = defineStore("user", () => {
  const _user = ref(window?.USER ?? null);

  function getUser() {
    return _user.value;
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

  function getStartingSystem() {
    return getAgent().starting_system;
  }

  return {
    getUser,
    login,
    logout,
    isAuthenticated,
    getAgent,
    getStartingSystem,
  };
});

export default useUserStore;
