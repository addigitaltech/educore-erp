<template>
  <div class="exam-wrapper">
    <!-- Loading -->
    <div v-if="loading" class="loading-overlay" style="height:400px"><div class="spinner"></div></div>

    <!-- Submitted -->
    <div v-else-if="submitted" class="card" style="max-width:500px;margin:60px auto;text-align:center">
      <div style="font-size:56px;margin-bottom:16px">🎓</div>
      <div style="width:100px;height:100px;border-radius:50%;border:4px solid var(--accent);display:flex;flex-direction:column;align-items:center;justify-content:center;margin:0 auto 20px">
        <div style="font-family:'Syne',sans-serif;font-size:36px;font-weight:800;color:var(--accent2)">{{ result.grade }}</div>
        <div style="font-size:12px;color:var(--text2)">{{ result.score }}/{{ result.total }}</div>
      </div>
      <div style="font-size:18px;font-weight:700;margin-bottom:8px">Exam Submitted!</div>
      <div style="font-size:13px;color:var(--text2);margin-bottom:24px">{{ result.percentage }}% · {{ result.passed?'Passed ✅':'Failed ❌' }}</div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:24px">
        <div style="background:var(--card2);border-radius:10px;padding:14px"><div style="font-size:20px;font-weight:800;color:var(--green2)">{{ result.score }}</div><div style="font-size:11px;color:var(--text3)">Correct</div></div>
        <div style="background:var(--card2);border-radius:10px;padding:14px"><div style="font-size:20px;font-weight:800;color:var(--red)">{{ result.total-result.score }}</div><div style="font-size:11px;color:var(--text3)">Wrong</div></div>
        <div style="background:var(--card2);border-radius:10px;padding:14px"><div style="font-size:20px;font-weight:800;color:var(--yellow)">{{ result.percentage }}%</div><div style="font-size:11px;color:var(--text3)">Score</div></div>
      </div>
      <router-link to="/student/results" class="btn btn-blue" style="width:100%;justify-content:center">View Full Results</router-link>
    </div>

    <!-- Exam in progress -->
    <template v-else-if="attempt && questions.length">
      <!-- Header -->
      <div class="exam-header">
        <div>
          <div style="font-size:18px;font-weight:700">{{ exam.title }}</div>
          <div style="font-size:13px;color:var(--text2);margin-top:4px">{{ exam.school_class?.name }} · {{ questions.length }} Questions · {{ exam.total_marks }} Marks</div>
        </div>
        <div style="text-align:right">
          <div style="font-size:11px;color:var(--text3);margin-bottom:4px">Time Remaining</div>
          <div :class="['exam-timer',timeLeft<300?'danger':timeLeft<600?'warning':'']">{{ formatTime(timeLeft) }}</div>
        </div>
      </div>

      <!-- Question Navigation -->
      <div class="card" style="margin-bottom:16px;padding:16px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;flex-wrap:wrap;gap:8px">
          <span style="font-size:12px;color:var(--text2)">Question Navigation</span>
          <div style="display:flex;gap:10px;font-size:11px;color:var(--text3)">
            <span>🟩 Answered ({{ answeredCount }})</span>
            <span>🟦 Current</span>
            <span>🟨 Flagged</span>
          </div>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:7px">
          <button v-for="(q,i) in questions" :key="q.id"
            :class="['q-btn', i===currentIdx?'current': flagged[q.id]?'flagged': answers[q.id]!==undefined?'answered':'']"
            @click="goTo(i)">{{ i+1 }}</button>
        </div>
      </div>

      <!-- Question Card -->
      <div class="question-card">
        <div class="question-num">
          <span style="background:var(--bg2);padding:3px 10px;border-radius:20px">Question {{ currentIdx+1 }} of {{ questions.length }}</span>
          <span class="badge badge-info">{{ qTypeLabel(currentQ.type) }}</span>
          <span style="margin-left:auto;color:var(--text3)">{{ currentQ.marks }} mark{{ currentQ.marks>1?'s':'' }}</span>
        </div>
        <div class="question-text">{{ currentQ.question_text }}</div>
        <div v-for="(opt,oi) in parsedOptions" :key="oi"
          :class="['option',answers[currentQ.id]===oi?'selected':'']"
          @click="selectAnswer(oi)">
          <div class="option-letter">{{ String.fromCharCode(65+oi) }}</div>
          <span>{{ opt.text }}</span>
        </div>
      </div>

      <!-- Controls -->
      <div style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;flex-wrap:wrap;gap:10px">
        <button class="btn btn-ghost" :disabled="currentIdx===0" @click="goTo(currentIdx-1)">← Previous</button>
        <div style="display:flex;gap:10px">
          <button class="btn btn-ghost" @click="toggleFlag()">{{ flagged[currentQ.id]?'🚩 Unflag':'🚩 Flag' }}</button>
          <button class="btn btn-ghost" :disabled="currentIdx===questions.length-1" @click="goTo(currentIdx+1)">Next →</button>
        </div>
        <button class="btn btn-blue" @click="confirmSubmit">Submit Exam ✓</button>
      </div>

      <!-- Progress bar -->
      <div style="margin-top:14px;padding:14px;background:var(--card);border-radius:10px;border:1px solid var(--border)">
        <div style="display:flex;justify-content:space-between;font-size:12px;color:var(--text3);margin-bottom:8px">
          <span>Progress: <strong style="color:var(--text)">{{ answeredCount }}/{{ questions.length }}</strong></span>
          <span>Flagged: <strong style="color:var(--yellow)">{{ flaggedCount }}</strong></span>
          <span>Remaining: <strong style="color:var(--text)">{{ questions.length-answeredCount }}</strong></span>
        </div>
        <div class="progress"><div class="progress-bar" :style="{width:(answeredCount/questions.length*100)+'%',background:'var(--accent)'}"></div></div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { examsAPI, examAttemptsAPI } from '../../services/api'
