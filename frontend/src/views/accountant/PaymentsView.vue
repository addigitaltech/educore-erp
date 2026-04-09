<template>
  <div>
    <div class="page-header"><div><h2>Payment Records</h2><p>All confirmed fee payments</p></div><button class="btn btn-blue" @click="showModal=true">+ Record Payment</button></div>
    <div class="card">
      <div v-if="loading" class="loading-overlay"><div class="spinner"></div></div>
      <div v-else class="table-wrap">
        <table>
          <thead><tr><th>Student</th><th>Amount Paid</th><th>Method</th><th>Date</th><th>Reference #</th><th>Status</th></tr></thead>
          <tbody>
            <tr v-for="p in payments" :key="p.id">
              <td style="font-weight:500">{{ p.student?.user?.first_name }} {{ p.student?.user?.last_name }}</td>
              <td style="color:var(--green2);font-weight:600">₦{{ Number(p.amount).toLocaleString() }}</td>
              <td><span class="badge badge-info">{{ p.method?.replace('_',' ') }}</span></td>
              <td style="font-size:12px">{{ p.payment_date }}</td>
              <td class="mono" style="font-size:11px">{{ p.reference_number }}</td>
              <td><span :class="['badge',p.status==='confirmed'?'badge-success':'badge-warning']">{{ p.status }}</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <div v-if="showModal" class="modal-overlay" @click.self="showModal=false">
      <div class="modal">
        <div class="modal-header"><div class="modal-title">Record Payment</div><button class="btn btn-ghost btn-sm" @click="showModal=false">×</button></div>
        <div class="form-group"><label class="form-label">Invoice ID</label><input v-model="form.invoice_id" class="form-input" type="number"/></div>
        <div class="form-row"><div class="form-group"><label class="form-label">Amount (₦)</label><input v-model="form.amount" class="form-input" type="number"/></div><div class="form-group"><label class="form-label">Date</label><input v-model="form.payment_date" class="form-input" type="date"/></div></div>
        <div class="form-group"><label class="form-label">Method</label><select v-model="form.method" class="form-input"><option value="bank_transfer">Bank Transfer</option><option value="cash">Cash</option><option value="card">Card</option></select></div>
        <div style="display:flex;gap:10px;margin-top:8px"><button class="btn btn-blue" @click="record" :disabled="saving"><span v-if="saving" class="spinner"></span><span v-else>Record</span></button><button class="btn btn-ghost" @click="showModal=false">Cancel</button></div>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, reactive, onMounted } from 'vue'
import { financeAPI } from '../../services/api'
import { useToast } from 'vue-toastification'
const toast=useToast(),loading=ref(true),saving=ref(false),showModal=ref(false)
const payments=ref([]),form=reactive({invoice_id:'',amount:'',payment_date:new Date().toISOString().split('T')[0],method:'bank_transfer'})
onMounted(async()=>{loading.value=true;try{const r=await financeAPI.payments({});payments.value=r.data?.data||[]}finally{loading.value=false}})
async function record(){saving.value=true;try{await financeAPI.recordPayment(form);toast.success('Recorded!');showModal.value=false;const r=await financeAPI.payments({});payments.value=r.data?.data||[]}catch{toast.error('Failed')}finally{saving.value=false}}
</script>
