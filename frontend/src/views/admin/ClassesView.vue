<template>
  <div>
    <div class="page-header">
      <div><h2>Classes & Arms</h2><p>Manage class groups, arms, and assignments</p></div>
      <button class="btn btn-primary" @click="openAdd"><i class="fa fa-plus" style="margin-right:6px"></i>Add Class</button>
    </div>

    <!-- Summary -->
    <div class="stats-grid" style="grid-template-columns:repeat(4,1fr)">
      <div class="stat-card">
        <div class="stat-card-top"><div class="stat-icon blue">🏛️</div></div>
        <div class="stat-value">{{ classes.length }}</div>
        <div class="stat-label">Total Classes</div>
      </div>
      <div class="stat-card">
        <div class="stat-card-top"><div class="stat-icon green">👥</div></div>
        <div class="stat-value">{{ totalStudents }}</div>
        <div class="stat-label">Total Students</div>
      </div>
      <div class="stat-card">
        <div class="stat-card-top"><div class="stat-icon purple">🏷️</div></div>
        <div class="stat-value">{{ uniqueArms }}</div>
        <div class="stat-label">Unique Arms</div>
      </div>
      <div class="stat-card">
        <div class="stat-card-top"><div class="stat-icon orange">👨‍🏫</div></div>
        <div class="stat-value">{{ assignedTeachers }}</div>
        <div class="stat-label">Form Teachers</div>
      </div>
    </div>

    <!-- Level Filter -->
    <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap">
      <button :class="['btn btn-sm', levelFilter===''?'btn-primary':'btn-ghost']" @click="levelFilter=''">All</button>
      <button :class="['btn btn-sm', levelFilter==='JSS'?'btn-primary':'btn-ghost']" @click="levelFilter='JSS'">JSS</button>
      <button :class="['btn btn-sm', levelFilter==='SS'?'btn-primary':'btn-ghost']" @click="levelFilter='SS'">SS</button>
      <button :class="['btn btn-sm', levelFilter==='Primary'?'btn-primary':'btn-ghost']" @click="levelFilter='Primary'">Primary</button>
    </div>

    <div v-if="loading" class="loading-overlay"><div class="spinner"></div></div>
    <div v-else>
      <!-- Class Cards -->
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px">
        <div v-for="c in filteredClasses" :key="c.id" class="card" style="margin:0;cursor:default;transition:box-shadow 0.2s,transform 0.2s" @mouseenter="$event.currentTarget.style.cssText+='box-shadow:var(--shadow-md);transform:translateY(-2px)'" @mouseleave="$event.currentTarget.style.cssText+=';box-shadow:var(--shadow);transform:none'">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:14px">
            <div style="width:46px;height:46px;border-radius:10px;background:var(--primary-light);display:flex;align-items:center;justify-content:center;font-size:22px">🏛️</div>
            <div style="display:flex;gap:4px">
              <span :class="['badge', c.level==='SS'?'badge-primary':c.level==='JSS'?'badge-info':'badge-purple']">{{ c.level }}</span>
              <span class="badge badge-gray">Arm {{ c.arm }}</span>
            </div>
          </div>
          <div style="font-size:20px;font-weight:800;margin-bottom:4px;color:var(--text)">{{ c.name }}</div>
          <div style="font-size:12px;color:var(--text3);margin-bottom:12px">
            <i class="fa fa-user-tie" style="margin-right:4px"></i>
            Form Teacher: <strong>{{ c.form_teacher ? c.form_teacher.first_name+' '+c.form_teacher.last_name : 'Unassigned' }}</strong>
          </div>
          <div style="display:flex;justify-content:space-between;align-items:center;padding-top:12px;border-top:1px solid var(--border)">
            <div style="display:flex;gap:12px;font-size:12px;color:var(--text2)">
              <span><i class="fa fa-users" style="margin-right:4px;color:var(--primary)"></i>{{ c.students_count ?? 0 }} students</span>
              <span><i class="fa fa-chair" style="margin-right:4px;color:var(--text3)"></i>Cap {{ c.capacity }}</span>
            </div>
            <div style="display:flex;gap:4px">
              <button class="btn btn-ghost btn-sm btn-icon" @click="editClass(c)" title="Edit"><i class="fa fa-pen" style="font-size:12px"></i></button>
              <button class="btn btn-ghost btn-sm btn-icon" @click="deleteClass(c)" title="Delete"><i class="fa fa-trash" style="font-size:12px;color:var(--danger)"></i></button>
            </div>
          </div>
        </div>
      </div>
      <div v-if="!filteredClasses.length" class="empty-state card"><div class="empty-icon">🏛️</div><h3>No classes found</h3><p>Create your first class to get started</p></div>
    </div>

    <!-- Add/Edit Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal=false">
      <div class="modal">
        <div class="modal-header">
          <div class="modal-title">{{ editing?'Edit Class':'Add New Class' }}</div>
          <button class="modal-close" @click="showModal=false"><i class="fa fa-times"></i></button>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Class Name *</label>
            <input v-model="form.name" class="form-input" placeholder="e.g JSS 1, SS 2, Primary 3"/>
          </div>
          <div class="form-group">
            <label class="form-label">Arm *</label>
            <select v-model="form.arm" class="form-input">
              <option value="A">A</option>
              <option value="B">B</option>
              <option value="C">C</option>
              <option value="D">D</option>
              <option value="E">E</option>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Level *</label>
            <select v-model="form.level" class="form-input">
              <option value="JSS">JSS (Junior Secondary)</option>
              <option value="SS">SS (Senior Secondary)</option>
              <option value="Primary">Primary</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Capacity</label>
            <input v-model.number="form.capacity" type="number" class="form-input" min="1" max="100"/>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Form Teacher</label>
          <select v-model="form.form_teacher_id" class="form-input">
            <option value="">— Unassigned —</option>
            <option v-for="t in teachers" :key="t.id" :value="t.user_id">{{ t.user?.first_name }} {{ t.user?.last_name }}</option>
          </select>
        </div>
        <div class="alert alert-info" style="margin-bottom:12px">
          <i class="fa fa-info-circle" style="margin-right:6px"></i>
          The class display name will be "<strong>{{ form.name }} {{ form.arm }}</strong>" (e.g. JSS 1A, SS 2B)
        </div>
        <div style="display:flex;gap:10px">
          <button class="btn btn-primary" @click="saveClass" :disabled="saving">
            <span v-if="saving" class="spinner" style="width:14px;height:14px;border-color:rgba(255,255,255,0.3);border-top-color:#fff"></span>
            <span v-else>{{ editing?'Save Changes':'Add Class' }}</span>
          </button>
          <button class="btn btn-ghost" @click="showModal=false">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { classesAPI, teachersAPI } from '../../services/api'
