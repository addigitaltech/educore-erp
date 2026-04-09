<template>
  <div>
    <div class="page-header">
      <div><h2>Course Materials</h2><p>Upload study resources for your students</p></div>
      <button class="btn btn-primary" @click="openModal"><i class="fa fa-upload" style="margin-right:6px"></i>Upload Material</button>
    </div>

    <!-- Stats -->
    <div class="stats-grid" style="grid-template-columns:repeat(4,1fr)">
      <div class="stat-card"><div class="stat-card-top"><div class="stat-icon blue">📚</div></div><div class="stat-value">{{ materials.length }}</div><div class="stat-label">Total Uploads</div></div>
      <div class="stat-card"><div class="stat-card-top"><div class="stat-icon green">✅</div></div><div class="stat-value">{{ materials.filter(m=>m.is_published).length }}</div><div class="stat-label">Published</div></div>
      <div class="stat-card"><div class="stat-card-top"><div class="stat-icon purple">📥</div></div><div class="stat-value">{{ materials.reduce((a,m)=>a+(m.download_count||0),0) }}</div><div class="stat-label">Downloads</div></div>
      <div class="stat-card"><div class="stat-card-top"><div class="stat-icon orange">📖</div></div><div class="stat-value">{{ new Set(materials.map(m=>m.subject_id).filter(Boolean)).size }}</div><div class="stat-label">Subjects</div></div>
    </div>

    <!-- Filters -->
    <div class="card" style="padding:14px 20px;margin-bottom:16px">
      <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
        <div class="search-wrap"><i class="fa fa-search search-icon"></i><input v-model="search" class="search-input" placeholder="Search materials..."/></div>
        <select v-model="typeFilter" class="form-input" style="max-width:140px">
          <option value="">All Types</option>
          <option value="PDF">PDF</option><option value="PPT">PPT</option>
          <option value="DOC">DOC</option><option value="VIDEO">Video</option><option value="LINK">Link</option>
        </select>
        <div style="margin-left:auto;display:flex;gap:6px">
          <button :class="['btn btn-sm',view==='grid'?'btn-primary':'btn-ghost']" @click="view='grid'"><i class="fa fa-th-large"></i></button>
          <button :class="['btn btn-sm',view==='list'?'btn-primary':'btn-ghost']" @click="view='list'"><i class="fa fa-list"></i></button>
        </div>
      </div>
    </div>

    <div v-if="loading" class="loading-overlay" style="height:200px"><div class="spinner"></div></div>

    <!-- Grid View -->
    <div v-else-if="view==='grid'" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:14px">
      <div v-for="m in filtered" :key="m.id" class="card" style="margin:0;transition:box-shadow 0.2s,transform 0.2s" @mouseenter="$event.currentTarget.style.cssText+='box-shadow:var(--shadow-md);transform:translateY(-2px)'" @mouseleave="$event.currentTarget.style.cssText+=';box-shadow:var(--shadow);transform:none'">
        <div style="display:flex;justify-content:space-between;margin-bottom:10px">
          <span :class="['badge',typeClass(m.file_type)]">{{ m.file_type }}</span>
          <div style="display:flex;gap:4px">
            <button class="btn btn-ghost btn-sm btn-icon" @click="deleteMaterial(m.id)"><i class="fa fa-trash" style="color:var(--danger);font-size:11px"></i></button>
          </div>
        </div>
        <div style="font-size:28px;margin-bottom:8px">{{ typeIcon(m.file_type) }}</div>
        <div style="font-weight:600;font-size:13px;margin-bottom:4px;line-height:1.3">{{ m.title }}</div>
        <div style="font-size:11px;color:var(--text3);margin-bottom:6px">{{ m.subject?.name ?? 'All Subjects' }}</div>
        <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--text3)">
          <span>{{ m.file_size ?? '—' }}</span>
          <span>{{ m.download_count ?? 0 }} dl</span>
        </div>
      </div>
      <div v-if="!filtered.length" class="empty-state" style="grid-column:1/-1"><div class="empty-icon">📁</div><h3>No materials yet</h3><p>Upload your first resource</p></div>
    </div>

    <!-- List View -->
    <div v-else class="table-container">
      <div class="table-wrap">
        <table>
          <thead><tr><th>Title</th><th>Subject</th><th>Type</th><th>Size</th><th>Downloads</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody>
            <tr v-for="m in filtered" :key="m.id">
              <td style="font-weight:500">{{ m.title }}</td>
              <td>{{ m.subject?.name ?? '—' }}</td>
              <td><span :class="['badge',typeClass(m.file_type)]">{{ m.file_type }}</span></td>
              <td style="font-size:12px;color:var(--text3)">{{ m.file_size ?? '—' }}</td>
              <td style="font-size:12px;color:var(--text3)">{{ m.download_count ?? 0 }}</td>
              <td><span :class="['badge',m.is_published?'badge-success':'badge-warning']">{{ m.is_published?'Published':'Draft' }}</span></td>
              <td><button class="btn btn-ghost btn-sm btn-icon" @click="deleteMaterial(m.id)"><i class="fa fa-trash" style="color:var(--danger)"></i></button></td>
            </tr>
            <tr v-if="!filtered.length"><td colspan="7"><div class="empty-state" style="padding:30px"><div class="empty-icon">📁</div><h3>No materials</h3></div></td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Upload Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal=false">
      <div class="modal" style="max-width:500px">
        <div class="modal-header"><div class="modal-title">Upload Material</div><button class="modal-close" @click="showModal=false"><i class="fa fa-times"></i></button></div>
        <div class="form-group"><label class="form-label">Title *</label><input v-model="form.title" class="form-input" placeholder="e.g. Chapter 5 Notes"/></div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Subject</label><select v-model="form.subject_id" class="form-input"><option value="">All Subjects</option><option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option></select></div>
          <div class="form-group"><label class="form-label">Type *</label><select v-model="form.file_type" class="form-input"><option value="PDF">📄 PDF</option><option value="PPT">📊 PowerPoint</option><option value="DOC">📝 Word Doc</option><option value="VIDEO">🎬 Video</option><option value="LINK">🔗 External Link</option><option value="OTHER">📦 Other</option></select></div>
        </div>
        <div v-if="form.file_type==='LINK'" class="form-group"><label class="form-label">URL</label><input v-model="form.external_url" class="form-input" type="url" placeholder="https://..."/></div>
        <div v-else class="form-group">
          <label class="form-label">File</label>
          <div class="upload-zone" @click="$refs.fileInput.click()" @dragover.prevent @drop.prevent="e=>{fileRef=e.dataTransfer.files[0];form.file_name=fileRef.name}">
            <input ref="fileInput" type="file" style="display:none" @change="e=>{fileRef=e.target.files[0];form.file_name=fileRef?.name}"/>
            <div v-if="form.file_name"><i class="fa fa-check-circle" style="color:var(--success);font-size:24px;margin-bottom:6px"></i><div style="font-size:13px;font-weight:500">{{ form.file_name }}</div></div>
            <div v-else><i class="fa fa-cloud-upload-alt" style="font-size:24px;color:var(--text3);margin-bottom:6px"></i><div style="font-size:13px;color:var(--text3)">Click or drag file here</div></div>
          </div>
        </div>
        <div class="form-group"><label class="form-label">Description</label><textarea v-model="form.description" class="form-input" rows="2" placeholder="Brief description..."></textarea></div>
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px"><input type="checkbox" v-model="form.is_published" id="pub" style="accent-color:var(--primary)"/><label for="pub" style="font-size:13px;cursor:pointer">Publish immediately (visible to students)</label></div>
        <div style="display:flex;gap:10px">
          <button class="btn btn-primary" @click="save" :disabled="saving">
            <span v-if="saving" class="spinner" style="width:14px;height:14px;border-color:rgba(255,255,255,0.3);border-top-color:#fff"></span>
            <span v-else><i class="fa fa-upload" style="margin-right:6px"></i>Upload</span>
          </button>
          <button class="btn btn-ghost" @click="showModal=false">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { materialsAPI, subjectsAPI } from '../../services/api'
