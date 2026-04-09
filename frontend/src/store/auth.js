import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authAPI } from '../services/api'
import router from '../router'

export const useAuthStore = defineStore('auth', () => {
  const user  = ref(JSON.parse(localStorage.getItem('educore_user') || 'null'))
  const token = ref(localStorage.getItem('educore_token') || null)
  const loading = ref(false)
  const error   = ref(null)

  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const role            = computed(() => user.value?.role || null)
  const schoolId        = computed(() => user.value?.school_id || null)
  const isAdmin         = computed(() => role.value === 'admin')
  const isTeacher       = computed(() => role.value === 'teacher')
  const isStudent       = computed(() => role.value === 'student')
  const isSuperAdmin    = computed(() => role.value === 'superadmin')

  async function login(credentials) {
    loading.value = true
    error.value   = null
    try {
      const res = await authAPI.login(credentials)
      token.value = res.data.token
      user.value  = res.data.user
      localStorage.setItem('educore_token', res.data.token)
      localStorage.setItem('educore_user',  JSON.stringify(res.data.user))
      redirectAfterLogin(res.data.user.role)
      return res.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Login failed'
      throw err
    } finally {
      loading.value = false
    }
  }

  function redirectAfterLogin(role) {
    const routes = {
      superadmin: '/superadmin/dashboard',
      admin:      '/admin/dashboard',
      teacher:    '/teacher/dashboard',
      student:    '/student/dashboard',
      parent:     '/parent/dashboard',
      accountant: '/accountant/dashboard',
      librarian:  '/librarian/dashboard',
    }
    router.push(routes[role] || '/dashboard')
  }

  async function logout() {
    try { await authAPI.logout() } catch {}
    token.value = null
    user.value  = null
    localStorage.removeItem('educore_token')
    localStorage.removeItem('educore_user')
    router.push('/login')
  }

  async function fetchMe() {
    try {
      const res = await authAPI.me()
      user.value = res.data
      localStorage.setItem('educore_user', JSON.stringify(res.data))
    } catch {}
  }

  async function updateProfile(data) {
    const res = await authAPI.updateProfile(data)
    user.value = res.data
    localStorage.setItem('educore_user', JSON.stringify(res.data))
    return res.data
  }

  return {
    user, token, loading, error,
    isAuthenticated, role, schoolId,
    isAdmin, isTeacher, isStudent, isSuperAdmin,
    login, logout, fetchMe, updateProfile,
    redirectAfterLogin,
  }
})
