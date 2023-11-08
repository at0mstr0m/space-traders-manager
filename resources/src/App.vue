<template>
  <router-view />
</template>

<script setup>
import axios from "axios";

const apiClient = axios.create({
  baseURL: process.env.VUE_APP_API_URL,
  withCredentials: true, // required to handle the CSRF token
});

apiClient.get("/sanctum/csrf-cookie").then((response) => {
  console.log(response);
  apiClient
    .post("/login", {
      email: process.env.VUE_APP_API_URL,
      password: "password",
    })
    .then((response) => {
      console.log(response);
      apiClient.post("/logout").then((response) => {
        console.log(response);
      });
    });
});
</script>
