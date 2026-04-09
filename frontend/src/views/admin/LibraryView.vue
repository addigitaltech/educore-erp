<template>
  <div>
    <div class="page-header">
      <div><h2>Library</h2><p>Book catalog and borrowing management</p></div>
      <div style="display:flex;gap:10px">
        <button class="btn btn-ghost" @click="showBorrowModal=true">📤 Issue Book</button>
        <button class="btn btn-blue" @click="showAddModal=true">+ Add Book</button>
      </div>
    </div>

    <div class="stats-grid" style="grid-template-columns:repeat(4,1fr)">
      <div class="stat-card"><div class="stat-icon blue">📚</div><div class="stat-value">{{ books.length }}</div><div class="stat-label">Total Titles</div></div>
      <div class="stat-card"><div class="stat-icon green">✅</div><div class="stat-value">{{ books.reduce((a,b)=>a+(b.available_copies||0),0) }}</div><div class="stat-label">Available</div></div>
      <div class="stat-card"><div class="stat-icon yellow">📤</div><div class="stat-value">{{ books.reduce((a,b)=>a+(b.total_copies-b.available_copies||0),0) }}</div><div class="stat-label">Borrowed</div></div>
      <div class="stat-card"><div class="stat-icon red">⚠️</div><div class="stat-value">{{ overdueCount }}</div><div class="stat-label">Overdue</div></div>
    </div>

    <div class="card">
      <div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap">
        <input v-model="search" class="form-input" style="max-width:260px" placeholder="🔍 Search books..." @input="fetchBooks"/>
        <select v-model="catFilter" class="form-input" style="max-width:160px" @change="fetchBooks">
          <option value="">All Categories</option>
          <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
        </select>
      </div>

      <div v-if="loading" class="loading-overlay"><div class="spinner"></div></div>
      <div v-else class="book-grid">
        <div v-for="b in books" :key="b.id" class="book-card">
          <div class="book-cover">{{ bookEmoji(b.category) }}</div>
          <div class="book-info">
            <div class="book-title">{{ b.title }}</div>
            <div class="book-author">{{ b.author }}</div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px">
              <span :class="['badge',b.available_copies>2?'badge-success':b.available_copies>0?'badge-warning':'badge-danger']" style="font-size:10px">{{ b.available_copies }} avail.</span>
              <span style="font-size:10px;color:var(--text3)">{{ b.category }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Issue Book Modal -->
    <div v-if="showBorrowModal" class="modal-overlay" @click.self="showBorrowModal=false">
      <div class="modal">
        <div class="modal-header"><div class="modal-title">Issue Book to Student</div><button class="btn btn-ghost btn-sm" @click="showBorrowModal=false">×</button></div>
        <div class="form-group"><label class="form-label">Book</label>
          <select v-model="borrowForm.book_id" class="form-input">
            <option v-for="b in books.filter(x=>x.available_copies>0)" :key="b.id" :value="b.id">{{ b.title }} ({{ b.available_copies }} available)</option>
          </select>
        </div>
        <div class="form-group"><label class="form-label">Student ID</label><input v-model="borrowForm.student_id" class="form-input" type="number" placeholder="Student ID"/></div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Issue Date</label><input v-model="borrowForm.issue_date" class="form-input" type="date"/></div>
          <div class="form-group"><label class="form-label">Due Date</label><input v-model="borrowForm.due_date" class="form-input" type="date"/></div>
        </div>
        <div style="display:flex;gap:10px;margin-top:8px">
          <button class="btn btn-blue" @click="issueBook" :disabled="saving"><span v-if="saving" class="spinner"></span><span v-else>Issue Book</span></button>
          <button class="btn btn-ghost" @click="showBorrowModal=false">Cancel</button>
        </div>
      </div>
    </div>

    <!-- Add Book Modal -->
    <div v-if="showAddModal" class="modal-overlay" @click.self="showAddModal=false">
      <div class="modal">
        <div class="modal-header"><div class="modal-title">Add New Book</div><button class="btn btn-ghost btn-sm" @click="showAddModal=false">×</button></div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Title</label><input v-model="addForm.title" class="form-input"/></div>
          <div class="form-group"><label class="form-label">Author</label><input v-model="addForm.author" class="form-input"/></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">ISBN</label><input v-model="addForm.isbn" class="form-input"/></div>
          <div class="form-group"><label class="form-label">Category</label>
            <select v-model="addForm.category" class="form-input">
              <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Total Copies</label><input v-model="addForm.total_copies" class="form-input" type="number" min="1"/></div>
          <div class="form-group"><label class="form-label">Shelf Number</label><input v-model="addForm.shelf_number" class="form-input"/></div>
        </div>
        <div style="display:flex;gap:10px;margin-top:8px">
          <button class="btn btn-blue" @click="addBook" :disabled="saving2"><span v-if="saving2" class="spinner"></span><span v-else>Add Book</span></button>
          <button class="btn btn-ghost" @click="showAddModal=false">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { libraryAPI } from '../../services/api'
import { useToast } from 'vue-toastification'
const toast = useToast()
const loading = ref(true), saving = ref(false), saving2 = ref(false)
const showBorrowModal = ref(false), showAddModal = ref(false)
const books = ref([]), overdueCount = ref(0)
const search = ref(''), catFilter = ref('')
const categories = ['Mathematics','Physics','Chemistry','Biology','English','History','Economics','Literature','Geography','Agriculture']
const bookEmoji = c => ({'Mathematics':'📐','Physics':'⚛️','Chemistry':'🧪','Biology':'🧬','English':'📖','History':'🌍','Economics':'💹','Literature':'📚'})[c]||'📗'

const borrowForm = reactive({ book_id:'', student_id:'', issue_date: new Date().toISOString().split('T')[0], due_date:'' })
const addForm = reactive({ title:'', author:'', isbn:'', category:'Mathematics', total_copies:1, shelf_number:'' })

onMounted(async () => {
  await fetchBooks()
  try { const r = await libraryAPI.overdue(); overdueCount.value = r.data?.length||0 } catch {}
})

async function fetchBooks() {
  loading.value=true
  try { const r = await libraryAPI.books({ search:search.value, category:catFilter.value }); books.value = r.data.data||[] }
  finally { loading.value=false }
}

async function issueBook() {
  saving.value=true
  try { await libraryAPI.borrow(borrowForm); toast.success('Book issued!'); showBorrowModal.value=false; fetchBooks() }
  catch(e) { toast.error(e.response?.data?.message||'Failed') } finally { saving.value=false }
}

async function addBook() {
  saving2.value=true
  try { await libraryAPI.addBook(addForm); toast.success('Book added!'); showAddModal.value=false; fetchBooks() }
  catch(e) { toast.error(e.response?.data?.message||'Failed') } finally { saving2.value=false }
}
</script>

<style scoped>
.book-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:16px; }
.book-card { background:var(--card2); border:1px solid var(--border); border-radius:12px; overflow:hidden; transition:transform .2s,border-color .2s; cursor:pointer; }
.book-card:hover { transform:translateY(-3px); border-color:var(--border2); }
.book-cover { height:110px; display:flex; align-items:center; justify-content:center; font-size:42px; background:linear-gradient(135deg,var(--card2),var(--bg3)); }
.book-info { padding:12px; }
.book-title { font-size:12px; font-weight:600; margin-bottom:4px; }
.book-author { font-size:11px; color:var(--text3); }
</style>
