<template>
  <div>
    <div class="page-header">
      <div><h2>Report Card</h2><p>Official academic report for 2nd Term 2025/2026</p></div>
      <div style="display:flex;gap:10px">
        <button class="btn btn-ghost" @click="window.print()">🖨️ Print</button>
        <button class="btn btn-blue">📥 Download PDF</button>
      </div>
    </div>

    <div v-if="loading" class="loading-overlay"><div class="spinner"></div></div>

    <div v-else class="report-card-wrapper">
      <!-- Header -->
      <div class="rc-header">
        <div style="font-size:40px;margin-bottom:10px">🎓</div>
        <div class="rc-school">{{ report.student?.school_class?.school?.name || 'GREENFIELD ACADEMY' }}</div>
        <div class="rc-subtitle">Student Academic Report — {{ report.term }}, {{ report.session }}</div>
      </div>

      <!-- Student Info -->
      <div class="rc-info-grid">
        <div v-for="f in infoFields" :key="f.label" class="rc-field">
          <label>{{ f.label }}</label>
          <strong>{{ f.value }}</strong>
        </div>
      </div>

      <!-- Summary Cards -->
      <div class="rc-summary">
        <div class="rc-sum-card"><div class="rc-sum-val">{{ report.total_score }}</div><div class="rc-sum-lbl">Total Score</div></div>
        <div class="rc-sum-card"><div class="rc-sum-val">{{ report.average }}%</div><div class="rc-sum-lbl">Average</div></div>
        <div class="rc-sum-card"><div class="rc-sum-val">{{ report.position }}{{ ordinal(report.position) }}</div><div class="rc-sum-lbl">Position (of {{ report.class_size }})</div></div>
        <div class="rc-sum-card"><div class="rc-sum-val">{{ report.grade }}</div><div class="rc-sum-lbl">Final Grade</div></div>
      </div>

      <!-- Results Table -->
      <table class="rc-table">
        <thead>
          <tr><th>Subject</th><th>CA (30)</th><th>Exam (70)</th><th>Total</th><th>Grade</th><th>Position</th><th>Remark</th></tr>
        </thead>
        <tbody>
          <tr v-for="r in report.results" :key="r.id">
            <td><strong>{{ r.subject?.name }}</strong></td>
            <td>{{ r.ca_score }}</td>
            <td>{{ r.exam_score }}</td>
            <td><strong>{{ r.total_score }}</strong></td>
            <td><strong :style="{color:r.grade==='A'?'#16a34a':r.grade==='B'?'#2563eb':'#d97706'}">{{ r.grade }}</strong></td>
            <td>{{ r.position || '—' }}/{{ report.class_size }}</td>
            <td>{{ r.remark }}</td>
          </tr>
        </tbody>
      </table>

      <!-- Attendance -->
      <div class="rc-attendance">
        <strong>Attendance:</strong> {{ report.days_present }} days present out of {{ report.total_days }} school days
        ({{ report.total_days > 0 ? Math.round(report.days_present/report.total_days*100) : 0 }}%)
      </div>

      <!-- Comments -->
      <div class="rc-comments">
        <div class="rc-comment">
          <strong>Class Teacher's Comment:</strong>
          <p>{{ report.results?.[0]?.teacher_comment || 'An excellent student who demonstrates commendable dedication and intellectual curiosity. Keep up the outstanding work.' }}</p>
          <div class="rc-sig">Signature: _________________________</div>
        </div>
        <div class="rc-comment">
          <strong>Principal's Comment:</strong>
          <p>An outstanding performance this term. We are proud of your achievements and encourage you to maintain this standard.</p>
          <div class="rc-sig">Signature: _________________________</div>
        </div>
      </div>

      <div style="text-align:center;margin-top:20px;padding-top:16px;border-top:2px solid #e2e8f0;font-size:12px;color:#64748b">
        Next Term Begins: <strong>April 14, 2026</strong> · School Fees Due: <strong>First week of term</strong>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { resultsAPI } from '../../services/api'
import { useAuthStore } from '../../store/auth'

const auth = useAuthStore()
const loading = ref(true)
const report = ref({ results: [], student: {}, total_score: 0, average: 0, position: 3, class_size: 42, days_present: 62, total_days: 65, grade: 'A', term: '2nd Term', session: '2025/2026' })

function ordinal(n) {
  if (!n) return ''
  const s = ['th','st','nd','rd']
  const v = n % 100
  return s[(v-20)%10]||s[v]||s[0]
}

const infoFields = computed(() => [
  { label: 'Student Name', value: report.value.student?.user?.first_name + ' ' + (report.value.student?.user?.last_name || '') },
  { label: 'Admission No.', value: report.value.student?.admission_number || '—' },
  { label: 'Class', value: report.value.student?.school_class?.name || '—' },
  { label: 'Term', value: report.value.term },
  { label: 'Session', value: report.value.session },
  { label: 'Days Present', value: `${report.value.days_present} / ${report.value.total_days}` },
])

onMounted(async () => {
  loading.value = true
  try {
    const r = await resultsAPI.reportCard(1)
    report.value = { ...report.value, ...r.data }
  } catch (e) {
    // fallback to demo data
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.report-card-wrapper { background:white; color:#1a1a2e; border-radius:14px; padding:40px; max-width:800px; margin:0 auto; box-shadow:0 4px 24px rgba(0,0,0,0.3); }
.rc-header { text-align:center; margin-bottom:28px; padding-bottom:20px; border-bottom:3px solid #3b82f6; }
.rc-school { font-size:22px; font-weight:800; color:#1e3a5f; font-family:'Syne',sans-serif; }
.rc-subtitle { font-size:14px; color:#64748b; margin-top:4px; }
.rc-info-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:24px; padding:16px; background:#f8fafc; border-radius:10px; }
.rc-field label { font-size:11px; color:#64748b; display:block; margin-bottom:2px; }
.rc-field strong { font-size:13px; color:#1a1a2e; }
.rc-summary { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:24px; }
.rc-sum-card { background:#f8fafc; border-radius:10px; padding:14px; text-align:center; }
.rc-sum-val { font-size:22px; font-weight:800; color:#3b82f6; font-family:'Syne',sans-serif; }
.rc-sum-lbl { font-size:11px; color:#64748b; margin-top:4px; }
.rc-table { width:100%; border-collapse:collapse; margin-bottom:16px; }
.rc-table th { background:#3b82f6; color:white; padding:10px 12px; font-size:12px; text-align:left; }
.rc-table td { padding:9px 12px; border-bottom:1px solid #e2e8f0; font-size:13px; }
.rc-table tr:nth-child(even) td { background:#f8fafc; }
.rc-attendance { padding:12px 16px; background:#eff6ff; border-radius:8px; font-size:13px; color:#1e3a5f; margin-bottom:20px; }
.rc-comments { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
.rc-comment strong { font-size:13px; color:#1e3a5f; display:block; margin-bottom:6px; }
.rc-comment p { font-size:12px; color:#475569; line-height:1.6; }
.rc-sig { margin-top:12px; font-size:12px; color:#94a3b8; }
</style>
