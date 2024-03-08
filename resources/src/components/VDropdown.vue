<template>
  <v-autocomplete
    v-model="selected"
    :items="items"
    :item-title="props.itemTitle"
    :item-value="props.itemValue"
    :label="props.label"
    no-filter
    hide-selected
  >
    <template #append-item>
      <div
        v-if="page < lastPage"
        v-intersect="{
          handler: fetchItems,
          options: {
            threshold: [1.0]
          }
        }"
        class="pa-4 teal--text"
      >
        Loading more items ...
      </div>
    </template>
  </v-autocomplete>
</template>

<script setup>
import { ref, defineModel } from 'vue';
import { useRepository } from "@/repos/repoGenerator.js";

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
  items.value = items.value.concat(response.data.data);
}
</script>
