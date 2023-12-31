<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <v-container>
    <v-btn
      elevation="1"
      class="mb-4"
      :loading="refreshing"
      color="primary"
      @click="refetchContracts"
    >
      Refresh
      <template v-slot:loader>
        <v-progress-linear indeterminate />
      </template>
    </v-btn>
    <v-data-table
      v-model:expanded="expanded"
      :loading="busy"
      :headers="contractColumns"
      :items="contracts"
      :items-per-page="perPage"
      item-value="id"
    >
      <template #top>
        <v-toolbar flat>
          <v-toolbar-title>Contracts</v-toolbar-title>
        </v-toolbar>
      </template>

      <template #expanded-row="{ columns, item }">
        <tr>
          <td :colspan="columns.length">
            <v-data-table :headers="deliveryColumns" :items="item.deliveries">
              <template #top>
                <v-toolbar flat>
                  <v-toolbar-title>Deliveries</v-toolbar-title>
                </v-toolbar>
              </template>

              <!-- eslint-disable-next-line vue/valid-v-slot -->
              <template #item.units_fulfilled="{ item, value }">
                <v-chip :color="getColor((value / item.units_required) * 100)">
                  {{ value }}
                </v-chip>
              </template>
              <template #bottom>
                <!-- removes footer -->
              </template>
            </v-data-table>
          </td>
        </tr>
      </template>

      <!-- eslint-disable-next-line vue/valid-v-slot -->
      <template #item.accepted="{ value }">
        <v-checkbox :model-value="value" readonly disabled />
      </template>

      <!-- eslint-disable-next-line vue/valid-v-slot -->
      <template #item.fulfilled="{ value }">
        <v-checkbox :model-value="value" readonly disabled />
      </template>

      <!-- eslint-disable-next-line vue/valid-v-slot -->
      <template #item._accept="{ item }">
        <v-btn
          v-if="!item.accepted"
          elevation="1"
          :loading="accepting"
          color="primary"
          @click="acceptContract(item)"
        >
          Accept
          <template v-slot:loader>
            <v-progress-linear indeterminate />
          </template>
        </v-btn>
      </template>

      <!-- eslint-disable-next-line vue/valid-v-slot -->
      <template #item.deliveries="{ value }">
        {{ value.length }}
      </template>
      <template #bottom>
        <v-row align-content="center">
          <v-col>
            <v-pagination
              v-model="page"
              class="w-50"
              :length="totalPages"
              :total-visible="6"
              @update:model-value="getContracts"
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
import { useRepository } from "@/repos/repoGenerator.js";

const repo = useRepository("contracts");
const expanded = ref([]);
const busy = ref(false);
const page = ref(1);
const perPage = ref(15);
const totalItems = ref(0);
const totalPages = ref(0);
const refreshing = ref(false);
const accepting = ref(false);
const contracts = ref([]);
const contractColumns = ref([
  {
    title: "Identification",
    key: "identification",
  },
  {
    title: "Faction Symbol",
    key: "faction_symbol",
  },
  {
    title: "Type",
    key: "type",
  },
  {
    title: "Accepted",
    key: "accepted",
  },
  {
    title: "Fulfilled",
    key: "fulfilled",
  },
  {
    title: "Accept",
    key: "_accept",
  },
  {
    title: "Deadline",
    key: "deadline",
  },
  {
    title: "Payment on accepted",
    key: "payment_on_accepted",
  },
  {
    title: "Payment on fulfilled",
    key: "payment_on_fulfilled",
  },
  {
    title: "Delivieries",
    key: "deliveries",
  },
]);

const deliveryColumns = ref([
  {
    title: "Trade Symbol",
    key: "trade_symbol",
  },
  {
    title: "Destination Symbol",
    key: "destination_symbol",
  },
  {
    title: "Units required",
    key: "units_required",
  },
  {
    title: "Units fulfilled",
    key: "units_fulfilled",
  },
]);

async function getContracts() {
  busy.value = true;
  try {
    const {
      data: { data, meta },
    } = await repo.index(page.value, perPage.value);
    contracts.value = data;
    totalPages.value = meta.last_page;
    totalItems.value = meta.total;
  } catch (error) {
    console.error(error);
  }
  busy.value = false;
}

async function refetchContracts() {
  refreshing.value = true;
  busy.value = true;
  try {
    const response = await repo.refetch();
    contracts.value = response.data.data;
  } catch (error) {
    console.error(error);
  }
  refreshing.value = false;
  busy.value = false;
}

function getColor(number) {
  if (number > 90) return "green";
  else if (number > 75) return "lime";
  else if (number > 50) return "yellow";
  else if (number > 25) return "orange";
  else return "red";
}

async function acceptContract(contract) {
  accepting.value = true;
  try {
    await repo.accept(contract.id);
    accepting.value = false;
    getContracts();
  } catch (error) {
    console.error(error);
  }
}

onMounted(() => {
  getContracts();
});
</script>
