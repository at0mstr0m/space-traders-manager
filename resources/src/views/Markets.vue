<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <v-container fluid>
    <v-btn
      elevation="1"
      class="mb-4"
      :loading="refreshing"
      color="primary"
      @click="refetchTradeOpportunities"
    >
      Refresh
      <template #loader>
        <v-progress-linear indeterminate />
      </template>
    </v-btn>
    <v-table
      ref="table"
      title="Markets"
      :columns="columns"
      repo-name="trade-opportunities"
    >
      <!-- eslint-disable-next-line vue/valid-v-slot -->
      <template #item.supply="{ item, value }">
        <v-chip :color="getSupplyColor(item.type, value)">
          {{ value }}
        </v-chip>
      </template>
    </v-table>
  </v-container>
</template>

<script setup>
import VTable from "@/components/VTable.vue";
import { ref } from "vue";
import SupplyLevels from "@/enums/supplyLevels";
import TradeGoodTypes from "@/enums/tradeGoodTypes";

const columns = [
  {
    title: "Symbol",
    key: "symbol",
  },
  {
    title: "Waypoint Symbol",
    key: "waypoint_symbol",
  },
  {
    title: "Purchase Price",
    key: "purchase_price",
  },
  {
    title: "Sell Price",
    key: "sell_price",
  },
  {
    title: "Type",
    key: "type",
  },
  {
    title: "Trade Volume",
    key: "trade_volume",
  },
  {
    title: "Supply",
    key: "supply",
  },
  {
    title: "Activity",
    key: "activity",
  },
];

const table = ref(null);
const refreshing = ref(false);

function getSupplyColorAscending (supply)  {
  if (supply === SupplyLevels.ABUNDANT) return "green";
  else if (supply === SupplyLevels.HIGH) return "lime";
  else if (supply === SupplyLevels.MODERATE) return "yellow";
  else if (supply === SupplyLevels.LIMITED) return "orange";
  else return "red";
}

function getSupplyColorDescending(supply) {
  if (supply === SupplyLevels.SCARCE) return "green";
  else if (supply === SupplyLevels.LIMITED) return "lime";
  else if (supply === SupplyLevels.MODERATE) return "yellow";
  else if (supply === SupplyLevels.HIGH) return "orange";
  else return "red";
}


function getSupplyColor(type, supply) {
  if (type === TradeGoodTypes.EXPORT) return getSupplyColorAscending(supply);
  else if (type === TradeGoodTypes.IMPORT) return getSupplyColorDescending(supply);
  else return "black";
}

async function refetchTradeOpportunities() {
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
