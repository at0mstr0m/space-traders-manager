<template>
  <v-data-table
    v-model="selectedShips"
    v-model:expanded="expanded"
    :items="props.ships"
    :headers="waypointTableColumns"
    density="compact"
    :items-per-page="0"
    hover
    expandable
    show-expand
    expand-on-click
    show-select
    select-strategy="single"
    @update:model-value="handleShipSelected"
  >
    <!-- :sort-by="{ type: 'type', order: 'asc' }" -->
    <template #top>
      <v-toolbar
        flat
        density="compact"
      >
        <v-toolbar-title>Ships</v-toolbar-title>
      </v-toolbar>
    </template>

    <!-- disable footer -->
    <template #bottom />

    <!-- eslint-disable-next-line vue/valid-v-slot -->
    <template #item.fuel_current="{ item }">
      {{ item.fuel_current }} / {{ item.fuel_capacity }}
    </template>

    <!-- eslint-disable-next-line vue/valid-v-slot -->
    <template #item.cargo_capacity="{ item }">
      {{ item.cargo_units }} / {{ item.cargo_capacity }}
    </template>

    <template #expanded-row="{ item, columns }">
      <v-ship-expanded-details
        :ship="item"
        :columns="columns"
        @update:row="emits('update:row')"
      />
    </template>
  </v-data-table>
</template>

<script setup>
import VShipExpandedDetails from '@/components/VShipExpandedDetails.vue';
import { VDataTable } from "vuetify/lib/components/index.mjs";
import { ref, watch } from 'vue';
import useNavigationStore from "@/store/navigation";
import { storeToRefs } from 'pinia';
import useShipUtils from "@/utils/ships";

const { waypointTableColumns } = useShipUtils();
const navigationStore = useNavigationStore();
const { currentShip } = storeToRefs(navigationStore);

const expanded = ref([]);
const selectedShips = ref((() => currentShip.value ? [currentShip.value] : [])());

const props = defineProps({
  ships: {
    type: Array,
    required: true,
  },
});

const emits = defineEmits(['update:row']);

function findShip(id) {
  return props.ships.find((ship) => ship.id === id);
}

function handleShipSelected(shipId) {
  currentShip.value = shipId.length
    ? findShip(shipId[0])
    : null;
}

watch(currentShip, (newShip) => {
  if (!findShip(newShip?.id)) {
    selectedShips.value = [];
  }
});

</script>