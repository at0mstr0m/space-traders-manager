<template>
  <v-hover>
    <template #default="{ isHovering, props: hoverProps }">
      <v-chip
        v-bind="hoverProps"
        :variant="isHovering ? 'flat' : 'outlined'"
        color="white"
        @click.stop.prevent="handleClick"
      >
        {{ waypointSymbol }}
      </v-chip>
    </template>
  </v-hover>
</template>

<script setup>
import useNavigationStore from "@/store/navigation";
import { computed } from "vue";

const emit = defineEmits(['waypoint-clicked']);
const { getSystemFromWaypointSymbol } = useNavigationStore();

const props = defineProps({
  ship: {
    type: Object,
    default: null,
  },
  waypoint: {
    type: Object,
    default: null,
  },
});

const waypointSymbol = computed(() => props.ship?.waypoint_symbol || props.waypoint.symbol);

function handleClick() {
  emit('waypoint-clicked', {
    system: getSystemFromWaypointSymbol(waypointSymbol.value),
    waypoint_symbol: waypointSymbol.value,
  });
}
</script>
