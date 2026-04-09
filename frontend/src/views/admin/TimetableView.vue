<!-- TimetableView.vue -->
<template>
  <div>
    <div class="page-header">
      <div><h2>Timetable</h2><p>Weekly class schedule</p></div>
      <div style="display:flex;gap:10px">
        <select v-model="classId" class="form-input" style="max-width:160px" @change="fetchTimetable">
          <option value="">All Classes</option>
          <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
        </select>
        <button class="btn btn-ghost">🖨️ Print</button>
      </div>
    </div>
    <div class="card">
      <div v-if="loading" class="loading-overlay"><div class="spinner"></div></div>
      <div v-else style="overflow-x:auto">
        <table style="min-width:700px">
          <thead>
            <tr>
              <th style="width:90px">Period</th>
              <th v-for="d in days" :key="d" style="text-align:center">{{ d }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in periods" :key="p.label">
              <td style="font-size:11px;color:var(--text3);font-weight:600">{{ p.label }}</td>
              <td v-for="d in days" :key="d" style="text-align:center;padding:6px">
                <template v-if="p.isBreak">
                  <span style="color:var(--text3);font-size:11px">{{ p.label }}</span>
                </template>
                <template v-else>
                  <div v-if="getSlot(d,p.period)" class="period-cell" :style="{background:subjectBg(getSlot(d,p.period)?.subject?.name),color:subjectColor(getSlot(d,p.period)?.subject?.name)}">
                    {{ getSlot(d,p.period)?.subject?.name }}
                  </div>
                  <div v-else class="period-cell" style="background:var(--bg3);color:var(--text3);font-size:10px">Free</div>
                </template>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue'
import { timetableAPI, classesAPI } from '../../services/api'
const loading = ref(true), timetable = ref([]), classes = ref([])
const classId = ref('')
const days = ['Monday','Tuesday','Wednesday','Thursday','Friday']
const periods = [
  {label:'8:00–9:00',period:1},{label:'9:00–10:00',period:2},{label:'10:00–11:00',period:3},
  {label:'Break',isBreak:true},
  {label:'11:30–12:30',period:4},{label:'12:30–1:30',period:5},
  {label:'Lunch',isBreak:true},
  {label:'2:00–3:00',period:6}
]
const COLORS = {'Mathematics':'#3b82f6','English':'#10b981','Physics':'#f59e0b','Chemistry':'#8b5cf6','Biology':'#ec4899','Economics':'#14b8a6','Government':'#ef4444','Geography':'#6366f1'}
function subjectBg(n) { return (COLORS[n]||'#64748b')+'26' }
function subjectColor(n) { return COLORS[n]||'#94a3b8' }
function getSlot(day, period) { return timetable.value.find(t=>t.day_of_week===day.toLowerCase()&&t.period_number===period) }
onMounted(async () => { const [_,c] = await Promise.all([fetchTimetable(),classesAPI.list()]); classes.value=c.data||[] })
async function fetchTimetable() { loading.value=true; try { const r=await timetableAPI.list(classId.value?{class_id:classId.value}:{}); timetable.value=r.data||[] } finally { loading.value=false } }
</script>
<style scoped>
.period-cell { padding:6px 8px; border-radius:8px; font-size:11px; font-weight:500; white-space:nowrap; }
</style>
