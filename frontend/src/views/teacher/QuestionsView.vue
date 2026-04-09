<!-- QuestionsView teacher -->
<template>
  <div>
    <div class="page-header"><div><h2>Question Bank</h2><p>Create and manage exam questions</p></div><button class="btn btn-blue" @click="showModal=true">+ Add Question</button></div>
    <div class="card">
      <div style="display:flex;gap:12px;margin-bottom:16px">
        <select v-model="examFilter" class="form-input" style="max-width:220px" @change="fetchQuestions">
          <option value="">Select Exam</option>
          <option v-for="e in exams" :key="e.id" :value="e.id">{{ e.title }}</option>
        </select>
      </div>
      <div v-if="loading" class="loading-overlay"><div class="spinner"></div></div>
      <div v-else>
        <div v-for="(q,i) in questions" :key="q.id" style="padding:14px;background:var(--card2);border-radius:10px;margin-bottom:10px;border:1px solid var(--border)">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px">
            <div style="flex:1">
              <div style="font-size:11px;color:var(--text3);margin-bottom:6px">Q{{ i+1 }} · <span class="badge badge-info">{{ q.type }}</span> · {{ q.marks }} mark(s)</div>
              <div style="font-size:14px;font-weight:500">{{ q.question_text }}</div>
              <div v-if="q.options" style="margin-top:8px;display:flex;flex-wrap:wrap;gap:6px">
                <span v-for="(opt,oi) in parseOptions(q.options)" :key="oi" :style="{padding:'4px 10px',borderRadius:'6px',fontSize:'12px',background:opt.is_correct?'rgba(16,185,129,0.2)':'var(--bg3)',color:opt.is_correct?'var(--green2)':'var(--text2)'}">{{ String.fromCharCode(65+oi) }}. {{ opt.text }}</span>
              </div>
            </div>
            <button class="btn btn-red btn-sm" @click="deleteQ(q.id)">🗑️</button>
          </div>
        </div>
        <div v-if="questions.length===0" class="empty-state"><div class="empty-icon">❓</div><h3>No questions yet</h3><p>Select an exam and add questions</p></div>
      </div>
    </div>
    <div v-if="showModal" class="modal-overlay" @click.self="showModal=false">
      <div class="modal">
        <div class="modal-header"><div class="modal-title">Add Question</div><button class="btn btn-ghost btn-sm" @click="showModal=false">×</button></div>
        <div class="form-group"><label class="form-label">Exam</label><select v-model="form.exam_id" class="form-input"><option v-for="e in exams" :key="e.id" :value="e.id">{{ e.title }}</option></select></div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Type</label><select v-model="form.type" class="form-input"><option value="mcq">Multiple Choice</option><option value="true_false">True/False</option><option value="essay">Essay</option></select></div>
          <div class="form-group"><label class="form-label">Marks</label><input v-model="form.marks" class="form-input" type="number" min="1"/></div>
        </div>
        <div class="form-group"><label class="form-label">Question Text</label><textarea v-model="form.question_text" class="form-input" rows="3"></textarea></div>
        <div v-if="form.type==='mcq'">
          <div v-for="(opt,i) in form.options" :key="i" class="form-group" style="display:flex;align-items:center;gap:10px">
            <input v-model="opt.text" class="form-input" :placeholder="`Option ${String.fromCharCode(65+i)}`" style="flex:1"/>
            <label style="display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer;white-space:nowrap"><input type="radio" :name="'correct'" :checked="opt.is_correct" @change="setCorrect(i)" style="accent-color:var(--green)"> Correct</label>
          </div>
        </div>
        <div v-if="form.type==='true_false'" style="display:flex;gap:10px">
          <button :class="['btn',form.correct_answer==='True'?'btn-green':'btn-ghost']" @click="form.correct_answer='True'">True</button>
          <button :class="['btn',form.correct_answer==='False'?'btn-green':'btn-ghost']" @click="form.correct_answer='False'">False</button>
        </div>
        <div style="display:flex;gap:10px;margin-top:12px"><button class="btn btn-blue" @click="saveQuestion" :disabled="saving"><span v-if="saving" class="spinner"></span><span v-else>Add Question</span></button><button class="btn btn-ghost" @click="showModal=false">Cancel</button></div>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, reactive, onMounted } from 'vue'
import { questionsAPI, examsAPI } from '../../services/api'
import { useToast } from 'vue-toastification'
const toast=useToast(),loading=ref(false),saving=ref(false),showModal=ref(false)
const questions=ref([]),exams=ref([]),examFilter=ref('')
const form=reactive({exam_id:'',type:'mcq',question_text:'',marks:2,options:[{text:'',is_correct:true},{text:'',is_correct:false},{text:'',is_correct:false},{text:'',is_correct:false}],correct_answer:''})
function parseOptions(o){try{return JSON.parse(o)}catch{return[]}}
function setCorrect(i){form.options.forEach((o,j)=>o.is_correct=j===i)}
onMounted(async()=>{const r=await examsAPI.list({});exams.value=r.data.data||[]})
async function fetchQuestions(){if(!examFilter.value)return;loading.value=true;try{const r=await questionsAPI.list({exam_id:examFilter.value});questions.value=r.data||[]}finally{loading.value=false}}
async function saveQuestion(){saving.value=true;try{const payload={...form,options:form.type==='mcq'?form.options:form.type==='true_false'?[{text:'True',is_correct:form.correct_answer==='True'},{text:'False',is_correct:form.correct_answer==='False'}]:undefined};await questionsAPI.create(payload);toast.success('Question added!');showModal.value=false;fetchQuestions()}catch{toast.error('Failed')}finally{saving.value=false}}
async function deleteQ(id){if(!confirm('Delete?'))return;try{await questionsAPI.delete(id);questions.value=questions.value.filter(q=>q.id!==id);toast.success('Deleted')}catch{toast.error('Failed')}}
</script>
