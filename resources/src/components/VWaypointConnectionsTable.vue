<template>
  <v-data-table
    :items="props.connectedWaypoints"
    :headers="headers"
    density="compact"
    hover
    :items-per-page="0"
  >
    <template #top>
      <v-toolbar
        flat
        density="compact"
      >
        <v-toolbar-title>Connected Jump Gates</v-toolbar-title>
      </v-toolbar>
    </template>

    <template #[`item.symbol`]="{ item }">
      <v-hover>
        <template #default="{ isHovering, props: hoverProps }">
          <v-chip
            v-bind="hoverProps"
            :variant="isHovering ? 'flat' : 'outlined'"
            color="white"
            @click.stop.prevent="handleClick(item)"
          >
            {{ item.symbol }}
          </v-chip>
        </template>
      </v-hover>
      <v-navigate-here-chip
        v-if="currentShip && currentShip.waypoint_symbol === props.waypoint.symbol"
        :waypoint="item"
        jumpgate
      />
    </template>

    <template #[`item.is_under_construction`]="{ item }">
      <v-checkbox
        v-model="item.is_under_construction"
        disabled
        hide-details
      />
    </template>

    <!-- disable footer -->
    <template #bottom />
  </v-data-table>
</template>

<script setup>
import VNavigateHereChip from '@/components/VNavigateHereChip.vue';
import useNavigationStore from "@/store/navigation";
import { storeToRefs } from 'pinia';

const emit = defineEmits(['connected-jump-gate-clicked']);

const props = defineProps({
  waypoint: {
    type: Object,
    required: true,
  },
  connectedWaypoints: {
    type: Array,
    required: true,
  },
});

const navigationStore = useNavigationStore();
const { currentShip } = storeToRefs(navigationStore);

const headers = [
  {
    title: 'Waypoint Symbol',
    key: 'symbol'
  },
  {
    title: 'Is Under Construction',
    key: 'is_under_construction'
  },
];

function handleClick(waypoint) {
  emit('connected-jump-gate-clicked', {
    system: navigationStore.getSystemFromWaypointSymbol(waypoint.symbol),
    waypoint_symbol: waypoint.symbol,
  });
}
</script>