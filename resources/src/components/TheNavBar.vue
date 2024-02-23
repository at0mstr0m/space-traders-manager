<template>
  <v-app-bar :elevation="1" dense short>
    <v-app-bar-nav-icon variant="text" @click.stop="toggleNavDrawer" />
  </v-app-bar>
</template>

<script setup>
import useGlobalsStore from "@/store/globals";

const { toggleNavDrawer } = useGlobalsStore();
</script>
