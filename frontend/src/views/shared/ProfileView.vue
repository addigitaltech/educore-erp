<template>
  <div>
    <div class="profile-header">
      <div class="avatar avatar-xl">{{ auth.user?.initials }}</div>
      <div>
        <div class="profile-name">{{ auth.user?.name }}</div>
        <div class="profile-meta">
          <span :class="['role-badge',auth.role]" style="margin-right:8px">{{ auth.role }}</span>
          {{ auth.user?.school?.name }} · {{ auth.user?.email }}
        </div>
        <div style="display:flex;gap:10px;margin-top:12px">
          <button class="btn btn-blue btn-sm" @click="activeTab='edit'">Edit Profile</button>
          <button class="btn btn-ghost btn-sm" @click="activeTab='password'">Change Password</button>
        </div>
      </div>
    </div>

    <div class="tabs">
      <button v-for="t in tabs" :key="t.value" :class="['tab',{active:activeTab===t.value}]" @click="activeTab=t.value">{{ t.label }}</button>
    </div>

    <!-- Info tab -->
    <div v-if="activeTab==='info'" class="grid-2">
      <div class="card" style="margin-bottom:0">
        <div class="card-title" style="margin-bottom:16px">Personal Information</div>
        <div v-for="item in profileFields" :key="item.label" style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border)">
          <span style="font-size:12px;color:var(--text3)">{{ item.label }}</span>
          <span style="font-size:13px;font-weight:500">{{ item.value||'—' }}</span>
        </div>
      </div>
      <div class="card" style="margin-bottom:0">
        <div class="card-title" style="margin-bottom:16px">Account Details</div>
        <div v-for="item in accountFields" :key="item.label" style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border)">
          <span style="font-size:12px;color:var(--text3)">{{ item.label }}</span>
          <span style="font-size:13px;font-weight:500">{{ item.value||'—' }}</span>
        </div>
      </div>
    </div>

    <!-- Edit tab -->
    <div v-if="activeTab==='edit'" class="card" style="max-width:560px">
      <div class="card-title" style="margin-bottom:20px">Edit Profile</div>
      <div class="form-row">
        <div class="form-group"><label class="form-label">First Name</label><input v-model="editForm.first_name" class="form-input"/></div>
        <div class="form-group"><label class="form-label">Last Name</label><input v-model="editForm.last_name" class="form-input"/></div>
      </div>
      <div class="form-group"><label class="form-label">Phone</label><input v-model="editForm.phone" class="form-input"/></div>
      <div class="form-row">
        <div class="form-group"><label class="form-label">Date of Birth</label><input v-model="editForm.date_of_birth" class="form-input" type="date"/></div>
        <div class="form-group"><label class="form-label">Gender</label><select v-model="editForm.gender" class="form-input"><option value="male">Male</option><option value="female">Female</option></select></div>
      </div>
      <div class="form-group"><label class="form-label">Address</label><input v-model="editForm.address" class="form-input"/></div>
      <div style="display:flex;gap:10px;margin-top:8px">
        <button class="btn btn-blue" @click="saveProfile" :disabled="saving"><span v-if="saving" class="spinner"></span><span v-else>Save Changes</span></button>
        <button class="btn btn-ghost" @click="activeTab='info'">Cancel</button>
      </div>
    </div>

    <!-- Password tab -->
    <div v-if="activeTab==='password'" class="card" style="max-width:460px">
      <div class="card-title" style="margin-bottom:20px">Change Password</div>
      <div class="form-group"><label class="form-label">Current Password</label><input v-model="pwForm.current_password" class="form-input" type="password"/></div>
      <div class="form-group"><label class="form-label">New Password</label><input v-model="pwForm.new_password" class="form-input" type="password"/></div>
      <div class="form-group"><label class="form-label">Confirm New Password</label><input v-model="pwForm.new_password_confirmation" class="form-input" type="password"/></div>
      <button class="btn btn-blue" @click="changePassword" :disabled="savingPw"><span v-if="savingPw" class="spinner"></span><span v-else>Update Password</span></button>
    </div>
  </div>
</template>
<script setup>
import { ref, reactive, computed } from 'vue'
import { useAuthStore } from '../../store/auth'
import { authAPI } from '../../services/api'
import { useToast } from 'vue-toastification'
const auth=useAuthStore(),toast=useToast()
const activeTab=ref('info'),saving=ref(false),savingPw=ref(false)
const tabs=[{value:'info',label:'Personal Info'},{value:'edit',label:'Edit Profile'},{value:'password',label:'Security'}]
const profileFields=computed(()=>[
  {label:'Full Name',value:auth.user?.name},{label:'Email',value:auth.user?.email},
  {label:'Phone',value:auth.user?.phone},{label:'Gender',value:auth.user?.gender},
  {label:'Date of Birth',value:auth.user?.date_of_birth},{label:'Address',value:auth.user?.address},
])
const accountFields=computed(()=>[
  {label:'Role',value:auth.role},{label:'School',value:auth.user?.school?.name},
  {label:'School Plan',value:auth.user?.school?.plan},{label:'Current Session',value:auth.user?.school?.current_session},
  {label:'Current Term',value:auth.user?.school?.current_term+' Term'},{label:'Account Status',value:'Active'},
])
const editForm=reactive({first_name:auth.user?.first_name||'',last_name:auth.user?.last_name||'',phone:auth.user?.phone||'',date_of_birth:auth.user?.date_of_birth||'',gender:auth.user?.gender||'male',address:auth.user?.address||''})
const pwForm=reactive({current_password:'',new_password:'',new_password_confirmation:''})
async function saveProfile(){saving.value=true;try{await auth.updateProfile(editForm);toast.success('Profile updated!');activeTab.value='info'}catch(e){toast.error(e.response?.data?.message||'Failed')}finally{saving.value=false}}
async function changePassword(){savingPw.value=true;try{await authAPI.changePassword(pwForm);toast.success('Password updated!');Object.assign(pwForm,{current_password:'',new_password:'',new_password_confirmation:''})}catch(e){toast.error(e.response?.data?.message||'Failed')}finally{savingPw.value=false}}
</script>
<style scoped>
.profile-header{background:linear-gradient(135deg,var(--bg2),var(--card));border:1px solid var(--border);border-radius:14px;padding:28px;display:flex;gap:24px;align-items:center;margin-bottom:24px;flex-wrap:wrap}
.profile-name{font-size:22px;font-weight:800}
.profile-meta{font-size:13px;color:var(--text2);margin-top:6px}
</style>
