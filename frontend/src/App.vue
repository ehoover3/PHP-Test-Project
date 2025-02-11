<!-- App.vue -->
<script setup>
import { ref, onMounted, computed } from "vue";
import NavigationButtons from "./components/NavigationButtons.vue";
import SortButtons from "./components/SortButtons.vue";
import CommentList from "./components/CommentList.vue";

const commentsData = ref([]);
const currentSort = ref("orderid");
const sortDirection = ref("asc");
const currentCategory = ref("all");
const isLoaded = ref(false);

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
  if (isLoaded.value) return;
  try {
    await fetch("http://localhost:8081/updateCommentsInBatchProcess.php", {
      method: "POST",
    });
    await fetchComments();
    isLoaded.value = true;
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
    <NavigationButtons v-model="currentCategory" @updateComments="updateAndReloadComments" />
    <SortButtons :currentSort="currentSort" :sortDirection="sortDirection" @setSort="setSort" />

    <p class="highlight-explanation">
      Some comments are highlighted because the expected ship date is missing
      <br />and the comment references a specific date, day of the week, or month.
    </p>

    <CommentList :comments="sortedAndFilteredComments" />
  </div>
</template>

<style scoped>
.container {
  font-family: Arial, sans-serif;
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
  background-color: #f9f9f9;
}

h1 {
  text-align: center;
  color: #333;
}

.highlight-explanation {
  font-size: 1rem;
  color: #555;
  background-color: #fff3cd;
  padding: 10px;
  border-radius: 5px;
  margin-bottom: 20px;
}

.comments-container {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.comment {
  background-color: #fff;
  border: 1px solid #ddd;
  padding: 15px;
  border-radius: 5px;
  transition: background-color 0.3s;
}

.comment.highlight {
  background-color: #fff3cd;
}

.no-comments {
  text-align: center;
  font-size: 1.2rem;
  color: #777;
}
</style>
