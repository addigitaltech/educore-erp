<template>
  <div>
    <div class="page-header"><div><h2>Global Analytics</h2><p>Platform-wide performance metrics across all schools</p></div></div>
    <div class="stats-grid" style="grid-template-columns:repeat(4,1fr)">
      <div class="stat-card"><div class="stat-icon blue">📈</div><div class="stat-value">76.8%</div><div class="stat-label">Avg Pass Rate</div></div>
      <div class="stat-card"><div class="stat-icon green">✅</div><div class="stat-value">89.2%</div><div class="stat-label">Avg Attendance</div></div>
      <div class="stat-card"><div class="stat-icon yellow">📝</div><div class="stat-value">24,841</div><div class="stat-label">Exams Taken</div></div>
      <div class="stat-card"><div class="stat-icon purple">🏆</div><div class="stat-value">3.68</div><div class="stat-label">Avg School GPA</div></div>
    </div>
    <div class="grid-2">
      <div class="card" style="margin-bottom:0">
        <div class="card-header"><div class="card-title">School Performance Comparison</div></div>
        <Bar :data="schoolPerfChart" :options="barOpts" style="height:260px"/>
      </div>
      <div class="card" style="margin-bottom:0">
        <div class="card-header"><div class="card-title">Subscription Plans</div></div>
        <Doughnut :data="subChart" :options="doughnutOpts" style="height:260px"/>
      </div>
    </div>
    <div class="card" style="margin-top:20px">
      <div class="card-header"><div class="card-title">School-by-School Summary</div></div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>School</th><th>Students</th><th>Pass Rate</th><th>Attendance</th><th>Revenue</th><th>Plan</th></tr></thead>
          <tbody>
            <tr v-for="s in schoolStats" :key="s.name">
              <td style="font-weight:500">{{ s.name }}</td>
              <td>{{ s.students.toLocaleString() }}</td>
              <td><div style="display:flex;align-items:center;gap:8px"><div class="progress" style="width:60px"><div class="progress-bar" :style="{width:s.pass+'%',background:'var(--accent)'}"></div></div>{{ s.pass }}%</div></td>
              <td><span :class="['badge',s.att>=90?'badge-success':s.att>=80?'badge-warning':'badge-danger']">{{ s.att }}%</span></td>
              <td style="color:var(--green2)">₦{{ s.rev }}M</td>
              <td><span :class="['badge',s.plan==='enterprise'?'badge-purple':s.plan==='professional'?'badge-info':'badge-warning']">{{ s.plan }}</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
<script setup>
import { Bar, Doughnut } from 'vue-chartjs'
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, ArcElement, Tooltip, Legend } from 'chart.js'
ChartJS.register(CategoryScale,LinearScale,BarElement,ArcElement,Tooltip,Legend)
const barOpts={responsive:true,maintainAspectRatio:false,plugins:{legend:{labels:{color:'#94a3b8',font:{family:'DM Sans',size:11}}}},scales:{x:{grid:{color:'rgba(30,45,71,0.5)'},ticks:{color:'#64748b',font:{size:11}}},y:{grid:{color:'rgba(30,45,71,0.5)'},ticks:{color:'#64748b',font:{size:11},callback:v=>v+'%'},max:100}}}
const doughnutOpts={responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom',labels:{color:'#94a3b8',padding:14}}}}
const schoolPerfChart={labels:['Greenfield','Sunrise','Horizon','Unity High','Excellence'],datasets:[{label:'Pass Rate (%)',data:[78,74,71,80,68],backgroundColor:'rgba(59,130,246,0.6)',borderRadius:6}]}
const subChart={labels:['Enterprise (4)','Professional (5)','Starter (3)'],datasets:[{data:[4,5,3],backgroundColor:['#8b5cf6','#3b82f6','#f59e0b'],borderWidth:0,hoverOffset:8}]}
const schoolStats=[
  {name:'Greenfield Academy',students:1284,pass:78,att:91,rev:42.8,plan:'enterprise'},
  {name:'Sunrise International',students:982,pass:74,att:88,rev:35.2,plan:'professional'},
  {name:'Horizon College',students:756,pass:71,att:86,rev:28.4,plan:'professional'},
  {name:'Unity High School',students:1423,pass:80,att:93,rev:48.6,plan:'enterprise'},
  {name:'Excellence Prep',students:542,pass:68,att:85,rev:19.4,plan:'starter'},
]
</script>
