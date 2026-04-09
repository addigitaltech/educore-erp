import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

// Request interceptor — attach token
api.interceptors.request.use(config => {
  const token = localStorage.getItem('educore_token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
}, error => Promise.reject(error))

// Response interceptor — handle 401
api.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      localStorage.removeItem('educore_token')
      localStorage.removeItem('educore_user')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default api

// ===================== API MODULES =====================

export const authAPI = {
  login:          (data) => api.post('/auth/login', data),
  logout:         ()     => api.post('/auth/logout'),
  me:             ()     => api.get('/auth/me'),
  updateProfile:  (data) => api.put('/auth/profile', data),
  changePassword: (data) => api.put('/auth/change-password', data),
}

export const dashboardAPI = {
  index: () => api.get('/dashboard'),
  stats: () => api.get('/dashboard/stats'),
}

export const studentsAPI = {
  list:       (params) => api.get('/students', { params }),
  get:        (id)     => api.get(`/students/${id}`),
  create:     (data)   => api.post('/students', data),
  update:     (id, data) => api.put(`/students/${id}`, data),
  delete:     (id)     => api.delete(`/students/${id}`),
  results:    (id)     => api.get(`/students/${id}/results`),
  attendance: (id)     => api.get(`/students/${id}/attendance`),
  reportCard: (id)     => api.get(`/students/${id}/report-card`),
}

export const teachersAPI = {
  list:   (params) => api.get('/teachers', { params }),
  get:    (id)     => api.get(`/teachers/${id}`),
  create: (data)   => api.post('/teachers', data),
  update: (id, data) => api.put(`/teachers/${id}`, data),
  delete: (id)     => api.delete(`/teachers/${id}`),
}

export const classesAPI = {
  list:      () => api.get('/classes'),
  get:       (id) => api.get(`/classes/${id}`),
  create:    (data) => api.post('/classes', data),
  update:    (id, data) => api.put(`/classes/${id}`, data),
  delete:    (id) => api.delete(`/classes/${id}`),
  students:  (id) => api.get(`/classes/${id}/students`),
  timetable: (id) => api.get(`/classes/${id}/timetable`),
}

export const subjectsAPI = {
  list:   () => api.get('/subjects'),
  create: (data) => api.post('/subjects', data),
  update: (id, data) => api.put(`/subjects/${id}`, data),
  delete: (id) => api.delete(`/subjects/${id}`),
}

export const examsAPI = {
  list:      (params) => api.get('/exams', { params }),
  get:       (id)     => api.get(`/exams/${id}`),
  create:    (data)   => api.post('/exams', data),
  update:    (id, data) => api.put(`/exams/${id}`, data),
  delete:    (id)     => api.delete(`/exams/${id}`),
  publish:   (id)     => api.post(`/exams/${id}/publish`),
  questions: (id)     => api.get(`/exams/${id}/questions`),
  results:   (id)     => api.get(`/exams/${id}/results`),
  start:     (id)     => api.get(`/exams/${id}/start`),
}

export const questionsAPI = {
  list:      (params) => api.get('/questions', { params }),
  create:    (data)   => api.post('/questions', data),
  bulkStore: (data)   => api.post('/questions/bulk', data),
  update:    (id, data) => api.put(`/questions/${id}`, data),
  delete:    (id)     => api.delete(`/questions/${id}`),
}

export const examAttemptsAPI = {
  start:      (data) => api.post('/exam-attempts/start', data),
  submit:     (id)   => api.post(`/exam-attempts/${id}/submit`),
  saveAnswer: (id, data) => api.post(`/exam-attempts/${id}/save-answer`, data),
  get:        (id)   => api.get(`/exam-attempts/${id}`),
  history:    ()     => api.get('/exam-attempts/student/history'),
}

export const resultsAPI = {
  list:       (params) => api.get('/results', { params }),
  create:     (data)   => api.post('/results', data),
  bulkStore:  (data)   => api.post('/results/bulk', data),
  byStudent:  (id)     => api.get(`/results/student/${id}`),
  reportCard: (id)     => api.get(`/results/report-card/${id}`),
  publish:    (data)   => api.post('/results/publish', data),
}

export const attendanceAPI = {
  list:      (params) => api.get('/attendance', { params }),
  mark:      (data)   => api.post('/attendance/mark', data),
  bulkMark:  (data)   => api.post('/attendance/bulk-mark', data),
  byClass:   (id)     => api.get(`/attendance/class/${id}`),
  byStudent: (id)     => api.get(`/attendance/student/${id}`),
  summary:   ()       => api.get('/attendance/summary'),
}

export const financeAPI = {
  invoices:       (params) => api.get('/finance/invoices', { params }),
  createInvoice:  (data)   => api.post('/finance/invoices', data),
  bulkGenerate:   (data)   => api.post('/finance/invoices/bulk-generate', data),
  showInvoice:    (id)     => api.get(`/finance/invoices/${id}`),
  payments:       (params) => api.get('/finance/payments', { params }),
  recordPayment:  (data)   => api.post('/finance/payments', data),
  summary:        ()       => api.get('/finance/summary'),
  report:         ()       => api.get('/finance/report'),
}

export const libraryAPI = {
  books:      (params) => api.get('/library/books', { params }),
  addBook:    (data)   => api.post('/library/books', data),
  updateBook: (id, data) => api.put(`/library/books/${id}`, data),
  deleteBook: (id)     => api.delete(`/library/books/${id}`),
  borrows:    ()       => api.get('/library/borrows'),
  borrow:     (data)   => api.post('/library/borrow', data),
  return:     (id)     => api.post(`/library/return/${id}`),
  overdue:    ()       => api.get('/library/overdue'),
}

export const announcementsAPI = {
  list:   (params) => api.get('/announcements', { params }),
  create: (data)   => api.post('/announcements', data),
  get:    (id)     => api.get(`/announcements/${id}`),
  update: (id, data) => api.put(`/announcements/${id}`, data),
  delete: (id)     => api.delete(`/announcements/${id}`),
}

export const notificationsAPI = {
  list:       () => api.get('/notifications'),
  markRead:   (id) => api.post(`/notifications/${id}/read`),
  markAllRead:() => api.post('/notifications/read-all'),
  delete:     (id) => api.delete(`/notifications/${id}`),
}

export const schoolsAPI = {
  list:   (params) => api.get('/schools', { params }),
  get:    (id)     => api.get(`/schools/${id}`),
  create: (data)   => api.post('/schools', data),
  update: (id, data) => api.put(`/schools/${id}`, data),
  delete: (id)     => api.delete(`/schools/${id}`),
  stats:  (id)     => api.get(`/schools/${id}/stats`),
}

export const timetableAPI = {
  list:   (params) => api.get('/timetable', { params }),
  create: (data)   => api.post('/timetable', data),
  update: (id, data) => api.put(`/timetable/${id}`, data),
  delete: (id)     => api.delete(`/timetable/${id}`),
}

// ===== NEW ENDPOINTS =====

export const settingsAPI = {
  get:    ()     => api.get('/settings'),
  update: (data) => api.post('/settings', data, { headers: { 'Content-Type': 'multipart/form-data' } }),
}

export const gradingAPI = {
  list:        ()     => api.get('/grading'),
  update:      (data) => api.post('/grading', data),
  recalculate: ()     => api.post('/grading/recalculate'),
}

export const enhancedResultsAPI = {
  list:               (params)   => api.get('/v2/results', { params }),
  store:              (data)     => api.post('/v2/results', data),
  bulkStore:          (data)     => api.post('/v2/results/bulk', data),
  get:                (id)       => api.get(`/v2/results/${id}`),
  update:             (id, data) => api.put(`/v2/results/${id}`, data),
  publish:            (data)     => api.post('/v2/results/publish', data),
  lock:               (data)     => api.post('/v2/results/lock', data),
  unlock:             (data)     => api.post('/v2/results/unlock', data),
  approve:            (id)       => api.post(`/v2/results/${id}/approve`),
  calculatePositions: (data)     => api.post('/v2/results/calculate-positions', data),
  byClass:            (id)       => api.get(`/v2/results/class/${id}`),
  byStudent:          (id)       => api.get(`/v2/results/student/${id}`),
  reportCard:         (id)       => api.get(`/v2/results/report-card/${id}`),
  pdfUrl:             (id)       => `${api.defaults.baseURL}/v2/results/pdf/${id}`,
}

export const enhancedStudentsAPI = {
  list:           (params)   => api.get('/v2/students', { params }),
  store:          (data)     => api.post('/v2/students', data, { headers: { 'Content-Type': 'multipart/form-data' } }),
  get:            (id)       => api.get(`/v2/students/${id}`),
  update:         (id, data) => api.post(`/v2/students/${id}`, data, { headers: { 'Content-Type': 'multipart/form-data' } }),
  delete:         (id)       => api.delete(`/v2/students/${id}`),
  uploadPassport: (id, data) => api.post(`/v2/students/${id}/passport`, data, { headers: { 'Content-Type': 'multipart/form-data' } }),
}

export const materialsAPI = {
  list:      (params)   => api.get('/materials', { params }),
  myUploads: ()         => api.get('/materials/my-uploads'),
  get:       (id)       => api.get(`/materials/${id}`),
  create:    (data)     => api.post('/materials', data, { headers: { 'Content-Type': 'multipart/form-data' } }),
  update:    (id, data) => api.put(`/materials/${id}`, data),
  delete:    (id)       => api.delete(`/materials/${id}`),
}
