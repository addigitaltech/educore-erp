<template>
  <div>
    <div class="page-header">
      <div><h2>School Settings</h2><p>Manage school profile, branding and grading system</p></div>
    </div>
    <div v-if="loading" class="loading-overlay"><div class="spinner"></div></div>
    <div v-else>
      <div class="tabs">
        <button :class="['tab-btn',tab==='school'?'active':'']" @click="tab='school'"><i class="fa fa-school" style="margin-right:6px"></i>School Info</button>
        <button :class="['tab-btn',tab==='academic'?'active':'']" @click="tab='academic'"><i class="fa fa-graduation-cap" style="margin-right:6px"></i>Academic</button>
        <button :class="['tab-btn',tab==='grading'?'active':'']" @click="tab='grading'"><i class="fa fa-star" style="margin-right:6px"></i>WAEC Grading</button>
      </div>

      <!-- SCHOOL INFO -->
      <div v-if="tab==='school'">
        <div class="grid-21">
          <div class="card">
            <div class="card-title" style="margin-bottom:20px">School Information</div>
            <div class="form-row">
              <div class="form-group"><label class="form-label">School Name *</label><input v-model="form.name" class="form-input" placeholder="Enter school name"/></div>
              <div class="form-group"><label class="form-label">Email *</label><input v-model="form.email" class="form-input" type="email"/></div>
            </div>
            <div class="form-row">
              <div class="form-group"><label class="form-label">Phone</label><input v-model="form.phone" class="form-input"/></div>
              <div class="form-group"><label class="form-label">Website</label><input v-model="form.website" class="form-input" placeholder="https://..."/></div>
            </div>
            <div class="form-group"><label class="form-label">Address</label><input v-model="form.address" class="form-input"/></div>
            <div class="form-row">
              <div class="form-group"><label class="form-label">State</label><input v-model="form.state" class="form-input"/></div>
              <div class="form-group"><label class="form-label">School Type</label><select v-model="form.type" class="form-input"><option value="mixed">Mixed</option><option value="boys">Boys Only</option><option value="girls">Girls Only</option></select></div>
            </div>
            <div class="form-group"><label class="form-label">School Motto</label><input v-model="form.motto" class="form-input" placeholder="Excellence in Education"/></div>
            <div class="form-row">
              <div class="form-group"><label class="form-label">Principal</label><input v-model="form.principal" class="form-input"/></div>
              <div class="form-group"><label class="form-label">Proprietor</label><input v-model="form.proprietor" class="form-input"/></div>
            </div>
            <button class="btn btn-primary" @click="saveSchoolInfo" :disabled="saving">
              <span v-if="saving" class="spinner" style="width:14px;height:14px;border-color:rgba(255,255,255,0.3);border-top-color:#fff"></span>
              <span v-else><i class="fa fa-save" style="margin-right:6px"></i>Save School Info</span>
            </button>
          </div>
          <div>
            <div class="card" style="margin-bottom:16px">
              <div class="card-title" style="margin-bottom:16px">School Logo</div>
              <div style="display:flex;flex-direction:column;align-items:center;gap:14px">
                <div style="width:110px;height:110px;border-radius:50%;border:3px solid var(--border);overflow:hidden;display:flex;align-items:center;justify-content:center;background:var(--bg)">
                  <img v-if="logoPreview||school?.logo" :src="logoPreview||storageUrl(school?.logo)" style="width:100%;height:100%;object-fit:contain"/>
                  <span v-else style="font-size:36px">🏫</span>
                </div>
                <input ref="logoInput" type="file" accept="image/*" style="display:none" @change="e=>{logoFile=e.target.files[0];logoPreview=URL.createObjectURL(logoFile)}"/>
                <button class="btn btn-outline btn-sm" @click="$refs.logoInput.click()"><i class="fa fa-upload" style="margin-right:4px"></i>Upload Logo</button>
                <p style="font-size:11px;color:var(--text3);text-align:center">PNG/JPG · Max 2MB<br>Shows on result sheets</p>
              </div>
            </div>
            <div class="card">
              <div class="card-title" style="margin-bottom:16px">Official Stamp</div>
              <div style="display:flex;flex-direction:column;align-items:center;gap:14px">
                <div style="width:90px;height:90px;border-radius:8px;border:2px solid var(--border);overflow:hidden;display:flex;align-items:center;justify-content:center;background:var(--bg)">
                  <img v-if="stampPreview||school?.stamp" :src="stampPreview||storageUrl(school?.stamp)" style="width:100%;height:100%;object-fit:contain"/>
                  <span v-else style="font-size:28px">🔏</span>
                </div>
                <input ref="stampInput" type="file" accept="image/*" style="display:none" @change="e=>{stampFile=e.target.files[0];stampPreview=URL.createObjectURL(stampFile)}"/>
                <button class="btn btn-outline btn-sm" @click="$refs.stampInput.click()"><i class="fa fa-upload" style="margin-right:4px"></i>Upload Stamp</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ACADEMIC -->
      <div v-if="tab==='academic'">
        <div class="card" style="max-width:540px">
          <div class="card-title" style="margin-bottom:20px">Academic Configuration</div>
          <div class="form-row">
            <div class="form-group"><label class="form-label">Current Session</label><input v-model="form.current_session" class="form-input" placeholder="2025/2026"/></div>
            <div class="form-group"><label class="form-label">Current Term</label><select v-model="form.current_term" class="form-input"><option value="1st">1st Term</option><option value="2nd">2nd Term</option><option value="3rd">3rd Term</option></select></div>
          </div>
          <div class="form-row">
            <div class="form-group"><label class="form-label">School Level</label><select v-model="form.level" class="form-input"><option value="primary">Primary</option><option value="secondary">Secondary</option><option value="both">Both</option></select></div>
            <div class="form-group"><label class="form-label">Status</label><select v-model="form.status" class="form-input"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
          </div>
          <div class="alert alert-info" style="margin-bottom:16px"><i class="fa fa-info-circle" style="margin-right:6px"></i>Session and term appear on all result sheets.</div>
          <button class="btn btn-primary" @click="saveSchoolInfo" :disabled="saving">
            <span v-if="saving" class="spinner" style="width:14px;height:14px;border-color:rgba(255,255,255,0.3);border-top-color:#fff"></span>
            <span v-else><i class="fa fa-save" style="margin-right:6px"></i>Save</span>
          </button>
        </div>
      </div>

      <!-- GRADING -->
      <div v-if="tab==='grading'">
        <div class="card">
          <div class="card-header">
            <div><div class="card-title">WAEC Grading System</div><div class="card-subtitle">Saving will recalculate all existing results automatically.</div></div>
            <div style="display:flex;gap:8px">
              <button class="btn btn-ghost btn-sm" @click="resetToDefault"><i class="fa fa-rotate-left" style="margin-right:4px"></i>Reset Default</button>
              <button class="btn btn-primary btn-sm" @click="saveGrading" :disabled="savingGrading">
                <span v-if="savingGrading" class="spinner" style="width:12px;height:12px;border-color:rgba(255,255,255,0.3);border-top-color:#fff"></span>
                <span v-else><i class="fa fa-save" style="margin-right:4px"></i>Save & Recalculate</span>
              </button>
            </div>
          </div>
          <div class="alert alert-warning" style="margin-bottom:16px"><i class="fa fa-triangle-exclamation" style="margin-right:6px"></i><strong>Warning:</strong> Saving will recalculate all existing results using the new ranges.</div>
          <div class="table-wrap">
            <table>
              <thead><tr><th>Grade</th><th>Min Score</th><th>Max Score</th><th>Remark</th><th>Category</th><th></th></tr></thead>
              <tbody>
                <tr v-for="(g,i) in grades" :key="i">
                  <td><input v-model="g.grade" class="form-input" style="width:70px;text-align:center;font-weight:700" :style="{color:g.category==='Pass'?'var(--success)':'var(--danger)'}"/></td>
                  <td><input v-model.number="g.min_score" type="number" class="form-input" style="width:80px" min="0" max="100"/></td>
                  <td><input v-model.number="g.max_score" type="number" class="form-input" style="width:80px" min="0" max="100"/></td>
                  <td><input v-model="g.remark" class="form-input" style="width:140px"/></td>
                  <td><select v-model="g.category" class="form-input" style="width:90px"><option value="Pass">Pass</option><option value="Fail">Fail</option></select></td>
                  <td><button class="btn btn-ghost btn-sm" @click="grades.splice(i,1)"><i class="fa fa-trash" style="color:var(--danger)"></i></button></td>
                </tr>
              </tbody>
            </table>
          </div>
          <button class="btn btn-ghost btn-sm" style="margin-top:12px" @click="grades.push({grade:'',min_score:0,max_score:0,remark:'',category:'Pass'})"><i class="fa fa-plus" style="margin-right:4px"></i>Add Grade</button>
        </div>
        <div class="card">
          <div class="card-title" style="margin-bottom:14px">Grade Preview</div>
          <div style="display:flex;gap:8px;flex-wrap:wrap">
            <div v-for="g in grades" :key="g.grade" style="padding:10px 14px;border-radius:8px;text-align:center;min-width:76px;border:1.5px solid" :style="{background:g.category==='Pass'?'var(--success-light)':'var(--danger-light)',borderColor:g.category==='Pass'?'var(--success-soft)':'var(--danger-soft)'}">
              <div style="font-size:17px;font-weight:800" :style="{color:g.category==='Pass'?'var(--success)':'var(--danger)'}">{{g.grade}}</div>
              <div style="font-size:10px;color:var(--text3)">{{g.min_score}}–{{g.max_score}}</div>
              <div style="font-size:10px;font-weight:600;margin-top:2px" :style="{color:g.category==='Pass'?'var(--success)':'var(--danger)'}">{{g.remark}}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { settingsAPI, gradingAPI } from '../../services/api'
