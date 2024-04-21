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
    <v-scrollable-map
      :data="data"
      height="500"
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

const systemId = ref(null);
const data = ref({ datasets: [] });
const page = ref(0);
const lastPage = ref(1);
const loading = ref(false);
const systemDropdown = ref(null);

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
            x: waypoint.x,
            y: waypoint.y
          }]
      });
    } else {
      currentData.datasets[index].data.push({
        x: waypoint.x,
        y: waypoint.y
      });
    }
  });
  data.value = currentData;
}

async function fetchWaypoints() {
  page.value++;
  if (page.value > lastPage.value) {
    return;
  }
  loading.value = true;
  const response = await repo.waypoints(systemId.value, page.value);
  lastPage.value = response.data.meta.last_page;
  addWaypointsToData(response.data.data);
  fetchWaypoints();
}

watch(
  data, (newValue) => {
    if (newValue) {
      loading.value = false;
    }
  },
  { deep: true }
);

watch(
  systemId, (newValue) => {
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
</script>