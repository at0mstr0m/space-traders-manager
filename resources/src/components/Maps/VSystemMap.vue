<template>
  <v-col>
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
    <!-- <v-row class="fill-height"> -->
    <v-row class="d-flex flex-column fill-height">
      <!-- <v-row> -->
      <v-progress-linear
        v-if="loading"
        color="secondary"
        :model-value="page ? ((page - 1) / lastPage * 100) : 0"
      />
      <v-scrollable-map
        v-else
        :data="data"
        :height="mapHeight"
        @select="handleWaypointsSelected"
      />
    </v-row>
  </v-col>
</template>

<script setup>
import VScrollableMap from '@/components/Maps/VScrollableMap.vue';
import VDropdown from '@/components/VDropdown.vue';
import { computed, ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import { useRepository } from "@/repos/repoGenerator.js";
import _cloneDeep from "lodash/cloneDeep";
import { useElementSize } from '@vueuse/core'
import { getWaypointColor } from '@/enums/waypointTypes';
import { getSystemColor } from '@/enums/systemTypes';
import useNavigationStore from "@/store/navigation";

const repo = useRepository('systems');
const navigationStore = useNavigationStore();
const { currentSystem } = storeToRefs(navigationStore);

const emit = defineEmits(['select']);

const props = defineProps({
  height: {
    type: Number,
    required: true,
  },
});

const systemId = ref(null);
const data = ref({ datasets: [] });
const page = ref(0);
const lastPage = ref(1);
const loading = ref(false);
const systemDropdown = ref(null);
const preselectedWaypoint = ref(null);

const { height: systemDropdownHeight } = useElementSize(systemDropdown);

const mapHeight = computed(() => props.height - (systemDropdownHeight.value * 1.2));

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

function getWaypointsAtLocation({ x, y, symbol }) {
  return data.value.datasets
    .flatMap((dataset) => dataset.data)
    .filter(
      (waypointData) => waypointData.x === x
        && waypointData.y === y
        && waypointData.symbol !== symbol
    );
}

function getWaypointBySymbol(symbol) {
  return data.value.datasets
    .flatMap((dataset) => dataset.data)
    .find((waypoint) => waypoint.symbol === symbol);
}

function handleShipWaypointSelection(waypointSymbol) {
  const waypoint = getWaypointBySymbol(waypointSymbol);
  if (waypoint) {
    // also other waypoints with similar coordinates, but selected waypoint first
    handleWaypointsSelected([
      waypoint,
      ...getWaypointsAtLocation(waypoint)
    ]);
  }
}

async function fetchWaypoints() {
  page.value++;
  if (page.value > lastPage.value) {
    loading.value = false;
    if (preselectedWaypoint.value) {
      handleShipWaypointSelection(preselectedWaypoint.value)
      preselectedWaypoint.value = null;
    }
    return;
  }
  loading.value = true;
  const response = await repo.waypoints(systemId.value, page.value);
  lastPage.value = response.data.meta.last_page;
  addWaypointsToData(response.data.data);
  fetchWaypoints();
}

function setSystem(system) {
  systemDropdown.value.setCurrentItem(system);
}

function setWaypoint(data) {
  if (systemId.value !== data.system.id) {
    setSystem(data.system);
    preselectedWaypoint.value = data.waypoint_symbol;
    return;
  }
  handleShipWaypointSelection(data.waypoint_symbol);
}

watch(systemId, (newValue) => {
  emit('select', null);
  data.value = { datasets: [] };
  page.value = 0;
  currentSystem.value = systemDropdown.value.currentItem || null;
  if (newValue) {
    const color = getSystemColor(currentSystem.value.type);
    data.value = {
      datasets: [
        {
          label: 'Center: ' + currentSystem.value.type,
          fill: false,
          borderColor: color,
          backgroundColor: color,
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
});

defineExpose({
  setSystem,
  setWaypoint
});
</script>