<template>
  <v-card outlined>
    <template #title>
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
    </template>

    <template #subtitle>
      <v-chip
        v-for="trait in props.waypoint.traits"
        :key="props.waypoint.id + '_' + trait.id"
        variant="outlined"
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
        v-if="tradeOpportunities.length"
        :trade-opportunities="tradeOpportunities"
      />
      <div v-if="ships.length">
        <v-divider class="mb-2" />
        <v-waypoint-ships-table 
          :ships="ships"
          @update="fetchShips"
        />
      </div>
    </template>
  </v-card>
</template>

<script setup>
import VWaypointMarketTable from '@/components/VWaypointMarketTable.vue';
import VWaypointShipsTable from '@/components/VWaypointShipsTable.vue';
import waypointTypes, { getWaypointColor } from '@enums/waypointTypes';
import waypointTraitSymbols from '@enums/waypointTraitSymbols';
import { ref, onMounted } from 'vue';
import { useRepository } from "@/repos/repoGenerator.js";

const repo = useRepository("waypoints");

const props = defineProps({
  waypoint: {
    type: Object,
    required: true,
  },
});

const tradeOpportunities = ref([]);
const ships = ref([]);

async function fetchTradeOpportunities() {
  const isMarketplace = props.waypoint.traits.some(
    (trait) => trait.symbol === waypointTraitSymbols.MARKETPLACE
  );
  if (!isMarketplace) return;
  const response = await repo.market(props.waypoint.id);
  tradeOpportunities.value = response.data.data;
}

async function fetchShips() {
  if (!props.waypoint.id) return;
  const response = await repo.ships(props.waypoint.id);
  ships.value = response.data.data;
}

onMounted(() => {
  fetchTradeOpportunities();
  fetchShips();
});
</script>