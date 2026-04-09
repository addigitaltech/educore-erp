<template>
  <div>
    <div class="page-header">
      <div><h2>Teachers</h2><p>{{ total }} teaching staff</p></div>
      <button class="btn btn-primary" @click="openAdd"><i class="fa fa-plus" style="margin-right:6px"></i>Add Teacher</button>
    </div>

    <div class="card" style="padding:14px 20px;margin-bottom:16px">
      <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
        <div class="search-wrap">
          <i class="fa fa-search search-icon"></i>
          <input v-model="search" class="search-input" placeholder="Search teachers..." @input="debounceSearch"/>
        </div>
        <select v-model="empFilter" class="form-input" style="max-width:160px" @change="loadTeachers">
          <option value="">All Types</option>
          <option value="full_time">Full Time</option>
          <option value="part_time">Part Time</option>
          <option value="contract">Contract</option>
        </select>
      </div>
    </div>

    <div class="table-container">
      <div v-if="loading" class="loading-overlay"><div class="spinner"></div></div>
      <div v-else>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th style="width:40px"></th>
                <th>Teacher</th>
                <th>Staff ID</th>
                <th>Specialization</th>
                <th>Qualification</th>
                <th>Type</th>
                <th>Date Joined</th>
                <th style="width:90px">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="t in teachers" :key="t.id">
                <td>
                  <div class="avatar avatar-sm">{{ (t.user?.first_name?.[0]??'')+(t.user?.last_name?.[0]??'') }}</div>
                </td>
                <td>
                  <div style="font-weight:600">{{ t.user?.first_name }} {{ t.user?.last_name }}</div>
                  <div style="font-size:11px;color:var(--text3)">{{ t.user?.email }}</div>
                </td>
                <td><span class="badge badge-gray">{{ t.staff_id }}</span></td>
                <td>{{ t.specialization }}</td>
                <td style="font-size:12px;color:var(--text2)">{{ t.qualification }}</td>
                <td>
                  <span :class="['badge', t.employment_type==='full_time'?'badge-success':t.employment_type==='part_time'?'badge-warning':'badge-info']" style="text-transform:capitalize">
                    {{ t.employment_type?.replace('_',' ') }}
                  </span>
                </td>
                <td style="font-size:12px;color:var(--text3)">{{ t.date_joined }}</td>
                <td>
                  <div style="display:flex;gap:4px">
                    <button class="btn btn-ghost btn-sm btn-icon" @click="editTeacher(t)"><i class="fa fa-pen"></i></button>
                    <button class="btn btn-ghost btn-sm btn-icon" @click="deleteTeacher(t)"><i class="fa fa-trash" style="color:var(--danger)"></i></button>
                  </div>
                </td>
              </tr>
              <tr v-if="!teachers.length">
                <td colspan="8"><div class="empty-state"><div class="empty-icon">👨‍🏫</div><h3>No teachers found</h3></div></td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="pagination">
          <div class="pagination-info">{{ total }} teachers total</div>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal=false">
      <div class="modal">
        <div class="modal-header">
          <div class="modal-title">{{ editing?'Edit Teacher':'Add Teacher' }}</div>
          <button class="modal-close" @click="showModal=false"><i class="fa fa-times"></i></button>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">First Name *</label><input v-model="form.first_name" class="form-input"/></div>
          <div class="form-group"><label class="form-label">Last Name *</label><input v-model="form.last_name" class="form-input"/></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Email *</label><input v-model="form.email" class="form-input" type="email" :disabled="!!editing"/></div>
          <div class="form-group"><label class="form-label">Phone</label><input v-model="form.phone" class="form-input"/></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Qualification *</label><input v-model="form.qualification" class="form-input" placeholder="e.g B.Sc Education"/></div>
          <div class="form-group"><label class="form-label">Specialization *</label><input v-model="form.specialization" class="form-input" placeholder="e.g Mathematics"/></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Date Joined *</label><input v-model="form.date_joined" class="form-input" type="date"/></div>
          <div class="form-group"><label class="form-label">Employment Type</label>
            <select v-model="form.employment_type" class="form-input">
              <option value="full_time">Full Time</option>
              <option value="part_time">Part Time</option>
              <option value="contract">Contract</option>
            </select>
          </div>
        </div>
        <div style="display:flex;gap:10px;margin-top:16px">
          <button class="btn btn-primary" @click="saveTeacher" :disabled="saving">
            <span v-if="saving" class="spinner" style="width:14px;height:14px;border-color:rgba(255,255,255,0.3);border-top-color:#fff"></span>
            <span v-else>{{ editing?'Save Changes':'Add Teacher' }}</span>
          </button>
          <button class="btn btn-ghost" @click="showModal=false">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { teachersAPI } from '../../services/api'
import { useToast } from 'vue-toastification'

const toast = useToast()
const loading = ref(false), saving = ref(false), showModal = ref(false), editing = ref(null)
const teachers = ref([]), total = ref(0), search = ref(''), empFilter = ref('')
const form = reactive({ first_name:'',last_name:'',email:'',phone:'',qualification:'',specialization:'',date_joined:new Date().toISOString().slice(0,10),employment_type:'full_time' })

let debounceTimer=null
function debounceSearch(){clearTimeout(debounceTimer);debounceTimer=setTimeout(()=>loadTeachers(),400)}

function openAdd(){editing.value=null;Object.assign(form,{first_name:'',last_name:'',email:'',phone:'',qualification:'',specialization:'',date_joined:new Date().toISOString().slice(0,10),employment_type:'full_time'});showModal.value=true}
function editTeacher(t){editing.value=t;Object.assign(form,{first_name:t.user?.first_name,last_name:t.user?.last_name,email:t.user?.email,phone:t.user?.phone??'',qualification:t.qualification,specialization:t.specialization,date_joined:t.date_joined,employment_type:t.employment_type});showModal.value=true}

async function saveTeacher(){
  if(!form.first_name||!form.qualification)return toast.error('Fill required fields')
  saving.value=true
  try{
    if(editing.value) await teachersAPI.update(editing.value.id,form)
    else await teachersAPI.create(form)
    toast.success(editing.value?'Teacher updated!':'Teacher added!'); showModal.value=false; loadTeachers()
  }catch(e){toast.error(e.response?.data?.message??'Failed to save')}
  finally{saving.value=false}
}

async function deleteTeacher(t){
  if(!confirm(`Delete ${t.user?.first_name} ${t.user?.last_name}?`))return
  try{await teachersAPI.delete(t.id);toast.success('Deleted');loadTeachers()}
  catch{toast.error('Failed to delete')}
}

async function loadTeachers(){
  loading.value=true
  try{const res=await teachersAPI.list({search:search.value,employment_type:empFilter.value});teachers.value=res.data.data;total.value=res.data.total}
  catch{toast.error('Failed to load')}
  finally{loading.value=false}
}

onMounted(loadTeachers)
</script>
