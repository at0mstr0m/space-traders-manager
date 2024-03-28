<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <v-container fluid>
    <v-btn
      elevation="1"
      class="mb-4"
      :loading="refreshing"
      color="primary"
      @click="table?.refresh"
    >
      Refresh
      <template #loader>
        <v-progress-linear indeterminate />
      </template>
    </v-btn>
    <v-table
      ref="table"
      title="Transactions"
      :columns="columns"
      repo-name="transactions"
      :initial-per-page="100"
    >
      <!-- eslint-disable-next-line vue/valid-v-slot -->
      <template #item.type="{ item, value }">
        <v-chip :color="getTypeColor(item.type, value)">
          {{ value }}
        </v-chip>
      </template>
    </v-table>
  </v-container>
</template>

<script setup>
import VTable from "@/components/VTable.vue";
import { ref } from "vue";

const columns = [
  {
    title: "Agent",
    key: "agent_symbol",
  },
  {
    title: "Ship",
    key: "ship_symbol",
  },
  {
    title: "Waypoint",
    key: "waypoint_symbol",
  },
  {
    title: "Timestamp",
    key: "timestamp",
  },
  {
    title: "Type",
    key: "type",
  },
  {
    title: "Trade Symbol",
    key: "trade_symbol",
  },
  {
    title: "Units",
    key: "units",
  },
  {
    title: "Price Per Unit",
    key: "price_per_unit",
  },
  {
    title: "Total Price",
    key: "total_price",
  },
];

const table = ref(null);

function getTypeColor(type) {
  switch (type) {
    case "PURCHASE":
      return "orange";
    case "SELL":
      return "green";
    default:
      return "blue";
  }
}
</script>