import { useToast } from 'vue-toastification'

const toast = useToast()
const loading = ref(true), saving = ref(false), savingGrading = ref(false)
const tab = ref('school')
const school = ref(null)
const logoPreview = ref(null), stampPreview = ref(null)
const logoFile = ref(null), stampFile = ref(null)

const form = reactive({ name:'',email:'',phone:'',address:'',state:'',website:'',motto:'',principal:'',proprietor:'',type:'mixed',level:'secondary',current_session:'2025/2026',current_term:'2nd',status:'active' })
const grades = ref([])

const waecDefault = [
  {grade:'A1',min_score:75,max_score:100,remark:'Excellent',category:'Pass'},
  {grade:'B2',min_score:70,max_score:74,remark:'Very Good',category:'Pass'},
  {grade:'B3',min_score:65,max_score:69,remark:'Good',category:'Pass'},
  {grade:'C4',min_score:60,max_score:64,remark:'Credit',category:'Pass'},
  {grade:'C5',min_score:55,max_score:59,remark:'Credit',category:'Pass'},
  {grade:'C6',min_score:50,max_score:54,remark:'Credit',category:'Pass'},
  {grade:'D7',min_score:45,max_score:49,remark:'Pass',category:'Pass'},
  {grade:'E8',min_score:40,max_score:44,remark:'Pass',category:'Pass'},
  {grade:'F9',min_score:0,max_score:39,remark:'Fail',category:'Fail'},
]

