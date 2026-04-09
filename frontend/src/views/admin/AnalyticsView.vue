<template>
  <div>
    <div class="page-header">
      <div><h2>Analytics</h2><p>School performance insights and trends</p></div>
      <button class="btn btn-blue">📊 Export Report</button>
    </div>
    <div class="stats-grid" style="grid-template-columns:repeat(4,1fr)">
      <div class="stat-card"><div class="stat-icon blue">📈</div><div class="stat-value">78.4%</div><div class="stat-label">Avg Pass Rate</div><div class="stat-change up">↑ 4.2% vs last term</div></div>
      <div class="stat-card"><div class="stat-icon green">✅</div><div class="stat-value">91.4%</div><div class="stat-label">Attendance Rate</div><div class="stat-change up">↑ 1.8%</div></div>
      <div class="stat-card"><div class="stat-icon yellow">🎯</div><div class="stat-value">3.72</div><div class="stat-label">School GPA Avg</div><div class="stat-change up">↑ 0.14</div></div>
      <div class="stat-card"><div class="stat-icon purple">🏆</div><div class="stat-value">94.2%</div><div class="stat-label">Exam Completion</div></div>
    </div>
    <div class="grid-2">
      <div class="card" style="margin-bottom:0">
        <div class="card-header"><div class="card-title">Subject Performance</div><div class="card-subtitle">Average scores by subject — 2nd Term</div></div>
        <Bar :data="subjectChart" :options="barOpts" style="height:260px"/>
      </div>
      <div class="card" style="margin-bottom:0">
        <div class="card-header"><div class="card-title">Grade Distribution</div></div>
        <Doughnut :data="gradeChart" :options="doughnutOpts" style="height:260px"/>
      </div>
    </div>
    <div class="card" style="margin-top:20px">
      <div class="card-header"><div class="card-title">Performance Trend — Last 5 Terms</div></div>
      <Line :data="trendChart" :options="lineOpts" style="height:220px"/>
    </div>
  </div>
</template>
<script setup>
import { Bar, Doughnut, Line } from 'vue-chartjs'
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, PointElement, LineElement, ArcElement, Tooltip, Legend, Filler } from 'chart.js'
ChartJS.register(CategoryScale,LinearScale,BarElement,PointElement,LineElement,ArcElement,Tooltip,Legend,Filler)
const c='#94a3b8',g={color:'rgba(30,45,71,0.5)'},t={color:'#64748b',font:{family:'DM Sans',size:11}}
const barOpts={responsive:true,maintainAspectRatio:false,plugins:{legend:{labels:{color:c,font:{family:'DM Sans',size:11}}}},scales:{x:{grid:g,ticks:t},y:{grid:g,ticks:t,max:100}}}
const doughnutOpts={responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom',labels:{color:c,font:{family:'DM Sans',size:11},padding:12}}}}
const lineOpts={responsive:true,maintainAspectRatio:false,plugins:{legend:{labels:{color:c,font:{family:'DM Sans',size:11}}}},scales:{x:{grid:g,ticks:t},y:{grid:g,ticks:{...t,callback:v=>v+'%'},min:65,max:85}}}
const subjectChart={labels:['Math','English','Physics','Chemistry','Biology','Economics','Gov','Geo'],datasets:[{label:'Class Average',data:[78,72,81,74,85,65,71,68],backgroundColor:['#3b82f699','#10b98199','#f59e0b99','#8b5cf699','#ec489999','#14b8a699','#ef444499','#6366f199'],borderRadius:6}]}
const gradeChart={labels:['A (70-100)','B (60-69)','C (50-59)','D (45-49)','F (<45)'],datasets:[{data:[312,498,284,108,82],backgroundColor:['#10b981','#3b82f6','#f59e0b','#8b5cf6','#ef4444'],borderWidth:0,hoverOffset:8}]}
const trendChart={labels:['1st 24/25','2nd 24/25','3rd 24/25','1st 25/26','2nd 25/26'],datasets:[{label:'School Average',data:[71,73,75,76,78.4],borderColor:'#3b82f6',backgroundColor:'rgba(59,130,246,0.1)',fill:true,tension:0.4,pointRadius:5},{label:'Target (80%)',data:[80,80,80,80,80],borderColor:'#ef4444',borderDash:[5,5],pointRadius:0}]}
</script>
