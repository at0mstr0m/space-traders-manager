<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <v-container>
    <v-btn
      elevation="1"
      class="mb-4"
      :loading="refreshing"
      color="primary"
      @click="refetchTradeRoutes"
    >
      Refresh
      <template v-slot:loader>
        <v-progress-linear indeterminate />
      </template>
    </v-btn>
    <v-data-table
      :loading="busy"
      :headers="tableColumns"
      :items="tradeRoutes"
      :items-per-page="perPage"
      item-value="id"
    >
      <template #top>
        <v-toolbar flat>
          <v-toolbar-title>Potential Trade Routes</v-toolbar-title>
        </v-toolbar>
      </template>

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
import useStringify from "@/utils/stringify";
import Supplies from "@/enums/supplies";
import { useRepository } from "@/repos/repoGenerator.js";

const { decimal } = useStringify();
const repo = useRepository("potential-trade-routes");

const tableColumns = [
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

const busy = ref(false);
const tradeRoutes = ref([]);
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
    tradeRoutes.value = data;
    totalPages.value = meta.last_page;
    totalItems.value = meta.total;
  } catch (error) {
    console.error(error);
  }
  busy.value = false;
}

function getProfitColor(value) {
  if (parseFloat(value) > 4) return "green";
  else if (parseFloat(value) > 3.5) return "lime";
  else if (parseFloat(value) > 3) return "yellow";
  else if (parseFloat(value) > 2.5) return "orange";
  else return "red";
}

function getSupplyAtOriginColor(supply) {
  if (supply === Supplies.ABUNDANT) return "green";
  else if (supply === Supplies.HIGH) return "lime";
  else if (supply === Supplies.MODERATE) return "yellow";
  else if (supply === Supplies.LIMITED) return "orange";
  else return "red";
}

function getSupplyAtDestinationColor(supply) {
  if (supply === Supplies.SCARCE) return "green";
  else if (supply === Supplies.LIMITED) return "lime";
  else if (supply === Supplies.MODERATE) return "yellow";
  else if (supply === Supplies.HIGH) return "orange";
  else return "red";
}

async function refetchTradeRoutes() {
  refreshing.value = true;
  busy.value = true;
  try {
    const response = await repo.refetch();
    tradeRoutes.value = response.data.data;
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
