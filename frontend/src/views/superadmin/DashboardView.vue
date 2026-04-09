<template>
  <div>
    <div class="page-header"><div><h2>Platform Overview 🌐</h2><p>EduCore multi-school platform statistics</p></div></div>
    <div class="stats-grid">
      <div class="stat-card"><div class="stat-icon blue">🏫</div><div class="stat-value">{{ stats.schools||12 }}</div><div class="stat-label">Active Schools</div><div class="stat-change up">↑ 3 new this month</div></div>
      <div class="stat-card"><div class="stat-icon green">🎒</div><div class="stat-value">{{ Number(stats.students||18472).toLocaleString() }}</div><div class="stat-label">Total Students</div><div class="stat-change up">↑ 1.2K this month</div></div>
      <div class="stat-card"><div class="stat-icon yellow">👨‍🏫</div><div class="stat-value">{{ stats.teachers||842 }}</div><div class="stat-label">Total Teachers</div></div>
      <div class="stat-card"><div class="stat-icon purple">💰</div><div class="stat-value">₦{{ ((stats.revenue||582000000)/1e6).toFixed(0) }}M</div><div class="stat-label">Platform Revenue</div><div class="stat-change up">↑ 18% YoY</div></div>
      <div class="stat-card"><div class="stat-icon pink">📝</div><div class="stat-value">2,841</div><div class="stat-label">Exams This Month</div></div>
      <div class="stat-card"><div class="stat-icon teal">⚡</div><div class="stat-value">99.8%</div><div class="stat-label">Platform Uptime</div></div>
    </div>
    <div class="card">
      <div class="card-header"><div class="card-title">Platform Growth — 2025</div></div>
      <Line v-if="chartReady" :data="growthChart" :options="lineOpts" style="height:250px"/>
    </div>
    <div class="card">
      <div class="card-header"><div class="card-title">School Overview</div><router-link to="/superadmin/schools" class="btn btn-ghost btn-sm">View All Schools</router-link></div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>School</th><th>State</th><th>Plan</th><th>Students</th><th>Status</th></tr></thead>
          <tbody>
            <tr v-for="s in schools" :key="s.id">
              <td style="font-weight:500">{{ s.name }}</td>
              <td style="font-size:12px;color:var(--text2)">{{ s.state }}</td>
              <td><span :class="['badge',s.plan==='enterprise'?'badge-purple':s.plan==='professional'?'badge-info':'badge-warning']">{{ s.plan }}</span></td>
              <td>{{ Number(s.students_count||0).toLocaleString() }}</td>
              <td><span :class="['badge',s.status==='active'?'badge-success':'badge-warning']">{{ s.status }}</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue'
import { Line } from 'vue-chartjs'
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Filler, Tooltip, Legend } from 'chart.js'
ChartJS.register(CategoryScale,LinearScale,PointElement,LineElement,Filler,Tooltip,Legend)
import { dashboardAPI, schoolsAPI } from '../../services/api'
const stats=ref({}),schools=ref([]),chartReady=ref(false)
const lineOpts={responsive:true,maintainAspectRatio:false,plugins:{legend:{labels:{color:'#94a3b8',font:{family:'DM Sans',size:11}}}},scales:{x:{grid:{color:'rgba(30,45,71,0.5)'},ticks:{color:'#64748b',font:{size:11}}},y:{grid:{color:'rgba(30,45,71,0.5)'},ticks:{color:'#64748b',font:{size:11}}}}}
const growthChart={labels:['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],datasets:[{label:'Students',data:[14200,14800,15400,15600,15900,16100,16400,16800,17200,17800,18100,18472],borderColor:'#3b82f6',backgroundColor:'rgba(59,130,246,0.1)',fill:true,tension:0.4},{label:'Schools',data:[8,8,9,9,9,10,10,11,11,12,12,12],borderColor:'#10b981',tension:0.4,yAxisID:'y2'}]}
onMounted(async()=>{
  try{
    const[d,s]=await Promise.all([dashboardAPI.index(),schoolsAPI.list({})])
    stats.value=d.data||{}
    schools.value=s.data?.data||[]
  }catch{
    schools.value=[{id:1,name:'Greenfield Academy',state:'Lagos',plan:'enterprise',students_count:1284,status:'active'},{id:2,name:'Sunrise International',state:'Abuja',plan:'professional',students_count:982,status:'active'}]
  }
  chartReady.value=true
})
</script>
