<template>
  <v-btn variant="plain">
    <v-icon
      color="secondary"
      icon="mdi mdi-dots-vertical"
    />
    <v-overlay
      v-model="visible"
      activator="parent"
      location-strategy="connected"
      scroll-strategy="reposition"
      :scrim="false"
    >
      <v-card
        class="pa-2"
        color="secondary"
      >
        <v-col>
          <v-row>
            <v-btn
              elevation="1"
              class="mb-2"
              color="primary"
              text="Edit"
              @click="editItem"
            />
          </v-row>
          <v-row>
            <v-btn
              elevation="1"
              color="warning"
              text="Delete"
              @click="deleteItem"
            />
          </v-row>
        </v-col>
      </v-card>
    </v-overlay>
  </v-btn>
</template>

<script setup>
import { ref, defineProps } from 'vue';

const visible = ref(false);
const props = defineProps({
  item: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(['edit', 'delete']);

function editItem() {
  visible.value = false;
  emit('edit', props.item);
}

function deleteItem() {
  visible.value = false;
  emit('delete', props.item);
}

</script>
