<!-- Student DashboardView -->
<template>
  <div>
    <div class="page-header"><div><h2>Good morning, {{ auth.user?.first_name }} 👋</h2><p>Your academic overview for this term</p></div></div>
    <div class="stats-grid">
      <div class="stat-card"><div class="stat-icon blue">📊</div><div class="stat-value">{{ stats.average||90.8 }}%</div><div class="stat-label">Term Average</div><div class="stat-change up">↑ 3rd in class</div></div>
      <div class="stat-card"><div class="stat-icon green">✅</div><div class="stat-value">{{ stats.attendance_rate||95.4 }}%</div><div class="stat-label">Attendance</div></div>
      <div class="stat-card"><div class="stat-icon yellow">📝</div><div class="stat-value">{{ stats.upcoming_exams||2 }}</div><div class="stat-label">Upcoming Exams</div></div>
      <div class="stat-card"><div class="stat-icon purple">📚</div><div class="stat-value">{{ stats.books_borrowed||3 }}</div><div class="stat-label">Books Borrowed</div></div>
    </div>
    <div class="grid-21">
      <div class="card" style="margin-bottom:0">
        <div class="card-header"><div class="card-title">My Subject Performance</div></div>
        <Radar :data="radarData" :options="radarOpts" style="height:260px"/>
      </div>
      <div style="display:flex;flex-direction:column;gap:16px">
        <div class="card" style="margin-bottom:0">
          <div class="card-header"><div class="card-title">Next Exam</div></div>
          <div style="text-align:center;padding:10px 0">
            <div style="font-size:32px;margin-bottom:8px">📝</div>
            <div style="font-size:16px;font-weight:700">Mathematics Mid-Term</div>
            <div style="font-size:12px;color:var(--text2);margin:6px 0">March 20, 2026 · 90 min</div>
            <div style="font-family:'JetBrains Mono',monospace;font-size:22px;font-weight:700;color:var(--accent2);margin:12px 0">3 days left</div>
            <router-link to="/student/exams" class="btn btn-blue" style="width:100%;justify-content:center">View Exams</router-link>
          </div>
        </div>
        <div class="card" style="margin-bottom:0">
          <div class="card-header"><div class="card-title">Quick Stats</div></div>
          <div v-for="item in quickStats" :key="item.label" style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border);font-size:12px">
            <span style="color:var(--text3)">{{ item.label }}</span>
            <span style="font-weight:600">{{ item.value }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue'
import { Radar } from 'vue-chartjs'
import { Chart as ChartJS, RadialLinearScale, PointElement, LineElement, Filler, Tooltip, Legend } from 'chart.js'
ChartJS.register(RadialLinearScale,PointElement,LineElement,Filler,Tooltip,Legend)
import { useAuthStore } from '../../store/auth'
import { dashboardAPI } from '../../services/api'
const auth=useAuthStore(),stats=ref({})
const quickStats=[{label:'Position in Class',value:'3rd / 42'},{label:'Total Score',value:'726/800'},{label:'Best Subject',value:'Biology (93%)'},{label:'Needs Work',value:'Economics (65%)'}]
const radarData={labels:['Math','English','Physics','Chemistry','Biology','Economics'],datasets:[{label:'Your Score',data:[90,79,80,74,93,65],backgroundColor:'rgba(59,130,246,0.2)',borderColor:'#3b82f6',pointBackgroundColor:'#3b82f6',pointRadius:4}]}
const radarOpts={responsive:true,maintainAspectRatio:false,plugins:{legend:{labels:{color:'#94a3b8'}}},scales:{r:{grid:{color:'rgba(30,45,71,0.5)'},angleLines:{color:'rgba(30,45,71,0.5)'},ticks:{color:'#64748b',backdropColor:'transparent',font:{size:10}},pointLabels:{color:'#94a3b8',font:{size:11}},min:0,max:100}}}
onMounted(async()=>{try{const r=await dashboardAPI.index();stats.value=r.data||{}}catch{}})
</script>
