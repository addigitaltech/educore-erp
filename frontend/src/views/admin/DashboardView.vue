<template>
  <div>
    <div class="page-header">
      <div>
        <h2>Good morning, {{ auth.user?.first_name }} 👋</h2>
        <p>Here's what's happening at {{ auth.user?.school?.name }} today</p>
      </div>
      <button class="btn btn-blue" @click="showAddStudent = true">+ Add Student</button>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card" v-for="s in stats" :key="s.label">
        <div :class="['stat-icon', s.color]">{{ s.icon }}</div>
        <div class="stat-value">{{ loading ? '...' : s.value }}</div>
        <div class="stat-label">{{ s.label }}</div>
        <div v-if="s.change" :class="['stat-change', s.changeType]">{{ s.change }}</div>
      </div>
    </div>

    <div class="grid-21">
      <!-- Enrollment Chart -->
      <div class="card" style="margin-bottom:0">
        <div class="card-header">
          <div>
            <div class="card-title">Enrollment & Attendance Trends</div>
            <div class="card-subtitle">Monthly overview — 2025/2026 Academic Year</div>
          </div>
        </div>
        <Line v-if="enrollmentChart.labels" :data="enrollmentChart" :options="lineOpts" style="height:250px" />
        <div v-else class="loading-overlay"><div class="spinner"></div></div>
      </div>

      <!-- Fee Donut -->
      <div class="card" style="margin-bottom:0">
        <div class="card-header">
          <div class="card-title">Fee Collection</div>
          <div class="card-subtitle">2nd Term 2025/26</div>
        </div>
        <Doughnut v-if="feeChart.labels" :data="feeChart" :options="doughnutOpts" style="height:250px" />
        <div v-else class="loading-overlay"><div class="spinner"></div></div>
      </div>
    </div>

    <div class="grid-2" style="margin-top:20px">
      <!-- Recent Students -->
      <div class="card" style="margin-bottom:0">
        <div class="card-header">
          <div class="card-title">Recent Students</div>
          <router-link to="/admin/students" class="btn btn-ghost btn-sm">View All</router-link>
        </div>
        <div v-if="loadingStudents" class="loading-overlay"><div class="spinner"></div></div>
        <div v-else class="table-wrap">
          <table>
            <thead><tr><th>Name</th><th>Class</th><th>Status</th></tr></thead>
            <tbody>
              <tr v-for="s in recentStudents" :key="s.id">
                <td>
                  <div style="display:flex;align-items:center;gap:10px">
                    <div class="avatar avatar-sm">{{ initials(s.user?.first_name, s.user?.last_name) }}</div>
                    <span>{{ s.user?.first_name }} {{ s.user?.last_name }}</span>
                  </div>
                </td>
                <td>{{ s.school_class?.name }}</td>
                <td><span class="badge badge-success">active</span></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Upcoming Exams -->
      <div class="card" style="margin-bottom:0">
        <div class="card-header">
          <div class="card-title">Upcoming Exams</div>
          <router-link to="/admin/exams" class="btn btn-ghost btn-sm">View All</router-link>
        </div>
        <div v-if="loadingExams" class="loading-overlay"><div class="spinner"></div></div>
        <div v-else style="display:flex;flex-direction:column;gap:10px">
          <div v-for="e in upcomingExams" :key="e.id"
            style="display:flex;align-items:center;justify-content:space-between;padding:12px;background:var(--card2);border-radius:10px;border:1px solid var(--border)">
            <div>
              <div style="font-size:13px;font-weight:600">{{ e.title }}</div>
              <div style="font-size:11px;color:var(--text3);margin-top:3px">{{ e.school_class?.name }} · {{ e.duration_minutes }}min</div>
            </div>
            <span class="badge badge-info">{{ formatDate(e.starts_at) }}</span>
          </div>
          <div v-if="upcomingExams.length === 0" class="empty-state" style="padding:20px">
            <div>📝</div><p>No upcoming exams</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Student Modal -->
    <div v-if="showAddStudent" class="modal-overlay" @click.self="showAddStudent = false">
      <div class="modal">
        <div class="modal-header">
          <div class="modal-title">Enroll New Student</div>
          <button class="btn btn-ghost btn-sm" @click="showAddStudent = false">×</button>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">First Name</label><input v-model="newStudent.first_name" class="form-input" placeholder="First name" /></div>
          <div class="form-group"><label class="form-label">Last Name</label><input v-model="newStudent.last_name" class="form-input" placeholder="Last name" /></div>
        </div>
        <div class="form-group"><label class="form-label">Email</label><input v-model="newStudent.email" class="form-input" type="email" placeholder="student@email.com" /></div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Date of Birth</label><input v-model="newStudent.date_of_birth" class="form-input" type="date" /></div>
          <div class="form-group"><label class="form-label">Gender</label>
            <select v-model="newStudent.gender" class="form-input"><option value="male">Male</option><option value="female">Female</option></select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Class</label>
            <select v-model="newStudent.school_class_id" class="form-input">
              <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
          <div class="form-group"><label class="form-label">Admission Date</label><input v-model="newStudent.admission_date" class="form-input" type="date" /></div>
        </div>
        <div style="display:flex;gap:10px;margin-top:8px">
          <button class="btn btn-blue" @click="addStudent" :disabled="addingStudent">
            <span v-if="addingStudent" class="spinner"></span>
            <span v-else>Enroll Student</span>
          </button>
          <button class="btn btn-ghost" @click="showAddStudent = false">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { Line, Doughnut } from 'vue-chartjs'
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, ArcElement, Tooltip, Legend, Filler } from 'chart.js'
import { useAuthStore } from '../../store/auth'
import { dashboardAPI, studentsAPI, examsAPI, classesAPI } from '../../services/api'
import { useToast } from 'vue-toastification'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, ArcElement, Tooltip, Legend, Filler)

