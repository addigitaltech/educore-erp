<template>
  <div>
    <div class="page-header">
      <div><h2>Attendance Management</h2><p>Track and manage student attendance</p></div>
      <button class="btn btn-blue" @click="showModal=true">Mark Attendance</button>
    </div>
    <div class="stats-grid" style="grid-template-columns:repeat(4,1fr)">
      <div class="stat-card"><div class="stat-icon green">✅</div><div class="stat-value">{{ summary.rate || 91.4 }}%</div><div class="stat-label">Today's Rate</div></div>
      <div class="stat-card"><div class="stat-icon red">❌</div><div class="stat-value">{{ summary.today_absent || 110 }}</div><div class="stat-label">Absent Today</div></div>
      <div class="stat-card"><div class="stat-icon yellow">⏰</div><div class="stat-value">{{ summary.today_late || 38 }}</div><div class="stat-label">Late Arrivals</div></div>
      <div class="stat-card"><div class="stat-icon blue">📊</div><div class="stat-value">93.2%</div><div class="stat-label">Term Average</div></div>
    </div>
    <div class="card">
      <div class="card-header">
        <div class="card-title">Attendance Records</div>
        <div style="display:flex;gap:10px">
          <select v-model="classFilter" class="form-input" style="max-width:150px;padding:7px 12px;font-size:13px" @change="fetchAttendance">
            <option value="">All Classes</option>
            <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
          <input v-model="dateFilter" type="date" class="form-input" style="max-width:160px;padding:7px 12px;font-size:13px" @change="fetchAttendance" />
        </div>
      </div>
      <div v-if="loading" class="loading-overlay"><div class="spinner"></div></div>
      <div v-else class="table-wrap">
        <table>
          <thead><tr><th>Student</th><th>Class</th><th>Date</th><th>Status</th><th>Marked By</th></tr></thead>
          <tbody>
            <tr v-for="a in records" :key="a.id">
              <td style="font-weight:500">{{ a.student?.user?.first_name }} {{ a.student?.user?.last_name }}</td>
              <td>{{ a.school_class?.name }}</td>
              <td style="font-size:12px">{{ a.date }}</td>
              <td><span :class="['badge',a.status==='present'?'badge-success':a.status==='late'?'badge-warning':'badge-danger']">{{ a.status }}</span></td>
              <td style="font-size:12px;color:var(--text2)">{{ a.marked_by?.first_name }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Bulk Mark Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal=false">
      <div class="modal" style="width:600px">
        <div class="modal-header"><div class="modal-title">Mark Class Attendance</div><button class="btn btn-ghost btn-sm" @click="showModal=false">×</button></div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Class</label>
            <select v-model="markForm.class_id" class="form-input" @change="loadClassStudents">
              <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
          <div class="form-group"><label class="form-label">Date</label><input v-model="markForm.date" class="form-input" type="date"/></div>
        </div>
        <div style="display:flex;gap:10px;margin-bottom:14px">
          <button class="btn btn-green btn-sm" @click="markAll('present')">✓ All Present</button>
          <button class="btn btn-ghost btn-sm" @click="markAll('absent')">✗ All Absent</button>
        </div>
        <div style="max-height:280px;overflow-y:auto">
          <div v-for="s in classStudents" :key="s.id" style="display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border)">
            <div style="display:flex;align-items:center;gap:10px">
              <div class="avatar avatar-sm">{{ ((s.user?.first_name?.[0]||'')+(s.user?.last_name?.[0]||'')).toUpperCase() }}</div>
              <span style="font-size:13px">{{ s.user?.first_name }} {{ s.user?.last_name }}</span>
            </div>
            <div style="display:flex;gap:6px">
              <button v-for="opt in ['present','absent','late','excused']" :key="opt"
                :class="['btn','btn-sm',markForm.records[s.id]===opt?'btn-blue':'btn-ghost']"
                @click="markForm.records[s.id]=opt">{{ opt[0].toUpperCase() }}</button>
            </div>
          </div>
        </div>
        <div style="display:flex;gap:10px;margin-top:14px">
          <button class="btn btn-blue" @click="saveAttendance" :disabled="saving"><span v-if="saving" class="spinner"></span><span v-else>Save Attendance</span></button>
          <button class="btn btn-ghost" @click="showModal=false">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, reactive, onMounted } from 'vue'
import { attendanceAPI, classesAPI } from '../../services/api'
import { useToast } from 'vue-toastification'
const toast = useToast()
const loading = ref(true), saving = ref(false), showModal = ref(false)
const records = ref([]), classes = ref([]), classStudents = ref([])
const summary = ref({})
const classFilter = ref(''), dateFilter = ref(new Date().toISOString().split('T')[0])
const markForm = reactive({ class_id:'', date: new Date().toISOString().split('T')[0], records:{} })
onMounted(async () => {
  const [_s, c, sum] = await Promise.all([fetchAttendance(), classesAPI.list(), attendanceAPI.summary()])
  classes.value = c.data||[]; summary.value = sum.data||{}
})
async function fetchAttendance() { loading.value=true; try { const r = await attendanceAPI.list({ class_id:classFilter.value, date:dateFilter.value }); records.value = r.data.data||[] } finally { loading.value=false } }
async function loadClassStudents() {
  if(!markForm.class_id) return
  const r = await classesAPI.students(markForm.class_id)
  classStudents.value = r.data||[]
  classStudents.value.forEach(s => { markForm.records[s.id] = 'present' })
}
function markAll(status) { classStudents.value.forEach(s => { markForm.records[s.id] = status }) }
async function saveAttendance() {
  saving.value=true
  try {
    const records_arr = Object.entries(markForm.records).map(([sid,status]) => ({ student_id:parseInt(sid), status }))
    await attendanceAPI.bulkMark({ class_id:markForm.class_id, date:markForm.date, records:records_arr })
    toast.success('Attendance saved!'); showModal.value=false; fetchAttendance()
  } catch { toast.error('Failed') } finally { saving.value=false }
}
</script>
