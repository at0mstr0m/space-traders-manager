<template>
  <v-autocomplete
    v-model="selected"
    v-bind="{ ...$attrs, ...$props }"
    :items="items"
    :item-title="props.itemTitle"
    :item-value="props.itemValue"
    :label="props.label"
    no-filter
    hide-selected
    @update:model-value="handleModelUpdated"
    @update:search="updateSearch"
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

    <template #append-item>
      <div
        v-if="page < lastPage"
        v-intersect="{
          handler: fetchItems,
          options: { threshold: [1.0] }
        }"
        class="pa-4 teal--text"
      >
        Loading more items ...
      </div>
    </template>
  </v-autocomplete>
</template>

<script setup>
import { ref, defineModel, onMounted } from 'vue';
import { useRepository } from "@/repos/repoGenerator.js";
import _uniqBy from "lodash/uniqBy";
import _debounce from "lodash/debounce";

const selected = defineModel(
  'selected',
  {
    required: false,
    type: [String, Number, Object, Array, Boolean]
  }
);
const items = ref([]);
const page = ref(0);
const lastPage = ref(1);
const currentItem = ref(null);
const search = ref('');
const debouncer = ref(_debounce(executeSearch, 600));

const props = defineProps({
  itemTitle: {
    type: String,
    required: false,
    default: 'name',
  },
  itemValue: {
    type: String,
    required: false,
    default: 'id',
  },
  label: {
    type: String,
    required: false,
    default: 'Search...',
  },
  repoName: {
    type: String,
    required: true,
  },
  repoMethod: {
    type: String,
    default: 'index',
  },
  additionalParams: {
    type: Object,
    default: () => ({}),
  },
  requestParams: {
    type: Object,
    default: () => ({}),
  },
});

const repo = useRepository(props.repoName);

async function fetchItems() {
  page.value++;
  if (page.value > lastPage.value) {
    return;
  }
  const response = await repo[props.repoMethod]({
    page: page.value,
    perPage: 15,
    params: {
      ...search.value ? { search: search.value } : {},
      ...props.requestParams,
      ...props.additionalParams,
    },
  });
  lastPage.value = response.data.meta.last_page;
  items.value = _uniqBy(items.value.concat(response.data.data), props.itemValue);
}

async function fetchPreviouslySelected() {
  const response = await repo.show(selected.value);
  items.value.push(response.data.data);
}

function handleModelUpdated(value) {
  currentItem.value = items.value.find((item) => item[props.itemValue] === value);
}

function updateSearch(newSearchTerm) {
  search.value = newSearchTerm;
  debouncer.value();
}

function executeSearch() {
  if (search.value === selected.value) {
    return;
  }
  items.value = [];
  page.value = 0;
  lastPage.value = 1;
  fetchItems();
}

function setCurrentItem(element) {
  if (
    element 
    && !items.value.find((item) => item[props.itemValue] === element[props.itemValue])
  ) {
    items.value.push(element);
  }
  currentItem.value = element ?? null;
  selected.value = element ? element[props.itemValue] : null;
}

onMounted(() => {
  if (
    selected.value
    && Number.isInteger(selected.value)
    && items.value.length === 0
    && page.value === 0
    && props.repoName
  ) {
    fetchPreviouslySelected();
  }
});

defineExpose({
  currentItem,
  setCurrentItem
});
</script>
