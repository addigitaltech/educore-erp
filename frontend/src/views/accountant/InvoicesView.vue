<template>
  <div>
    <div class="page-header"><div><h2>Invoices</h2><p>All term fee invoices</p></div>
      <div style="display:flex;gap:10px"><button class="btn btn-ghost" @click="bulkGenerate">Generate All</button><button class="btn btn-blue" @click="showModal=true">+ Create Invoice</button></div>
    </div>
    <div class="card">
      <div style="display:flex;gap:12px;margin-bottom:16px;flex-wrap:wrap">
        <select v-model="statusFilter" class="form-input" style="max-width:140px" @change="fetch"><option value="">All Status</option><option value="paid">Paid</option><option value="partial">Partial</option><option value="unpaid">Unpaid</option></select>
        <button class="btn btn-ghost btn-sm" style="margin-left:auto">📥 Export CSV</button>
      </div>
      <div v-if="loading" class="loading-overlay"><div class="spinner"></div></div>
      <div v-else class="table-wrap">
        <table>
          <thead><tr><th>Student</th><th>Invoice #</th><th>Amount</th><th>Paid</th><th>Balance</th><th>Due Date</th><th>Status</th></tr></thead>
          <tbody>
            <tr v-for="inv in invoices" :key="inv.id">
              <td style="font-weight:500">{{ inv.student?.user?.first_name }} {{ inv.student?.user?.last_name }}</td>
              <td class="mono" style="font-size:11px">{{ inv.invoice_number }}</td>
              <td>₦{{ Number(inv.total_amount).toLocaleString() }}</td>
              <td style="color:var(--green2)">₦{{ Number(inv.amount_paid).toLocaleString() }}</td>
              <td :style="{color:(inv.balance>0)?'var(--red)':'var(--text3)'}">₦{{ Number(inv.balance||0).toLocaleString() }}</td>
              <td style="font-size:12px">{{ inv.due_date }}</td>
              <td><span :class="['badge',inv.status==='paid'?'badge-success':inv.status==='partial'?'badge-warning':'badge-danger']">{{ inv.status }}</span></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div style="padding:12px 0;font-size:13px;color:var(--text2)">Total: {{ invoices.length }} invoices</div>
    </div>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue'
import { financeAPI } from '../../services/api'
import { useToast } from 'vue-toastification'
const toast=useToast(),loading=ref(true),showModal=ref(false)
const invoices=ref([]),statusFilter=ref('')
onMounted(fetch)
async function fetch(){loading.value=true;try{const r=await financeAPI.invoices({status:statusFilter.value});invoices.value=r.data?.data||[]}finally{loading.value=false}}
async function bulkGenerate(){try{const r=await financeAPI.bulkGenerate({});toast.success(r.data.message);fetch()}catch{toast.error('Failed')}}
</script>
