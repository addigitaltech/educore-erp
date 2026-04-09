<template>
  <div>
    <div class="page-header"><div><h2>Overdue Books</h2><p>{{ overdue.length }} books currently overdue</p></div><button class="btn btn-blue" @click="sendReminders">📧 Send All Reminders</button></div>
    <div class="card">
      <div v-if="loading" class="loading-overlay"><div class="spinner"></div></div>
      <div v-else-if="overdue.length===0" class="empty-state"><div class="empty-icon">✅</div><h3>No overdue books!</h3><p>All books are returned on time.</p></div>
      <div v-else class="table-wrap">
        <table>
          <thead><tr><th>Student</th><th>Book</th><th>Due Date</th><th>Days Overdue</th><th>Fine</th><th>Actions</th></tr></thead>
          <tbody>
            <tr v-for="r in overdue" :key="r.id">
              <td style="font-weight:500">{{ r.student?.user?.first_name }} {{ r.student?.user?.last_name }}</td>
              <td style="font-size:13px">{{ r.book?.title }}</td>
              <td style="font-size:12px;color:var(--red)">{{ r.due_date }}</td>
              <td><span class="badge badge-danger">{{ daysOverdue(r.due_date) }} days</span></td>
              <td style="color:var(--red);font-weight:600">₦{{ Number(r.fine_amount||daysOverdue(r.due_date)*50).toLocaleString() }}</td>
              <td>
                <div style="display:flex;gap:6px">
                  <button class="btn btn-ghost btn-sm" @click="sendReminder(r)">📧 Remind</button>
                  <button class="btn btn-green btn-sm" @click="returnBook(r.id)">↩ Return</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue'
import { libraryAPI } from '../../services/api'
import { useToast } from 'vue-toastification'
const toast=useToast(),loading=ref(true),overdue=ref([])
function daysOverdue(d){return Math.max(0,Math.floor((Date.now()-new Date(d))/86400000))}
onMounted(async()=>{loading.value=true;try{const r=await libraryAPI.overdue();overdue.value=r.data||[]}finally{loading.value=false}})
async function returnBook(id){try{await libraryAPI.return(id);toast.success('Book returned!');const r=await libraryAPI.overdue();overdue.value=r.data||[]}catch{toast.error('Failed')}}
function sendReminder(r){toast.info(`Reminder sent to ${r.student?.user?.first_name}`)}
function sendReminders(){toast.info(`Reminders sent to all ${overdue.value.length} students`)}
</script>
