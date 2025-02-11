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
  </div>
</template>

<style scoped>
.nav-buttons {
  display: flex;
  justify-content: center;
  gap: 15px;
  margin-bottom: 20px;
}

.nav-buttons button {
  padding: 10px 20px;
  font-size: 1rem;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.nav-buttons button.selected {
  background-color: #0056b3;
}

.nav-buttons button:hover {
  background-color: #0056b3;
}
</style>
