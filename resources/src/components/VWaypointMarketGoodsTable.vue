<template>
  <v-data-table
    v-model:sort-by="sortBy"
    :items="props.marketGoods"
    :headers="columns"
    density="compact"
    hover
    :items-per-page="0"
  >
    <template #top>
      <v-toolbar
        flat
        density="compact"
      >
        <v-toolbar-title>Market</v-toolbar-title>
      </v-toolbar>
    </template>

    <!-- disable footer -->
    <template #bottom />
  </v-data-table>
</template>

<script setup>
import { ref } from "vue";
import { VDataTable } from "vuetify/lib/components/index.mjs";

const columns = [
  {
    title: "Type",
    key: "type",
  },
  {
    title: "Trade Symbol",
    key: "trade_symbol",
  },
];
const sortBy = ref([{ key: 'type', order: 'asc' }]);

const props = defineProps({
  marketGoods: {
    type: Array,
    required: true,
  },
});
</script>
