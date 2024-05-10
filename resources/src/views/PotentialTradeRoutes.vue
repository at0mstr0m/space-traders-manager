<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <v-container fluid>
    <v-btn
      elevation="1"
      class="mb-4"
      :loading="refreshing"
      color="primary"
      @click="refetchTradeRoutes"
    >
      Refresh
      <template #loader>
        <v-progress-linear indeterminate />
      </template>
    </v-btn>
    <v-table
      ref="table"
      title="Potential Trade Routes"
      :columns="columns"
      repo-name="potential-trade-routes"
    >
      <!-- eslint-disable-next-line vue/valid-v-slot -->
      <template #item.profit="{ value }">
        <v-chip :color="getProfitColor(value)">
          {{ decimal(value) }}
        </v-chip>
      </template>

      <!-- eslint-disable-next-line vue/valid-v-slot -->
      <template #item.supply_at_origin="{ value }">
        <v-chip :color="getSupplyAtOriginColor(value)">
          {{ value }}
        </v-chip>
      </template>

      <!-- eslint-disable-next-line vue/valid-v-slot -->
      <template #item.supply_at_destination="{ value }">
        <v-chip :color="getSupplyAtDestinationColor(value)">
          {{ value }}
        </v-chip>
      </template>

      <!-- eslint-disable-next-line vue/valid-v-slot -->
      <template #item.ship_id="{ item }">
        {{ item.ship?.symbol }}
      </template>
    </v-table>
  </v-container>
</template>

<script setup>
import VTable from "@/components/VTable.vue";
import { ref } from "vue";
import useStringify from "@/utils/stringify";
import SupplyLevels from "@/enums/supplyLevels";

const { decimal } = useStringify();

const columns = [
  {
    title: "Served By",
    key: "ship_id",
  },
  {
    title: "Trade Symbol",
    key: "trade_symbol",
  },
  {
    title: "Origin",
    key: "origin",
  },
  {
    title: "Destination",
    key: "destination",
  },
  {
    title: "Profit",
    key: "profit",
  },
  {
    title: "Profit per Flight",
    key: "profit_per_flight",
  },
  {
    title: "Distance",
    key: "distance",
  },
  {
    title: "Purchase Price",
    key: "purchase_price",
  },
  {
    title: "Supply at Origin",
    key: "supply_at_origin",
  },
  {
    title: "Trade Volume at Origin",
    key: "trade_volume_at_origin",
  },
  {
    title: "Activity at Origin",
    key: "activity_at_origin",
  },
  {
    title: "Sell Price",
    key: "sell_price",
  },
  {
    title: "Supply at Destination",
    key: "supply_at_destination",
  },
  {
    title: "Trade Volume at Destination",
    key: "trade_volume_at_destination",
  },
  {
    title: "Activity at Destination",
    key: "activity_at_destination",
  },
];

const table = ref(null);
const refreshing = ref(false);

function getProfitColor(value) {
  if (parseFloat(value) > 4) return "green";
  else if (parseFloat(value) > 3.5) return "lime";
  else if (parseFloat(value) > 3) return "yellow";
  else if (parseFloat(value) > 2.5) return "orange";
  else return "red";
}

function getSupplyAtOriginColor(supply) {
  if (supply === SupplyLevels.ABUNDANT) return "green";
  else if (supply === SupplyLevels.HIGH) return "lime";
  else if (supply === SupplyLevels.MODERATE) return "yellow";
  else if (supply === SupplyLevels.LIMITED) return "orange";
  else return "red";
}

function getSupplyAtDestinationColor(supply) {
  if (supply === SupplyLevels.SCARCE) return "green";
  else if (supply === SupplyLevels.LIMITED) return "lime";
  else if (supply === SupplyLevels.MODERATE) return "yellow";
  else if (supply === SupplyLevels.HIGH) return "orange";
  else return "red";
}

async function refetchTradeRoutes() {
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
</script>
