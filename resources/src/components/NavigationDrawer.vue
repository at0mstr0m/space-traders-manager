<template>
  <v-navigation-drawer expand-on-hover rail>
    <v-list>
      <v-list-item
        prepend-avatar="https://spacetraders.io/logo/logo-over-black.svg"
        :title="user.name"
        :subtitle="user.email"
      ></v-list-item>
    </v-list>
    <v-divider />
    <v-list density="compact" nav>
      <v-list-item
        prepend-icon="mdi-rocket-launch-outline"
        title="Ships"
        value="ships"
      />
    </v-list>
  </v-navigation-drawer>

  <v-main style="height: 250px"></v-main>
</template>

<script setup>
import useUserStore from "@/store/user";

const userStore = useUserStore();
const user = userStore.getUser();
</script>
