<template>
  <div>
    <div class="page-header"><div><h2>Add New Book</h2><p>Add books to the library catalog</p></div></div>
    <div class="card" style="max-width:620px">
      <div class="form-row">
        <div class="form-group"><label class="form-label">Book Title *</label><input v-model="form.title" class="form-input" placeholder="Full book title"/></div>
        <div class="form-group"><label class="form-label">Author *</label><input v-model="form.author" class="form-input" placeholder="Author name"/></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label class="form-label">ISBN</label><input v-model="form.isbn" class="form-input" placeholder="978-xxx-xxx-xxx-x"/></div>
        <div class="form-group"><label class="form-label">Publisher</label><input v-model="form.publisher" class="form-input" placeholder="Publisher name"/></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label class="form-label">Category *</label>
          <select v-model="form.category" class="form-input">
            <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
          </select>
        </div>
        <div class="form-group"><label class="form-label">Year Published</label><input v-model="form.year_published" class="form-input" type="number" placeholder="2024"/></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label class="form-label">Total Copies *</label><input v-model="form.total_copies" class="form-input" type="number" min="1"/></div>
        <div class="form-group"><label class="form-label">Shelf Number</label><input v-model="form.shelf_number" class="form-input" placeholder="e.g. MAT-012"/></div>
      </div>
      <div class="form-group"><label class="form-label">Description</label><textarea v-model="form.description" class="form-input" rows="3" placeholder="Brief description of the book..."></textarea></div>
      <div style="display:flex;gap:10px;margin-top:8px">
        <button class="btn btn-blue" @click="addBook" :disabled="saving"><span v-if="saving" class="spinner"></span><span v-else>➕ Add Book</span></button>
        <button class="btn btn-ghost" @click="resetForm">Clear</button>
      </div>
      <div v-if="successMsg" style="margin-top:12px;padding:12px;background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.3);border-radius:8px;color:var(--green2);font-size:13px">✅ {{ successMsg }}</div>
    </div>
  </div>
</template>
<script setup>
import { ref, reactive } from 'vue'
import { libraryAPI } from '../../services/api'
import { useToast } from 'vue-toastification'
const toast=useToast(),saving=ref(false),successMsg=ref('')
const categories=['Mathematics','Physics','Chemistry','Biology','English','History','Economics','Literature','Geography','Agriculture','Computer Science','French','Music','Arts','Physical Education']
const form=reactive({title:'',author:'',isbn:'',publisher:'',category:'Mathematics',year_published:'',total_copies:1,shelf_number:'',description:''})
async function addBook(){if(!form.title||!form.author){toast.error('Title and Author are required');return}saving.value=true;successMsg.value='';try{await libraryAPI.addBook(form);successMsg.value=`"${form.title}" added successfully!`;toast.success('Book added to catalog!');resetForm()}catch(e){toast.error(e.response?.data?.message||'Failed to add book')}finally{saving.value=false}}
function resetForm(){Object.assign(form,{title:'',author:'',isbn:'',publisher:'',category:'Mathematics',year_published:'',total_copies:1,shelf_number:'',description:''})}
</script>
