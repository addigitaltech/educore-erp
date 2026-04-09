<template>
  <div>
    <div class="page-header">
      <div><h2>Notifications</h2><p>{{ unread }} unread notifications</p></div>
      <button class="btn btn-ghost" @click="markAll">Mark All Read</button>
    </div>
    <div class="card">
      <div v-if="loading" class="loading-overlay"><div class="spinner"></div></div>
      <div v-else-if="notifications.length===0" class="empty-state"><div class="empty-icon">🔔</div><h3>No notifications</h3><p>You're all caught up!</p></div>
      <div v-else class="notif-list">
        <div v-for="n in notifications" :key="n.id" :class="['notif-item',!n.read_at?'unread':'']" @click="markRead(n)">
          <div class="notif-icon" style="background:rgba(59,130,246,0.15);width:36px;height:36px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0">
            {{ typeIcon(n.type) }}
          </div>
          <div style="flex:1">
            <div style="font-size:13px;font-weight:600">{{ parseData(n).title }}</div>
            <div style="font-size:12px;color:var(--text2);margin-top:2px">{{ parseData(n).body }}</div>
            <div style="font-size:11px;color:var(--text3);margin-top:4px">{{ timeAgo(n.created_at) }}</div>
          </div>
          <div v-if="!n.read_at" style="width:7px;height:7px;background:var(--accent);border-radius:50%;flex-shrink:0;margin-top:6px"></div>
        </div>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, computed, onMounted } from 'vue'
import { notificationsAPI } from '../../services/api'
const loading=ref(true),notifications=ref([])
const unread=computed(()=>notifications.value.filter(n=>!n.read_at).length)
const typeIcon=t=>({'exam':'📝','result':'📊','payment':'💰','attendance':'✅','announcement':'📢','library':'📚'})[t?.split('\\').pop()?.toLowerCase()]||'🔔'
function parseData(n){try{return JSON.parse(n.data||'{}')}catch{return{title:'Notification',body:''}}}
function timeAgo(d){if(!d)return'';const s=Math.floor((Date.now()-new Date(d))/1000);return s<60?`${s}s ago`:s<3600?`${Math.floor(s/60)}m ago`:s<86400?`${Math.floor(s/3600)}h ago`:`${Math.floor(s/86400)}d ago`}
onMounted(async()=>{loading.value=true;try{const r=await notificationsAPI.list();notifications.value=r.data||[]}finally{loading.value=false}})
async function markRead(n){if(n.read_at)return;try{await notificationsAPI.markRead(n.id);n.read_at=new Date().toISOString()}catch{}}
async function markAll(){try{await notificationsAPI.markAllRead();notifications.value.forEach(n=>n.read_at=new Date().toISOString())}catch{}}
</script>
<style scoped>
.notif-list{display:flex;flex-direction:column}
.notif-item{display:flex;align-items:flex-start;gap:12px;padding:14px 12px;cursor:pointer;transition:background .15s;border-bottom:1px solid var(--border);border-radius:8px}
.notif-item:hover{background:var(--card2)}
.notif-item.unread{background:rgba(59,130,246,0.04)}
</style>
