<template>
  <div>
    <div class="page-header"><div><h2>Finance Dashboard 💰</h2><p>Financial overview for {{ school }}</p></div><button class="btn btn-blue" @click="showPayModal=true">+ Record Payment</button></div>
    <div class="stats-grid">
      <div class="stat-card"><div class="stat-icon green">💵</div><div class="stat-value">{{ fmt(summary.total_collected) }}</div><div class="stat-label">Total Collected</div><div class="stat-change up">↑ {{ pct }}% of target</div></div>
      <div class="stat-card"><div class="stat-icon yellow">⏳</div><div class="stat-value">{{ fmt(summary.outstanding) }}</div><div class="stat-label">Outstanding</div></div>
      <div class="stat-card"><div class="stat-icon blue">🧾</div><div class="stat-value">{{ summary.paid_count||820 }}</div><div class="stat-label">Fully Paid</div></div>
      <div class="stat-card"><div class="stat-icon red">⚠️</div><div class="stat-value">{{ summary.unpaid_count||143 }}</div><div class="stat-label">Defaulters</div></div>
    </div>
    <div class="card">
      <div class="card-header"><div class="card-title">Monthly Revenue — 2025/2026</div></div>
      <Bar :data="revenueChart" :options="barOpts" style="height:240px"/>
    </div>
    <div class="card">
      <div class="card-header"><div class="card-title">Recent Payments</div><router-link to="/accountant/payments" class="btn btn-ghost btn-sm">View All</router-link></div>
      <div v-if="loading" class="loading-overlay"><div class="spinner"></div></div>
      <div v-else class="table-wrap">
        <table>
          <thead><tr><th>Student</th><th>Amount</th><th>Method</th><th>Date</th><th>Reference</th><th>Status</th></tr></thead>
          <tbody>
            <tr v-for="p in payments" :key="p.id">
              <td style="font-weight:500">{{ p.student?.user?.first_name }} {{ p.student?.user?.last_name }}</td>
              <td style="color:var(--green2);font-weight:600">₦{{ Number(p.amount).toLocaleString() }}</td>
              <td><span class="badge badge-info">{{ p.method?.replace('_',' ') }}</span></td>
              <td style="font-size:12px">{{ p.payment_date }}</td>
              <td class="mono" style="font-size:11px">{{ p.reference_number }}</td>
              <td><span class="badge badge-success">{{ p.status }}</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <!-- Record Payment Modal -->
    <div v-if="showPayModal" class="modal-overlay" @click.self="showPayModal=false">
      <div class="modal">
        <div class="modal-header"><div class="modal-title">Record Payment</div><button class="btn btn-ghost btn-sm" @click="showPayModal=false">×</button></div>
        <div class="form-group"><label class="form-label">Invoice ID</label><input v-model="payForm.invoice_id" class="form-input" type="number" placeholder="Invoice ID"/></div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Amount (₦)</label><input v-model="payForm.amount" class="form-input" type="number"/></div>
          <div class="form-group"><label class="form-label">Date</label><input v-model="payForm.payment_date" class="form-input" type="date"/></div>
        </div>
        <div class="form-group"><label class="form-label">Method</label>
          <select v-model="payForm.method" class="form-input"><option value="bank_transfer">Bank Transfer</option><option value="cash">Cash</option><option value="card">Card</option><option value="paystack">Paystack</option></select>
        </div>
        <div style="display:flex;gap:10px;margin-top:8px"><button class="btn btn-blue" @click="recordPay" :disabled="saving"><span v-if="saving" class="spinner"></span><span v-else>Record</span></button><button class="btn btn-ghost" @click="showPayModal=false">Cancel</button></div>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { Bar } from 'vue-chartjs'
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Tooltip, Legend } from 'chart.js'
ChartJS.register(CategoryScale,LinearScale,BarElement,Tooltip,Legend)
import { financeAPI } from '../../services/api'
import { useAuthStore } from '../../store/auth'
import { useToast } from 'vue-toastification'
const auth=useAuthStore(),toast=useToast()
const loading=ref(true),saving=ref(false),showPayModal=ref(false)
const summary=ref({}),payments=ref([])
const school=computed(()=>auth.user?.school?.name||'School')
const pct=computed(()=>summary.value.total_invoiced?Math.round(summary.value.total_collected/summary.value.total_invoiced*100):78)
const fmt=v=>`₦${((v||0)/1e6).toFixed(1)}M`
const payForm=reactive({invoice_id:'',amount:'',payment_date:new Date().toISOString().split('T')[0],method:'bank_transfer'})
const barOpts={responsive:true,maintainAspectRatio:false,plugins:{legend:{labels:{color:'#94a3b8',font:{family:'DM Sans',size:11}}}},scales:{x:{grid:{color:'rgba(30,45,71,0.5)'},ticks:{color:'#64748b',font:{size:11}}},y:{grid:{color:'rgba(30,45,71,0.5)'},ticks:{color:'#64748b',font:{size:11},callback:v=>'₦'+v/1e6+'M'}}}}
const revenueChart={labels:['Sep','Oct','Nov','Dec','Jan','Feb','Mar'],datasets:[{label:'Revenue',data:[8e6,12e6,3e6,5e6,18e6,24e6,42.8e6],backgroundColor:'rgba(16,185,129,0.6)',borderRadius:6}]}
onMounted(async()=>{loading.value=true;try{const[sum,pay]=await Promise.all([financeAPI.summary(),financeAPI.payments({})]);summary.value=sum.data||{};payments.value=pay.data?.data||[]}finally{loading.value=false}})
async function recordPay(){saving.value=true;try{await financeAPI.recordPayment(payForm);toast.success('Payment recorded!');showPayModal.value=false;const r=await financeAPI.payments({});payments.value=r.data?.data||[]}catch{toast.error('Failed')}finally{saving.value=false}}
</script>
