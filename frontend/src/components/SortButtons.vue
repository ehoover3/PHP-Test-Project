<!-- components/SortButtons.vue -->
<script setup>
import { defineProps, defineEmits } from "vue";

defineProps({
  currentSort: { type: String, required: true },
  sortDirection: { type: String, required: true },
});

const emit = defineEmits(["setSort"]);

const sortOptions = [
  { key: "orderid", label: "Sort by Order ID" },
  { key: "comments", label: "Sort by Comments" },
  { key: "shipdate_expected", label: "Sort by Expected Ship Date" },
];
</script>

<template>
  <div class="sort-buttons">
    <button v-for="option in sortOptions" :key="option.key" @click="emit('setSort', option.key)" :class="{ selected: currentSort === option.key }">
      {{ option.label }}
      <span v-if="currentSort === option.key">
        {{ sortDirection === "asc" ? "↑" : "↓" }}
      </span>
    </button>
  </div>
</template>
