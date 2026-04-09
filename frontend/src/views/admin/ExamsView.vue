<template>
  <div>
    <div class="page-header">
      <div><h2>Examinations</h2><p>Manage CBT exams and assessments</p></div>
      <button class="btn btn-blue" @click="showModal=true">+ Create Exam</button>
    </div>

    <div class="tabs">
      <button v-for="t in tabs" :key="t.value" :class="['tab',{active:activeTab===t.value}]" @click="activeTab=t.value;fetchExams()">{{ t.label }}</button>
    </div>

    <div v-if="loading" class="loading-overlay"><div class="spinner"></div></div>
    <div v-else style="display:flex;flex-direction:column;gap:14px">
      <div v-for="e in exams" :key="e.id" class="card" style="margin-bottom:0">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
          <div style="display:flex;align-items:center;gap:16px">
            <div style="width:48px;height:48px;border-radius:12px;background:rgba(59,130,246,0.15);display:flex;align-items:center;justify-content:center;font-size:24px">📝</div>
            <div>
              <div style="font-size:15px;font-weight:700">{{ e.title }}</div>
              <div style="font-size:12px;color:var(--text2);margin-top:4px">{{ e.school_class?.name }} · {{ e.subject?.name }} · {{ e.duration_minutes }} min · {{ e.num_questions }} questions</div>
            </div>
          </div>
          <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
            <div style="text-align:right">
              <div style="font-size:11px;color:var(--text3)">Scheduled</div>
              <div style="font-size:13px;font-weight:600">{{ formatDate(e.starts_at) }}</div>
            </div>
            <span :class="['badge', statusBadge(e.status)]">{{ e.status }}</span>
            <div style="display:flex;gap:8px">
              <button v-if="e.status==='draft'" class="btn btn-green btn-sm" @click="publishExam(e.id)">Publish</button>
              <button class="btn btn-ghost btn-sm" @click="viewResults(e.id)">Results</button>
              <button class="btn btn-red btn-sm" @click="deleteExam(e.id)">🗑️</button>
            </div>
          </div>
        </div>
      </div>
      <div v-if="exams.length===0" class="empty-state"><div class="empty-icon">📝</div><h3>No exams found</h3><p>Create your first exam to get started</p></div>
    </div>

    <!-- Create Exam Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal=false">
      <div class="modal">
        <div class="modal-header"><div class="modal-title">Create New Exam</div><button class="btn btn-ghost btn-sm" @click="showModal=false">×</button></div>
        <div class="form-group"><label class="form-label">Exam Title</label><input v-model="form.title" class="form-input" placeholder="e.g. Mathematics Mid-Term Exam"/></div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Class</label>
            <select v-model="form.school_class_id" class="form-input">
              <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
          <div class="form-group"><label class="form-label">Subject</label>
            <select v-model="form.subject_id" class="form-input">
              <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Duration (minutes)</label><input v-model="form.duration_minutes" class="form-input" type="number" value="90"/></div>
          <div class="form-group"><label class="form-label">Total Marks</label><input v-model="form.total_marks" class="form-input" type="number" value="100"/></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Start Date & Time</label><input v-model="form.starts_at" class="form-input" type="datetime-local"/></div>
          <div class="form-group"><label class="form-label">End Date & Time</label><input v-model="form.ends_at" class="form-input" type="datetime-local"/></div>
        </div>
        <div style="display:flex;gap:16px;margin:14px 0;flex-wrap:wrap">
          <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer"><input type="checkbox" v-model="form.randomize_questions" style="accent-color:var(--accent)"> Randomize questions</label>
          <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer"><input type="checkbox" v-model="form.anti_cheat" style="accent-color:var(--accent)"> Anti-cheat</label>
        </div>
        <div style="display:flex;gap:10px"><button class="btn btn-blue" @click="saveExam" :disabled="saving"><span v-if="saving" class="spinner"></span><span v-else>Create Exam</span></button><button class="btn btn-ghost" @click="showModal=false">Cancel</button></div>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, reactive, onMounted } from 'vue'
import { examsAPI, classesAPI, subjectsAPI } from '../../services/api'
import { useToast } from 'vue-toastification'
const toast = useToast()
const loading = ref(true), saving = ref(false), showModal = ref(false)
const exams = ref([]), classes = ref([]), subjects = ref([])
const activeTab = ref('all')
const tabs = [{ value:'all',label:'All' },{ value:'draft',label:'Draft' },{ value:'published',label:'Published' },{ value:'completed',label:'Completed' }]
const form = reactive({ title:'', school_class_id:'', subject_id:'', duration_minutes:90, total_marks:100, starts_at:'', ends_at:'', randomize_questions:true, anti_cheat:true })
function formatDate(d) { return d ? new Date(d).toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'}) : '—' }
function statusBadge(s) { return s==='published'?'badge-info':s==='completed'?'badge-success':s==='ongoing'?'badge-warning':'badge-purple' }
onMounted(async () => { const [_,c,s] = await Promise.all([fetchExams(), classesAPI.list(), subjectsAPI.list()]); classes.value = c.data||[]; subjects.value = s.data||[] })
async function fetchExams() { loading.value=true; try { const r = await examsAPI.list(activeTab.value!=='all'?{status:activeTab.value}:{}); exams.value = r.data.data||[] } finally { loading.value=false } }
async function saveExam() { saving.value=true; try { await examsAPI.create(form); toast.success('Exam created!'); showModal.value=false; fetchExams() } catch(e) { toast.error(e.response?.data?.message||'Failed') } finally { saving.value=false } }
async function publishExam(id) { try { await examsAPI.publish(id); toast.success('Exam published!'); fetchExams() } catch { toast.error('Failed') } }
async function deleteExam(id) { if(!confirm('Delete exam?')) return; try { await examsAPI.delete(id); toast.success('Deleted'); fetchExams() } catch { toast.error('Failed') } }
function viewResults(id) { /* navigate */ }
</script>
