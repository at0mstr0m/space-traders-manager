<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <v-container>
    <v-btn
      elevation="1"
      class="mb-4"
      :loading="refreshing"
      color="primary"
      @click="refetchTradeOpportunities"
    >
      Refresh
      <template v-slot:loader>
        <v-progress-linear indeterminate />
      </template>
    </v-btn>
    <v-data-table
      :loading="busy"
      :headers="tableColumns"
      :items="tradeOpportunities"
      :items-per-page="perPage"
      item-value="id"
    >
      <template #top>
        <v-toolbar flat>
          <v-toolbar-title>Market</v-toolbar-title>
        </v-toolbar>
      </template>

      <!-- eslint-disable-next-line vue/valid-v-slot -->
      <template #item.supply="{ item, value }">
        <v-chip :color="getSupplyColor(item.type, value)">
          {{ value }}
        </v-chip>
      </template>

      <template #bottom>
        <v-row align-content="center">
          <v-col>
            <v-pagination
              v-model="page"
              class="w-50"
              :length="totalPages"
              :total-visible="6"
              @update:model-value="getTradeRoutes"
            />
          </v-col>
          <!-- todo: implement -->
          <!-- <v-col class="w-25">
            <v-select
              v-model="perPage"
              class="mt-3 w-25"
              :items="[15, 25, 50, 100]"
              label="Items per page"
              variant="outlined"
              density="compact"
              @update:model-value="getShips"
            />
          </v-col> -->
        </v-row>
      </template>
    </v-data-table>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { VDataTable } from "vuetify/lib/components/index.mjs";
import Supplies from "@/enums/supplies";
import TradeGoodTypes from "@/enums/tradeGoodTypes";
import { useRepository } from "@/repos/repoGenerator.js";

const repo = useRepository("trade-opportunities");

const tableColumns = [
  {
    title: "Symbol",
    key: "symbol",
  },
  {
    title: "Waypoint Symbol",
    key: "waypoint_symbol",
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

const busy = ref(false);
const tradeOpportunities = ref([]);
const page = ref(1);
const perPage = ref(15);
const totalItems = ref(0);
const totalPages = ref(0);
const refreshing = ref(false);

async function getTradeRoutes() {
  busy.value = true;
  try {
    const {
      data: { data, meta },
    } = await repo.index(page.value, perPage.value);
    tradeOpportunities.value = data;
    totalPages.value = meta.last_page;
    totalItems.value = meta.total;
  } catch (error) {
    console.error(error);
  }
  busy.value = false;
}

function getSupplyColorAscending (supply)  {
  if (supply === Supplies.ABUNDANT) return "green";
  else if (supply === Supplies.HIGH) return "lime";
  else if (supply === Supplies.MODERATE) return "yellow";
  else if (supply === Supplies.LIMITED) return "orange";
  else return "red";
}

function getSupplyColorDescending(supply) {
  if (supply === Supplies.SCARCE) return "green";
  else if (supply === Supplies.LIMITED) return "lime";
  else if (supply === Supplies.MODERATE) return "yellow";
  else if (supply === Supplies.HIGH) return "orange";
  else return "red";
}


function getSupplyColor(type, supply) {
  if (type === TradeGoodTypes.EXPORT) return getSupplyColorAscending(supply);
  else if (type === TradeGoodTypes.IMPORT) return getSupplyColorDescending(supply);
  else return "black";
}

async function refetchTradeOpportunities() {
  refreshing.value = true;
  busy.value = true;
  try {
    const response = await repo.refetch();
    tradeOpportunities.value = response.data.data;
  } catch (error) {
    console.error(error);
  }
  refreshing.value = false;
  busy.value = false;
}

onMounted(() => {
  getTradeRoutes();
});
</script>
