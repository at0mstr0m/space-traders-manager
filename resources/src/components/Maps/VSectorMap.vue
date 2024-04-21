<template>
  <v-scrollable-map
    v-if="!loading"
    :data="data"
  />
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRepository } from "@/repos/repoGenerator.js";
import _cloneDeep from "lodash/cloneDeep";
import VScrollableMap from '@/components/Maps/VScrollableMap.vue';

const repo = useRepository('systems');

const data = ref({
  datasets: [
    {
      // todo: change when more sectors become available
      label: 'X1',
      fill: false,
      borderColor: '#f87979',
      backgroundColor: '#f87979',
      data: []
    },
  ]
});
const page = ref(0);
const lastPage = ref(1);
const loading = ref(false);

function addSystemsToData(systems) {
  const currentData = _cloneDeep(data.value);
  systems.forEach((system) => {
    currentData.datasets[0].data.push({
      x: system.x,
      y: system.y
    });
  });
  data.value = currentData;
}

async function fetchSystems() {
  page.value++;
  if (page.value > lastPage.value) {
    return;
  }
  loading.value = true;
  const response = await repo.index(page.value, 1000);
  lastPage.value = response.data.meta.last_page;
  addSystemsToData(response.data.data);
  setTimeout(fetchSystems, 500);
}

watch(
  data, (newValue) => {
    if (newValue) {
      loading.value = false;
    }
  },
  { deep: true }
);

onMounted(fetchSystems);
</script>