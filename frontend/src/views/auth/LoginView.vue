<template>
  <div style="min-height:100vh;background:var(--bg);display:flex;align-items:center;justify-content:center;padding:16px">
    <div style="width:100%;max-width:420px">
      <!-- Logo -->
      <div style="text-align:center;margin-bottom:32px">
        <div style="width:60px;height:60px;background:var(--primary);border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:28px;margin:0 auto 12px">🎓</div>
        <h1 style="font-size:22px;font-weight:800;color:var(--text)">EduCore ERP</h1>
        <p style="font-size:13px;color:var(--text3);margin-top:4px">Sign in to your school portal</p>
      </div>

      <!-- Card -->
      <div class="card" style="padding:32px">
        <div v-if="auth.error" class="alert alert-danger" style="margin-bottom:16px">
          <i class="fa fa-circle-exclamation" style="margin-right:6px"></i>{{ auth.error }}
        </div>

        <div class="form-group">
          <label class="form-label">Email Address</label>
          <div class="input-group">
            <i class="fa fa-envelope input-icon"></i>
            <input v-model="email" class="form-input" type="email" placeholder="your@email.com" @keyup.enter="handleLogin"/>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Password</label>
          <div class="input-group">
            <i class="fa fa-lock input-icon"></i>
            <input v-model="password" :type="showPw?'text':'password'" class="form-input" placeholder="Enter password" @keyup.enter="handleLogin" style="padding-right:40px"/>
            <button @click="showPw=!showPw" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text3);cursor:pointer;font-size:14px">
              <i :class="['fa', showPw?'fa-eye-slash':'fa-eye']"></i>
            </button>
          </div>
        </div>

        <button class="btn btn-primary" style="width:100%;padding:11px;font-size:14px;margin-top:4px" @click="handleLogin" :disabled="auth.loading">
          <span v-if="auth.loading" class="spinner" style="width:16px;height:16px;border-color:rgba(255,255,255,0.3);border-top-color:#fff"></span>
          <span v-else><i class="fa fa-sign-in-alt" style="margin-right:8px"></i>Sign In</span>
        </button>

        <!-- Demo accounts -->
        <div style="margin-top:24px;padding-top:16px;border-top:1px solid var(--border)">
          <div style="font-size:11px;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:10px">Quick Demo Login</div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px">
            <button v-for="d in demoAccounts" :key="d.role" class="btn btn-ghost btn-sm" @click="quickLogin(d)" style="justify-content:flex-start;font-size:12px;padding:6px 10px">
              {{ d.icon }} {{ d.role }}
            </button>
          </div>
        </div>
      </div>

      <div style="text-align:center;margin-top:16px;font-size:12px;color:var(--text3)">
        EduCore ERP · School Management System
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from '../../store/auth'
const auth = useAuthStore()
const email = ref(''), password = ref(''), showPw = ref(false)

const demoAccounts = [
  { role:'Admin',      icon:'🔑', email:'john@greenfield.edu',  password:'password' },
  { role:'Teacher',    icon:'👨‍🏫', email:'sarah@greenfield.edu', password:'password' },
  { role:'Student',    icon:'🎒', email:'michael@student.edu',  password:'password' },
  { role:'Accountant', icon:'💰', email:'james@greenfield.edu', password:'password' },
]

async function handleLogin(){
  if(!email.value||!password.value)return
  await auth.login({email:email.value,password:password.value})
}
function quickLogin(d){email.value=d.email;password.value=d.password;handleLogin()}
</script>
