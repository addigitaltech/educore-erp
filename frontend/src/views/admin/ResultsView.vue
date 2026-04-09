<template>
  <div>
    <div class="page-header">
      <div><h2>Result Management</h2><p>Enter scores, calculate positions, publish and download results</p></div>
      <div class="page-header-actions">
        <button class="btn btn-ghost" @click="showPublishModal=true"><i class="fa fa-bullhorn" style="margin-right:6px"></i>Publish</button>
        <button v-if="results.length" class="btn btn-ghost" @click="calcPositions" :disabled="calcing"><i class="fa fa-calculator" style="margin-right:6px"></i>Calc Positions</button>
        <button v-if="results.length" class="btn btn-ghost" @click="lockResults"><i class="fa fa-lock" style="margin-right:6px"></i>Lock</button>
        <button class="btn btn-primary" @click="loadResults" :disabled="!filters.class_id||!filters.subject_id||loading">
          <i class="fa fa-table" style="margin-right:6px"></i>Load Results
        </button>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="card" style="padding:14px 20px;margin-bottom:16px">
      <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
        <div class="form-group" style="margin:0">
          <label class="form-label" style="font-size:10px">Class</label>
          <select v-model="filters.class_id" class="form-input" style="width:150px">
            <option value="">Select class</option>
            <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>
        <div class="form-group" style="margin:0">
          <label class="form-label" style="font-size:10px">Subject</label>
          <select v-model="filters.subject_id" class="form-input" style="width:170px">
            <option value="">Select subject</option>
            <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
        </div>
        <div class="form-group" style="margin:0">
          <label class="form-label" style="font-size:10px">Term</label>
          <select v-model="filters.term_id" class="form-input" style="width:130px">
            <option v-for="t in terms" :key="t.id" :value="t.id">{{ t.name }} Term</option>
          </select>
        </div>
        <button v-if="results.length" class="btn btn-primary btn-sm" @click="saveAll" :disabled="saving">
          <span v-if="saving" class="spinner" style="width:12px;height:12px;border-color:rgba(255,255,255,0.3);border-top-color:#fff"></span>
          <span v-else><i class="fa fa-save" style="margin-right:4px"></i>Save All</span>
        </button>
      </div>
    </div>

    <!-- Stats -->
    <div v-if="results.length" class="stats-grid" style="grid-template-columns:repeat(6,1fr);margin-bottom:16px">
      <div class="stat-card" v-for="s in resultStats" :key="s.label" style="padding:14px">
        <div class="stat-value" style="font-size:20px">{{ s.value }}</div>
        <div class="stat-label">{{ s.label }}</div>
      </div>
    </div>

    <!-- Results Table -->
    <div class="table-container">
      <div v-if="loading" class="loading-overlay"><div class="spinner"></div></div>
      <div v-else-if="!results.length && !filters.class_id" class="empty-state">
        <div class="empty-icon">📝</div>
        <h3>Select class and subject</h3>
        <p>Then click "Load Results" to begin entering scores</p>
      </div>
      <div v-else-if="results.length">
        <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 20px;border-bottom:1px solid var(--border);flex-wrap:wrap;gap:10px">
          <div style="font-size:13px;color:var(--text2)">{{ results.length }} students · CA max 30 · Exam max 70</div>
          <div class="search-wrap">
            <i class="fa fa-search search-icon"></i>
            <input v-model="search" class="search-input" placeholder="Filter student..."/>
          </div>
        </div>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th>Student</th>
                <th style="width:90px">CA<br><span style="font-weight:400;color:var(--text3)">(max 30)</span></th>
                <th style="width:90px">Exam<br><span style="font-weight:400;color:var(--text3)">(max 70)</span></th>
                <th style="width:70px">Total</th>
                <th style="width:70px">WAEC Grade</th>
                <th style="width:90px">Subject Pos.</th>
                <th style="width:80px">Class Pos.</th>
                <th>Comment</th>
                <th style="width:60px">Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(r,i) in filteredResults" :key="r.student_id" :style="{background:r.is_locked?'var(--bg)':''}">
                <td style="color:var(--text3);font-size:12px">{{ i+1 }}</td>
                <td>
                  <div style="display:flex;align-items:center;gap:8px">
                    <div class="avatar avatar-xs">
                      <img v-if="r.passport_url" :src="r.passport_url"/>
                      <span v-else>{{ (r.first_name?.[0]??'')+(r.last_name?.[0]??'') }}</span>
                    </div>
                    <div>
                      <div style="font-weight:600;font-size:13px">{{ r.first_name }} {{ r.last_name }}</div>
                      <div style="font-size:10px;color:var(--text3)">{{ r.admission_number }}</div>
                    </div>
                  </div>
                </td>
                <td>
                  <input v-if="!r.is_locked" type="number" v-model.number="r.ca_score" class="form-input" style="padding:5px 8px;width:75px;font-size:13px" min="0" max="30" @input="recalc(r)"/>
                  <span v-else style="font-weight:600">{{ r.ca_score }}</span>
                </td>
                <td>
                  <input v-if="!r.is_locked" type="number" v-model.number="r.exam_score" class="form-input" style="padding:5px 8px;width:75px;font-size:13px" min="0" max="70" @input="recalc(r)"/>
                  <span v-else style="font-weight:600">{{ r.exam_score }}</span>
                </td>
                <td>
                  <span style="font-size:16px;font-weight:800" :style="{color:scoreColor(r.total)}">{{ r.total }}</span>
                </td>
                <td>
                  <span :class="['badge', gradeClass(r.waec_grade)]" style="font-size:13px;font-weight:800;min-width:32px;justify-content:center">
                    {{ r.waec_grade || '—' }}
                  </span>
                </td>
                <td style="text-align:center">
                  <span v-if="r.subject_position" style="font-weight:700">{{ ordinal(r.subject_position) }}</span>
                  <span v-else style="color:var(--text4)">—</span>
                </td>
                <td style="text-align:center">
                  <span v-if="r.class_position" style="font-weight:700">{{ ordinal(r.class_position) }}</span>
                  <span v-else style="color:var(--text4)">—</span>
                </td>
                <td>
                  <input v-if="!r.is_locked" v-model="r.teacher_comment" class="form-input" style="padding:5px 8px;font-size:11px" placeholder="Optional"/>
                  <span v-else style="font-size:12px;color:var(--text3)">{{ r.teacher_comment || '—' }}</span>
                </td>
                <td>
                  <span v-if="r.is_locked" class="badge badge-gray"><i class="fa fa-lock" style="font-size:10px"></i></span>
                  <span v-else-if="r.is_approved" class="badge badge-success" style="font-size:10px">✓</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div style="padding:12px 20px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:8px">
          <button class="btn btn-ghost btn-sm" @click="loadResults"><i class="fa fa-rotate-right" style="margin-right:4px"></i>Reload</button>
          <button class="btn btn-primary" @click="saveAll" :disabled="saving">
            <span v-if="saving" class="spinner" style="width:14px;height:14px;border-color:rgba(255,255,255,0.3);border-top-color:#fff"></span>
            <span v-else><i class="fa fa-save" style="margin-right:6px"></i>Save All Results</span>
          </button>
        </div>
      </div>
      <div v-else class="empty-state"><div class="empty-icon">📊</div><h3>Click "Load Results"</h3><p>Select a class and subject first</p></div>
    </div>

    <!-- Publish Modal -->
    <div v-if="showPublishModal" class="modal-overlay" @click.self="showPublishModal=false">
      <div class="modal" style="max-width:400px">
        <div class="modal-header"><div class="modal-title">Publish Results</div><button class="modal-close" @click="showPublishModal=false"><i class="fa fa-times"></i></button></div>
        <p style="font-size:13px;color:var(--text2);margin-bottom:16px">Published results become visible to students and parents.</p>
        <div class="form-group">
          <label class="form-label">Term</label>
          <select v-model="publishTermId" class="form-input">
            <option v-for="t in terms" :key="t.id" :value="t.id">{{ t.name }} Term</option>
          </select>
        </div>
        <div style="display:flex;gap:10px;margin-top:16px">
          <button class="btn btn-primary" @click="publishResults" :disabled="publishing">
            <span v-if="publishing" class="spinner" style="width:14px;height:14px;border-color:rgba(255,255,255,0.3);border-top-color:#fff"></span>
            <span v-else><i class="fa fa-bullhorn" style="margin-right:6px"></i>Publish Now</span>
          </button>
          <button class="btn btn-ghost" @click="showPublishModal=false">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { enhancedResultsAPI, classesAPI, subjectsAPI } from '../../services/api'
