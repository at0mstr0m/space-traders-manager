<template>
  <v-dialog
    v-model="visible"
    width="500"
  >
    <v-card>
      <v-card-title
        class="headline black"
        primary-title
      >
        {{ task.id ? "Edit" : "Create" }} Task
      </v-card-title>
      <v-card-text class="pa-5">
        <v-form>
          <v-autocomplete
            v-model="task.type"
            label="Task Type"
            :items="taskTypesSelectItems"
            variant="outlined"
            @update:model-value="resetPayload"
          />
          <v-dropdown
            v-if="task.type === taskTypes.COLLECTIVE_MINING || task.type === taskTypes.COLLECTIVE_SIPHONING"
            v-model:selected="task.payload.extraction_location"
            item-title="symbol"
            item-value="symbol"
            repo-name="waypoints"
            label="Extraction Location"
            :additional-params="{ 
              onlyAsteroids: task.type === taskTypes.COLLECTIVE_MINING,
              onlyGasGiants: task.type === taskTypes.COLLECTIVE_SIPHONING,
            }"
          />
          <v-dropdown
            v-if="task.type === taskTypes.SUPPORT_COLLECTIVE_MINERS"
            v-model:selected="task.payload.waiting_location"
            item-title="symbol"
            item-value="symbol"
            repo-name="waypoints"
            label="Waiting Location"
          />
        </v-form>
      </v-card-text>
      <v-card-actions class="pa-5">
        <v-btn
          class="ml-auto"
          outlined
          color="primary"
          :text="task.id ? 'Edit' : 'Create'"
          @click="handleSubmit"
        />
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import VDropdown from '@/components/VDropdown.vue';
import { defineModel, onBeforeMount, watch } from 'vue';
import { useRepository } from "@/repos/repoGenerator.js";
import { taskTypes, taskTypesSelectItems } from "@/enums/taskTypes.js";

const repo = useRepository("tasks");
const visible = defineModel('visible', { required: true, type: [Boolean]});
const task = defineModel('task', {
  required: false,
  type: [Object],
});

const emit = defineEmits(['created', 'updated']);

function resetPayload() {
  task.value.payload = {};
}

async function handleSubmit() {
  const isUpdating = task.value.id;
  try {
    if (isUpdating) {
      await repo.update(task.value.id, task.value);
    } else {
      await repo.store(task.value);
    }
  } catch (error) {
    console.error(error);
    return;
  }

  emit(isUpdating ? 'updated' : 'created');
  visible.value = false;
}

onBeforeMount(() => {
  if (task.value == null) {
    task.value = {
      type: null,
      payload: {},
    };
  }
});

watch(visible, (value) => {
  if (!value) {
    task.value = {
      type: null,
      payload: {},
    };
  }
});
</script>
