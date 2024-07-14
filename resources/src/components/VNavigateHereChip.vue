<template>
  <v-hover>
    <template #default="{ isHovering, props: hoverProps }">
      <v-chip
        v-bind="hoverProps"
        class="ml-2"
        small
        :variant="isHovering ? 'flat' : 'outlined'"
        color="red"
        @click="handleClick"
      >
        {{ props.jumpgate ? 'Use Jump Gate' : 'Navigate Here ' + currentShip.symbol }}
      </v-chip>
    </template>
  </v-hover>
</template>

<script setup>
import { storeToRefs } from 'pinia';
import useNavigationStore from "@/store/navigation";
import { useRepository } from "@/repos/repoGenerator.js";

const repo = useRepository("ships");
const navigationStore = useNavigationStore();
const { currentShip } = storeToRefs(navigationStore);

const props = defineProps({
  waypoint: {
    type: Object,
    required: true,
  },
  jumpgate: {
    type: Boolean,
    default: false,
  },
});

async function handleClick() {
  const response = await repo.navigate(currentShip.value.id, props.waypoint.id);
  navigationStore.refresh(response.data.data);
  currentShip.value = null;
}
</script>