<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <v-container
    fluid
    class="justify-center align-center"
  >
    <v-row no-gutters>
      <v-col>
        <v-sheet class="pa-6 ma-1">
          <v-system-map />
        </v-sheet>
      </v-col>
      <v-col>
        <v-sheet class="pa-2 ma-1">
          <v-sector-map height="600" />
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
import VSectorMap from '@/components/Maps/VSectorMap.vue';
import VSystemMap from '@/components/Maps/VSystemMap.vue';
</script>