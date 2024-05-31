<template>
  <router-view />
</template>

<script setup>
import { useRouter, useRoute } from "vue-router";
import useUserStore from "@/store/user";

// console.log(window.USER);

const userStore = useUserStore();
const router = useRouter();
const route = useRoute();

// console.log(userStore.isAuthenticated());

if (!userStore.isAuthenticated() && route.name !== "Login") {
  router.push({ name: "login" });
} else if (userStore.isAuthenticated() && route.name === "Login") {
  router.push({ name: "home" });
}

// async function foo() {
//   const user = await userStore.login(
//     import.meta.env.VITE_USER_EMAIL,
//     "password"
//   );
//   console.log(user);
//   const agent = userStore.getAgent();
//   console.log(JSON.stringify(agent));

//   // console.log(userStore.isAuthenticated());
//   // if (!userStore.isAuthenticated()) {
//   //   console.log("not yet authenticated");
//   //   const user = await userStore.login(
//   //     import.meta.env.VITE_USER_EMAIL,
//   //     "password"
//   //   );
//   //   console.log(user);
//   // }
//   // userStore.logout();
//   // const result = await AuthService.currentUser();
//   // console.log(result);
//   // userStore.logout();
//   // console.log(await AuthService.currentUser());
// }

// foo();
</script>
