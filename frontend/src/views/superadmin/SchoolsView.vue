<template>
  <div>
    <div class="page-header"><div><h2>All Schools</h2><p>Managing {{ schools.length }} schools on the platform</p></div><button class="btn btn-blue" @click="showModal=true">+ Onboard School</button></div>
    <div v-if="loading" class="loading-overlay"><div class="spinner"></div></div>
    <div v-else>
      <div v-for="s in schools" :key="s.id" class="school-card">
        <div class="school-logo">{{ schoolEmoji(s.name) }}</div>
        <div style="flex:1">
          <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
            <div style="font-size:15px;font-weight:700">{{ s.name }}</div>
            <span :class="['badge',s.status==='active'?'badge-success':'badge-warning']">{{ s.status }}</span>
            <span :class="['badge',s.plan==='enterprise'?'badge-purple':s.plan==='professional'?'badge-info':'badge-warning']">{{ s.plan }}</span>
          </div>
          <div style="font-size:12px;color:var(--text2);margin-top:4px">📍 {{ s.state }}, {{ s.country }} · {{ s.email }}</div>
          <div style="display:flex;gap:20px;margin-top:8px;font-size:12px;color:var(--text3)">
            <span><strong style="color:var(--text)">{{ s.students_count||0 }}</strong> students</span>
            <span><strong style="color:var(--text)">{{ s.teachers_count||0 }}</strong> teachers</span>
            <span>Session: <strong style="color:var(--text)">{{ s.current_session }}</strong></span>
          </div>
        </div>
        <div style="display:flex;gap:8px;flex-shrink:0">
          <button class="btn btn-ghost btn-sm">Settings</button>
          <button class="btn btn-blue btn-sm">View</button>
        </div>
      </div>
    </div>
    <div v-if="showModal" class="modal-overlay" @click.self="showModal=false">
      <div class="modal">
        <div class="modal-header"><div class="modal-title">Onboard New School</div><button class="btn btn-ghost btn-sm" @click="showModal=false">×</button></div>
        <div class="form-group"><label class="form-label">School Name</label><input v-model="form.name" class="form-input"/></div>
        <div class="form-row"><div class="form-group"><label class="form-label">Email</label><input v-model="form.email" class="form-input" type="email"/></div><div class="form-group"><label class="form-label">Phone</label><input v-model="form.phone" class="form-input"/></div></div>
        <div class="form-row"><div class="form-group"><label class="form-label">State</label><input v-model="form.state" class="form-input"/></div><div class="form-group"><label class="form-label">Plan</label><select v-model="form.plan" class="form-input"><option value="starter">Starter</option><option value="professional">Professional</option><option value="enterprise">Enterprise</option></select></div></div>
        <div style="display:flex;gap:10px;margin-top:8px"><button class="btn btn-blue" @click="addSchool" :disabled="saving"><span v-if="saving" class="spinner"></span><span v-else>Onboard</span></button><button class="btn btn-ghost" @click="showModal=false">Cancel</button></div>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, reactive, onMounted } from 'vue'
import { schoolsAPI } from '../../services/api'
import { useToast } from 'vue-toastification'
const toast=useToast(),loading=ref(true),saving=ref(false),showModal=ref(false)
const schools=ref([]),form=reactive({name:'',email:'',phone:'',state:'',plan:'starter'})
function schoolEmoji(n){const e={'Greenfield':'🌿','Sunrise':'🌅','Horizon':'🌄','Unity':'🦅','Excellence':'⭐'};return Object.entries(e).find(([k])=>n.includes(k))?.[1]||'🏫'}
onMounted(async()=>{loading.value=true;try{const r=await schoolsAPI.list({});schools.value=r.data?.data||[]}catch{schools.value=[{id:1,name:'Greenfield Academy',state:'Lagos',country:'Nigeria',email:'info@greenfield.edu',plan:'enterprise',status:'active',current_session:'2025/2026',students_count:1284,teachers_count:64}]}finally{loading.value=false}})
async function addSchool(){saving.value=true;try{await schoolsAPI.create(form);toast.success('School onboarded!');showModal.value=false;const r=await schoolsAPI.list({});schools.value=r.data?.data||[]}catch{toast.error('Failed')}finally{saving.value=false}}
</script>
<style scoped>
.school-card{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:20px;display:flex;gap:16px;align-items:flex-start;margin-bottom:12px;transition:border-color .2s}
.school-card:hover{border-color:var(--border2)}
.school-logo{width:48px;height:48px;border-radius:10px;background:rgba(59,130,246,0.15);display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0}
</style>
