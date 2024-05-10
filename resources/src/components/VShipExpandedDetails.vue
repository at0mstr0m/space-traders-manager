<template>
  <tr>
    <td :colspan="props.columns.length">
      <v-row>
        <v-col cols="4">
          <v-card
            variant="tonal"
            color="primary"
            class="ma-3"
            title="Task"
          >
            <template #text>
              <v-update-ship-task
                :ship="ship"
                @update="updateRow"
              />
            </template>
          </v-card>
        </v-col>

        <v-col cols="4">
          <v-card
            variant="tonal"
            color="primary"
            class="ma-3"
            title="Flight Mode"
          >
            <template #text>
              <v-update-flight-mode
                :ship="ship"
                @update="updateRow"
              />
            </template>
          </v-card>
        </v-col>
      </v-row>

      <v-row>
        <v-card
          v-if="ship.cargos?.length > 0"
          class="ma-3"
          title="Cargo"
          :subtitle="'Quantity: ' + ship.cargos.length"
        >
          <v-card
            v-for="cargo in ship.cargos"
            :key="'cargo_' + cargo.id"
            class="ma-3"
            :title="cargo.name"
            :subtitle="'Units: ' + cargo.units"
            :text="cargo.description"
          />
        </v-card>
      </v-row>

      <v-row>
        <v-col cols="4">
          <v-card
            variant="tonal"
            color="primary"
            class="ma-3"
            title="Frame"
            :subtitle="ship.frame.name"
          >
            <template #text>
              <v-row>
                <v-col>
                  Module Slots: {{ ship.frame.module_slots }}
                </v-col>
                <v-col>
                  Mounting Points: {{ ship.frame.mounting_points }}
                </v-col>
                <v-col>
                  Fuel Capacity: {{ ship.frame.fuel_capacity }}
                </v-col>
                <v-col>
                  Fuel Capacity: {{ ship.frame.required_power }}
                </v-col>
                <v-col>
                  Required Crew: {{ ship.frame.required_crew }}
                </v-col>
              </v-row>
              <v-card-text class="pa-1 mt-2">
                {{ ship.frame.description }}
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
            :subtitle="ship.reactor.name"
          >
            <template #text>
              <v-row>
                <v-col>
                  Required Crew: {{ ship.reactor.required_crew }}
                </v-col>
                <v-col>
                  Power Output: {{ ship.reactor.power_output }}
                </v-col>
              </v-row>
              <v-card-text class="pa-1 mt-2">
                {{ ship.reactor.description }}
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
            :subtitle="ship.engine.name"
          >
            <template #text>
              <v-row>
                <v-col> Speed: {{ ship.engine.speed }} </v-col>
                <v-col>
                  Required Power: {{ ship.engine.required_power }}
                </v-col>
                <v-col>
                  Required Crew: {{ ship.engine.required_crew }}
                </v-col>
              </v-row>
              <v-card-text class="pa-1 mt-2">
                {{ ship.engine.description }}
              </v-card-text>
            </template>
          </v-card>
        </v-col>
      </v-row>
      <v-row>
        <v-col>
          <v-card
            v-if="ship.modules?.length > 0"
            variant="tonal"
            color="primary"
            class="ma-3"
            title="Modules"
            :subtitle="'Quantity: ' + ship.modules.length"
          >
            <v-card
              v-for="module in ship.modules"
              :key="'module_' + module.id"
              variant="tonal"
              color="primary"
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
            v-if="ship.mounts?.length > 0"
            variant="tonal"
            color="primary"
            class="ma-3"
            title="Mounts"
            :subtitle="'Quantity: ' + ship.mounts.length"
          >
            <v-card
              v-for="mount in ship.mounts"
              :key="'mount_' + mount.id"
              variant="tonal"
              color="primary"
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
    </td>
  </tr>
</template>

<script setup>
import VUpdateFlightMode from '@/components/VUpdateFlightMode.vue';
import VUpdateShipTask from '@/components/VUpdateShipTask.vue';
import { toRef } from 'vue';

const props = defineProps({
  ship: {
    type: Object,
    required: true,
  },
  columns: {
    type: Array,
    required: true,
  },
});

const emit = defineEmits(['update:row']);

const ship = toRef(props, 'ship');

function updateRow(updatedShip) {
  emit('update:row', updatedShip);
}
</script>