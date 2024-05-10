<template>
  <v-data-table
    v-model:sort-by="sortBy"
    :items="props.tradeOpportunities"
    :headers="marketTableColumns"
    density="compact"
    hover
    :items-per-page="0"
  >
    <!-- :sort-by="{ type: 'type', order: 'asc' }" -->
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

    <!-- eslint-disable-next-line vue/valid-v-slot -->
    <template #item.purchase_price="{ item, value }">
      <v-chip :color="getSupplyColor(item.type, item.supply)">
        {{ value }}
      </v-chip>
    </template>

    <!-- eslint-disable-next-line vue/valid-v-slot -->
    <template #item.sell_price="{ item, value }">
      <v-chip :color="getSupplyColor(item.type, item.supply)">
        {{ value }}
      </v-chip>
    </template>
  </v-data-table>
</template>

<script setup>
import useTradeOpportunityUtils from "@/utils/tradeOpportunities";
import { ref } from "vue";
import { VDataTable } from "vuetify/lib/components/index.mjs";
import { getSupplyColor } from "@enums/supplyLevels";

const { marketTableColumns } = useTradeOpportunityUtils();

const sortBy = ref([{ key: 'type', order: 'asc' }]);

const props = defineProps({
  tradeOpportunities: {
    type: Array,
    required: true,
  },
});
</script>