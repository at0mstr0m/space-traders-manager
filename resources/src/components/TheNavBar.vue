<template>
  <v-app-bar
    :elevation="1"
    dense
    short
  >
    <v-app-bar-nav-icon
      variant="text"
      @click.stop="toggleNavDrawer"
    />
    <v-spacer />
    <v-btn
      icon
      @click="toggleMap"
    >
      <v-icon icon="mdi-map-outline" />
    </v-btn>
    <v-spacer />
  </v-app-bar>

  <v-dialog
    v-model="mapVisible"
    fullscreen
    transition="dialog-top-transition"
  >
    <v-toolbar dark>
      <v-btn
        icon
        dark
        @click="toggleMap"
      >
        <v-icon icon="mdi-close" />
      </v-btn>
      <v-spacer />
      <v-spacer />
      <v-toolbar-title text="Map" />
      <v-spacer />
    </v-toolbar>
    <v-card>
      <iframe
        src="https://docs.spacetraders.io/playground"
        :style="{
          border: '0',
          width: '100%',
          height: dynamicHeight + 'px',
        }"
      />
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, onBeforeMount, onBeforeUnmount } from "vue";
import useGlobalsStore from "@/store/globals";

const { toggleNavDrawer } = useGlobalsStore();
const mapVisible = ref(false);
const dynamicHeight = ref(calculateDynamicHeight());

function toggleMap() {
  mapVisible.value = !mapVisible.value;
}

function calculateDynamicHeight() {
  return Math.floor(window.innerHeight * 0.97);
}

function onResize() {
  dynamicHeight.value = calculateDynamicHeight();
}

onBeforeMount(() => {
  window.addEventListener("resize", onResize);
});

onBeforeUnmount(() => {
  window.removeEventListener("resize", onResize);
});
</script>
