<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <v-container
    fluid
    class="d-flex justify-center align-center"
    :style="{
      width: '50%',
      height: '70%',
    }"
  >
    <v-system-map :data="data" />
  </v-container>
</template>

<script setup>
import VSystemMap from '@/components/VSystemMap.vue';

const data = {
  datasets: [
    {
      label: 'Scatter Dataset 1',
      fill: false,
      borderColor: '#f87979',
      backgroundColor: '#f87979',
      data: [
        {
          x: -2,
          y: 4
        },
        {
          x: -1,
          y: 1
        },
        {
          x: 0,
          y: 0
        },
        {
          x: 1,
          y: 1
        },
        {
          x: 2,
          y: 4
        }
      ]
    },
    {
      label: 'Scatter Dataset 2',
      fill: false,
      borderColor: '#7acbf9',
      backgroundColor: '#7acbf9',
      data: [
        {
          x: -2,
          y: -4
        },
        {
          x: -1,
          y: -1
        },
        {
          x: 0,
          y: 1
        },
        {
          x: 1,
          y: -1
        },
        {
          x: 2,
          y: -4
        }
      ]
    }
  ]
};
</script>