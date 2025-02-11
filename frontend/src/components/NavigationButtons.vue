<script setup>
import { defineProps, defineEmits } from "vue";

const props = defineProps({
  currentCategory: String,
});

const emit = defineEmits(["update:currentCategory", "updateComments"]);

const categories = ["all", "candy", "call", "referred", "signature", "misc", "missingShipDate"];

function formatCategoryName(category) {
  return category === "all" ? "All" : category.charAt(0).toUpperCase() + category.slice(1);
}
</script>

<template>
  <div class="nav-buttons">
    <button v-for="category in categories" :key="category" @click="emit('update:currentCategory', category)" :class="{ selected: currentCategory === category }">
      {{ formatCategoryName(category) }}
    </button>
    <button @click="emit('updateComments')">Update Comments</button>
  </div>
</template>
