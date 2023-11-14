<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <v-app>
    <the-navigation-drawer />
    <router-view />
  </v-app>
</template>

<script setup>
import TheNavigationDrawer from "@/components/TheNavigationDrawer.vue";
</script>
