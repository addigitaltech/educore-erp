<template>
  <div>
    <div class="page-header"><div><h2>Manage Exams</h2><p>Create and manage your CBT examinations</p></div><button class="btn btn-blue" @click="showModal=true">+ Create Exam</button></div>
    <div v-if="loading" class="loading-overlay"><div class="spinner"></div></div>
    <div v-else class="card">
      <div class="table-wrap">
        <table>
          <thead><tr><th>Title</th><th>Class</th><th>Subject</th><th>Date</th><th>Duration</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody>
            <tr v-for="e in exams" :key="e.id">
              <td style="font-weight:500">{{ e.title }}</td>
              <td>{{ e.school_class?.name }}</td>
              <td>{{ e.subject?.name }}</td>
              <td style="font-size:12px">{{ formatDate(e.starts_at) }}</td>
              <td>{{ e.duration_minutes }} min</td>
              <td><span :class="['badge',e.status==='published'?'badge-info':e.status==='completed'?'badge-success':'badge-purple']">{{ e.status }}</span></td>
              <td>
                <div style="display:flex;gap:6px">
                  <button v-if="e.status==='draft'" class="btn btn-green btn-sm" @click="publishExam(e.id)">Publish</button>
                  <router-link to="/teacher/questions" class="btn btn-ghost btn-sm">Questions</router-link>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <div v-if="showModal" class="modal-overlay" @click.self="showModal=false">
      <div class="modal">
        <div class="modal-header"><div class="modal-title">Create Exam</div><button class="btn btn-ghost btn-sm" @click="showModal=false">×</button></div>
        <div class="form-group"><label class="form-label">Title</label><input v-model="form.title" class="form-input"/></div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Class</label><select v-model="form.school_class_id" class="form-input"><option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option></select></div>
          <div class="form-group"><label class="form-label">Subject</label><select v-model="form.subject_id" class="form-input"><option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option></select></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Duration (min)</label><input v-model="form.duration_minutes" class="form-input" type="number"/></div>
          <div class="form-group"><label class="form-label">Total Marks</label><input v-model="form.total_marks" class="form-input" type="number"/></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Start</label><input v-model="form.starts_at" class="form-input" type="datetime-local"/></div>
          <div class="form-group"><label class="form-label">End</label><input v-model="form.ends_at" class="form-input" type="datetime-local"/></div>
        </div>
        <div style="display:flex;gap:10px;margin-top:8px"><button class="btn btn-blue" @click="saveExam" :disabled="saving"><span v-if="saving" class="spinner"></span><span v-else>Create</span></button><button class="btn btn-ghost" @click="showModal=false">Cancel</button></div>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, reactive, onMounted } from 'vue'
import { examsAPI, classesAPI, subjectsAPI } from '../../services/api'
import { useToast } from 'vue-toastification'
const toast=useToast(),loading=ref(true),saving=ref(false),showModal=ref(false)
const exams=ref([]),classes=ref([]),subjects=ref([])
const form=reactive({title:'',school_class_id:'',subject_id:'',duration_minutes:60,total_marks:100,starts_at:'',ends_at:''})
function formatDate(d){return d?new Date(d).toLocaleDateString('en-GB',{day:'2-digit',month:'short'}):'—'}
onMounted(async()=>{loading.value=true;const[r,c,s]=await Promise.all([examsAPI.list({}),classesAPI.list(),subjectsAPI.list()]);exams.value=r.data.data||[];classes.value=c.data||[];subjects.value=s.data||[];loading.value=false})
async function saveExam(){saving.value=true;try{await examsAPI.create(form);toast.success('Exam created!');showModal.value=false;const r=await examsAPI.list({});exams.value=r.data.data||[]}catch{toast.error('Failed')}finally{saving.value=false}}
async function publishExam(id){try{await examsAPI.publish(id);toast.success('Published!');const r=await examsAPI.list({});exams.value=r.data.data||[]}catch{toast.error('Failed')}}
</script>
