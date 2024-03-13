<template>
  <v-row>
    <v-col>
      <v-dropdown
        v-model:selected="selected"
        repo-name="tasks"
        label="Select Task"
        variant="outlined"
        item-title="type"
        clearable
      >
        <template #item="{ item, props: _props }">
          <v-list-item
            v-bind="_props"
            :title="item.raw.type + ' ' + JSON.stringify(item.raw.payload)"
          />
        </template>

        <template #selection="{ item }">
          {{ item.raw.type + ' ' + JSON.stringify(item.raw.payload) }}
        </template>
      </v-dropdown>
    </v-col>
  </v-row>
  <v-row>
    <v-col>
      <v-btn
        elevation="1"
        :loading="sending"
        color="primary"
        @click="updateShipTask"
      >
        Update Task
        <template #loader>
          <v-progress-linear indeterminate />
        </template>
      </v-btn>
    </v-col>
  </v-row>
</template>

<script setup>
import VDropdown from '@/components/VDropdown.vue';
import { ref, computed } from 'vue';
import { useRepository } from "@/repos/repoGenerator.js";

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
const selected = ref(props.ship?.task?.id);

async function updateShipTask() {
  sending.value = true;

  let response;
  try {
    response = await repo.updateTask(shipId.value, selected.value);
  } catch (error) {
    console.error(error);
    sending.value = false;
    return;
  }
  emit('update', response.data.data);
  sending.value = false;
}
</script>
