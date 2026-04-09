<template>
  <div>
    <div class="page-header"><div><h2>Borrow Records</h2><p>All active book borrowing records</p></div><button class="btn btn-blue" @click="showModal=true">+ Issue Book</button></div>
    <div class="card">
      <div v-if="loading" class="loading-overlay"><div class="spinner"></div></div>
      <div v-else class="table-wrap">
        <table>
          <thead><tr><th>Student</th><th>Book</th><th>Issued</th><th>Due Date</th><th>Status</th><th>Fine</th><th>Actions</th></tr></thead>
          <tbody>
            <tr v-for="r in records" :key="r.id">
              <td style="font-weight:500">{{ r.student?.user?.first_name }} {{ r.student?.user?.last_name }}</td>
              <td style="font-size:13px">{{ r.book?.title }}</td>
              <td style="font-size:12px">{{ r.issue_date }}</td>
              <td style="font-size:12px">{{ r.due_date }}</td>
              <td><span :class="['badge',r.status==='active'?'badge-success':r.status==='returned'?'badge-info':'badge-danger']">{{ r.status }}</span></td>
              <td style="color:var(--red)">{{ r.fine_amount>0?'₦'+Number(r.fine_amount).toLocaleString():'—' }}</td>
              <td>
                <button v-if="r.status==='active'||r.status==='overdue'" class="btn btn-green btn-sm" @click="returnBook(r.id)">↩ Return</button>
                <span v-else style="font-size:12px;color:var(--text3)">Returned {{ r.return_date }}</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <div v-if="showModal" class="modal-overlay" @click.self="showModal=false">
      <div class="modal">
        <div class="modal-header"><div class="modal-title">Issue Book</div><button class="btn btn-ghost btn-sm" @click="showModal=false">×</button></div>
        <div class="form-group"><label class="form-label">Book</label><select v-model="form.book_id" class="form-input"><option v-for="b in books" :key="b.id" :value="b.id">{{ b.title }} ({{ b.available_copies }} avail.)</option></select></div>
        <div class="form-group"><label class="form-label">Student ID</label><input v-model="form.student_id" class="form-input" type="number" placeholder="Student ID"/></div>
        <div class="form-row"><div class="form-group"><label class="form-label">Issue Date</label><input v-model="form.issue_date" class="form-input" type="date"/></div><div class="form-group"><label class="form-label">Due Date</label><input v-model="form.due_date" class="form-input" type="date"/></div></div>
        <div style="display:flex;gap:10px;margin-top:8px"><button class="btn btn-blue" @click="issueBook" :disabled="saving"><span v-if="saving" class="spinner"></span><span v-else>Issue</span></button><button class="btn btn-ghost" @click="showModal=false">Cancel</button></div>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, reactive, onMounted } from 'vue'
import { libraryAPI } from '../../services/api'
import { useToast } from 'vue-toastification'
const toast=useToast(),loading=ref(true),saving=ref(false),showModal=ref(false)
const records=ref([]),books=ref([])
const form=reactive({book_id:'',student_id:'',issue_date:new Date().toISOString().split('T')[0],due_date:''})
onMounted(async()=>{loading.value=true;try{const[r,b]=await Promise.all([libraryAPI.borrows(),libraryAPI.books({})]);records.value=r.data?.data||[];books.value=b.data?.data||[]}finally{loading.value=false}})
async function issueBook(){saving.value=true;try{await libraryAPI.borrow(form);toast.success('Book issued!');showModal.value=false;const r=await libraryAPI.borrows();records.value=r.data?.data||[]}catch(e){toast.error(e.response?.data?.message||'Failed')}finally{saving.value=false}}
async function returnBook(id){try{const r=await libraryAPI.return(id);toast.success(`Book returned${r.data.fine>0?' (Fine: ₦'+r.data.fine+')':''}`);const res=await libraryAPI.borrows();records.value=res.data?.data||[]}catch{toast.error('Failed')}}
</script>
