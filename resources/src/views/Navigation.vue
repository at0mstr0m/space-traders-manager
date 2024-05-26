<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <v-container
    fluid
    class="justify-center align-center"
  >
    <v-row no-gutters>
      <v-col>
        <v-sheet class="pa-2 ma-1">
          <v-sector-map 
            height="600"
            @select="handleSystemSelected"
          />
        </v-sheet>
      </v-col>
      <v-col>
        <v-sheet
          class="pa-5 ma-1"
          height="600"
        >
          <v-system-map
            ref="systemMap"
            @select="handleWaypointsSelected"
          />
        </v-sheet>
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
          </v-tab>
        </v-tabs>

        <v-card-text>
          <v-tabs-window v-model="currentTab">
            <v-tabs-window-item value="ships">
              <v-navitgation-ships-table />
            </v-tabs-window-item>

            <v-tabs-window-item value="waypoints">
              <v-waypoint-card
                v-for="waypoint in currentWaypoints"
                :key="'waypoint_' + waypoint.id"
                :waypoint="waypoint"
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
import VNavitgationShipsTable from '@/components/VNavitgationShipsTable.vue';
import { ref } from 'vue';
import useNavigationStore from "@/store/navigation";
import { storeToRefs } from 'pinia';

const navigationStore = useNavigationStore();
const { currentTab } = storeToRefs(navigationStore);

const systemMap = ref(null);
const currentWaypoints = ref([]);

function handleSystemSelected(system) {
  if (!system.length) {
    return;
  }
  systemMap.value.setSystem(system[0]);
}

function handleWaypointsSelected(waypoints) {
  currentWaypoints.value = waypoints;
  currentTab.value = waypoints ? 'waypoints' : 'ships';
}
</script>