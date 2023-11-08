<template>
  <router-view />
</template>

<script setup>
import axios from "axios";

console.log(import.meta.env.BASE_URL);

const apiClient = axios.create({
  baseURL: process.env.VUE_APP_API_URL,
  withCredentials: true, // required to handle the CSRF token
});

apiClient.get("/sanctum/csrf-cookie").then((response) => {
  console.log(response);
  apiClient
    .post("/auth/login", {
      email: import.meta.env.VITE_USER_EMAIL,
      password: "password",
    })
    .then((response) => {
      console.log(response);
      apiClient.post("/auth/logout").then((response) => {
        console.log(response);
      });
    });
});
</script>
