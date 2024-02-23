<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <v-app>
    <the-nav-bar />
    <the-navigation-drawer />
    <v-main>
      <router-view />
    </v-main>
  </v-app>
</template>

<script setup>
import TheNavBar from "@/components/TheNavBar.vue";
import TheNavigationDrawer from "@/components/TheNavigationDrawer.vue";
</script>
