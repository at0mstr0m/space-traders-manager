<template>
  <v-data-table-server
    v-model:expanded="expanded"
    v-bind="{...$attrs, ...$props}"
    :loading="busy"
    :headers="columns"
    :items="items"
    :items-length="totalItems"
    :items-per-page="perPage"
    item-value="id"
    :items-per-page-options="props.itemsPerPageOptions"
    :show-expand="props.expandable"
    :expand-on-click="props.expandable"
    @update:options="fetchItems"
  >
    <template
      v-for="(index, name) in $slots"
      #[name]="data"
    >
      <slot
        :name="name"
        v-bind="data"
      />
    </template>

    <template
      v-if="props.title"
      #top
    >
      <v-toolbar flat>
        <v-toolbar-title :text="props.title" />
      </v-toolbar>
    </template>
  </v-data-table-server>
</template>

<script setup>
import { VDataTableServer } from "vuetify/lib/components/index.mjs";
import { ref, computed } from 'vue';
import { useRepository } from "@/repos/repoGenerator.js";
import _first from "lodash/first";

const props = defineProps({
  title: {
    type: String,
    required: false,
    default: '',
  },
  columns: {
    type: Array,
    required: true,
  },
  repoName: {
    type: String,
    required: true,
  },
  expandable: {
    type: Boolean,
    required: false,
    default: false,
  },
  itemsPerPageOptions: {
    type: Array,
    required: false,
    default: () => [15, 25, 50, 100],
  },
  initialPerPage: {
    type: Number,
    required: false,
    default: 15,
  },
});

const emit = defineEmits(['itemsFetched']);

const busy = ref(false);
const items = ref([]);
const perPage = ref(props.initialPerPage);
const totalItems = ref(0);
const page = ref(1);
const totalPages = ref(0);
const repo = computed(() => useRepository(props.repoName));
const expanded = ref([]);
const currentTableOptions = ref({});

async function fetchItems(options) {
  // options === { groupBy, itemsPerPage, page, search, sortBy }
  currentTableOptions.value = options;
  busy.value = true;
  page.value = options.page;
  perPage.value = options.itemsPerPage;
  const sortBy = _first(options.sortBy);

  try {
    const { data: { data, meta } } = await repo.value
      .index(
        page.value,
        perPage.value,
        {
          sortBy: sortBy?.key,
          sortDirection: sortBy?.order,
        }
      );
    items.value = data;
    totalPages.value = meta.last_page;
    totalItems.value = meta.total;
  } catch (error) {
    console.error(error);
  }
  emit('itemsFetched');
  busy.value = false;
}

function setIsBusy() {
  busy.value = true;
}

function setNotBusy() {
  busy.value = false;
}

async function refresh() {
  return fetchItems(currentTableOptions.value);
}

defineExpose({
  setIsBusy,
  setNotBusy,
  repo,
  items,
  refresh,
})
</script>
