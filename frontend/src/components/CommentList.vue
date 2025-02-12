<script setup>
import { defineProps, ref } from "vue";

defineProps({
  comments: {
    type: Array,
    required: true,
  },
});

const editingId = ref(null);
const tempDateTime = ref("");

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

function startEdit(comment) {
  editingId.value = comment.orderid;
  tempDateTime.value = comment.shipdate_expected || "";
}

function cancelEdit() {
  editingId.value = null;
  tempDateTime.value = "";
}

async function saveEdit(comment) {
  try {
    const response = await fetch("http://localhost:8081/updateShipDateTime.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        orderId: comment.orderid,
        shipDateTime: tempDateTime.value,
      }),
    });

    const data = await response.json();
    if (data.success) {
      comment.shipdate_expected = tempDateTime.value;
      editingId.value = null;
      tempDateTime.value = "";
    } else {
      alert("Failed to update ship date and time");
    }
  } catch (error) {
    console.error("Error updating ship date and time:", error);
    alert("Error updating ship date and time");
  }
}
</script>

<template>
  <div class="comments-container">
    <div v-if="comments && Array.isArray(comments) && comments.length > 0">
      <div v-for="comment in comments" :key="comment.orderid" class="comment" :class="{ highlight: isCommentHighlighted(comment) }">
        <strong>Order ID:</strong> {{ comment.orderid }}<br />
        <strong>Comments:</strong> {{ comment.comments }}<br />
        <strong>Expected Ship Date & Time: </strong>
        <template v-if="editingId === comment.orderid">
          <input type="datetime-local" v-model="tempDateTime" class="datetime-input" />
          <div class="button-group">
            <button @click="saveEdit(comment)" class="save-btn">Save</button>
            <button @click="cancelEdit" class="cancel-btn">Cancel</button>
          </div>
        </template>
        <template v-else>
          <span>{{ comment.shipdate_expected || "N/A" }}</span>
          <button @click="startEdit(comment)" class="edit-btn">Edit</button>
        </template>
      </div>
    </div>
    <p v-if="!comments || !comments.length" class="no-comments">No comments available.</p>
  </div>
</template>

<style scoped>
.comments-container {
  display: flex;
  flex-direction: column;
  gap: 15px;
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

.datetime-input {
  padding: 5px;
  margin: 0 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.button-group {
  display: inline-block;
  margin-left: 10px;
}

.edit-btn,
.save-btn,
.cancel-btn {
  padding: 5px 10px;
  margin-left: 5px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.edit-btn {
  background-color: #007bff;
  color: white;
}

.save-btn {
  background-color: #28a745;
  color: white;
}

.cancel-btn {
  background-color: #dc3545;
  color: white;
}

.edit-btn:hover,
.save-btn:hover,
.cancel-btn:hover {
  opacity: 0.9;
}
</style>
