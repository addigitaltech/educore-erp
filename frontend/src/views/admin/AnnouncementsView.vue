<template>
  <div>
    <div class="page-header">
      <div><h2>Announcements</h2><p>School-wide notices and communications</p></div>
      <button class="btn btn-blue" @click="showModal=true">+ Post Announcement</button>
    </div>
    <div class="grid-21">
      <div>
        <div v-if="loading" class="loading-overlay"><div class="spinner"></div></div>
        <template v-else>
          <div v-for="a in announcements" :key="a.id" :class="['announcement', a.priority==='urgent'?'urgent':a.priority==='important'?'important':'normal']">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px">
              <div>
                <div class="ann-title">{{ a.title }}</div>
                <div class="ann-body">{{ a.body }}</div>
                <div class="ann-meta">📌 {{ a.created_by?.first_name }} {{ a.created_by?.last_name }} · {{ formatDate(a.created_at) }} · <span :class="['badge','badge-'+priorityBadge(a.priority)]">{{ a.priority }}</span></div>
              </div>
              <button class="btn btn-red btn-sm" @click="deleteAnn(a.id)" style="flex-shrink:0">🗑️</button>
            </div>
          </div>
          <div v-if="announcements.length===0" class="empty-state"><div class="empty-icon">📢</div><h3>No announcements</h3></div>
        </template>
      </div>
      <div class="card" style="margin-bottom:0;height:fit-content">
        <div class="card-header"><div class="card-title">Quick Post</div></div>
        <div class="form-group"><label class="form-label">Title</label><input v-model="form.title" class="form-input" placeholder="Announcement title"/></div>
        <div class="form-group"><label class="form-label">Message</label><textarea v-model="form.body" class="form-input" rows="4" placeholder="Write your announcement..."></textarea></div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Audience</label>
            <select v-model="form.audience" class="form-input">
              <option value="all">Everyone</option><option value="students">Students</option><option value="teachers">Teachers</option><option value="parents">Parents</option>
            </select>
          </div>
          <div class="form-group"><label class="form-label">Priority</label>
            <select v-model="form.priority" class="form-input">
              <option value="normal">Normal</option><option value="important">Important</option><option value="urgent">Urgent</option>
            </select>
          </div>
        </div>
        <button class="btn btn-blue" style="width:100%" @click="postAnn" :disabled="saving">
          <span v-if="saving" class="spinner"></span><span v-else>📢 Post Announcement</span>
        </button>
      </div>
    </div>

    <div v-if="showModal" class="modal-overlay" @click.self="showModal=false">
      <div class="modal">
        <div class="modal-header"><div class="modal-title">New Announcement</div><button class="btn btn-ghost btn-sm" @click="showModal=false">×</button></div>
        <div class="form-group"><label class="form-label">Title</label><input v-model="form.title" class="form-input"/></div>
        <div class="form-group"><label class="form-label">Message</label><textarea v-model="form.body" class="form-input" rows="5"></textarea></div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Audience</label><select v-model="form.audience" class="form-input"><option value="all">Everyone</option><option value="students">Students</option><option value="teachers">Teachers</option><option value="parents">Parents</option></select></div>
          <div class="form-group"><label class="form-label">Priority</label><select v-model="form.priority" class="form-input"><option value="normal">Normal</option><option value="important">Important</option><option value="urgent">Urgent</option></select></div>
        </div>
        <div style="display:flex;gap:10px;margin-top:8px"><button class="btn btn-blue" @click="postAnn" :disabled="saving"><span v-if="saving" class="spinner"></span><span v-else>Post</span></button><button class="btn btn-ghost" @click="showModal=false">Cancel</button></div>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, reactive, onMounted } from 'vue'
import { announcementsAPI } from '../../services/api'
import { useToast } from 'vue-toastification'
const toast = useToast()
const loading=ref(true), saving=ref(false), showModal=ref(false)
const announcements=ref([])
const form=reactive({title:'',body:'',audience:'all',priority:'normal'})
function formatDate(d){return d?new Date(d).toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'}):''}
function priorityBadge(p){return p==='urgent'?'danger':p==='important'?'warning':'info'}
onMounted(async()=>{loading.value=true;try{const r=await announcementsAPI.list();announcements.value=r.data.data||[]}finally{loading.value=false}})
async function postAnn(){if(!form.title||!form.body)return;saving.value=true;try{await announcementsAPI.create(form);toast.success('Posted!');showModal.value=false;const r=await announcementsAPI.list();announcements.value=r.data.data||[];Object.assign(form,{title:'',body:'',audience:'all',priority:'normal'})}catch{toast.error('Failed')}finally{saving.value=false}}
async function deleteAnn(id){if(!confirm('Delete?'))return;try{await announcementsAPI.delete(id);announcements.value=announcements.value.filter(a=>a.id!==id);toast.success('Deleted')}catch{toast.error('Failed')}}
</script>
<style scoped>
.announcement{padding:16px;border-radius:10px;margin-bottom:12px}
.announcement.urgent{border-left:3px solid var(--red);background:rgba(239,68,68,0.05)}
.announcement.important{border-left:3px solid var(--yellow);background:rgba(245,158,11,0.05)}
.announcement.normal{border-left:3px solid var(--accent);background:rgba(59,130,246,0.05)}
.ann-title{font-size:14px;font-weight:600;margin-bottom:6px}
.ann-body{font-size:13px;color:var(--text2);line-height:1.6}
.ann-meta{font-size:11px;color:var(--text3);margin-top:8px}
</style>
