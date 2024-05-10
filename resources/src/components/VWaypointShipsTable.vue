<template>
  <v-data-table
    v-model:expanded="expanded"
    :items="props.ships"
    :headers="waypointTableColumns"
    density="compact"
    :items-per-page="0"
    hover
    expandable
    show-expand
    expand-on-click
    item-selectable
  >
    <!-- :sort-by="{ type: 'type', order: 'asc' }" -->
    <template #top>
      <v-toolbar
        flat
        density="compact"
      >
        <v-toolbar-title>Ships</v-toolbar-title>
      </v-toolbar>
    </template>

    <!-- disable footer -->
    <template #bottom />

    <!-- eslint-disable-next-line vue/valid-v-slot -->
    <template #item.fuel_current="{ item }">
      {{ item.fuel_current }} / {{ item.fuel_capacity }}
    </template>

    <!-- eslint-disable-next-line vue/valid-v-slot -->
    <template #item.cargo_capacity="{ item }">
      {{ item.cargo_units }} / {{ item.cargo_capacity }}
    </template>

    <template #expanded-row="{ item, columns }">
      <v-ship-expanded-details
        :ship="item"
        :columns="columns"
        @update-row="emits('update')"
      />
    </template>
  </v-data-table>
</template>

<script setup>
import VShipExpandedDetails from '@/components/VShipExpandedDetails.vue';
import { VDataTable } from "vuetify/lib/components/index.mjs";
import { ref } from 'vue';
import useShipUtils from "@/utils/ships";

const { waypointTableColumns } = useShipUtils();
const expanded = ref([]);

const props = defineProps({
  ships: {
    type: Array,
    required: true,
  },
});

const emits = defineEmits(['update']);
</script>