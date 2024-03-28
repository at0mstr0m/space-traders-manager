<template>
  <!-- permanent -->
  <v-navigation-drawer
    ref="drawer"
    :rail="!navDrawerIsOpen"
    :expand-on-hover="!navDrawerIsOpen"
    :permanent="navDrawerIsOpen"
  >
    <v-list>
      <v-list-item
        prepend-avatar="https://spacetraders.io/logo/logo-over-black.svg"
        :title="user.agent.symbol"
        :subtitle="user.email"
      >
        $ {{ integer(user.agent.credits) }}
      </v-list-item>
    </v-list>
    <v-divider />
    <v-list
      density="compact"
      nav
    >
      <v-list-item
        prepend-icon="mdi-home"
        title="Home"
        value="home"
        @click="navigateTo('home')"
      />
      <v-list-item
        prepend-icon="mdi-map-outline"
        title="Map"
        value="map"
        @click="navigateTo('map')"
      />
      <v-list-item
        prepend-icon="mdi-rocket-launch-outline"
        title="Ships"
        value="ships"
        @click="navigateTo('ships')"
      />
      <v-list-item
        prepend-icon="mdi mdi-account-hard-hat-outline"
        title="Tasks"
        value="Tasks"
        @click="navigateTo('tasks')"
      />
      <v-list-item
        prepend-icon="mdi-file-sign"
        title="Contracts"
        value="contracts"
        @click="navigateTo('contracts')"
      />
      <v-list-item
        prepend-icon="mdi-map-marker-distance"
        title="Potential Trade Routes"
        value="Potential Trade Routes"
        @click="navigateTo('potential-trade-routes')"
      />
      <v-list-item
        prepend-icon="mdi mdi-cash-multiple"
        title="Purchasable Ships"
        value="Purchasable Ships"
        @click="navigateTo('purchasable-ships')"
      />
      <v-list-item
        prepend-icon="mdi mdi-storefront-outline"
        title="Markets"
        value="Markets"
        @click="navigateTo('markets')"
      />
      <v-list-item
        prepend-icon="mdi mdi-swap-vertical-bold"
        title="Transactions"
        value="Transactions"
        @click="navigateTo('transactions')"
      />
    </v-list>
  </v-navigation-drawer>
</template>

<script setup>
import useUserStore from "@/store/user";
import useGlobalsStore from "@/store/globals";
import { useRouter } from "vue-router";
import { storeToRefs } from "pinia";
import useStringify from "@/utils/stringify";

const router = useRouter();
const userStore = useUserStore();
const globalsStore = useGlobalsStore();
const { navDrawerIsOpen } = storeToRefs(globalsStore);
const { integer } = useStringify();

const user = userStore.getUser();

function navigateTo(routeName) {
  globalsStore.closeNavDrawer();
  router.push({ name: routeName });
}
</script>
