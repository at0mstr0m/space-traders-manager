<template>
  <v-row>
    <v-col>
      <v-autocomplete
        v-model="selected"
        label="Flight Modes"
        :items="flightModes"
        variant="outlined"
      />
    </v-col>
  </v-row>
  <v-row>
    <v-col>
      <v-btn
        elevation="1"
        class="mb-4"
        :loading="sending"
        color="primary"
        @click="updateFlightMode"
      >
        Update Flight Mode
        <template #loader>
          <v-progress-linear indeterminate />
        </template>
      </v-btn>
    </v-col>
  </v-row>
</template>

<script setup>
import { ref, computed, defineProps } from 'vue';
import useShipUtils from "@/utils/ships.js";
import { useRepository } from "@/repos/repoGenerator.js";

const { flightModes } = useShipUtils();
const repo = useRepository("ships");

const props = defineProps({
  ship: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(['update']);

const shipId = computed(() => props.ship.id);
const sending = ref(false);
const selected = ref(props.ship.flight_mode);

async function updateFlightMode() {
  sending.value = true;
  console.log('Updating flight mode to: ', selected.value);

  let response;
  try {
    response = await repo.updateFlightMode(shipId.value, selected.value);
  } catch (error) {
    console.error(error);
    sending.value = false;
    return;
  }
  emit('update', response.data.data);
  sending.value = false;
}

</script>
