<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <v-container fluid>
    <v-btn
      elevation="1"
      class="mb-4"
      :loading="refreshing"
      color="primary"
      @click="refetchShips"
    >
      Refresh
      <template #loader>
        <v-progress-linear indeterminate />
      </template>
    </v-btn>

    <v-table
      ref="table"
      title="Ships"
      :columns="tableColumns"
      repo-name="ships"
      expandable
    >
      <template #expanded-row="{ item, columns }">
        <v-ship-expanded-details 
          :ship="item"
          :columns="columns"
          @update-row="updateRow"
        />
      </template>

      <!-- eslint-disable-next-line vue/valid-v-slot -->
      <template #item.crew_morale="{ value }">
        <v-chip :color="getColor(value)">
          {{ value }}
        </v-chip>
      </template>

      <!-- eslint-disable-next-line vue/valid-v-slot -->
      <template #item.fuel_current="{ item, value }">
        <v-chip
          :color="
            item.fuel_capacity
              ? getColor((value / item.fuel_capacity) * 100)
              : 'green'
          "
        >
          {{ value }}
        </v-chip>
      </template>

      <!-- eslint-disable-next-line vue/valid-v-slot -->
      <template #item.frame_condition="{ value }">
        <v-chip :color="getColor(value)">
          {{ decimal(value) }}
        </v-chip>
      </template>

      <!-- eslint-disable-next-line vue/valid-v-slot -->
      <template #item.frame_integrity="{ value }">
        <v-chip :color="getColor(value)">
          {{ decimal(value) }}
        </v-chip>
      </template>

      <!-- eslint-disable-next-line vue/valid-v-slot -->
      <template #item.reactor_condition="{ value }">
        <v-chip :color="getColor(value)">
          {{ decimal(value) }}
        </v-chip>
      </template>

      <!-- eslint-disable-next-line vue/valid-v-slot -->
      <template #item.reactor_integrity="{ value }">
        <v-chip :color="getColor(value)">
          {{ decimal(value) }}
        </v-chip>
      </template>

      <!-- eslint-disable-next-line vue/valid-v-slot -->
      <template #item.engine_condition="{ value }">
        <v-chip :color="getColor(value)">
          {{ decimal(value) }}
        </v-chip>
      </template>

      <!-- eslint-disable-next-line vue/valid-v-slot -->
      <template #item.engine_integrity="{ value }">
        <v-chip :color="getColor(value)">
          {{ decimal(value) }}
        </v-chip>
      </template>
    </v-table>
  </v-container>
</template>

<script setup>
import VTable from "@/components/VTable.vue";
import VShipExpandedDetails from '@/components/VShipExpandedDetails.vue';
import { ref, } from "vue";
import useShipUtils from "@/utils/ships.js";
import useStringify from "@/utils/stringify";

const { tableColumns } = useShipUtils();
const { decimal } = useStringify();

const table = ref(false);
const refreshing = ref(false);

function getColor(number) {
  if (number > 0.9) return "green";
  else if (number > 0.75) return "lime";
  else if (number > 0.5) return "yellow";
  else if (number > 0.25) return "orange";
  else return "red";
}

async function refetchShips() {
  refreshing.value = true;
  table.value.setIsBusy();
  try {
    await table.value.repo.refetch();
  } catch (error) {
    console.error(error);
  }
  refreshing.value = false;
  table.value.setNotBusy();
}

function updateRow(updatedShip) {
  const index = table.value.items.findIndex((ship) => ship.id === updatedShip.id);
  table.value.items.splice(index, 1, updatedShip);
}
</script>
