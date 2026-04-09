<template>
  <div>
    <div class="page-header"><div><h2>My Attendance</h2><p>Your attendance record this term</p></div></div>
    <div class="stats-grid" style="grid-template-columns:repeat(4,1fr)">
      <div class="stat-card"><div class="stat-icon green">✅</div><div class="stat-value">{{ present }}</div><div class="stat-label">Days Present</div></div>
      <div class="stat-card"><div class="stat-icon red">❌</div><div class="stat-value">{{ absent }}</div><div class="stat-label">Days Absent</div></div>
      <div class="stat-card"><div class="stat-icon yellow">⏰</div><div class="stat-value">{{ late }}</div><div class="stat-label">Late Arrivals</div></div>
      <div class="stat-card"><div class="stat-icon blue">📊</div><div class="stat-value">{{ rate }}%</div><div class="stat-label">Attendance Rate</div></div>
    </div>
    <div class="card">
      <div class="card-header"><div class="card-title">March 2026 — Attendance Calendar</div>
        <div style="display:flex;gap:10px;font-size:11px;color:var(--text2)">
          <span>🟩 Present</span><span>🟥 Absent</span><span>🟨 Late</span>
        </div>
      </div>
      <div class="att-grid">
        <div v-for="d in dayHeaders" :key="d" class="att-cell header">{{ d }}</div>
        <div v-for="(cell, i) in calendar" :key="i" :class="['att-cell', cell.type]" :title="cell.date">
          {{ cell.day || '' }}
        </div>
      </div>
      <div style="margin-top:16px;display:flex;justify-content:space-around;font-size:13px;padding:12px;background:var(--card2);border-radius:10px">
        <span style="color:var(--green2)">✓ Present: {{ present }}</span>
        <span style="color:#fca5a5">✗ Absent: {{ absent }}</span>
        <span style="color:#fcd34d">~ Late: {{ late }}</span>
        <span style="color:var(--accent2)">Rate: {{ rate }}%</span>
      </div>
    </div>
    <div class="card">
      <div class="card-header"><div class="card-title">Attendance Log</div></div>
      <div v-if="loading" class="loading-overlay"><div class="spinner"></div></div>
      <div v-else class="table-wrap">
        <table>
          <thead><tr><th>Date</th><th>Day</th><th>Status</th><th>Remark</th></tr></thead>
          <tbody>
            <tr v-for="a in records" :key="a.id">
              <td style="font-size:12px">{{ a.date }}</td>
              <td style="font-size:12px;color:var(--text2)">{{ dayName(a.date) }}</td>
              <td><span :class="['badge',a.status==='present'?'badge-success':a.status==='late'?'badge-warning':'badge-danger']">{{ a.status }}</span></td>
              <td style="font-size:12px;color:var(--text3)">{{ a.remark || '—' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, computed, onMounted } from 'vue'
import { attendanceAPI } from '../../services/api'
const loading = ref(true), records = ref([])
const dayHeaders = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat']

// Build calendar for March 2026
const calendar = computed(() => {
  const cells = []
  const firstDay = new Date(2026, 2, 1).getDay() // March starts on Sunday=0
  for (let i = 0; i < firstDay; i++) cells.push({ type: 'empty' })
  for (let d = 1; d <= 31; d++) {
    const date = `2026-03-${String(d).padStart(2,'0')}`
    const rec = records.value.find(r => r.date === date)
    const dow = new Date(2026, 2, d).getDay()
    if (dow === 0 || dow === 6) cells.push({ day: d, type: 'weekend', date })
    else cells.push({ day: d, type: rec ? rec.status : 'no-class', date })
  }
  return cells
})

const present = computed(() => records.value.filter(r => r.status === 'present').length)
const absent  = computed(() => records.value.filter(r => r.status === 'absent').length)
const late    = computed(() => records.value.filter(r => r.status === 'late').length)
const rate    = computed(() => {
  const total = present.value + absent.value + late.value
  return total > 0 ? Math.round(((present.value + late.value) / total) * 100) : 0
})

function dayName(d) { return d ? new Date(d).toLocaleDateString('en-US', { weekday: 'long' }) : '' }

onMounted(async () => {
  loading.value = true
  try {
    const r = await attendanceAPI.byStudent(1)
    records.value = r.data || []
  } catch {
    // demo fallback
    records.value = [
      { id:1, date:'2026-03-02', status:'present', remark:'' },
      { id:2, date:'2026-03-03', status:'present', remark:'' },
      { id:3, date:'2026-03-04', status:'late', remark:'Traffic' },
      { id:4, date:'2026-03-05', status:'present', remark:'' },
      { id:5, date:'2026-03-06', status:'absent', remark:'Sick' },
      { id:6, date:'2026-03-09', status:'present', remark:'' },
      { id:7, date:'2026-03-10', status:'present', remark:'' },
      { id:8, date:'2026-03-11', status:'present', remark:'' },
      { id:9, date:'2026-03-12', status:'present', remark:'' },
      { id:10, date:'2026-03-13', status:'present', remark:'' },
    ]
  } finally { loading.value = false }
})
</script>
<style scoped>
.att-grid { display:grid; grid-template-columns:repeat(7,1fr); gap:4px; }
.att-cell { aspect-ratio:1; border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:500; }
.att-cell.header { background:none; color:var(--text3); font-size:10px; font-weight:600; }
.att-cell.present { background:rgba(16,185,129,0.2); color:var(--green2); }
.att-cell.absent  { background:rgba(239,68,68,0.2); color:#fca5a5; }
.att-cell.late    { background:rgba(245,158,11,0.2); color:#fcd34d; }
.att-cell.empty, .att-cell.no-class { background:var(--bg3); color:var(--text3); }
.att-cell.weekend { background:var(--bg3); color:var(--text3); opacity:0.4; }
</style>
