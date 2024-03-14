<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <v-container fluid>
    <v-row align-content="center">
      <v-col>
        <v-btn
          elevation="1"
          class="mb-4 mr-4"
          :loading="refreshing"
          color="primary"
          @click="getTasks"
        >
          Refresh
          <template #loader>
            <v-progress-linear indeterminate />
          </template>
        </v-btn>

        <v-btn
          elevation="1"
          class="mb-4 mr-4"
          :loading="refreshing"
          text="Create"
          color="primary"
          @click="toggleModal"
        />

        <v-btn
          elevation="1"
          class="mb-4"
          text="Trigger Tasks"
          color="primary"
          @click="triggerTasks"
        />
      </v-col>
    </v-row>

    <v-data-table
      :loading="busy"
      :headers="columns"
      :items="tasks"
      :items-per-page="perPage"
      item-value="id"
    >
      <template #top>
        <v-toolbar flat>
          <v-toolbar-title>Tasks</v-toolbar-title>
        </v-toolbar>
      </template>

      <template #bottom>
        <v-row align-content="center">
          <v-col>
            <v-pagination
              v-model="page"
              class="w-50"
              :length="totalPages"
              :total-visible="6"
              @update:model-value="getTasks"
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

      <!-- eslint-disable-next-line vue/valid-v-slot -->
      <template #item._actions="{ item }">
        <v-table-column-actions
          :item="item"
          @edit="editTask"
          @delete="deleteTask"
        />
      </template>
    </v-data-table>
  </v-container>

  <v-task-modal
    v-model:visible="showModal"
    v-model:task="currentTask"
    @created="getTasks"
    @updated="getTasks"
  />
</template>

<script setup>
import VTableColumnActions from "@/components/VTableColumnActions.vue";
import VTaskModal from "@/components/VTaskModal.vue";
import { ref, onMounted } from "vue";
import { VDataTable } from "vuetify/lib/components/index.mjs";
import { useRepository } from "@/repos/repoGenerator.js";

const repo = useRepository("tasks");
const busy = ref(false);
const page = ref(1);
const perPage = ref(15);
const totalItems = ref(0);
const totalPages = ref(0);
const refreshing = ref(false);
const tasks = ref([]);
const currentTask = ref(null);
const columns = ref([
  {
    title: "Type",
    key: "type",
  },
  {
    title: "Payload",
    key: "payload",
  },
  {
    title: "Actions",
    key: "_actions",
  },
]);
const showModal = ref(false);

async function getTasks() {
  busy.value = true;
  try {
    const {
      data: { data, meta },
    } = await repo.index(page.value, perPage.value);
    tasks.value = data;
    totalPages.value = meta.last_page;
    totalItems.value = meta.total;
  } catch (error) {
    console.error(error);
  }
  busy.value = false;
}

function toggleModal() {
  showModal.value = !showModal.value;
}

function editTask(item) {
  currentTask.value = item;
  toggleModal();
}

async function deleteTask(item) {
  await repo.delete(item.id);
  getTasks();
}

async function triggerTasks() {
  await repo.triggerAll();
}

onMounted(getTasks);
</script>
