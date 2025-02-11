<!-- components/NavigationButtons.vue -->
<script setup>
import { defineProps, defineEmits } from "vue";

defineProps({
  modelValue: { type: String, required: true },
});

const emit = defineEmits(["update:modelValue", "updateComments"]);
const categories = ["all", "candy", "call", "referred", "signature", "misc", "Missing Ship Date"];

function formatCategoryName(category) {
  return category === "all" ? "All" : category.charAt(0).toUpperCase() + category.slice(1);
}
</script>

<template>
  <div class="nav-buttons">
    <button v-for="category in categories" :key="category" @click="emit('update:modelValue', category)" :class="{ selected: modelValue === category }">
      {{ formatCategoryName(category) }}
    </button>
    <button @click="emit('updateComments')">Update Comments</button>
  </div>
</template>