import { useToast } from 'vue-toastification'

const toast = useToast()
const loading = ref(false), saving = ref(false)
const showModal = ref(false), editing = ref(null)
const classes = ref([]), teachers = ref([])
const levelFilter = ref('')
const form = reactive({ name:'', arm:'A', level:'JSS', capacity:45, form_teacher_id:'' })

const filteredClasses = computed(() => levelFilter.value ? classes.value.filter(c=>c.level===levelFilter.value) : classes.value)
const totalStudents = computed(() => classes.value.reduce((a,c)=>a+(c.students_count??0),0))
const uniqueArms = computed(() => new Set(classes.value.map(c=>c.arm)).size)
const assignedTeachers = computed(() => classes.value.filter(c=>c.form_teacher_id).length)

function openAdd(){editing.value=null;Object.assign(form,{name:'',arm:'A',level:'JSS',capacity:45,form_teacher_id:''});showModal.value=true}
function editClass(c){editing.value=c;Object.assign(form,{name:c.name,arm:c.arm??'A',level:c.level,capacity:c.capacity,form_teacher_id:c.form_teacher_id??''});showModal.value=true}

async function saveClass(){
  if(!form.name)return toast.error('Class name is required')
  saving.value=true
  try{
    const payload={name:`${form.name}${form.arm}`,arm:form.arm,level:form.level,capacity:form.capacity,form_teacher_id:form.form_teacher_id||null}
    if(editing.value) await classesAPI.update(editing.value.id,payload)
    else await classesAPI.create(payload)
    toast.success(editing.value?'Class updated!':'Class added!')
    showModal.value=false; loadClasses()
  }catch(e){toast.error(e.response?.data?.message??'Failed to save')}
  finally{saving.value=false}
}

async function deleteClass(c){
  if(!confirm(`Delete class "${c.name}"? This cannot be undone.`))return
  try{await classesAPI.delete(c.id);toast.success('Class deleted');loadClasses()}
  catch(e){toast.error(e.response?.data?.message??'Failed to delete')}
}

async function loadClasses(){
  loading.value=true
  try{const res=await classesAPI.list();classes.value=res.data}
  catch{toast.error('Failed to load classes')}
  finally{loading.value=false}
}

onMounted(async()=>{
  loadClasses()
  const tRes=await teachersAPI.list();teachers.value=tRes.data.data
})
</script>
