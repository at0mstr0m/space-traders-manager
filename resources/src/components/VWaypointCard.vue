<template>
  <v-card outlined>
    <template #title>
      <v-chip
        color="white"
        variant="flat"
        class="mr-2"
      >
        <v-icon 
          icon="mdi-refresh"
          @click="refresh"
        />
      </v-chip>
      {{ props.waypoint.symbol }}
      <v-chip
        :color="getWaypointColor(props.waypoint.type)"
        variant="flat"
        class="ml-2"
      >
        {{ props.waypoint.type }}
      </v-chip>
      
      <v-chip
        v-if="props.waypoint.type === waypointTypes.JUMP_GATE"
        :color="waypoint.is_under_construction ? 'red' : 'green'"
        variant="flat"
        class="ml-2"
      >
        {{ waypoint.is_under_construction ? 'Under Construction' : 'Operational' }}
      </v-chip>
      
      <v-navigate-here-chip 
        v-if="currentShip && currentShip.waypoint_symbol !== props.waypoint.symbol"
        :waypoint="props.waypoint"
      />
    </template>

    <template #subtitle>
      <v-chip
        v-for="trait in props.waypoint.traits"
        :key="props.waypoint.id + '_' + trait.id"
        variant="tonal"
        class="mr-1 mt-2"
      >
        <v-tooltip
          activator="parent"
          location="top"
          max-width="300"
        >
          {{ trait.description }}
        </v-tooltip>
        {{ trait.name }}
      </v-chip>
    </template>

    <template #text>
      <v-waypoint-market-table
        v-if="tradeOpportunities[props.waypoint.id]?.length"
        :waypoint="props.waypoint"
        :trade-opportunities="tradeOpportunities[props.waypoint.id]"
      />

      <div v-if="ships[props.waypoint.id]?.length">
        <v-divider class="mb-2" />
        <v-waypoint-ships-table
          :ships="ships[props.waypoint.id]"
          @update:row="navigationStore.fetchShips(props.waypoint)"
        />
      </div>
     
      <div v-if="props.waypoint.type === waypointTypes.JUMP_GATE">
        <v-divider class="mb-2" />
        <v-waypoint-connections-table
          :waypoint="props.waypoint"
          :connected-waypoints="props.waypoint.connected_waypoints"
          @connected-jump-gate-clicked="emit('connected-jump-gate-clicked', $event)"
        />
      </div>
    </template>
  </v-card>
</template>

<script setup>
import VNavigateHereChip from '@/components/VNavigateHereChip.vue';
import VWaypointMarketTable from '@/components/VWaypointMarketTable.vue';
import VWaypointShipsTable from '@/components/VWaypointShipsTable.vue';
import VWaypointConnectionsTable from '@/components/VWaypointConnectionsTable.vue';
import waypointTypes, { getWaypointColor } from '@enums/waypointTypes';
import { onMounted } from 'vue';
import useNavigationStore from "@/store/navigation";
import { storeToRefs } from 'pinia';

const emit = defineEmits(['connected-jump-gate-clicked']);

const props = defineProps({
  waypoint: {
    type: Object,
    required: true,
  },
});

const navigationStore = useNavigationStore();
const { 
  tradeOpportunities,
  ships,
  currentShip
} = storeToRefs(navigationStore);

function refresh() {
  navigationStore.load(props.waypoint);
}

onMounted(refresh);
</script>