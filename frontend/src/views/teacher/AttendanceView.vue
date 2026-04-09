<!-- AttendanceView teacher -->
<template>
  <div>
    <div class="page-header"><div><h2>Mark Attendance</h2><p>Record daily student attendance for your classes</p></div></div>
    <div class="card">
      <div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap">
        <select v-model="classId" class="form-input" style="max-width:160px" @change="loadStudents">
          <option value="">Select Class</option>
          <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
        </select>
        <input v-model="date" type="date" class="form-input" style="max-width:170px"/>
        <div style="margin-left:auto;display:flex;gap:8px">
          <button class="btn btn-green btn-sm" @click="markAll('present')">✓ All Present</button>
          <button class="btn btn-ghost btn-sm" @click="markAll('absent')">✗ All Absent</button>
          <button class="btn btn-blue" @click="saveAttendance" :disabled="saving"><span v-if="saving" class="spinner"></span><span v-else>✓ Save</span></button>
        </div>
      </div>
      <div v-if="!classId" class="empty-state"><div class="empty-icon">✅</div><h3>Select a class</h3><p>Choose a class above to mark attendance</p></div>
      <div v-else-if="loadingStudents" class="loading-overlay"><div class="spinner"></div></div>
      <div v-else class="table-wrap">
        <table>
          <thead><tr><th>Student</th><th>Present</th><th>Absent</th><th>Late</th><th>Excused</th></tr></thead>
          <tbody>
            <tr v-for="s in students" :key="s.id">
              <td><div style="display:flex;align-items:center;gap:8px"><div class="avatar avatar-sm">{{ ((s.user?.first_name?.[0]||'')+(s.user?.last_name?.[0]||'')).toUpperCase() }}</div>{{ s.user?.first_name }} {{ s.user?.last_name }}</div></td>
              <td v-for="st in ['present','absent','late','excused']" :key="st" style="text-align:center">
                <input type="radio" :name="`att-${s.id}`" :value="st" v-model="records[s.id]" style="accent-color:var(--accent)"/>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, reactive, onMounted } from 'vue'
import { classesAPI, attendanceAPI } from '../../services/api'
import { useToast } from 'vue-toastification'
const toast=useToast(),saving=ref(false),loadingStudents=ref(false)
const classes=ref([]),students=ref([]),records=reactive({})
const classId=ref(''),date=ref(new Date().toISOString().split('T')[0])
onMounted(async()=>{const r=await classesAPI.list();classes.value=r.data||[]})
async function loadStudents(){if(!classId.value)return;loadingStudents.value=true;try{const r=await classesAPI.students(classId.value);students.value=r.data||[];students.value.forEach(s=>{records[s.id]='present'})}finally{loadingStudents.value=false}}
function markAll(st){students.value.forEach(s=>{records[s.id]=st})}
async function saveAttendance(){if(!classId.value)return;saving.value=true;try{const recs=students.value.map(s=>({student_id:s.id,status:records[s.id]||'present'}));await attendanceAPI.bulkMark({class_id:classId.value,date:date.value,records:recs});toast.success(`Attendance saved for ${students.value.length} students!`)}catch{toast.error('Failed to save')}finally{saving.value=false}}
</script>
