<!-- ResultsView student -->
<template>
  <div>
    <div class="page-header"><div><h2>My Results</h2><p>Academic performance this term</p></div><router-link to="/student/report-card" class="btn btn-blue">📄 Full Report Card</router-link></div>
    <div v-if="loading" class="loading-overlay"><div class="spinner"></div></div>
    <template v-else>
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:20px">
        <div class="stat-card"><div class="stat-icon blue">📊</div><div class="stat-value">{{ totalScore }}/800</div><div class="stat-label">Total Score</div></div>
        <div class="stat-card"><div class="stat-icon green">📈</div><div class="stat-value">{{ average }}%</div><div class="stat-label">Average</div></div>
        <div class="stat-card"><div class="stat-icon yellow">🏆</div><div class="stat-value">3rd</div><div class="stat-label">Position</div></div>
        <div class="stat-card"><div class="stat-icon purple">🎓</div><div class="stat-value">A</div><div class="stat-label">Grade</div></div>
      </div>
      <div class="card">
        <div class="card-header"><div class="card-title">Subject Results — 2nd Term 2025/26</div></div>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Subject</th><th>CA (30)</th><th>Exam (70)</th><th>Total</th><th>Grade</th><th>Remark</th></tr></thead>
            <tbody>
              <tr v-for="r in results" :key="r.id">
                <td style="font-weight:500">{{ r.subject?.name }}</td>
                <td>{{ r.ca_score }}</td><td>{{ r.exam_score }}</td>
                <td style="font-weight:700;font-size:15px" :style="{color:r.total_score>=70?'var(--green2)':r.total_score>=60?'var(--accent2)':'var(--yellow)'}">{{ r.total_score }}</td>
                <td><span :class="['badge',r.grade==='A'?'badge-success':r.grade==='B'?'badge-info':'badge-warning']">{{ r.grade }}</span></td>
                <td style="font-size:12px;color:var(--text2)">{{ r.remark }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>
  </div>
</template>
<script setup>
import { ref, computed, onMounted } from 'vue'
import { resultsAPI } from '../../services/api'
import { useAuthStore } from '../../store/auth'
const auth=useAuthStore(),loading=ref(true),results=ref([])
const totalScore=computed(()=>results.value.reduce((a,r)=>a+(r.total_score||0),0))
const average=computed(()=>results.value.length?Math.round(totalScore.value/results.value.length):0)
onMounted(async()=>{loading.value=true;try{const r=await resultsAPI.list({});results.value=r.data.data||[]}finally{loading.value=false}})
</script>
