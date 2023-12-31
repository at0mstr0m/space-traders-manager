<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <v-container>
    <v-data-table
      v-model:expanded="expanded"
      :loading="busy"
      :headers="tableColumns"
      :items="ships"
      :items-per-page="perPage"
      :item-value="uniqueItemId"
      show-expand
      expand-on-click
    >
      <template #top>
        <v-toolbar flat>
          <v-toolbar-title>Ships</v-toolbar-title>
        </v-toolbar>
      </template>
      <template #expanded-row="{ columns, item }">
        <tr>
          <td :colspan="columns.length">
            <v-row>
              <v-col cols="4">
                <v-card class="ma-3" title="Frame" :subtitle="item.frame.name">
                  <template #text>
                    <v-row>
                      <v-col>
                        Module Slots: {{ item.frame.module_slots }}
                      </v-col>
                      <v-col>
                        Mounting Points: {{ item.frame.mounting_points }}
                      </v-col>
                      <v-col>
                        Fuel Capacity: {{ item.frame.fuel_capacity }}
                      </v-col>
                      <v-col>
                        Fuel Capacity: {{ item.frame.required_power }}
                      </v-col>
                      <v-col>
                        Required Crew: {{ item.frame.required_crew }}
                      </v-col>
                    </v-row>
                    <v-card-text class="pa-1 mt-2">
                      {{ item.frame.description }}
                    </v-card-text>
                  </template>
                </v-card>
              </v-col>
              <v-col cols="4">
                <v-card
                  class="ma-3"
                  title="Reactor"
                  :subtitle="item.reactor.name"
                >
                  <template #text>
                    <v-row>
                      <v-col>
                        Required Crew: {{ item.reactor.required_crew }}
                      </v-col>
                      <v-col>
                        Power Output: {{ item.reactor.power_output }}
                      </v-col>
                    </v-row>
                    <v-card-text class="pa-1 mt-2">
                      {{ item.reactor.description }}
                    </v-card-text>
                  </template>
                </v-card>
              </v-col>
              <v-col cols="4">
                <v-card
                  class="ma-3"
                  title="Engine"
                  :subtitle="item.engine.name"
                >
                  <template #text>
                    <v-row>
                      <v-col> Speed: {{ item.engine.speed }} </v-col>
                      <v-col>
                        Required Power: {{ item.engine.required_power }}
                      </v-col>
                      <v-col>
                        Required Crew: {{ item.engine.required_crew }}
                      </v-col>
                    </v-row>
                    <v-card-text class="pa-1 mt-2">
                      {{ item.engine.description }}
                    </v-card-text>
                  </template>
                </v-card>
              </v-col>
            </v-row>
            <v-row>
              <v-col>
                <v-card
                  v-if="item.modules?.length > 0"
                  class="ma-3"
                  title="Modules"
                  :subtitle="'Quantity: ' + item.modules.length"
                >
                  <v-card
                    v-for="module in item.modules"
                    :key="'module_' + module.id"
                    class="ma-3"
                    :title="module.name"
                    :subtitle="'Quantity: ' + module.quantity"
                  >
                    <template #text>
                      <v-row>
                        <v-col v-if="module.capacity">
                          Capacity: {{ module.capacity }}
                        </v-col>
                        <v-col v-if="module.range">
                          Range: {{ module.range }}
                        </v-col>
                        <v-col v-if="module.required_power">
                          Required Power: {{ module.required_power }}
                        </v-col>
                        <v-col>
                          Required Crew: {{ module.required_crew }}
                        </v-col>
                        <v-col>
                          Required Slots: {{ module.required_slots }}
                        </v-col>
                      </v-row>
                      <v-card-text class="pa-1 mt-2">
                        {{ module.description }}
                      </v-card-text>
                    </template>
                  </v-card>
                </v-card>
              </v-col>
              <v-col>
                <v-card
                  v-if="item.mounts?.length > 0"
                  class="ma-3"
                  title="Mounts"
                  :subtitle="'Quantity: ' + item.mounts.length"
                >
                  <v-card
                    v-for="mount in item.mounts"
                    :key="'mount_' + mount.id"
                    class="ma-3"
                    :title="mount.name"
                    :subtitle="'Quantity: ' + mount.quantity"
                  >
                    <template #text>
                      <v-row>
                        <v-col> Strength: {{ mount.strength }} </v-col>
                        <v-col>
                          Required Power: {{ mount.required_power }}
                        </v-col>
                        <v-col>
                          Required Crew: {{ mount.required_crew }}
                        </v-col>
                      </v-row>
                      <v-card-text class="pa-1 mt-2">
                        {{ mount.description }}
                      </v-card-text>
                    </template>
                  </v-card>
                </v-card>
              </v-col>
            </v-row>
            <v-row>
              <v-card
                v-if="item.cargos?.length > 0"
                class="ma-3"
                title="Cargo"
                :subtitle="'Quantity: ' + item.cargos.length"
              >
                <v-card
                  v-for="cargo in item.cargos"
                  :key="'cargo_' + cargo.id"
                  class="ma-3"
                  :title="cargo.name"
                  :subtitle="'Units: ' + cargo.units"
                  :text="cargo.description"
                />
              </v-card>
            </v-row>
          </td>
        </tr>
      </template>

      <!-- eslint-disable-next-line vue/valid-v-slot -->
      <template #item._purchase="{ item }">
        <v-btn
          elevation="1"
          :loading="buying"
          color="primary"
          @click="purchaseShip(item)"
        >
          Buy
          <template v-slot:loader>
            <v-progress-linear indeterminate />
          </template>
        </v-btn>
      </template>

      <template #bottom>
        <v-row align-content="center">
          <v-col>
            <v-pagination
              v-model="page"
              class="w-50"
              :length="totalPages"
              :total-visible="6"
              @update:model-value="getShips"
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

const repo = useRepository("live-data");
const shipRepo = useRepository("ships");

const tableColumns = [
  {
    title: "Type",
    key: "type",
  },
  {
    title: "name",
    key: "name",
  },
  {
    title: "Purchase Price",
    key: "purchasePrice",
  },
  {
    title: "Waypoint Symbol",
    key: "waypointSymbol",
  },
  {
    title: "Purchase",
    key: "_purchase",
  },
  {
    title: "Description",
    key: "description",
  },
];

const expanded = ref([]);
const busy = ref(false);
const buying = ref(false);
const ships = ref([]);
const page = ref(1);
const perPage = ref(15);
const totalItems = ref(0);
const totalPages = ref(0);

async function getShips() {
  busy.value = true;
  try {
    const {
      data: { data, meta },
    } = await repo.purchasableShipsInSystem();
    ships.value = data;
    totalPages.value = meta.last_page;
    totalItems.value = meta.total;
  } catch (error) {
    console.error(error);
  }
  busy.value = false;
}

// needed to only expand the row that was clicked
function uniqueItemId(item) {
  return item.type + item.waypointSymbol;
}

async function purchaseShip(item) {
  buying.value = true;
  try {
    await shipRepo.purchase(item.type, item.waypointSymbol);
    buying.value = false;
    getShips();
  } catch (error) {
    console.error(error);
  }
}

onMounted(() => {
  getShips();
});
</script>
