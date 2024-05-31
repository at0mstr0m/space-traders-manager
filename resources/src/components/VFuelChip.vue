<template>
  <v-hover>
    <template #default="{ isHovering, props: hoverProps }">
      <v-chip
        v-bind="hoverProps"
        :variant="getVariant(isHovering)"
        color="grey"
        @click.stop.prevent="handleClick"
      >
        {{ props.ship.fuel_current }} / {{ props.ship.fuel_capacity }}
      </v-chip>
    </template>
  </v-hover>
</template>

<script setup>
import { useRepository } from "@/repos/repoGenerator.js";
import ShipNavStatus from "@enums/shipNavStatus";

const repo = useRepository("ships");

const props = defineProps({
  ship: {
    type: Object,
    required: true,
  }
});

const emit = defineEmits(['refueled']);

async function handleClick() {
  const response = await repo.refuel(props.ship.id);
  emit('refueled', response.data.data);
}

function getVariant(isHovering) {
  switch (true) {
    case props.ship.status === ShipNavStatus.IN_TRANSIT:
    case props.ship.fuel_current === props.ship.fuel_capacity:
      return 'tonal';
    case isHovering:
      return 'flat';
    default:
      return 'outlined';
  }
}
</script>