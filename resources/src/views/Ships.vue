<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <v-container>
    <v-data-table
      v-model:expanded="expanded"
      :headers="columns"
      :items="ships"
      item-value="name"
      show-expand
    >
      <template #top>
        <v-toolbar flat>
          <v-toolbar-title>Ships</v-toolbar-title>
        </v-toolbar>
      </template>
      <template #expanded-row="{ columns, item }">
        <tr>
          <td :colspan="columns.length">More info about {{ item.name }}</td>
        </tr>
      </template>
      <template #item.crew_morale="{ value }">
        <v-chip :color="getColor(value)">
          {{ value }}
        </v-chip>
      </template>
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
      <template #item.frame_condition="{ value }">
        <v-chip :color="getColor(value)">
          {{ value }}
        </v-chip>
      </template>
      <template #item.reactor_condition="{ value }">
        <v-chip :color="getColor(value)">
          {{ value }}
        </v-chip>
      </template>
      <template #item.engine_condition="{ value }">
        <v-chip :color="getColor(value)">
          {{ value }}
        </v-chip>
      </template>
    </v-data-table>
  </v-container>
</template>

<script setup>
import { ref } from "vue";
import { VDataTable } from "vuetify/lib/components/index.mjs";
import api from "@/services/API.js";

const expanded = ref([]);
const busy = ref(false);
const ships = ref([]);
const columns = ref([
  {
    title: "Symbol",
    key: "symbol",
    width: "160px",
  },
  {
    title: "Role",
    key: "role",
  },
  {
    title: "Waypoint Symbol",
    key: "waypoint_symbol",
  },
  {
    title: "Status",
    key: "status",
  },
  {
    title: "Flight Mode",
    key: "flight_mode",
  },
  {
    title: "Crew Current",
    key: "crew_current",
  },
  {
    title: "Crew Capacity",
    key: "crew_capacity",
  },
  {
    title: "Crew Required",
    key: "crew_required",
  },
  {
    title: "Crew Rotation",
    key: "crew_rotation",
  },
  {
    title: "Crew Morale",
    key: "crew_morale",
  },
  {
    title: "Crew Wages",
    key: "crew_wages",
  },
  {
    title: "Fuel Current",
    key: "fuel_current",
  },
  {
    title: "Fuel Capacity",
    key: "fuel_capacity",
  },
  {
    title: "Fuel Consumed",
    key: "fuel_consumed",
  },
  {
    title: "Cooldown",
    key: "cooldown",
  },
  {
    title: "Frame Condition",
    key: "frame_condition",
  },
  {
    title: "Reactor Condition",
    key: "reactor_condition",
  },
  {
    title: "Engine Condition",
    key: "engine_condition",
  },
  {
    title: "Cargo Capacity",
    key: "cargo_capacity",
  },
  {
    title: "Cargo Units",
    key: "cargo_units",
  },
]);

async function getShips() {
  busy.value = true;
  try {
    const response = await api.get("/ships");
    ships.value = response.data.data;
  } catch (error) {
    console.error(error);
  }
  busy.value = false;
}

function getColor(number) {
  if (number > 90) return "green";
  else if (number > 75) return "lime";
  else if (number > 50) return "yellow";
  else if (number > 25) return "orange";
  else return "red";
}

getShips();
</script>
