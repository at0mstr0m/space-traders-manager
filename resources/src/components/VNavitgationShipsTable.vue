<template>
  <v-table
    ref="table"
    v-model="selectedShips"
    :columns="navigationTableColumns"
    repo-name="ships"
    expandable
    show-select
    select-strategy="single"
    @update:model-value="handleShipSelected"
    @items-fetched="handleItemsFetched"
  >
    <template #[`item.fuel_current`]="{ item }">
      <v-fuel-chip
        :ship="item"
        @refueled="updateRow"
      />
    </template>

    <template #[`item.cargo_capacity`]="{ item }">
      {{ item.cargo_units }} / {{ item.cargo_capacity }}
    </template>
    
    <template #expanded-row="{ item, columns }">
      <v-ship-expanded-details
        :ship="item"
        :columns="columns"
        @update:row="updateRow"
      />
    </template>
  </v-table>
</template>

<script setup>
import VTable from "@/components/VTable.vue";
import VShipExpandedDetails from '@/components/VShipExpandedDetails.vue';
import VFuelChip from '@/components/VFuelChip.vue';
import { ref, watch } from 'vue';
import useNavigationStore from "@/store/navigation";
import { storeToRefs } from 'pinia';
import useShipUtils from "@/utils/ships";

const navigationStore = useNavigationStore();
const { navigationTableColumns } = useShipUtils();
const { currentShip } = storeToRefs(navigationStore);

const selectedShips = ref((() => currentShip.value ? [currentShip.value] : [])());
const table = ref(null);

function findShip(id) {
  return table.value.items.find((ship) => ship.id === id);
}

function handleShipSelected(shipId) {
  currentShip.value = shipId.length
    ? findShip(shipId[0])
    : null;
}

function handleItemsFetched() {
  const presentShip = findShip(currentShip.value?.id);
  selectedShips.value = presentShip ? [presentShip.id] : [];
}

function updateRow(updatedShip) {
  table.value.updateItem(updatedShip);
}

watch(currentShip, (newShip) => {
  const presentShip = findShip(newShip?.id);
  selectedShips.value = presentShip ? [presentShip.id] : [];
});

navigationStore.onAction('refresh', ({ args, after }) => {
  const updatedShip = args[0];
  if (updatedShip) {
    after(() => table.value.updateItem(updatedShip));
  }
});

navigationStore.onAction('load', ({ args, after }) => {
  const updatedShip = args[1];
  if (updatedShip) {
    after(() => table.value.updateItem(updatedShip));
  }
});
</script>