import { useToast } from 'vue-toastification'

const route=useRoute(),router=useRouter(),toast=useToast()
const loading=ref(true),submitted=ref(false)
const exam=ref({}),questions=ref([]),attempt=ref(null)
const currentIdx=ref(0),answers=ref({}),flagged=ref({})
const timeLeft=ref(0),result=ref({})
let timer=null

const currentQ=computed(()=>questions.value[currentIdx.value]||{})
const parsedOptions=computed(()=>{try{return JSON.parse(currentQ.value.options||'[]')}catch{return[]}})
const answeredCount=computed(()=>Object.keys(answers.value).length)
const flaggedCount=computed(()=>Object.values(flagged.value).filter(Boolean).length)

function qTypeLabel(t){return{mcq:'Multiple Choice',true_false:'True/False',essay:'Essay',fill_blank:'Fill in Blank'}[t]||t}
function formatTime(s){const h=Math.floor(s/3600),m=Math.floor((s%3600)/60),sec=s%60;return`${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(sec).padStart(2,'0')}`}

function goTo(i){currentIdx.value=i}
function selectAnswer(optIdx){answers.value={...answers.value,[currentQ.value.id]:optIdx}}
function toggleFlag(){flagged.value={...flagged.value,[currentQ.value.id]:!flagged.value[currentQ.value.id]}}

function startTimer(mins){
  timeLeft.value=mins*60
  timer=setInterval(()=>{
    timeLeft.value--
    if(timeLeft.value<=0){clearInterval(timer);submitExam()}
  },1000)
}

async function confirmSubmit(){
  const unanswered=questions.value.length-answeredCount.value
  if(unanswered>0&&!confirm(`You have ${unanswered} unanswered question(s). Submit anyway?`))return
  await submitExam()
}

async function submitExam(){
  clearInterval(timer)
  try{
    const res=await examAttemptsAPI.submit(attempt.value.id)
    result.value=res.data
    submitted.value=true
  }catch{toast.error('Failed to submit exam')}
}

onMounted(async()=>{
  loading.value=true
  try{
    const examId=route.params.id
    const res=await examAttemptsAPI.start({exam_id:examId})
    attempt.value=res.data.attempt
    exam.value=res.data.exam
    questions.value=res.data.questions||[]
    startTimer(exam.value.duration_minutes||60)
  }catch(e){
    toast.error(e.response?.data?.message||'Failed to start exam')
    router.push('/student/exams')
  }finally{loading.value=false}
})

onUnmounted(()=>clearInterval(timer))
</script>

<style scoped>
.exam-wrapper{max-width:900px;margin:0 auto}
.exam-header{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:20px 24px;display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:12px}
.exam-timer{font-family:'JetBrains Mono',monospace;font-size:28px;font-weight:700;color:var(--green2)}
.exam-timer.warning{color:var(--yellow)}
.exam-timer.danger{color:var(--red);animation:pulse 1s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:0.5}}
.q-btn{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;cursor:pointer;border:1px solid var(--border);background:var(--bg2);color:var(--text2);transition:all .15s}
.q-btn.answered{background:rgba(16,185,129,0.2);border-color:var(--green);color:var(--green2)}
.q-btn.current{background:var(--accent);border-color:var(--accent);color:white}
.q-btn.flagged{background:rgba(245,158,11,0.2);border-color:var(--yellow);color:#fcd34d}
.question-card{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:28px}
.question-num{font-size:12px;color:var(--text3);margin-bottom:12px;display:flex;gap:8px;align-items:center;flex-wrap:wrap}
.question-text{font-size:16px;line-height:1.6;margin-bottom:24px}
.option{display:flex;align-items:center;gap:14px;padding:14px 18px;border-radius:10px;border:1px solid var(--border);margin-bottom:10px;cursor:pointer;transition:all .15s}
.option:hover{border-color:var(--accent);background:rgba(59,130,246,0.05)}
.option.selected{border-color:var(--accent);background:rgba(59,130,246,0.1)}
.option-letter{width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;background:var(--bg3);flex-shrink:0}
</style>
