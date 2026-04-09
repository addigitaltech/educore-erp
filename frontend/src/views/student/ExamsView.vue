<!-- ExamsView student -->
<template>
  <div>
    <div class="page-header"><div><h2>My Exams</h2><p>Upcoming and past examinations</p></div></div>
    <div class="tabs"><button :class="['tab',{active:tab==='upcoming'}]" @click="tab='upcoming'">Upcoming</button><button :class="['tab',{active:tab==='past'}]" @click="tab='past'">Past Exams</button></div>
    <div v-if="loading" class="loading-overlay"><div class="spinner"></div></div>
    <div v-else style="display:flex;flex-direction:column;gap:14px">
      <div v-for="e in filteredExams" :key="e.id" class="card" style="margin-bottom:0">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
          <div style="display:flex;gap:16px;align-items:center">
            <div style="width:48px;height:48px;border-radius:12px;background:rgba(59,130,246,0.15);display:flex;align-items:center;justify-content:center;font-size:24px">📝</div>
            <div><div style="font-size:15px;font-weight:700">{{ e.title }}</div><div style="font-size:12px;color:var(--text2);margin-top:4px">{{ e.subject?.name }} · {{ e.duration_minutes }} min · {{ e.num_questions }} questions</div></div>
          </div>
          <div style="display:flex;align-items:center;gap:12px">
            <span :class="['badge',e.status==='published'?'badge-info':'badge-success']">{{ e.status }}</span>
            <span style="font-size:13px;font-weight:600">{{ formatDate(e.starts_at) }}</span>
            <router-link v-if="e.status==='published'" :to="`/student/exam/${e.id}/take`" class="btn btn-blue btn-sm">Take Exam</router-link>
            <router-link v-else to="/student/results" class="btn btn-ghost btn-sm">View Result</router-link>
          </div>
        </div>
      </div>
      <div v-if="filteredExams.length===0" class="empty-state"><div class="empty-icon">📝</div><h3>No exams found</h3></div>
    </div>
  </div>
</template>
<script setup>
import { ref, computed, onMounted } from 'vue'
import { examsAPI } from '../../services/api'
const loading=ref(true),exams=ref([]),tab=ref('upcoming')
const filteredExams=computed(()=>exams.value.filter(e=>tab.value==='upcoming'?e.status==='published':e.status==='completed'))
function formatDate(d){return d?new Date(d).toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'}):'—'}
onMounted(async()=>{loading.value=true;try{const r=await examsAPI.list({});exams.value=r.data.data||[]}finally{loading.value=false}})
</script>
