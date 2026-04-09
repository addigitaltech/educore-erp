<template>
  <div>
    <div class="page-header">
      <div><h2>Finance & Fees</h2><p>Fee collection, invoices, and financial reports</p></div>
      <div style="display:flex;gap:10px">
        <button class="btn btn-ghost" @click="bulkGenerate">Generate All Invoices</button>
        <button class="btn btn-blue" @click="showPayModal=true">+ Record Payment</button>
      </div>
    </div>

    <div class="stats-grid" style="grid-template-columns:repeat(4,1fr)">
      <div class="stat-card"><div class="stat-icon green">💰</div><div class="stat-value">₦{{ (summary.total_collected/1e6||42.8).toFixed(1) }}M</div><div class="stat-label">Total Collected</div><div class="stat-change up">↑ {{ Math.round((summary.total_collected||42.8e6)/(summary.total_invoiced||54.9e6)*100) }}% of target</div></div>
      <div class="stat-card"><div class="stat-icon yellow">⏳</div><div class="stat-value">₦{{ ((summary.outstanding||12.1e6)/1e6).toFixed(1) }}M</div><div class="stat-label">Outstanding</div></div>
      <div class="stat-card"><div class="stat-icon blue">🧾</div><div class="stat-value">{{ summary.paid_count||820 }}</div><div class="stat-label">Fully Paid</div></div>
      <div class="stat-card"><div class="stat-icon red">⚠️</div><div class="stat-value">{{ summary.unpaid_count||143 }}</div><div class="stat-label">Defaulters</div></div>
    </div>

    <div class="card">
      <div class="card-header">
        <div class="card-title">Fee Payment Records</div>
        <div style="display:flex;gap:8px">
          <select v-model="statusFilter" class="form-input" style="max-width:140px;padding:6px 12px;font-size:12px" @change="fetchInvoices">
            <option value="">All Status</option>
            <option value="paid">Paid</option><option value="partial">Partial</option><option value="unpaid">Unpaid</option>
          </select>
        </div>
      </div>
      <div v-if="loading" class="loading-overlay"><div class="spinner"></div></div>
      <div v-else class="table-wrap">
        <table>
          <thead><tr><th>Student</th><th>Invoice #</th><th>Amount</th><th>Paid</th><th>Balance</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody>
            <tr v-for="inv in invoices" :key="inv.id">
              <td style="font-weight:500">{{ inv.student?.user?.first_name }} {{ inv.student?.user?.last_name }}</td>
              <td class="mono" style="font-size:11px">{{ inv.invoice_number }}</td>
              <td>₦{{ Number(inv.total_amount).toLocaleString() }}</td>
              <td style="color:var(--green2)">₦{{ Number(inv.amount_paid).toLocaleString() }}</td>
              <td :style="{color:(inv.balance>0)?'var(--red)':'var(--text3)'}">₦{{ Number(inv.balance||0).toLocaleString() }}</td>
              <td><span :class="['badge',inv.status==='paid'?'badge-success':inv.status==='partial'?'badge-warning':'badge-danger']">{{ inv.status }}</span></td>
              <td><button class="btn btn-ghost btn-sm" @click="selectInvoice(inv)">Record Payment</button></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Record Payment Modal -->
    <div v-if="showPayModal" class="modal-overlay" @click.self="showPayModal=false">
      <div class="modal">
        <div class="modal-header"><div class="modal-title">Record Payment</div><button class="btn btn-ghost btn-sm" @click="showPayModal=false">×</button></div>
        <div class="form-group"><label class="form-label">Invoice</label>
          <select v-model="payForm.invoice_id" class="form-input">
            <option v-for="inv in invoices.filter(i=>i.status!=='paid')" :key="inv.id" :value="inv.id">{{ inv.invoice_number }} — {{ inv.student?.user?.first_name }} (₦{{ Number(inv.balance||0).toLocaleString() }} due)</option>
          </select>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Amount (₦)</label><input v-model="payForm.amount" class="form-input" type="number"/></div>
          <div class="form-group"><label class="form-label">Date</label><input v-model="payForm.payment_date" class="form-input" type="date"/></div>
        </div>
        <div class="form-group"><label class="form-label">Method</label>
          <select v-model="payForm.method" class="form-input">
            <option value="bank_transfer">Bank Transfer</option><option value="cash">Cash</option><option value="card">Card</option><option value="paystack">Paystack</option>
          </select>
        </div>
        <div class="form-group"><label class="form-label">Notes</label><textarea v-model="payForm.notes" class="form-input" rows="2"></textarea></div>
        <div style="display:flex;gap:10px;margin-top:8px">
          <button class="btn btn-blue" @click="recordPayment" :disabled="saving"><span v-if="saving" class="spinner"></span><span v-else>Record Payment</span></button>
          <button class="btn btn-ghost" @click="showPayModal=false">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, reactive, onMounted } from 'vue'
import { financeAPI } from '../../services/api'
import { useToast } from 'vue-toastification'
const toast = useToast()
const loading = ref(true), saving = ref(false), showPayModal = ref(false)
const invoices = ref([]), summary = ref({})
const statusFilter = ref('')
const payForm = reactive({ invoice_id:'', amount:'', payment_date: new Date().toISOString().split('T')[0], method:'bank_transfer', notes:'' })
onMounted(async () => { await Promise.all([fetchInvoices(), financeAPI.summary().then(r=>summary.value=r.data||{})]) })
async function fetchInvoices() { loading.value=true; try { const r = await financeAPI.invoices({ status:statusFilter.value }); invoices.value = r.data.data||[] } finally { loading.value=false } }
function selectInvoice(inv) { payForm.invoice_id = inv.id; payForm.amount = inv.balance||0; showPayModal.value=true }
async function recordPayment() {
  saving.value=true; try { await financeAPI.recordPayment(payForm); toast.success('Payment recorded!'); showPayModal.value=false; fetchInvoices() }
  catch(e) { toast.error(e.response?.data?.message||'Failed') } finally { saving.value=false }
}
async function bulkGenerate() { try { const r = await financeAPI.bulkGenerate({}); toast.success(r.data.message); fetchInvoices() } catch { toast.error('Failed') } }
</script>
