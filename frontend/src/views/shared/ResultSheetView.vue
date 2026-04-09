<template>
  <div>
    <!-- Actions Bar (hidden on print) -->
    <div class="no-print" style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;align-items:center">
      <button class="btn btn-ghost" @click="$router.back()"><i class="fa fa-arrow-left" style="margin-right:6px"></i>Back</button>
      <button class="btn btn-outline" @click="window.print()"><i class="fa fa-print" style="margin-right:6px"></i>Print</button>
      <button class="btn btn-primary" @click="downloadPDF" :disabled="downloading">
        <span v-if="downloading" class="spinner" style="width:14px;height:14px;border-color:rgba(255,255,255,0.3);border-top-color:#fff"></span>
        <span v-else><i class="fa fa-download" style="margin-right:6px"></i>Download PDF</span>
      </button>
    </div>

    <div v-if="loading" class="loading-overlay" style="height:400px"><div class="spinner"></div></div>

    <div v-else-if="report" class="result-sheet" id="result-sheet">
      <!-- Header -->
      <div class="rs-header">
        <div v-if="report.school?.logo" style="width:70px;height:70px;border-radius:50%;overflow:hidden;flex-shrink:0;border:2px solid var(--border)">
          <img :src="storageUrl(report.school.logo)" style="width:100%;height:100%;object-fit:contain"/>
        </div>
        <div v-else style="width:70px;height:70px;border-radius:50%;background:var(--primary-light);display:flex;align-items:center;justify-content:center;font-size:32px;flex-shrink:0;border:2px solid var(--primary-soft)">🏫</div>
        <div style="flex:1;text-align:center">
          <div class="rs-school-name">{{ report.school?.name ?? 'School Name' }}</div>
          <div v-if="report.school?.motto" class="rs-school-motto">"{{ report.school.motto }}"</div>
          <div class="rs-school-addr">{{ report.school?.address }}</div>
          <div style="font-size:11px;color:var(--text3)">{{ report.school?.phone }} | {{ report.school?.email }}</div>
        </div>
        <div v-if="report.school?.stamp" style="width:60px;height:60px;flex-shrink:0;opacity:0.6">
          <img :src="storageUrl(report.school.stamp)" style="width:100%;height:100%;object-fit:contain"/>
        </div>
      </div>

      <!-- Report Title -->
      <div class="rs-report-title">
        {{ report.term }} &nbsp;|&nbsp; {{ report.session }} Academic Session &nbsp;|&nbsp; Student Report Card
      </div>

      <!-- Student Info + Passport -->
      <div style="display:flex;gap:20px;margin-bottom:20px;align-items:flex-start">
        <div>
          <div v-if="report.student?.passport_url" style="width:90px;height:110px;border-radius:8px;overflow:hidden;border:2px solid var(--border)">
            <img :src="report.student.passport_url" style="width:100%;height:100%;object-fit:cover"/>
          </div>
          <div v-else class="rs-passport-placeholder">👤</div>
          <div style="font-size:10px;text-align:center;color:var(--text3);margin-top:4px">Passport Photo</div>
        </div>
        <div style="flex:1">
          <div class="rs-info-grid">
            <div class="rs-info-item"><label>Full Name</label><strong>{{ report.student?.user?.first_name }} {{ report.student?.user?.last_name }}</strong></div>
            <div class="rs-info-item"><label>Admission No.</label><strong>{{ report.student?.admission_number }}</strong></div>
            <div class="rs-info-item"><label>Class</label><strong>{{ report.student?.school_class?.name }}</strong></div>
            <div class="rs-info-item"><label>Gender</label><strong>{{ report.student?.user?.gender | capitalize }}</strong></div>
            <div class="rs-info-item"><label>Date of Birth</label><strong>{{ formatDate(report.student?.user?.date_of_birth) }}</strong></div>
            <div class="rs-info-item"><label>Session</label><strong>{{ report.session }}</strong></div>
            <div class="rs-info-item"><label>Term</label><strong>{{ report.term }}</strong></div>
            <div class="rs-info-item"><label>Days Present</label><strong>{{ report.days_present }} / {{ report.total_days }}</strong></div>
            <div class="rs-info-item"><label>Parent/Guardian</label><strong>{{ report.student?.parent_name || '—' }}</strong></div>
          </div>
        </div>
      </div>

      <!-- Summary Cards -->
      <div class="rs-summary-grid">
        <div class="rs-summary-item">
          <div class="rs-summary-val">{{ report.total_score }}</div>
          <div class="rs-summary-lbl">Total Score</div>
        </div>
        <div class="rs-summary-item">
          <div class="rs-summary-val">{{ report.average }}%</div>
          <div class="rs-summary-lbl">Average Score</div>
        </div>
        <div class="rs-summary-item">
          <div class="rs-summary-val">{{ ordinal(report.class_position) }}</div>
          <div class="rs-summary-lbl">Class Position</div>
        </div>
        <div class="rs-summary-item">
          <div class="rs-summary-val">{{ report.class_size }}</div>
          <div class="rs-summary-lbl">Class Size</div>
        </div>
      </div>

      <!-- Results Table -->
      <table class="rs-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Subject</th>
            <th style="text-align:center">CA (30)</th>
            <th style="text-align:center">Exam (70)</th>
            <th style="text-align:center">Total (100)</th>
            <th style="text-align:center">Grade</th>
            <th style="text-align:center">Remark</th>
            <th style="text-align:center">Position</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(r,i) in report.results" :key="r.id">
            <td style="color:var(--text3)">{{ i+1 }}</td>
            <td style="font-weight:600">{{ r.subject?.name }}</td>
            <td style="text-align:center">{{ r.ca_score }}</td>
            <td style="text-align:center">{{ r.exam_score }}</td>
            <td style="text-align:center;font-weight:700" :style="{color:scoreColor(r.ca_score+r.exam_score)}">{{ r.ca_score + r.exam_score }}</td>
            <td style="text-align:center">
              <span style="font-weight:800;font-size:14px" :style="{color:gradeColor(r.waec_grade)}">{{ r.waec_grade || r.grade }}</span>
            </td>
            <td style="text-align:center;font-size:12px">{{ r.remark }}</td>
            <td style="text-align:center;font-weight:600">{{ ordinal(r.subject_position) }}</td>
          </tr>
        </tbody>
        <tfoot>
          <tr style="background:var(--primary-light)">
            <td colspan="4" style="font-weight:700;font-size:13px">Total / Average</td>
            <td style="text-align:center;font-weight:800;font-size:14px;color:var(--primary)">{{ report.total_score }}</td>
            <td colspan="3" style="text-align:center;font-size:12px;font-weight:600;color:var(--text2)">Avg: {{ report.average }}%</td>
          </tr>
        </tfoot>
      </table>

      <!-- Grading Key -->
      <div v-if="report.grading?.length" style="margin-bottom:20px">
        <div style="font-size:11px;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:8px">Grading Key</div>
        <div style="display:flex;gap:6px;flex-wrap:wrap">
          <div v-for="g in report.grading" :key="g.grade" style="padding:4px 10px;border-radius:4px;font-size:11px;text-align:center;border:1px solid var(--border)" :style="{background:g.category==='Pass'?'var(--success-light)':'var(--danger-light)',borderColor:g.category==='Pass'?'var(--success-soft)':'var(--danger-soft)'}">
            <div style="font-weight:800" :style="{color:g.category==='Pass'?'var(--success)':'var(--danger)'}">{{ g.grade }}</div>
            <div style="color:var(--text3)">{{ g.min_score }}-{{ g.max_score }}</div>
          </div>
        </div>
      </div>

      <!-- Teacher Comment / Signature -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:20px;padding-top:16px;border-top:2px solid var(--border)">
        <div>
          <div style="font-size:11px;font-weight:700;color:var(--text3);text-transform:uppercase;margin-bottom:8px">Class Teacher's Comment</div>
          <div style="min-height:48px;border-bottom:1px dashed var(--border2);font-size:13px;color:var(--text2)">{{ report.results?.[0]?.teacher_comment || 'Well done, keep it up!' }}</div>
          <div style="margin-top:20px;display:flex;flex-direction:column;gap:4px">
            <div style="width:120px;border-bottom:1px solid var(--text)"></div>
            <div style="font-size:11px;color:var(--text3)">Class Teacher's Signature & Date</div>
          </div>
        </div>
        <div>
          <div style="font-size:11px;font-weight:700;color:var(--text3);text-transform:uppercase;margin-bottom:8px">Principal's Comment</div>
          <div style="min-height:48px;border-bottom:1px dashed var(--border2)"></div>
          <div style="margin-top:20px;display:flex;flex-direction:column;gap:4px">
            <div style="width:120px;border-bottom:1px solid var(--text)"></div>
            <div style="font-size:11px;color:var(--text3)">Principal's Signature & Date</div>
          </div>
        </div>
      </div>

      <!-- School Stamp area -->
      <div style="margin-top:16px;text-align:right;font-size:10px;color:var(--text3);border-top:1px solid var(--border);padding-top:10px">
        Generated by EduCore ERP · {{ new Date().toLocaleDateString('en-NG', {day:'numeric',month:'long',year:'numeric'}) }}
      </div>
    </div>

    <div v-else class="empty-state card"><div class="empty-icon">📋</div><h3>Report not found</h3></div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { enhancedResultsAPI } from '../../services/api'
