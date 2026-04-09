<!-- ClassesView.vue teacher -->
<template>
  <div>
    <div class="page-header"><div><h2>My Classes</h2><p>Classes you are assigned to teach</p></div></div>
    <div v-if="loading" class="loading-overlay"><div class="spinner"></div></div>
    <div v-else style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px">
      <div v-for="c in classes" :key="c.id" class="card" style="margin-bottom:0">
        <div style="font-size:24px;margin-bottom:10px">🏛️</div>
        <div style="font-size:18px;font-weight:800">{{ c.name }}</div>
        <div style="font-size:12px;color:var(--text3);margin-top:4px">{{ c.level }} · Capacity {{ c.capacity }}</div>
        <div style="margin-top:12px;display:flex;gap:8px">
          <router-link to="/teacher/attendance" class="btn btn-ghost btn-sm">Mark Attendance</router-link>
          <router-link to="/teacher/performance" class="btn btn-blue btn-sm">Performance</router-link>
        </div>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue'
import { classesAPI } from '../../services/api'
const loading=ref(true),classes=ref([])
onMounted(async()=>{loading.value=true;try{const r=await classesAPI.list();classes.value=r.data||[]}finally{loading.value=false}})
</script>
