<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <v-card
    class="mx-auto"
    variant="tonal"
    color="primary"
    title="Construction"
    min-width="300px"
  >
    <v-list>
      <v-list-item
        v-for="item in items"
        :key="item.tradeSymbol"
      >
        <v-list-item-title>{{ item.tradeSymbol }}</v-list-item-title>
        <v-list-item-subtitle class="my-1">
          Required: {{ item.unitsRequired }}
        </v-list-item-subtitle>
        <v-list-item-subtitle class="my-1">
          Fulfilled: {{ item.unitsFulfilled }}
        </v-list-item-subtitle>
      </v-list-item>
    </v-list>
  </v-card>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRepository } from "@/repos/repoGenerator.js";

const repo = useRepository("live-data");
const items = ref([]);

async function fetchConstructionSite() {
  const response = await repo.constructionSiteInStartingSystem();
  items.value = response.data.data.constructionMaterial;
}

onMounted(fetchConstructionSite);
</script>