import { useToast } from 'vue-toastification'

const route = useRoute()
const toast = useToast()
const loading = ref(true), downloading = ref(false)
const report = ref(null)

const studentId = route.params.id ?? route.query.student_id

function storageUrl(p){if(!p)return null;const b=import.meta.env.VITE_API_URL?.replace('/api','')?? 'http://localhost:8000';return`${b}/storage/${p}`}
function formatDate(d){return d?new Date(d).toLocaleDateString('en-NG',{day:'numeric',month:'long',year:'numeric'}):'—'}
function ordinal(n){if(!n||n==='—')return'—';const s=['th','st','nd','rd'];const v=n%100;return n+(s[(v-20)%10]||s[v]||s[0])}
function scoreColor(t){return t>=70?'var(--success)':t>=50?'var(--text)':'var(--danger)'}
function gradeColor(g){if(!g)return'var(--text)';const pass=['A1','B2','B3','C4','C5','C6','D7','E8'];return pass.includes(g)?'var(--success)':'var(--danger)'}

async function downloadPDF(){
  downloading.value=true
  try{
    const token=localStorage.getItem('educore_token')
    const url=`${import.meta.env.VITE_API_URL}/v2/results/pdf/${studentId}`
    const res=await fetch(url,{headers:{Authorization:`Bearer ${token}`}})
    if(!res.ok)throw new Error('PDF generation failed')
    const blob=await res.blob()
    const a=document.createElement('a')
    a.href=URL.createObjectURL(blob)
    a.download=`result_${report.value?.student?.admission_number}_${new Date().getFullYear()}.pdf`
    a.click()
    toast.success('PDF downloaded!')
  }catch(e){
    toast.warning('PDF download failed. Try printing instead.')
  }finally{downloading.value=false}
}

onMounted(async()=>{
  try{
    const res=await enhancedResultsAPI.reportCard(studentId)
    report.value=res.data
  }catch{toast.error('Failed to load report card')}
  finally{loading.value=false}
})
</script>
