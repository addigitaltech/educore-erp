<!-- Teacher DashboardView.vue -->
<template>
  <div>
    <div class="page-header"><div><h2>Good morning, {{ auth.user?.first_name }} 👋</h2><p>Your teaching overview for today</p></div></div>
    <div class="stats-grid">
      <div class="stat-card"><div class="stat-icon blue">👥</div><div class="stat-value">{{ stats.my_classes||5 }}</div><div class="stat-label">My Classes</div></div>
      <div class="stat-card"><div class="stat-icon green">🎒</div><div class="stat-value">{{ stats.my_students||186 }}</div><div class="stat-label">My Students</div></div>
      <div class="stat-card"><div class="stat-icon yellow">📝</div><div class="stat-value">{{ stats.active_exams||3 }}</div><div class="stat-label">Active Exams</div></div>
      <div class="stat-card"><div class="stat-icon purple">📁</div><div class="stat-value">{{ stats.materials_uploaded||24 }}</div><div class="stat-label">Materials</div></div>
    </div>
    <div class="grid-2">
      <div class="card" style="margin-bottom:0">
        <div class="card-header"><div class="card-title">Today's Classes</div></div>
        <div v-for="c in todayClasses" :key="c.name" style="display:flex;align-items:center;justify-content:space-between;padding:12px;background:var(--card2);border-radius:10px;margin-bottom:10px;border:1px solid var(--border)">
          <div><div style="font-size:13px;font-weight:600">{{ c.name }}</div><div style="font-size:11px;color:var(--text3);margin-top:3px">{{ c.room }}</div></div>
          <span class="badge badge-info">{{ c.time }}</span>
        </div>
      </div>
      <div class="card" style="margin-bottom:0">
        <div class="card-header"><div class="card-title">Quick Actions</div></div>
        <div style="display:flex;flex-direction:column;gap:10px">
          <router-link to="/teacher/exams" class="btn btn-ghost" style="justify-content:flex-start">📝 Create New Exam</router-link>
          <router-link to="/teacher/attendance" class="btn btn-ghost" style="justify-content:flex-start">✅ Mark Today's Attendance</router-link>
          <router-link to="/teacher/performance" class="btn btn-ghost" style="justify-content:flex-start">📈 View Student Performance</router-link>
          <router-link to="/teacher/questions" class="btn btn-ghost" style="justify-content:flex-start">❓ Question Bank</router-link>
        </div>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '../../store/auth'
import { dashboardAPI } from '../../services/api'
const auth=useAuthStore()
const stats=ref({})
const todayClasses=[{name:'Mathematics — SS 2A',time:'8:00–9:00 AM',room:'Room 14'},{name:'English Lang — JSS 3A',time:'10:00–11:00 AM',room:'Room 08'},{name:'Further Math — SS 3A',time:'2:00–3:00 PM',room:'Room 14'}]
onMounted(async()=>{try{const r=await dashboardAPI.index();stats.value=r.data||{}}catch{}})
</script>
