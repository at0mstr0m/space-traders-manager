import { defineStore } from "pinia";
import { ref } from "vue";

const useGlobalsStore = defineStore("globals", () => {
  const _theme = ref(window?.THEME ?? "dark");
  const navDrawerIsOpen = ref(false);

  function getTheme() {
    return _theme.value;
  }

  function setTheme(theme) {
    _theme.value = theme;
  }

  function openNavDrawer() {
    navDrawerIsOpen.value = true;
  }

  function closeNavDrawer() {
    navDrawerIsOpen.value = false;
  }

  function toggleNavDrawer() {
    navDrawerIsOpen.value = !navDrawerIsOpen.value;
  }

  return {
    getTheme,
    setTheme,
    navDrawerIsOpen,
    openNavDrawer,
    closeNavDrawer,
    toggleNavDrawer,
  };
});

export default useGlobalsStore;
