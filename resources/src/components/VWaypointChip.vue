<template>
  <v-hover>
    <template #default="{ isHovering, props: hoverProps }">
      <v-chip
        v-bind="hoverProps"
        :variant="isHovering ? 'flat' : 'outlined'"
        color="white"
        @click.stop.prevent="handleClick"
      >
        {{ props.ship.waypoint_symbol }}
      </v-chip>
    </template>
  </v-hover>
</template>

<script setup>
import useNavigationStore from "@/store/navigation";

const emit = defineEmits(['waypoint-clicked']);
const { getSystemFromWaypointSymbol } = useNavigationStore();

const props = defineProps({
  ship: {
    type: Object,
    required: true,
  }
});

function handleClick() {
  emit('waypoint-clicked', {
    system: getSystemFromWaypointSymbol(props.ship.waypoint_symbol),
    waypoint_symbol: props.ship.waypoint_symbol,
  });
}
</script>