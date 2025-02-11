<script setup>
import { ref, onMounted, computed } from "vue";

const commentsData = ref([]);
const currentSort = ref("orderid");
const sortDirection = ref("asc");
const currentCategory = ref("all");

const sortedAndFilteredComments = computed(() => {
  const filtered = filterCommentsByCategory(commentsData.value, currentCategory.value);
  return sortComments(filtered);
});

function setSort(sortKey) {
  if (currentSort.value === sortKey) {
    sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
  } else {
    currentSort.value = sortKey;
    sortDirection.value = "asc";
  }
}

function sortComments(comments) {
  return [...comments].sort((a, b) => {
    let comparison = 0;

    if (currentSort.value === "orderid") {
      comparison = Number(a.orderid) - Number(b.orderid);
    } else if (currentSort.value === "comments") {
      comparison = a.comments.localeCompare(b.comments);
    } else if (currentSort.value === "shipdate_expected") {
      const dateA = a.shipdate_expected ? new Date(a.shipdate_expected) : new Date(0);
      const dateB = b.shipdate_expected ? new Date(b.shipdate_expected) : new Date(0);
      comparison = dateA - dateB;
    }

    return sortDirection.value === "asc" ? comparison : -comparison;
  });
}

function filterCommentsByCategory(comments, category) {
  if (category === "all") {
    return comments;
  } else if (category === "missingShipDate") {
    return comments.filter((comment) => !comment.shipdate_expected);
  }

  return comments.filter((comment) => {
    const lowerComments = comment.comments.toLowerCase();
    switch (category) {
      case "candy":
        return lowerComments.includes("candy");
      case "call":
        return lowerComments.includes("call me") || lowerComments.includes("don't call me");
      case "referred":
        return lowerComments.includes("referred");
      case "signature":
        return lowerComments.includes("signature");
      case "misc":
        return !["candy", "call", "referred", "signature"].some((keyword) => lowerComments.includes(keyword));
      default:
        return false;
    }
  });
}

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

async function fetchComments() {
  try {
    const response = await fetch("http://localhost:8081/readComments.php");
    const data = await response.json();

    if (Array.isArray(data) && data.length > 0) {
      commentsData.value = data;
    }
  } catch (error) {
    console.error("Error fetching comments:", error);
  }
}

async function updateAndReloadComments() {
  try {
    await fetch("http://localhost:8081/updateCommentsInBatchProcess.php", {
      method: "POST",
    });
    await fetchComments();
  } catch (error) {
    console.error("Error updating comments:", error);
  }
}

onMounted(() => {
  fetchComments();
});
</script>

<template>
  <div class="container">
    <h1>Order Comments</h1>

    <div class="nav-buttons">
      <button v-for="category in ['all', 'candy', 'call', 'referred', 'signature', 'misc', 'missingShipDate']" :key="category" @click="currentCategory = category" :class="{ selected: currentCategory === category }">
        {{ category === "all" ? "All" : category.charAt(0).toUpperCase() + category.slice(1) }}
      </button>
      <button @click="updateAndReloadComments">Update Comments</button>
    </div>

    <div class="sort-buttons">
      <button @click="setSort('orderid')">Sort by Order ID</button>
      <button @click="setSort('comments')">Sort by Comments</button>
      <button @click="setSort('shipdate_expected')">Sort by Expected Ship Date</button>
    </div>

    <p class="highlight-explanation">
      Some comments are highlighted because the expected ship date is missing
      <br />and the comment references a specific date, day of the week, or month.
    </p>

    <div class="comments-container">
      <div v-for="comment in sortedAndFilteredComments" :key="comment.orderid" class="comment" :class="{ highlight: isCommentHighlighted(comment) }">
        <strong>Order ID:</strong> {{ comment.orderid }}<br />
        <strong>Comments:</strong> {{ comment.comments }}<br />
        <strong>Expected Ship Date:</strong>
        <span>{{ comment.shipdate_expected || "N/A" }}</span>
      </div>

      <p v-if="!sortedAndFilteredComments.length" class="no-comments">No comments available.</p>
    </div>
  </div>
</template>

<style scoped>
.container {
  max-width: 900px;
  margin: 0 auto;
  padding: 20px;
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

h1 {
  text-align: center;
  margin-top: 20px;
  color: #333;
}

.nav-buttons {
  display: flex;
  justify-content: center;
  gap: 12px;
  margin-bottom: 20px;
}

.nav-buttons button {
  padding: 12px 20px;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 16px;
  transition: background-color 0.3s ease;
}

.nav-buttons button.selected {
  background-color: #ff9600;
  color: white;
}

.nav-buttons button:hover {
  background-color: #ffc800;
}

.sort-buttons {
  display: flex;
  justify-content: center;
  gap: 12px;
  margin-bottom: 20px;
}

.sort-buttons button {
  padding: 8px 16px;
  background-color: #6c757d;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.sort-buttons button:hover {
  background-color: #5a6268;
}

.comment {
  padding: 15px;
  border-bottom: 1px solid #ccc;
  margin-bottom: 12px;
  border-radius: 6px;
  background-color: #fafafa;
}

.comment strong {
  font-weight: bold;
  color: #007bff;
}

.comment span {
  color: #333;
}

.highlight {
  background-color: #f9f9a6;
}

.no-comments {
  text-align: center;
  color: #888;
  font-size: 18px;
}

.highlight-explanation {
  text-align: center;
  margin-top: 15px;
  font-size: 16px;
  color: #555;
}
</style>