import api from '../../services/api'
import { useToast } from 'vue-toastification'

const toast = useToast()
const loading = ref(false), saving = ref(false), publishing = ref(false), calcing = ref(false)
const showPublishModal = ref(false)
const classes = ref([]), subjects = ref([])
const terms = [{id:1,name:'1st'},{id:2,name:'2nd'},{id:3,name:'3rd'}]
const results = ref([]), search = ref(''), publishTermId = ref(2)
const filters = reactive({ class_id:'', subject_id:'', term_id:2 })

const filteredResults = computed(() => {
  if(!search.value) return results.value
  const s = search.value.toLowerCase()
  return results.value.filter(r=>`${r.first_name} ${r.last_name}`.toLowerCase().includes(s)||r.admission_number?.includes(s))
})

const resultStats = computed(()=>{
  const passed = results.value.filter(r=>r.total>=50).length
  const avg = results.value.length ? Math.round(results.value.reduce((a,r)=>a+r.total,0)/results.value.length) : 0
  return [
    {label:'Students',value:results.value.length},
    {label:'Average',value:avg},
    {label:'Passed',value:passed},
    {label:'Failed',value:results.value.length-passed},
    {label:'Pass Rate',value:results.value.length?Math.round((passed/results.value.length)*100)+'%':'0%'},
    {label:'Locked',value:results.value.filter(r=>r.is_locked).length},
  ]
})

