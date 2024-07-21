<template>
  <v-data-table
    :items="currentSystemWaypoints"
    density="compact"
    hover
    :headers="columns"
    :items-per-page="0"
  >
    <template #[`item.symbol`]="{ item }">
      <v-waypoint-chip
        :waypoint="item"
        @waypoint-clicked="handleWaypointClicked"
      />
    </template>

    <template #[`item.traits`]="{ item }">
      <v-trait-chip
        v-for="trait in item.traits"
        :key="item.symbol + '_' + trait.id"
        :trait="trait"
      />
    </template>

    <!-- disable footer -->
    <template #bottom />
  </v-data-table>
</template>

<script setup>
import VWaypointChip from '@/components/VWaypointChip.vue';
import VTraitChip from '@/components/VTraitChip.vue';
import { storeToRefs } from 'pinia';
import useNavigationStore from "@/store/navigation";

const emit = defineEmits(['waypoint-clicked']);

const columns = [
  {
    key: "symbol",
    title: "Symbol",
  },
  {
    key: "type",
    title: "Type",
  },
  {
    key: "faction.symbol",
    title: "Faction",
  },
  {
    key: "traits",
    title: "Traits",
  },
  {
    key: "x",
    title: "x",
  },
  {
    key: "y",
    title: "y",
  },
];

const navigationStore = useNavigationStore();
const { currentSystemWaypoints } = storeToRefs(navigationStore);

function handleWaypointClicked(data) {
  emit('waypoint-clicked', data);
}
</script>
