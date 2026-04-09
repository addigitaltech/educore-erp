<template>
  <div>
    <div class="page-header"><div><h2>Student Performance</h2><p>Track academic progress of your students</p></div>
      <select v-model="classId" class="form-input" style="max-width:160px" @change="loadResults">
        <option value="">Select Class</option>
        <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
      </select>
    </div>
    <div v-if="classId" class="card" style="margin-bottom:20px">
      <Bar :data="chartData" :options="barOpts" style="height:260px"/>
    </div>
    <div class="card">
      <div v-if="loading" class="loading-overlay"><div class="spinner"></div></div>
      <div v-else-if="!classId" class="empty-state"><div class="empty-icon">📈</div><h3>Select a class</h3><p>Choose a class to view performance</p></div>
      <div v-else class="table-wrap">
        <table>
          <thead><tr><th>Student</th><th>Avg CA</th><th>Avg Exam</th><th>Total Avg</th><th>Grade</th><th>Rank</th></tr></thead>
          <tbody>
            <tr v-for="(s,i) in studentStats" :key="s.id">
              <td><div style="display:flex;align-items:center;gap:10px"><div class="avatar avatar-sm">{{ ((s.name?.[0]||'')).toUpperCase() }}</div>{{ s.name }}</div></td>
              <td>{{ s.avgCA }}</td><td>{{ s.avgExam }}</td>
              <td style="font-weight:700" :style="{color:s.avg>=70?'var(--green2)':s.avg>=60?'var(--accent2)':'var(--yellow)'}">{{ s.avg }}</td>
              <td><span :class="['badge',s.avg>=70?'badge-success':s.avg>=60?'badge-info':'badge-warning']">{{ s.grade }}</span></td>
              <td><span :class="['badge',i<3?'badge-success':'badge-info']">#{{ i+1 }}</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, computed, onMounted } from 'vue'
import { Bar } from 'vue-chartjs'
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Tooltip, Legend } from 'chart.js'
ChartJS.register(CategoryScale,LinearScale,BarElement,Tooltip,Legend)
import { classesAPI, resultsAPI } from '../../services/api'
const loading=ref(false),classes=ref([]),results=ref([]),classId=ref('')
const barOpts={responsive:true,maintainAspectRatio:false,plugins:{legend:{labels:{color:'#94a3b8',font:{family:'DM Sans',size:11}}}},scales:{x:{grid:{color:'rgba(30,45,71,0.5)'},ticks:{color:'#64748b',font:{size:11}}},y:{grid:{color:'rgba(30,45,71,0.5)'},ticks:{color:'#64748b',font:{size:11}},max:100}}}
const studentStats=computed(()=>{
  const map={}
  results.value.forEach(r=>{
    const key=r.student_id
    if(!map[key]){map[key]={id:key,name:`${r.student?.user?.first_name||''} ${r.student?.user?.last_name||''}`,cas:[],exams:[]}}
    map[key].cas.push(r.ca_score);map[key].exams.push(r.exam_score)
  })
  return Object.values(map).map(s=>{
    const avgCA=s.cas.length?Math.round(s.cas.reduce((a,b)=>a+b,0)/s.cas.length):0
    const avgExam=s.exams.length?Math.round(s.exams.reduce((a,b)=>a+b,0)/s.exams.length):0
    const avg=avgCA+avgExam
    const grade=avg>=70?'A':avg>=60?'B':avg>=50?'C':'F'
    return{...s,avgCA,avgExam,avg,grade}
  }).sort((a,b)=>b.avg-a.avg)
})
const chartData=computed(()=>({
  labels:studentStats.value.map(s=>s.name.split(' ')[0]),
  datasets:[{label:'Total Average',data:studentStats.value.map(s=>s.avg),backgroundColor:'rgba(59,130,246,0.6)',borderRadius:6}]
}))
onMounted(async()=>{const r=await classesAPI.list();classes.value=r.data||[]})
async function loadResults(){if(!classId.value)return;loading.value=true;try{const r=await resultsAPI.list({class_id:classId.value});results.value=r.data.data||[]}finally{loading.value=false}}
</script>
