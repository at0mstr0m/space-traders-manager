<template>
  <v-dialog
    v-model="visible"
    width="400"
  >
    <v-card>
      <template #title>
        {{ props.action + ' ' + props.tradeOpportunity.symbol }}
      </template>

      <template #subtitle>
        {{ 'Ship: ' + props.ship.symbol }}
      </template>

      <template #text>
        <v-form class="mt-3">
          <v-text-field
            v-model="quantity"
            label="Quantity"
            variant="outlined"
          />
        </v-form>
      </template>

      <template #actions>
        <v-btn
          class="ml-auto"
          variant="outlined"
          color="primary"
          :text="props.action"
          @click="handleSubmit"
        />
      </template>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { defineModel, ref } from 'vue';
import { useRepository } from "@/repos/repoGenerator.js";

const repo = useRepository("ships");
const visible = defineModel('visible', { required: true, type: [Boolean] });

const props = defineProps({
  action: {
    type: String,
    required: true,
  },
  tradeOpportunity: {
    type: Object,
    required: true,
  },
  ship: {
    type: Object,
    required: true,
  },
});

const quantity = ref(
  props.action === 'Purchase'
    ? Math.min(
      props.ship.cargo_capacity - props.ship.cargo_units,
      props.tradeOpportunity.trade_volume
    )
    : (
        props.ship
          .cargos.find((cargo) => cargo.symbol === props.tradeOpportunity.symbol)
          ?.units 
        || 0
      )
);

const emit = defineEmits(['submitted']);

async function handleSubmit() {
  const response = await repo[props.action.toLowerCase() + 'TradeGood'](
    props.ship.id,
    props.tradeOpportunity.symbol,
    quantity.value
  );
  visible.value = false;
  emit('submitted', response.data.data);
}
</script>