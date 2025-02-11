<script setup>
import { defineProps, defineEmits } from "vue";

const props = defineProps({
  currentSort: String,
  sortDirection: String,
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
    <button v-for="option in sortOptions" :key="option.key" @click="emit('setSort', option.key)">
      {{ option.label }}
    </button>
  </div>
</template>
