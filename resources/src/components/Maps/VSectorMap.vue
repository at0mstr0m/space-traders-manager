<template>
  <v-progress-linear
    v-if="loading"
    color="secondary" 
    :model-value="page ? ((page - 1) / lastPage * 100) : 0"
  />
  <v-scrollable-map
    v-else
    :data="data"
  />
</template>

<script setup>
import VScrollableMap from '@/components/Maps/VScrollableMap.vue';
import { ref, onMounted } from 'vue';
import { useRepository } from "@/repos/repoGenerator.js";
import _cloneDeep from "lodash/cloneDeep";
import useNavigationStore from "@/store/navigation";

const repo = useRepository('systems');
const navigationStore = useNavigationStore();

const data = ref({
  datasets: [
    {
      // todo: change when more sectors become available
      label: 'X1',
      fill: false,
      borderColor: '#f87979',
      backgroundColor: '#f87979',
      hoverRadius: 8,
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
      ...system
    });
  });
  data.value = currentData;
}

async function fetchSystems() {
  page.value++;
  if (page.value > lastPage.value) {
    // loading.value = false;
    return;
  }
  // loading.value = true;
  const { data: { data, meta } } = await repo.index(page.value, 1000);
  lastPage.value = meta.last_page;
  navigationStore.addToAllSystems(data);
  addSystemsToData(data);

  // setTimeout(fetchSystems, 500);
}

onMounted(fetchSystems);
</script>