function ordinal(n){if(!n)return'—';const s=['th','st','nd','rd'];const v=n%100;return n+(s[(v-20)%10]||s[v]||s[0])}
function scoreColor(t){return t>=70?'var(--success)':t>=50?'var(--text)':'var(--danger)'}
function gradeClass(g){if(!g)return'badge-gray';const map={'A1':'badge-success','B2':'badge-success','B3':'badge-success','C4':'badge-info','C5':'badge-info','C6':'badge-info','D7':'badge-warning','E8':'badge-warning','F9':'badge-danger'};return map[g]??'badge-gray'}

function recalc(r){
  r.ca_score=Math.min(30,Math.max(0,r.ca_score||0))
  r.exam_score=Math.min(70,Math.max(0,r.exam_score||0))
  r.total=r.ca_score+r.exam_score
  // Client-side preview grade (actual WAEC grade set by server on save)
  r.waec_grade=r.total>=75?'A1':r.total>=70?'B2':r.total>=65?'B3':r.total>=60?'C4':r.total>=55?'C5':r.total>=50?'C6':r.total>=45?'D7':r.total>=40?'E8':'F9'
}

async function loadResults(){
  if(!filters.class_id||!filters.subject_id) return toast.warning('Select class and subject first')
  loading.value=true
  try{
    const [studRes,resRes]=await Promise.all([
      api.get('/v2/students',{params:{class_id:filters.class_id,per_page:100}}),
      enhancedResultsAPI.list({class_id:filters.class_id,subject_id:filters.subject_id,term_id:filters.term_id})
    ])
    const existing={}
    ;(resRes.data.data??[]).forEach(r=>{existing[r.student_id]=r})
    results.value=(studRes.data.data??[]).map(s=>{
      const ex=existing[s.id]
      const ca=ex?.ca_score??0, exam=ex?.exam_score??0, total=ca+exam
      const waec=total>=75?'A1':total>=70?'B2':total>=65?'B3':total>=60?'C4':total>=55?'C5':total>=50?'C6':total>=45?'D7':total>=40?'E8':'F9'
      return {student_id:s.id,first_name:s.user?.first_name,last_name:s.user?.last_name,admission_number:s.admission_number,passport_url:s.passport_url,ca_score:ca,exam_score:exam,total,waec_grade:ex?.waec_grade??waec,subject_position:ex?.subject_position,class_position:ex?.class_position,teacher_comment:ex?.teacher_comment??'',is_locked:ex?.is_locked??false,is_approved:ex?.is_approved??false}
    })
  }catch{toast.error('Failed to load results')}
  finally{loading.value=false}
}

async function saveAll(){
  if(!results.value.length)return
  saving.value=true
  try{
    await enhancedResultsAPI.bulkStore({results:results.value.map(r=>({student_id:r.student_id,subject_id:filters.subject_id,school_class_id:filters.class_id,term_id:filters.term_id,ca_score:r.ca_score,exam_score:r.exam_score,teacher_comment:r.teacher_comment}))})
    toast.success('Results saved!'); loadResults()
  }catch(e){toast.error(e.response?.data?.message??'Failed to save')}
  finally{saving.value=false}
}

async function calcPositions(){
  if(!filters.class_id)return toast.warning('Select a class first')
  calcing.value=true
  try{
    const res=await enhancedResultsAPI.calculatePositions({class_id:filters.class_id,term_id:filters.term_id})
    toast.success(res.data.message); loadResults()
  }catch{toast.error('Failed to calculate positions')}
  finally{calcing.value=false}
}

async function lockResults(){
  if(!filters.class_id)return toast.warning('Select a class first')
  if(!confirm('Lock all results for this class? Locked results cannot be edited.'))return
  try{await enhancedResultsAPI.lock({class_id:filters.class_id,term_id:filters.term_id});toast.success('Results locked');loadResults()}
  catch{toast.error('Failed to lock results')}
}

async function publishResults(){
  publishing.value=true
  try{await enhancedResultsAPI.publish({term_id:publishTermId.value});toast.success('Results published! Students can now view them.');showPublishModal.value=false}
  catch{toast.error('Failed to publish')}
  finally{publishing.value=false}
}

onMounted(async()=>{
  const [cRes,sRes]=await Promise.all([classesAPI.list(),subjectsAPI.list()])
  classes.value=cRes.data; subjects.value=sRes.data
})
</script>
