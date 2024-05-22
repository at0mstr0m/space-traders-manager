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
    v-if="currentShip"
    v-model:visible="showModal"
    :trade-opportunity="props.item"
    :ship="currentShip"
    :action="props.valueKey === 'purchase_price' ? 'Purchase' : 'Sell'"
    @submitted="handleModalSubmitted"
  />
</template>

<script setup>
import VPurchaseSellModal from '@/components/VPurchaseSellModal.vue';
import { getSupplyColor } from "@enums/supplyLevels";
import TradeGoodTypes from "@enums/tradeGoodTypes";
import ShipNavStatus from "@enums/shipNavStatus";
import { ref, computed } from "vue";
import useNavigationStore from "@/store/navigation";
import { storeToRefs } from 'pinia';

const navigationStore = useNavigationStore();
const { currentShip } = storeToRefs(navigationStore);

const props = defineProps({
  item: {
    type: Object,
    required: true,
  },
  waypoint: {
    type: Object,
    required: true,
  },
  valueKey: {
    type: String,
    required: true,
  },
});

const showModal = ref(false);
const color = computed(() => getSupplyColor(props.item.type, props.item.supply));
const transactionPossible = computed(() =>
  Boolean(
    currentShip.value
    && currentShip.value.waypoint_symbol === props.waypoint.symbol
    && currentShip.value.status !== ShipNavStatus.IN_TRANSIT
    && currentShip.value.cargo_capacity > currentShip.value.cargo_units
    && (
      (
        props.valueKey === 'purchase_price'
        && [TradeGoodTypes.EXPORT, TradeGoodTypes.EXCHANGE].includes(props.item.type)
      )
      || (
        props.valueKey === 'sell_price'
        && [TradeGoodTypes.IMPORT, TradeGoodTypes.EXCHANGE].includes(props.item.type)
        && currentShip.value.cargos.find((cargo) => cargo.symbol === props.item.symbol)
      )
    )
  )
);

function getVariant(isHovering) {
  switch (true) {
    case !transactionPossible.value:
      return 'tonal';
    case isHovering:
      return 'flat';
    default:
      return 'outlined';
  }
}

function handleClick() {
  if (transactionPossible.value) {
    showModal.value = true;
  }
}

function handleModalSubmitted() {
  currentShip.value = null;
  navigationStore.load(props.waypoint);
}
</script>