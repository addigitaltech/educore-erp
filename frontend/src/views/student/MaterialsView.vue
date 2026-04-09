<template>
  <div>
    <div class="page-header"><div><h2>Course Materials</h2><p>Study resources uploaded by your teachers</p></div></div>
    <div class="card">
      <div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap">
        <input v-model="search" class="form-input" style="max-width:260px" placeholder="🔍 Search materials..."/>
        <select v-model="subjectFilter" class="form-input" style="max-width:160px">
          <option value="">All Subjects</option>
          <option v-for="s in subjects" :key="s" :value="s">{{ s }}</option>
        </select>
      </div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Title</th><th>Subject</th><th>Type</th><th>Size</th><th>Uploaded</th><th>Action</th></tr></thead>
          <tbody>
            <tr v-for="m in filteredMaterials" :key="m.title">
              <td style="font-weight:500">{{ m.title }}</td>
              <td>{{ m.subject }}</td>
              <td><span :class="['badge',m.type==='PDF'?'badge-danger':m.type==='PPT'?'badge-warning':'badge-info']">{{ m.type }}</span></td>
              <td style="font-size:12px;color:var(--text3)">{{ m.size }}</td>
              <td style="font-size:12px;color:var(--text3)">{{ m.date }}</td>
              <td><button class="btn btn-ghost btn-sm">📥 Download</button></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, computed } from 'vue'
const search = ref(''), subjectFilter = ref('')
const subjects = ['Mathematics','Physics','Chemistry','Biology','English','Economics','Government','Geography']
const materials = [
  { title:'Calculus Notes — Chapter 5', subject:'Mathematics', type:'PDF', size:'2.4 MB', date:'Mar 10' },
  { title:'Mechanics Revision Guide', subject:'Physics', type:'PDF', size:'1.8 MB', date:'Mar 8' },
  { title:'Organic Chemistry Diagrams', subject:'Chemistry', type:'PPT', size:'5.1 MB', date:'Mar 5' },
  { title:'Past Questions 2020–2025', subject:'English', type:'PDF', size:'3.2 MB', date:'Mar 1' },
  { title:'Cell Biology Video Notes', subject:'Biology', type:'DOC', size:'890 KB', date:'Feb 28' },
  { title:'Demand & Supply Worksheet', subject:'Economics', type:'PDF', size:'420 KB', date:'Feb 25' },
  { title:'Government Structures Summary', subject:'Government', type:'PDF', size:'1.1 MB', date:'Feb 20' },
  { title:'Map Reading Practice', subject:'Geography', type:'PPT', size:'3.8 MB', date:'Feb 15' },
]
const filteredMaterials = computed(() => materials.filter(m =>
  (!subjectFilter.value || m.subject === subjectFilter.value) &&
  (!search.value || m.title.toLowerCase().includes(search.value.toLowerCase()))
))
</script>
