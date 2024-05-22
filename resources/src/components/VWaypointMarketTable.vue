<template>
  <v-data-table
    v-model:sort-by="sortBy"
    :items="props.tradeOpportunities"
    :headers="marketTableColumns"
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

    <template #[`item.purchase_price`]="{ item }">
      <v-price-chip
        :item="item"
        :waypoint="props.waypoint"
        value-key="purchase_price"
      />
    </template>
      
    <template #[`item.sell_price`]="{ item }">
      <v-price-chip
        :item="item"
        :waypoint="props.waypoint"
        value-key="sell_price"
      />
    </template>

    <!-- disable footer -->
    <template #bottom />
  </v-data-table>
</template>

<script setup>
import VPriceChip from "@/components/VPriceChip.vue";
import useTradeOpportunityUtils from "@/utils/tradeOpportunities";
import { ref } from "vue";
import { VDataTable } from "vuetify/lib/components/index.mjs";

const { marketTableColumns } = useTradeOpportunityUtils();

const sortBy = ref([{ key: 'type', order: 'asc' }]);

const props = defineProps({
  waypoint: {
    type: Object,
    required: true,
  },
  tradeOpportunities: {
    type: Array,
    required: true,
  },
});

</script>