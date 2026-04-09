import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../store/auth'

const routes = [
  // Public
  { path: '/', redirect: '/login' },
  { path: '/login', component: () => import('../views/auth/LoginView.vue'), meta: { guest: true } },

  // Admin
  {
    path: '/admin',
    component: () => import('../views/admin/AdminLayout.vue'),
    meta: { requiresAuth: true, role: 'admin' },
    children: [
      { path: 'dashboard',      component: () => import('../views/admin/DashboardView.vue') },
      { path: 'students',       component: () => import('../views/admin/StudentsView.vue') },
      { path: 'teachers',       component: () => import('../views/admin/TeachersView.vue') },
      { path: 'classes',        component: () => import('../views/admin/ClassesView.vue') },
      { path: 'exams',          component: () => import('../views/admin/ExamsView.vue') },
      { path: 'results',        component: () => import('../views/admin/ResultsView.vue') },
      { path: 'attendance',     component: () => import('../views/admin/AttendanceView.vue') },
      { path: 'finance',        component: () => import('../views/admin/FinanceView.vue') },
      { path: 'library',        component: () => import('../views/admin/LibraryView.vue') },
      { path: 'timetable',      component: () => import('../views/admin/TimetableView.vue') },
      { path: 'announcements',  component: () => import('../views/admin/AnnouncementsView.vue') },
      { path: 'analytics',      component: () => import('../views/admin/AnalyticsView.vue') },
      { path: 'settings',       component: () => import('../views/admin/SettingsView.vue') },
      { path: 'notifications',  component: () => import('../views/shared/NotificationsView.vue') },
      { path: 'result-sheet/:id', component: () => import('../views/shared/ResultSheetView.vue') },
      { path: 'profile',        component: () => import('../views/shared/ProfileView.vue') },
    ]
  },

  // Teacher
  {
    path: '/teacher',
    component: () => import('../views/teacher/TeacherLayout.vue'),
    meta: { requiresAuth: true, role: 'teacher' },
    children: [
      { path: 'dashboard',    component: () => import('../views/teacher/DashboardView.vue') },
      { path: 'classes',      component: () => import('../views/teacher/ClassesView.vue') },
      { path: 'exams',        component: () => import('../views/teacher/ExamsView.vue') },
      { path: 'questions',    component: () => import('../views/teacher/QuestionsView.vue') },
      { path: 'attendance',   component: () => import('../views/teacher/AttendanceView.vue') },
      { path: 'performance',  component: () => import('../views/teacher/PerformanceView.vue') },
      { path: 'announcements',component: () => import('../views/admin/AnnouncementsView.vue') },
      { path: 'notifications',component: () => import('../views/shared/NotificationsView.vue') },
      { path: 'profile',      component: () => import('../views/shared/ProfileView.vue') },
    ]
  },

  // Student
  {
    path: '/student',
    component: () => import('../views/student/StudentLayout.vue'),
    meta: { requiresAuth: true, role: 'student' },
    children: [
      { path: 'dashboard',    component: () => import('../views/student/DashboardView.vue') },
      { path: 'exams',        component: () => import('../views/student/ExamsView.vue') },
      { path: 'exam/:id/take',component: () => import('../views/student/ExamTakeView.vue') },
      { path: 'results',      component: () => import('../views/student/ResultsView.vue') },
      { path: 'report-card',  component: () => import('../views/student/ReportCardView.vue') },
      { path: 'result-sheet', component: () => import('../views/shared/ResultSheetView.vue') },
      { path: 'materials',    component: () => import('../views/student/MaterialsView.vue') },
      { path: 'timetable',    component: () => import('../views/admin/TimetableView.vue') },
      { path: 'library',      component: () => import('../views/admin/LibraryView.vue') },
      { path: 'attendance',   component: () => import('../views/student/AttendanceView.vue') },
      { path: 'announcements',component: () => import('../views/admin/AnnouncementsView.vue') },
      { path: 'notifications',component: () => import('../views/shared/NotificationsView.vue') },
      { path: 'profile',      component: () => import('../views/shared/ProfileView.vue') },
    ]
  },

  // Parent
  {
    path: '/parent',
    component: () => import('../views/parent/ParentLayout.vue'),
    meta: { requiresAuth: true, role: 'parent' },
    children: [
      { path: 'dashboard',    component: () => import('../views/parent/DashboardView.vue') },
      { path: 'results',      component: () => import('../views/student/ResultsView.vue') },
      { path: 'report-card',  component: () => import('../views/student/ReportCardView.vue') },
      { path: 'result-sheet', component: () => import('../views/shared/ResultSheetView.vue') },
      { path: 'attendance',   component: () => import('../views/student/AttendanceView.vue') },
      { path: 'finance',      component: () => import('../views/admin/FinanceView.vue') },
      { path: 'announcements',component: () => import('../views/admin/AnnouncementsView.vue') },
      { path: 'notifications',component: () => import('../views/shared/NotificationsView.vue') },
      { path: 'profile',      component: () => import('../views/shared/ProfileView.vue') },
    ]
  },

  // Accountant
  {
    path: '/accountant',
    component: () => import('../views/accountant/AccountantLayout.vue'),
    meta: { requiresAuth: true, role: 'accountant' },
    children: [
      { path: 'dashboard',    component: () => import('../views/accountant/DashboardView.vue') },
      { path: 'invoices',     component: () => import('../views/accountant/InvoicesView.vue') },
      { path: 'payments',     component: () => import('../views/accountant/PaymentsView.vue') },
      { path: 'reports',      component: () => import('../views/accountant/ReportsView.vue') },
      { path: 'notifications',component: () => import('../views/shared/NotificationsView.vue') },
      { path: 'profile',      component: () => import('../views/shared/ProfileView.vue') },
    ]
  },

  // Librarian
  {
    path: '/librarian',
    component: () => import('../views/librarian/LibrarianLayout.vue'),
    meta: { requiresAuth: true, role: 'librarian' },
    children: [
      { path: 'dashboard',    component: () => import('../views/librarian/DashboardView.vue') },
      { path: 'books',        component: () => import('../views/admin/LibraryView.vue') },
      { path: 'borrows',      component: () => import('../views/librarian/BorrowsView.vue') },
      { path: 'overdue',      component: () => import('../views/librarian/OverdueView.vue') },
      { path: 'add-book',     component: () => import('../views/librarian/AddBookView.vue') },
      { path: 'notifications',component: () => import('../views/shared/NotificationsView.vue') },
      { path: 'profile',      component: () => import('../views/shared/ProfileView.vue') },
    ]
  },

  // Super Admin
  {
    path: '/superadmin',
    component: () => import('../views/superadmin/SuperAdminLayout.vue'),
    meta: { requiresAuth: true, role: 'superadmin' },
    children: [
      { path: 'dashboard',      component: () => import('../views/superadmin/DashboardView.vue') },
      { path: 'schools',        component: () => import('../views/superadmin/SchoolsView.vue') },
      { path: 'analytics',      component: () => import('../views/superadmin/AnalyticsView.vue') },
      { path: 'profile',        component: () => import('../views/shared/ProfileView.vue') },
      { path: 'notifications',  component: () => import('../views/shared/NotificationsView.vue') },
      { path: 'result-sheet/:id', component: () => import('../views/shared/ResultSheetView.vue') },
    ]
  },

  // 404
  { path: '/:pathMatch(.*)*', redirect: '/login' },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// Navigation guard
router.beforeEach((to, from, next) => {
  const auth = useAuthStore()

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return next('/login')
  }

  if (to.meta.guest && auth.isAuthenticated) {
    return next(auth.user?.role ? `/${auth.user.role}/dashboard` : '/login')
  }

  if (to.meta.role && auth.role !== to.meta.role) {
    if (to.meta.role !== 'admin' || auth.role !== 'superadmin') {
      return next(`/${auth.role}/dashboard`)
    }
  }

  next()
})

export default router
