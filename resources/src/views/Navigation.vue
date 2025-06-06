<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <v-container
    fluid
    class="justify-center align-center"
  >
    <v-row no-gutters>
      <v-col>
        <v-card
          ref="sectorMapCard"
          class="pa-2 ma-1"
          :height="width * 6 / 7"
        >
          <v-sector-map
            @select="handleSystemSelected"
          />
        </v-card>
      </v-col>
      <v-col>
        <v-card
          class="pa-2 ma-1"
          :height="width * 6 / 7"
        >
          <v-system-map
            ref="systemMap"
            :height="width * 6 / 7"
            @select="handleWaypointsSelected"
          />
        </v-card>
      </v-col>

      <v-responsive width="100%" />

      <v-card
        width="100%"
        class="ma-1"
      >
        <v-tabs v-model="currentTab">
          <v-tab value="ships">
            <v-icon
              icon="mdi-rocket-launch-outline"
              class="mr-2"
            />
            Ships
          </v-tab>
          <v-tab value="waypoints">
            <v-icon
              icon="mdi-earth"
              class="mr-2"
            />
            Waypoints
            <v-icon 
              icon="mdi-refresh"
              class="ml-2"
              @click.stop.prevent="refetchWaypoints"
            />
          </v-tab>
          <v-tab value="waypoint-details">
            <v-icon
              icon="mdi-earth"
              class="mr-2"
            />
            Waypoint Details
          </v-tab>
        </v-tabs>

        <v-card-text>
          <v-tabs-window v-model="currentTab">
            <v-tabs-window-item value="ships">
              <v-navigation-ships-table
                @waypoint-clicked="handleWaypointClicked"
              />
            </v-tabs-window-item>

            <v-tabs-window-item value="waypoints">
              <v-system-waypoints-table
                @waypoint-clicked="handleWaypointClicked"
              />
            </v-tabs-window-item>

            <v-tabs-window-item value="waypoint-details">
              <v-waypoint-card
                v-for="waypoint in currentWaypoints"
                :key="'waypoint_' + waypoint.id"
                :waypoint="waypoint"
                @connected-jump-gate-clicked="handleWaypointClicked"
              />
            </v-tabs-window-item>
          </v-tabs-window>
        </v-card-text>
      </v-card>
    </v-row>
  </v-container>
</template>

<script setup>
import VSectorMap from '@/components/Maps/VSectorMap.vue';
import VSystemMap from '@/components/Maps/VSystemMap.vue';
import VWaypointCard from '@/components/VWaypointCard.vue';
import VSystemWaypointsTable from '@/components/VSystemWaypointsTable.vue';
import VNavigationShipsTable from '@/components/VNavigationShipsTable.vue';
import { onBeforeMount, onMounted, ref } from 'vue';
import { useElementSize } from '@vueuse/core'
import { storeToRefs } from 'pinia';
import useNavigationStore from "@/store/navigation";
import useUserStore from "@/store/user";

const navigationStore = useNavigationStore();
const userStore = useUserStore();
const { currentTab } = storeToRefs(navigationStore);

const systemMap = ref(null);
const sectorMapCard = ref(null);
const { width } = useElementSize(sectorMapCard);

const currentWaypoints = ref([]);

function handleSystemSelected(systems) {
  if (!systems.length) {
    return;
  }
  systemMap.value.setSystem(systems[0]);
}

function handleWaypointsSelected(waypoints) {
  currentWaypoints.value = waypoints;
  currentTab.value = waypoints ? 'waypoint-details' : 'ships';
}

function handleWaypointClicked(data) {
  systemMap.value.setWaypoint(data);
}

function refetchWaypoints() {
  systemMap.value.refetchWaypoints();
}

onBeforeMount(navigationStore.hardReset);

// set starting system as default system
onMounted(() => systemMap.value.setSystem(userStore.getStartingSystem()));
</script>
