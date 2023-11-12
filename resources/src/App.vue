<template>
  <router-view />
</template>

<script setup>
import useUserStore from "@/store/user";
import AuthService from "@/services/AuthService";

console.log(import.meta.env.VITE_APP_URL);

const userStore = useUserStore();

async function foo() {
  const user = await userStore.login(
    import.meta.env.VITE_USER_EMAIL,
    "password"
  );
  console.log(user);
  const agent = userStore.getAgent();
  console.log(JSON.stringify(agent));
  // console.log(userStore.isAuthenticated());
  // if (!userStore.isAuthenticated()) {
  //   console.log("not yet authenticated");
  //   const user = await userStore.login(
  //     import.meta.env.VITE_USER_EMAIL,
  //     "password"
  //   );
  //   console.log(user);
  // }
  // userStore.logout();
  // const result = await AuthService.currentUser();
  // console.log(result);
  // userStore.logout();
  // console.log(await AuthService.currentUser());
}

foo();
</script>
