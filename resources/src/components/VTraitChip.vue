<template>
  <v-chip
    variant="tonal"
    class="mr-1 mt-2"
  >
    <v-tooltip
      activator="parent"
      location="top"
      max-width="300"
    >
      {{ props.trait.description }}
    </v-tooltip>
    {{ props.trait.name }}
  </v-chip>
</template>

<script setup>
const props = defineProps({
  trait: {
    type: Object,
    required: true,
  }
});
</script>
