<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <v-container fluid>
    <iframe
      src="https://docs.spacetraders.io/playground"
      :style="{
        border: '0',
        width: '100%',
        height: dynamicHeight + 'px',
      }"
    />
  </v-container>
</template>

<script setup>
import { ref, onBeforeMount, onBeforeUnmount } from "vue";
const dynamicHeight = ref(calculateDynamicHeight());

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
