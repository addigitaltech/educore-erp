<template>
  <div>
    <div class="page-header"><div><h2>Library Dashboard 📚</h2><p>Book catalog and borrowing overview</p></div></div>
    <div class="stats-grid" style="grid-template-columns:repeat(4,1fr)">
      <div class="stat-card"><div class="stat-icon blue">📚</div><div class="stat-value">{{ stats.total_books||1847 }}</div><div class="stat-label">Total Books</div></div>
      <div class="stat-card"><div class="stat-icon green">✅</div><div class="stat-value">{{ stats.available_books||1456 }}</div><div class="stat-label">Available</div></div>
      <div class="stat-card"><div class="stat-icon yellow">📤</div><div class="stat-value">{{ stats.borrowed||391 }}</div><div class="stat-label">Borrowed</div></div>
      <div class="stat-card"><div class="stat-icon red">⚠️</div><div class="stat-value">{{ stats.overdue||34 }}</div><div class="stat-label">Overdue</div></div>
    </div>
    <div class="grid-2">
      <div class="card" style="margin-bottom:0">
        <div class="card-header"><div class="card-title">Most Borrowed Books</div></div>
        <div v-if="loading" class="loading-overlay"><div class="spinner"></div></div>
        <div v-else class="table-wrap">
          <table>
            <thead><tr><th>Title</th><th>Category</th><th>Available</th><th>Borrowed</th></tr></thead>
            <tbody>
              <tr v-for="b in books.slice(0,6)" :key="b.id">
                <td style="font-weight:500">{{ b.title }}</td>
                <td style="font-size:12px;color:var(--text2)">{{ b.category }}</td>
                <td><span :class="['badge',b.available_copies>2?'badge-success':b.available_copies>0?'badge-warning':'badge-danger']">{{ b.available_copies }}</span></td>
                <td style="font-size:12px">{{ b.total_copies - b.available_copies }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="card" style="margin-bottom:0">
        <div class="card-header"><div class="card-title">Quick Actions</div></div>
        <div style="display:flex;flex-direction:column;gap:10px">
          <router-link to="/librarian/borrows" class="btn btn-ghost" style="justify-content:flex-start">📤 Issue Book to Student</router-link>
          <router-link to="/librarian/overdue" class="btn btn-ghost" style="justify-content:flex-start">⚠️ View Overdue Books ({{ stats.overdue||34 }})</router-link>
          <router-link to="/librarian/add-book" class="btn btn-ghost" style="justify-content:flex-start">➕ Add New Book</router-link>
          <router-link to="/librarian/books" class="btn btn-ghost" style="justify-content:flex-start">📚 Browse Full Catalog</router-link>
        </div>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue'
import { dashboardAPI, libraryAPI } from '../../services/api'
const loading=ref(true),stats=ref({}),books=ref([])
onMounted(async()=>{loading.value=true;try{const[d,b]=await Promise.all([dashboardAPI.index(),libraryAPI.books({})]);stats.value=d.data||{};books.value=b.data?.data||[]}finally{loading.value=false}})
</script>
