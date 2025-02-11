<script setup>
import { defineProps } from "vue";

const props = defineProps({
  comments: {
    type: Array,
    required: true,
  },
});

function isCommentHighlighted(comment) {
  const daysOfWeek = ["monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday"];
  const monthsOfYear = ["january", "february", "march", "april", "may", "june", "july", "august", "september", "october", "november", "december"];
  const dateRegex = /\b(\d{1,2}[-/]\d{1,2}[-/]\d{2,4})\b/;
  const lowerComments = comment.comments.toLowerCase();
  const containsDayOfWeek = daysOfWeek.some((day) => lowerComments.includes(day));
  const containsMonth = monthsOfYear.some((month) => lowerComments.includes(month));
  const containsDate = dateRegex.test(comment.comments);
  const containsMonthWord = /\bmonth\b|\bmonths\b/.test(lowerComments);

  return !comment.shipdate_expected && (containsDayOfWeek || (containsMonth && !containsMonthWord) || containsDate);
}
</script>

<template>
  <div class="comments-container">
    <div v-for="comment in comments" :key="comment.orderid" class="comment" :class="{ highlight: isCommentHighlighted(comment) }">
      <strong>Order ID:</strong> {{ comment.orderid }}<br />
      <strong>Comments:</strong> {{ comment.comments }}<br />
      <strong>Expected Ship Date:</strong>
      <span>{{ comment.shipdate_expected || "N/A" }}</span>
    </div>

    <p v-if="!comments.length" class="no-comments">No comments available.</p>
  </div>
</template>