import { useToast } from 'vue-toastification'
const toast = useToast()
const loading = ref(false), saving = ref(false), showModal = ref(false)
const materials = ref([]), subjects = ref([])
const search = ref(''), typeFilter = ref(''), view = ref('grid')
const fileRef = ref(null)
const form = reactive({ title:'', subject_id:'', file_type:'PDF', external_url:'', description:'', is_published:true, file_name:'' })

const filtered = computed(()=>materials.value.filter(m=>{
  if(typeFilter.value&&m.file_type!==typeFilter.value)return false
  if(search.value&&!m.title.toLowerCase().includes(search.value.toLowerCase()))return false
  return true
}))

function typeClass(t){return{PDF:'badge-danger',PPT:'badge-warning',DOC:'badge-info',VIDEO:'badge-purple',LINK:'badge-success'}[t]??'badge-gray'}
function typeIcon(t){return{PDF:'📄',PPT:'📊',DOC:'📝',VIDEO:'🎬',LINK:'🔗',OTHER:'📦'}[t]??'📦'}

function openModal(){Object.assign(form,{title:'',subject_id:'',file_type:'PDF',external_url:'',description:'',is_published:true,file_name:''});fileRef.value=null;showModal.value=true}

async function save(){
  if(!form.title)return toast.error('Title is required')
  saving.value=true
  try{
    const fd=new FormData()
    Object.entries(form).forEach(([k,v])=>{if(k!=='file_name'&&v!==null&&v!=='')fd.append(k,v)})
    fd.append('audience','all')
    if(fileRef.value)fd.append('file',fileRef.value)
    await materialsAPI.create(fd)
    toast.success('Material uploaded!'); showModal.value=false; load()
  }catch(e){toast.error(e.response?.data?.message??'Upload failed')}
  finally{saving.value=false}
}

async function deleteMaterial(id){
  if(!confirm('Delete this material?'))return
  try{await materialsAPI.delete(id);materials.value=materials.value.filter(m=>m.id!==id);toast.success('Deleted')}
  catch{toast.error('Failed to delete')}
}

async function load(){
  loading.value=true
  try{const res=await materialsAPI.myUploads();materials.value=res.data.data??res.data}
  catch{toast.error('Failed to load')}
  finally{loading.value=false}
}

onMounted(async()=>{load();const r=await subjectsAPI.list();subjects.value=r.data})
</script>
