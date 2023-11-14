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
        prepend-icon="mdi-home"
        title="Home"
        value="home"
        @click="navigateTo('Home')"
      />
      <v-list-item
        prepend-icon="mdi-rocket-launch-outline"
        title="Ships"
        value="ships"
        @click="navigateTo('Ships')"
      />
      <v-list-item
        prepend-icon="mdi-map-outline"
        title="Map"
        value="map"
        @click="navigateTo('Map')"
      />
      <v-list-item
        prepend-icon="mdi-file-sign"
        title="Contracts"
        value="contracts"
        @click="navigateTo('Contracts')"
      />
    </v-list>
  </v-navigation-drawer>
</template>

<script setup>
import useUserStore from "@/store/user";
import { useRouter } from "vue-router";

const router = useRouter();
const userStore = useUserStore();

const user = userStore.getUser();

function navigateTo(routeName) {
  router.push({ name: routeName });
}
</script>
