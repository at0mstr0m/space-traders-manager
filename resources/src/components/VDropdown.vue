<template>
  <v-autocomplete
    v-model="selected"
    v-bind="{...$attrs, ...$props}"
    :items="items"
    :item-title="props.itemTitle"
    :item-value="props.itemValue"
    :label="props.label"
    no-filter
    hide-selected
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

const selected = defineModel('selected', { required: false, type: [String, Number, Object, Array, Boolean]});
const items = ref([]);
const page = ref(0);
const lastPage = ref(1);

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
});

const repo = useRepository(props.repoName);

async function fetchItems() {
  page.value++;
  if (page.value > lastPage.value) {
    return;
  }
  const response = await repo.index(page.value, 15);
  lastPage.value = response.data.meta.last_page;
  items.value = _uniqBy(items.value.concat(response.data.data), 'id');
}

async function fetchPreviouslySelected() {
  const response = await repo.show(selected.value);
  items.value.push(response.data.data);
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
</script>
