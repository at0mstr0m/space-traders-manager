<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <v-container
    fluid
    class="justify-center align-center"
  >
    <v-row no-gutters>
      <v-col>
        <v-sheet class="pa-2 ma-1">
          <v-sector-map 
            height="600"
            @select="handleSystemSelected"
          />
        </v-sheet>
      </v-col>
      <v-col>
        <v-sheet
          class="pa-5 ma-1"
          height="600"
        >
          <v-system-map
            ref="systemMap"
            @select="handleWaypointsSelected"
          />
        </v-sheet>
      </v-col>

      <v-responsive width="100%" />

      <v-col>
        <v-sheet class="pa-2 ma-1">
          .v-col-auto
        </v-sheet>
      </v-col>

      <v-col>
        <v-sheet class="pa-2 ma-1">
          .v-col-auto
        </v-sheet>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref } from 'vue';
import VSectorMap from '@/components/Maps/VSectorMap.vue';
import VSystemMap from '@/components/Maps/VSystemMap.vue';

const systemMap = ref(null);

function handleSystemSelected(system) {
  if (!system.length) {
    return;
  }
  systemMap.value.setSystem(system[0]);
}

function handleWaypointsSelected(system) {
  console.log('handleWaypointsSelected system', system);
}
</script>