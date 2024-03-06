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
    <v-data-table
      v-model:expanded="expanded"
      :loading="busy"
      :headers="tableColumns"
      :items="ships"
      :items-per-page="perPage"
      item-value="id"
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
                <v-card
                  variant="tonal"
                  color="primary"
                  class="ma-3"
                  title="Options"
                >
                  <template #text>
                    <v-update-flight-mode
                      :ship="item"
                      @update="handleFlightModeUpdate"
                    />
                  </template>
                </v-card>
              </v-col>
            </v-row>

            <v-row>
              <v-col cols="4">
                <v-card
                  variant="tonal"
                  color="primary"
                  class="ma-3"
                  title="Frame"
                  :subtitle="item.frame.name"
                >
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
                  variant="tonal"
                  color="primary"
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
                  variant="tonal"
                  color="primary"
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
                  variant="tonal"
                  color="primary"
                  class="ma-3"
                  title="Modules"
                  :subtitle="'Quantity: ' + item.modules.length"
                >
                  <v-card
                    v-for="module in item.modules"
                    variant="tonal"
                    color="primary"
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
                  variant="tonal"
                  color="primary"
                  class="ma-3"
                  title="Mounts"
                  :subtitle="'Quantity: ' + item.mounts.length"
                >
                  <v-card
                    v-for="mount in item.mounts"
                    variant="tonal"
                    color="primary"
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
          {{ value }}
        </v-chip>
      </template>

      <!-- eslint-disable-next-line vue/valid-v-slot -->
      <template #item.reactor_condition="{ value }">
        <v-chip :color="getColor(value)">
          {{ value }}
        </v-chip>
      </template>

      <!-- eslint-disable-next-line vue/valid-v-slot -->
      <template #item.engine_condition="{ value }">
        <v-chip :color="getColor(value)">
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
import useShipUtils from "@/utils/ships.js";
import { useRepository } from "@/repos/repoGenerator.js";
import VUpdateFlightMode from '@/components/VUpdateFlightMode.vue';

const repo = useRepository("ships");
const { tableColumns } = useShipUtils();

const expanded = ref([]);
const busy = ref(false);
const ships = ref([]);
const page = ref(1);
const perPage = ref(15);
const totalItems = ref(0);
const totalPages = ref(0);
const refreshing = ref(false);

async function getShips() {
  busy.value = true;
  try {
    const {
      data: { data, meta },
    } = await repo.index(page.value, perPage.value);
    ships.value = data;
    totalPages.value = meta.last_page;
    totalItems.value = meta.total;
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

async function refetchShips() {
  refreshing.value = true;
  busy.value = true;
  try {
    const response = await repo.refetch();
    ships.value = response.data.data;
  } catch (error) {
    console.error(error);
  }
  refreshing.value = false;
  busy.value = false;
}

function handleFlightModeUpdate(updatedShip) {
  const index = ships.value.findIndex((ship) => ship.id === updatedShip.id);
  ships.value.splice(index, 1, updatedShip);
}

onMounted(getShips);
</script>