function storageUrl(p) { if(!p)return null; const b=import.meta.env.VITE_API_URL?.replace('/api','')?? 'http://localhost:8000'; return `${b}/storage/${p}` }
function resetToDefault() { grades.value=waecDefault.map(g=>({...g})); toast.info('Reset to WAEC default. Save to apply.') }

async function saveSchoolInfo() {
  saving.value = true
  try {
    const fd = new FormData()
    Object.entries(form).forEach(([k,v]) => { if(v!==null&&v!==undefined) fd.append(k,v) })
    if(logoFile.value)  fd.append('logo',logoFile.value)
    if(stampFile.value) fd.append('stamp',stampFile.value)
    const res = await settingsAPI.update(fd)
    school.value = res.data.school
    logoFile.value=null; stampFile.value=null; logoPreview.value=null; stampPreview.value=null
    toast.success('Settings saved!')
  } catch(e) { toast.error(e.response?.data?.message??'Failed to save') }
  finally { saving.value=false }
}

async function saveGrading() {
  if(!grades.value.length) return toast.error('Add at least one grade')
  savingGrading.value=true
  try { await gradingAPI.update({grades:grades.value}); toast.success('Grading saved! Results recalculated.') }
  catch(e) { toast.error(e.response?.data?.message??'Failed to save grading') }
  finally { savingGrading.value=false }
}

onMounted(async()=>{
  try {
    const [sRes,gRes] = await Promise.all([settingsAPI.get(),gradingAPI.list()])
    school.value=sRes.data
    Object.assign(form,{name:sRes.data.name,email:sRes.data.email,phone:sRes.data.phone??'',address:sRes.data.address??'',state:sRes.data.state??'',website:sRes.data.website??'',motto:sRes.data.motto??'',principal:sRes.data.principal??'',proprietor:sRes.data.proprietor??'',type:sRes.data.type,level:sRes.data.level,current_session:sRes.data.current_session,current_term:sRes.data.current_term,status:sRes.data.status})
    grades.value=gRes.data.length?gRes.data.map(g=>({grade:g.grade,min_score:g.min_score,max_score:g.max_score,remark:g.remark,category:g.category})):waecDefault.map(g=>({...g}))
  } catch { grades.value=waecDefault.map(g=>({...g})); toast.error('Could not load settings') }
  finally { loading.value=false }
})
</script>
