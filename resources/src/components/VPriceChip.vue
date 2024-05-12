<template>
  <v-hover>
    <template #default="{ isHovering, props: hoverProps }">
      <v-chip
        v-bind="hoverProps"
        :variant="getVariant(isHovering)"
        density="comfortable"
        :color="color"
        @click="handleClick"
      >
        {{ props.item[props.valueKey] }}
      </v-chip>
    </template>
  </v-hover>
  <v-purchase-sell-modal
    v-if="props.ship"
    v-model:visible="showModal"
    :trade-opportunity="props.item"
    :ship="props.ship"
    :action="props.valueKey === 'purchase_price' ? 'Purchase' : 'Sell'"
  />
</template>

<script setup>
import VPurchaseSellModal from '@/components/VPurchaseSellModal.vue';
import { getSupplyColor } from "@enums/supplyLevels";
import TradeGoodTypes from "@enums/tradeGoodTypes";
import { ref, computed } from "vue";

const props = defineProps({
  item: {
    type: Object,
    required: true,
  },
  valueKey: {
    type: String,
    required: true,
  },
  ship: {
    type: Object,
    default: null,
  },
});

const showModal = ref(false);
const color = computed(() => getSupplyColor(props.item.type, props.item.supply));
const transactionImpossible = computed(() =>
    !props.ship
    || props.ship.cargo_capacity === props.ship.cargo_units 
    || (props.item.type === TradeGoodTypes.EXPORT && props.valueKey !== 'purchase_price') 
    || (props.item.type === TradeGoodTypes.IMPORT 
      && (
        props.valueKey !== 'sell_price' 
        || !props.ship.cargos.find((cargo) => cargo.symbol === props.item.symbol)
      )
    )
);

function getVariant(isHovering) {
  switch (true) {
    case transactionImpossible.value:
      return 'tonal';
    case isHovering:
      return 'flat';
    default:
      return 'outlined';
  }
}

function handleClick() {
  if (!transactionImpossible.value) {
    showModal.value = true;
  }
}
</script>