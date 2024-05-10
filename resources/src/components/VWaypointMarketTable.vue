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

    <template #[`item.purchase_price`]="{ item }">
      <v-price-chip 
        :item="item"
        value-key="purchase_price"
        :ship="currentShip"
      />
    </template>

    <template #[`item.sell_price`]="{ item }">
      <v-price-chip 
        :item="item"
        value-key="sell_price"
        :ship="currentShip"
      />
    </template>
  </v-data-table>
</template>

<script setup>
import VPriceChip from "@/components/VPriceChip.vue";
import useTradeOpportunityUtils from "@/utils/tradeOpportunities";
import { ref } from "vue";
import { VDataTable } from "vuetify/lib/components/index.mjs";

const { marketTableColumns } = useTradeOpportunityUtils();

const sortBy = ref([{ key: 'type', order: 'asc' }]);
const currentShip = ref(null);

const props = defineProps({
  tradeOpportunities: {
    type: Array,
    required: true,
  },
});

function setCurrentShip(ship) {
  currentShip.value = ship;
}

defineExpose({ setCurrentShip });
</script>