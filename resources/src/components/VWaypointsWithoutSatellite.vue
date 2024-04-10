<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <v-card
    class="mx-auto"
    variant="tonal"
    color="primary"
    title="Waypoints without satellite"
    min-width="300px"
  >
    <v-list
      :items="items"
      item-title="symbol"
      item-value="id"
    />
  </v-card>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRepository } from "@/repos/repoGenerator.js";

const repo = useRepository("waypoints");
const items = ref([]);

async function fetchWaypointSymbols() {
  const response = await repo.withoutSatellite();
  items.value = response.data.data;
}

onMounted(fetchWaypointSymbols);
</script>
