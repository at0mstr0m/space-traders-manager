<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <v-container>
    <v-data-table
      :headers="contractColumns"
      :items="contracts"
      item-value="id"
      show-expand
      expand-on-click
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
    </v-data-table>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { VDataTable } from "vuetify/lib/components/index.mjs";
import { useRepository } from "@/repos/repoGenerator.js";

const repo = useRepository("contracts");
const busy = ref(false);
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

async function fetchContracts() {
  busy.value = true;
  try {
    const response = await repo.index();
    contracts.value = response.data.data;
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

async function acceptContract(contract) {
  console.log(JSON.stringify(contract, null, 2));
  // return;
  accepting.value = true;
  try {
    await repo.accept(contract.id);
    accepting.value = false;
    fetchContracts();
  } catch (error) {
    console.error(error);
  }
}

onMounted(() => {
  fetchContracts();
});
</script>