const auth    = useAuthStore()
const toast   = useToast()
const loading = ref(true)
const loadingStudents = ref(true)
const loadingExams    = ref(true)
const showAddStudent  = ref(false)
const addingStudent   = ref(false)

const stats          = ref([])
const recentStudents = ref([])
const upcomingExams  = ref([])
const classes        = ref([])

const newStudent = reactive({ first_name:'', last_name:'', email:'', date_of_birth:'', gender:'male', school_class_id:'', admission_date: new Date().toISOString().split('T')[0] })

const enrollmentChart = ref({})
const feeChart        = ref({})

const lineOpts = {
  responsive: true, maintainAspectRatio: false,
  plugins: { legend: { labels: { color: '#94a3b8', font: { family: 'DM Sans', size: 11 } } } },
  scales: {
    x: { grid: { color: 'rgba(30,45,71,0.5)' }, ticks: { color: '#64748b', font: { size: 11 } } },
    y: { grid: { color: 'rgba(30,45,71,0.5)' }, ticks: { color: '#64748b', font: { size: 11 } } },
  }
}

const doughnutOpts = {
  responsive: true, maintainAspectRatio: false,
  plugins: { legend: { position: 'bottom', labels: { color: '#94a3b8', font: { family: 'DM Sans', size: 11 }, padding: 16 } } }
}

function initials(fn, ln) { return ((fn?.[0] || '') + (ln?.[0] || '')).toUpperCase() }
function formatDate(d) { return d ? new Date(d).toLocaleDateString('en-GB', { day:'2-digit', month:'short' }) : '—' }

onMounted(async () => {
  try {
    const [dashRes, stuRes, examRes, classRes] = await Promise.all([
      dashboardAPI.index(),
      studentsAPI.list({ per_page: 5 }),
      examsAPI.list({ status: 'published' }),
      classesAPI.list(),
    ])

    const d = dashRes.data
    stats.value = [
      { icon:'🎒', color:'blue',   label:'Total Students',    value: d.students?.toLocaleString() || '1,284', change:'↑ 48 new this term', changeType:'up' },
      { icon:'👨‍🏫', color:'green',  label:'Active Teachers',   value: d.teachers || 64, change:'↑ 3 hired', changeType:'up' },
      { icon:'✅', color:'yellow', label:"Today's Attendance", value: (d.attendance_rate || 91.4) + '%', change:'↑ 2.1% vs last week', changeType:'up' },
      { icon:'📝', color:'purple', label:'Exams This Term',    value: d.upcoming_exams || 18 },
      { icon:'💰', color:'pink',   label:'Fees Collected',     value: '₦' + ((d.fees_collected || 42800000) / 1e6).toFixed(1) + 'M', change:'↑ 78% collected', changeType:'up' },
      { icon:'📚', color:'red',    label:'Overdue Books',      value: d.overdue_books || 34 },
    ]

    const trend = d.enrollment_trend || {}
    enrollmentChart.value = {
      labels: trend.labels || ['Sep','Oct','Nov','Dec','Jan','Feb','Mar'],
      datasets: [
        { label:'Students', data: trend.data || [1180,1195,1210,1220,1252,1271,1284], borderColor:'#3b82f6', backgroundColor:'rgba(59,130,246,0.1)', fill:true, tension:0.4, pointRadius:4 },
      ]
    }

    const fee = d.fee_summary || {}
    feeChart.value = {
      labels: ['Paid','Partial','Unpaid'],
      datasets: [{ data: [fee.paid || 820, fee.partial || 284, fee.unpaid || 180], backgroundColor:['#10b981','#f59e0b','#ef4444'], borderWidth:0, hoverOffset:8 }]
    }

    recentStudents.value = stuRes.data?.data || []
    upcomingExams.value  = examRes.data?.data || []
    classes.value        = classRes.data || []
    loading.value = false
    loadingStudents.value = false
    loadingExams.value    = false
  } catch (e) {
    loading.value = false
    loadingStudents.value = false
    loadingExams.value    = false
  }
})

async function addStudent() {
  addingStudent.value = true
  try {
    await studentsAPI.create(newStudent)
    toast.success('Student enrolled successfully!')
    showAddStudent.value = false
    Object.assign(newStudent, { first_name:'', last_name:'', email:'', date_of_birth:'', gender:'male', school_class_id:'', admission_date: new Date().toISOString().split('T')[0] })
  } catch (e) {
    toast.error(e.response?.data?.message || 'Failed to enroll student')
  } finally {
    addingStudent.value = false
  }
}
</script>
