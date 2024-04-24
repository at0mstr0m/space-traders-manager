<template>
  <v-row>
    <v-dropdown
      ref="systemDropdown"
      v-model:selected="systemId"
      item-title="symbol"
      item-value="id"
      repo-name="systems"
      label="System"
      clearable
    />
  </v-row>
  <v-row>
    <v-progress-linear
      v-if="loading"
      color="secondary" 
      :model-value="page ? ((page - 1) / lastPage * 100) : 0"
    />
    <v-scrollable-map
      v-else
      :data="data"
      height="500"
      @select="handleWaypointsSelected"
    />
  </v-row>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useRepository } from "@/repos/repoGenerator.js";
import _cloneDeep from "lodash/cloneDeep";
import VScrollableMap from '@/components/Maps/VScrollableMap.vue';
import VDropdown from '@/components/VDropdown.vue';
import { getWaypointColor } from '@/enums/waypointTypes';
import { getSystemColor } from '@/enums/systemTypes';

const repo = useRepository('systems');

const emit = defineEmits(['select']);
const systemId = ref(null);
const data = ref({ datasets: [] });
const page = ref(0);
const lastPage = ref(1);
const loading = ref(false);
const systemDropdown = ref(null);

function handleWaypointsSelected(waypoints) {
  emit('select', waypoints.length ? waypoints : null);
}

function addWaypointsToData(waypoints) {
  const currentData = _cloneDeep(data.value);
  waypoints.forEach((waypoint) => {
    const index = currentData.datasets.findIndex((dataset) => dataset.label === waypoint.type);
    if (index === -1) {
      const color = getWaypointColor(waypoint.type);
      currentData.datasets.push({
        label: waypoint.type,
        fill: false,
        borderColor: color,
        backgroundColor: color,
        data: [{
            ...waypoint,
          }]
      });
    } else {
      currentData.datasets[index].data.push({
        ...waypoint,
      });
    }
  });
  data.value = currentData;
}

async function fetchWaypoints() {
  page.value++;
  if (page.value > lastPage.value) {
    loading.value = false;
    return;
  }
  loading.value = true;
  const response = await repo.waypoints(systemId.value, page.value);
  lastPage.value = response.data.meta.last_page;
  addWaypointsToData(response.data.data);
  fetchWaypoints();
}

watch(
  systemId, (newValue) => {
    emit('select', null);
    data.value = { datasets: [] };
    page.value = 0;
    if (newValue) {
      const currentSystem = systemDropdown.value.currentItem;
      const color = getSystemColor(currentSystem.type);
      data.value = {
        datasets: [
          {
            label: 'Center: ' + currentSystem.type,
            fill: false,
            borderColor: color,
            backgroundColor:color,
            data: [
              {
                x: 0,
                y: 0
              }
            ]
          },
        ]
      };
      fetchWaypoints();
    }
  }
);

defineExpose({
  setSystem: (system) => systemDropdown.value.setCurrentItem(system),
});
</script>