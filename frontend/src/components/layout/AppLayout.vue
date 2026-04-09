<template>
  <div class="app-layout">
    <!-- Mobile overlay -->
    <div :class="['sidebar-overlay', { show: sidebarOpen }]" @click="sidebarOpen = false"></div>

    <!-- Sidebar -->
    <aside :class="['sidebar', { open: sidebarOpen }]">
      <!-- Logo -->
      <div class="sidebar-logo">
        <div class="sidebar-logo-icon">🎓</div>
        <div class="sidebar-logo-text">
          <div class="sidebar-logo-name">{{ schoolName }}</div>
          <div class="sidebar-logo-sub">School ERP</div>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="sidebar-nav">
        <template v-for="item in navItems" :key="item.path || item.section">
          <div v-if="item.section" class="nav-section">{{ item.section }}</div>
          <router-link
            v-else
            :to="item.path"
            class="nav-item"
            :class="{ active: isActive(item.path) }"
            @click="sidebarOpen = false"
          >
            <div class="nav-icon">
              <i v-if="item.faIcon" :class="item.faIcon"></i>
              <span v-else>{{ item.icon }}</span>
            </div>
            <span class="nav-label">{{ item.label }}</span>
            <span v-if="item.badge" class="nav-badge">{{ item.badge }}</span>
          </router-link>
        </template>
      </nav>

      <!-- User Footer -->
      <div class="sidebar-footer">
        <div class="sidebar-user" @click="router.push(`/${auth.role}/profile`)">
          <div class="sidebar-user-avatar">
            <img v-if="auth.user?.avatar" :src="auth.user.avatar" :alt="auth.user?.name"/>
            <span v-else>{{ initials }}</span>
          </div>
          <div style="flex:1;min-width:0">
            <div class="sidebar-user-name">{{ auth.user?.first_name }} {{ auth.user?.last_name }}</div>
            <div class="sidebar-user-role">{{ auth.role }}</div>
          </div>
          <i class="fa fa-chevron-right" style="font-size:10px;color:var(--sidebar-text2)"></i>
        </div>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
      <!-- Topbar -->
      <header class="topbar">
        <button class="topbar-menu-btn" @click="sidebarOpen = !sidebarOpen">
          <i class="fa fa-bars"></i>
        </button>
        <div class="topbar-title">{{ currentPageTitle }}</div>
        <div class="topbar-right">
          <!-- Notifications -->
          <button class="topbar-btn" @click="router.push(`/${auth.role}/notifications`)" title="Notifications">
            <i class="fa fa-bell"></i>
            <span class="topbar-notif-dot"></span>
          </button>
          <!-- Profile -->
          <div class="topbar-profile" @click="router.push(`/${auth.role}/profile`)">
            <div class="topbar-avatar">
              <img v-if="auth.user?.avatar" :src="auth.user.avatar" :alt="auth.user?.name"/>
              <span v-else>{{ initials }}</span>
            </div>
            <div class="d-none-mobile">
              <div class="topbar-user-name">{{ auth.user?.first_name }}</div>
              <div class="topbar-user-role">{{ auth.role }}</div>
            </div>
          </div>
          <!-- Logout -->
          <button class="topbar-btn" @click="handleLogout" title="Logout">
            <i class="fa fa-sign-out-alt"></i>
          </button>
        </div>
      </header>

      <!-- Page Content -->
      <main class="page-content">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../../store/auth'

const props = defineProps({ navItems: Array })
const auth   = useAuthStore()
const route  = useRoute()
const router = useRouter()

const sidebarOpen = ref(false)

const initials = computed(() => {
  const f = auth.user?.first_name?.[0] ?? ''
  const l = auth.user?.last_name?.[0] ?? ''
  return (f + l).toUpperCase()
})

const schoolName = computed(() => auth.user?.school?.name ?? 'EduCore ERP')

const currentPageTitle = computed(() => {
  const labels = {
    dashboard: 'Dashboard', students: 'Students', teachers: 'Teachers',
    classes: 'Classes & Arms', subjects: 'Subjects', exams: 'Examinations',
    results: 'Results', attendance: 'Attendance', announcements: 'Announcements',
    settings: 'School Settings', finance: 'Finance', library: 'Library',
    timetable: 'Timetable', analytics: 'Analytics', profile: 'My Profile',
    notifications: 'Notifications', 'report-card': 'Report Card',
    materials: 'Course Materials', performance: 'Performance', questions: 'Question Bank',
  }
  const seg = route.path.split('/').pop()
  return labels[seg] ?? 'EduCore ERP'
})

function isActive(path) {
  return route.path === path || route.path.startsWith(path + '/')
}

async function handleLogout() {
  if (confirm('Are you sure you want to logout?')) {
    await auth.logout()
  }
}
</script>
