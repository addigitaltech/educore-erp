<template>
  <div>
    <div class="page-header">
      <div><h2>Students</h2><p>{{ total }} students enrolled</p></div>
      <div class="page-header-actions">
        <button class="btn btn-ghost btn-sm" @click="exportCSV"><i class="fa fa-download" style="margin-right:4px"></i>Export</button>
        <button class="btn btn-primary" @click="openAdd"><i class="fa fa-plus" style="margin-right:6px"></i>Add Student</button>
      </div>
    </div>

    <!-- Filters -->
    <div class="card" style="padding:14px 20px;margin-bottom:16px">
      <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
        <div class="search-wrap">
          <i class="fa fa-search search-icon"></i>
          <input v-model="search" class="search-input" placeholder="Search by name or ID..." @input="debounceSearch"/>
        </div>
        <select v-model="classFilter" class="form-input" style="max-width:160px" @change="loadStudents">
          <option value="">All Classes</option>
          <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
        </select>
        <select v-model="armFilter" class="form-input" style="max-width:100px" @change="loadStudents">
          <option value="">All Arms</option>
          <option value="A">Arm A</option>
          <option value="B">Arm B</option>
          <option value="C">Arm C</option>
          <option value="D">Arm D</option>
        </select>
        <div style="margin-left:auto;font-size:13px;color:var(--text3)">
          Showing {{ students.length }} of {{ total }}
        </div>
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
                <th>Student</th>
                <th>Admission No.</th>
                <th>Class</th>
                <th>Gender</th>
                <th>Parent</th>
                <th>Status</th>
                <th style="width:100px">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="s in students" :key="s.id">
                <td>
                  <div class="avatar avatar-sm">
                    <img v-if="s.passport_url" :src="s.passport_url" :alt="s.user?.first_name"/>
                    <span v-else>{{ initials(s.user?.first_name, s.user?.last_name) }}</span>
                  </div>
                </td>
                <td>
                  <div style="font-weight:600">{{ s.user?.first_name }} {{ s.user?.last_name }}</div>
                  <div style="font-size:11px;color:var(--text3)">{{ s.user?.email }}</div>
                </td>
                <td><span class="badge badge-gray">{{ s.admission_number }}</span></td>
                <td>{{ s.school_class?.name ?? '—' }}</td>
                <td>
                  <span :class="['badge', s.user?.gender==='male'?'badge-info':'badge-purple']" style="text-transform:capitalize">
                    {{ s.user?.gender ?? '—' }}
                  </span>
                </td>
                <td>
                  <div v-if="s.parent_name" style="font-size:12px">{{ s.parent_name }}</div>
                  <div v-if="s.parent_phone" style="font-size:11px;color:var(--text3)">{{ s.parent_phone }}</div>
                  <span v-if="!s.parent_name" style="color:var(--text4);font-size:12px">—</span>
                </td>
                <td><span :class="['badge', s.user?.is_active?'badge-success':'badge-danger']">{{ s.user?.is_active?'Active':'Inactive' }}</span></td>
                <td>
                  <div style="display:flex;gap:4px">
                    <button class="btn btn-ghost btn-sm btn-icon" @click="viewStudent(s)" title="View"><i class="fa fa-eye"></i></button>
                    <button class="btn btn-ghost btn-sm btn-icon" @click="editStudent(s)" title="Edit"><i class="fa fa-pen"></i></button>
                    <button class="btn btn-ghost btn-sm btn-icon" @click="deleteStudent(s)" title="Delete"><i class="fa fa-trash" style="color:var(--danger)"></i></button>
                  </div>
                </td>
              </tr>
              <tr v-if="!students.length && !loading">
                <td colspan="8"><div class="empty-state"><div class="empty-icon">🎒</div><h3>No students found</h3><p>Add a student or adjust your filters</p></div></td>
              </tr>
            </tbody>
          </table>
        </div>
        <!-- Pagination -->
        <div class="pagination">
          <div class="pagination-info">Page {{ currentPage }} of {{ totalPages }}</div>
          <div class="pagination-btns">
            <button class="page-btn" :disabled="currentPage<=1" @click="currentPage--;loadStudents()"><i class="fa fa-chevron-left"></i></button>
            <button v-for="p in visiblePages" :key="p" :class="['page-btn',p===currentPage?'active':'']" @click="currentPage=p;loadStudents()">{{p}}</button>
            <button class="page-btn" :disabled="currentPage>=totalPages" @click="currentPage++;loadStudents()"><i class="fa fa-chevron-right"></i></button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal=false">
      <div class="modal modal-lg" style="max-height:90vh;overflow-y:auto">
        <div class="modal-header">
          <div class="modal-title">{{ editing?'Edit Student':'Add New Student' }}</div>
          <button class="modal-close" @click="showModal=false"><i class="fa fa-times"></i></button>
        </div>

        <!-- Passport Upload -->
        <div style="display:flex;align-items:flex-start;gap:20px;margin-bottom:20px;padding:16px;background:var(--bg);border-radius:var(--radius-sm)">
          <div>
            <div style="width:90px;height:110px;border-radius:8px;border:2px solid var(--border);overflow:hidden;display:flex;align-items:center;justify-content:center;background:var(--white)">
              <img v-if="passportPreview||(editing&&editing.passport_url)" :src="passportPreview||editing.passport_url" style="width:100%;height:100%;object-fit:cover"/>
              <span v-else style="font-size:28px">👤</span>
            </div>
            <input ref="passportInput" type="file" accept="image/*" style="display:none" @change="onPassportChange"/>
            <button class="btn btn-ghost btn-sm" style="width:100%;margin-top:8px;font-size:11px" @click="$refs.passportInput.click()">
              <i class="fa fa-camera" style="margin-right:4px"></i>Upload Photo
            </button>
          </div>
          <div style="flex:1">
            <div style="font-size:12px;font-weight:600;color:var(--text2);margin-bottom:6px">Passport Photo</div>
            <div style="font-size:12px;color:var(--text3)">Upload a clear passport-sized photo of the student.<br>Recommended: 300×400px · JPG or PNG · Max 2MB</div>
          </div>
        </div>

        <!-- Personal Info -->
        <div style="font-size:12px;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:10px">Personal Information</div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">First Name *</label><input v-model="form.first_name" class="form-input"/></div>
          <div class="form-group"><label class="form-label">Last Name *</label><input v-model="form.last_name" class="form-input"/></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Email *</label><input v-model="form.email" class="form-input" type="email" :disabled="!!editing"/></div>
          <div class="form-group"><label class="form-label">Phone</label><input v-model="form.phone" class="form-input"/></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Date of Birth *</label><input v-model="form.date_of_birth" class="form-input" type="date"/></div>
          <div class="form-group"><label class="form-label">Gender *</label><select v-model="form.gender" class="form-input"><option value="">Select</option><option value="male">Male</option><option value="female">Female</option></select></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Class *</label>
            <select v-model="form.school_class_id" class="form-input">
              <option value="">Select class</option>
              <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
          <div class="form-group"><label class="form-label">Admission Date *</label><input v-model="form.admission_date" class="form-input" type="date"/></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Blood Group</label>
            <select v-model="form.blood_group" class="form-input">
              <option value="">Select</option>
              <option v-for="b in ['A+','A-','B+','B-','AB+','AB-','O+','O-']" :key="b" :value="b">{{b}}</option>
            </select>
          </div>
          <div class="form-group"><label class="form-label">Religion</label><input v-model="form.religion" class="form-input" placeholder="e.g Christianity"/></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">State of Origin</label><input v-model="form.state_of_origin" class="form-input"/></div>
          <div class="form-group"><label class="form-label">Address</label><input v-model="form.address" class="form-input"/></div>
        </div>

        <!-- Parent Info -->
        <div style="font-size:12px;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:0.06em;margin:16px 0 10px">Parent / Guardian</div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Parent Full Name</label><input v-model="form.parent_name" class="form-input" placeholder="Mr. John Doe"/></div>
          <div class="form-group"><label class="form-label">Relationship</label>
            <select v-model="form.parent_relationship" class="form-input">
              <option value="">Select</option>
              <option value="Father">Father</option>
              <option value="Mother">Mother</option>
              <option value="Guardian">Guardian</option>
              <option value="Other">Other</option>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Parent Phone</label><input v-model="form.parent_phone" class="form-input"/></div>
          <div class="form-group"><label class="form-label">Parent Email</label><input v-model="form.parent_email" class="form-input" type="email"/></div>
        </div>

        <div style="display:flex;gap:10px;margin-top:20px;padding-top:16px;border-top:1px solid var(--border)">
          <button class="btn btn-primary" @click="saveStudent" :disabled="saving">
            <span v-if="saving" class="spinner" style="width:14px;height:14px;border-color:rgba(255,255,255,0.3);border-top-color:#fff"></span>
            <span v-else>{{ editing?'Save Changes':'Add Student' }}</span>
          </button>
          <button class="btn btn-ghost" @click="showModal=false">Cancel</button>
        </div>
      </div>
    </div>

    <!-- View Modal -->
    <div v-if="viewModal" class="modal-overlay" @click.self="viewModal=null">
      <div class="modal modal-lg">
        <div class="modal-header">
          <div class="modal-title">Student Profile</div>
          <button class="modal-close" @click="viewModal=null"><i class="fa fa-times"></i></button>
        </div>
        <div v-if="viewModal" style="display:flex;gap:20px;flex-wrap:wrap">
          <div style="display:flex;flex-direction:column;align-items:center;gap:8px">
            <div style="width:100px;height:120px;border-radius:10px;border:2px solid var(--border);overflow:hidden;background:var(--bg);display:flex;align-items:center;justify-content:center">
              <img v-if="viewModal.passport_url" :src="viewModal.passport_url" style="width:100%;height:100%;object-fit:cover"/>
              <span v-else style="font-size:40px">👤</span>
            </div>
            <span class="badge badge-success">{{ viewModal.user?.is_active?'Active':'Inactive' }}</span>
          </div>
          <div style="flex:1;min-width:200px">
            <div style="font-size:18px;font-weight:800;margin-bottom:4px">{{ viewModal.user?.first_name }} {{ viewModal.user?.last_name }}</div>
            <div style="font-size:13px;color:var(--text3);margin-bottom:12px">{{ viewModal.admission_number }}</div>
            <div class="grid-2" style="gap:10px">
              <div v-for="f in profileFields" :key="f.label" style="background:var(--bg);padding:10px 12px;border-radius:8px">
                <div style="font-size:10px;font-weight:700;color:var(--text3);text-transform:uppercase;margin-bottom:3px">{{ f.label }}</div>
                <div style="font-size:13px;font-weight:600">{{ f.value || '—' }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { enhancedStudentsAPI, classesAPI } from '../../services/api'
import { useToast } from 'vue-toastification'

const toast = useToast()
const loading = ref(false), saving = ref(false)
const showModal = ref(false), viewModal = ref(null)
const editing = ref(null)
const students = ref([]), classes = ref([])
const total = ref(0), currentPage = ref(1), totalPages = ref(1)
const search = ref(''), classFilter = ref(''), armFilter = ref('')
const passportPreview = ref(null), passportFile = ref(null)

const form = reactive({ first_name:'',last_name:'',email:'',phone:'',date_of_birth:'',gender:'',school_class_id:'',admission_date:new Date().toISOString().slice(0,10),blood_group:'',religion:'',state_of_origin:'',address:'',parent_name:'',parent_phone:'',parent_email:'',parent_relationship:'' })

let debounceTimer = null
function debounceSearch() { clearTimeout(debounceTimer); debounceTimer = setTimeout(()=>{currentPage.value=1;loadStudents()},400) }

const visiblePages = computed(()=>{
  const pages=[];const start=Math.max(1,currentPage.value-2);const end=Math.min(totalPages.value,start+4);
  for(let i=start;i<=end;i++)pages.push(i);return pages
})

const profileFields = computed(()=>{
  if(!viewModal.value)return[]
  const s=viewModal.value
  return [
    {label:'Class',value:s.school_class?.name},
    {label:'Gender',value:s.user?.gender},
    {label:'Date of Birth',value:s.user?.date_of_birth},
    {label:'Blood Group',value:s.blood_group},
    {label:'Religion',value:s.religion},
    {label:'State of Origin',value:s.state_of_origin},
    {label:'Parent',value:s.parent_name},
    {label:'Parent Phone',value:s.parent_phone},
    {label:'Parent Relationship',value:s.parent_relationship},
    {label:'Address',value:s.user?.address},
  ]
})

function initials(f,l){return((f?.[0]??'')+(l?.[0]??'')).toUpperCase()}
function onPassportChange(e){passportFile.value=e.target.files[0];if(passportFile.value)passportPreview.value=URL.createObjectURL(passportFile.value)}

function openAdd(){editing.value=null;passportPreview.value=null;passportFile.value=null;Object.assign(form,{first_name:'',last_name:'',email:'',phone:'',date_of_birth:'',gender:'',school_class_id:'',admission_date:new Date().toISOString().slice(0,10),blood_group:'',religion:'',state_of_origin:'',address:'',parent_name:'',parent_phone:'',parent_email:'',parent_relationship:''});showModal.value=true}

function editStudent(s){editing.value=s;passportPreview.value=null;passportFile.value=null;Object.assign(form,{first_name:s.user?.first_name,last_name:s.user?.last_name,email:s.user?.email,phone:s.user?.phone??'',date_of_birth:s.user?.date_of_birth,gender:s.user?.gender,school_class_id:s.school_class_id,admission_date:s.admission_date,blood_group:s.blood_group??'',religion:s.religion??'',state_of_origin:s.state_of_origin??'',address:s.user?.address??'',parent_name:s.parent_name??'',parent_phone:s.parent_phone??'',parent_email:s.parent_email??'',parent_relationship:s.parent_relationship??''});showModal.value=true}

function viewStudent(s){viewModal.value=s}

async function saveStudent(){
  if(!form.first_name||!form.last_name||!form.school_class_id)return toast.error('Fill in required fields')
  saving.value=true
  try{
    const fd=new FormData()
    Object.entries(form).forEach(([k,v])=>{if(v!==null&&v!=='')fd.append(k,v)})
    if(passportFile.value)fd.append('passport_photo',passportFile.value)
    if(editing.value) await enhancedStudentsAPI.update(editing.value.id,fd)
    else await enhancedStudentsAPI.store(fd)
    toast.success(editing.value?'Student updated!':'Student added!')
    showModal.value=false; loadStudents()
  }catch(e){toast.error(e.response?.data?.message??'Failed to save')}
  finally{saving.value=false}
}

async function deleteStudent(s){
  if(!confirm(`Delete ${s.user?.first_name} ${s.user?.last_name}?`))return
  try{await enhancedStudentsAPI.delete(s.id);toast.success('Student deleted');loadStudents()}
  catch{toast.error('Failed to delete')}
}

async function loadStudents(){
  loading.value=true
  try{
    const res=await enhancedStudentsAPI.list({page:currentPage.value,per_page:20,search:search.value,class_id:classFilter.value,arm:armFilter.value})
    students.value=res.data.data;total.value=res.data.total;totalPages.value=res.data.pages
  }catch{toast.error('Failed to load students')}
  finally{loading.value=false}
}

function exportCSV(){toast.info('Export feature coming soon')}

onMounted(async()=>{
  loadStudents()
  const cRes=await classesAPI.list();classes.value=cRes.data
})
</script>
