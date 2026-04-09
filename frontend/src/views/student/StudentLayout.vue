<template><AppLayout :navItems="navItems" /></template>
<script setup>
import AppLayout from '../../components/layout/AppLayout.vue'
const base = '/student'
const navItems = [
  { section: 'Overview' },
  { path:`${base}/dashboard`,     icon:'🏠', label:'Dashboard' },
  { section: 'Academics' },
  { path:`${base}/exams`,         icon:'📝', label:'My Exams' },
  { path:`${base}/results`,       icon:'📈', label:'My Results' },
  { path:`${base}/report-card`,   icon:'🎓', label:'Report Card' },
  { path:`${base}/result-sheet`,  icon:'📋', label:'Result Sheet' },
  { path:`${base}/materials`,     icon:'📁', label:'Course Materials' },
  { section: 'School' },
  { path:`${base}/timetable`,     icon:'📅', label:'Timetable' },
  { path:`${base}/attendance`,    icon:'✅', label:'My Attendance' },
  { path:`${base}/library`,       icon:'📚', label:'Library' },
  { section: 'Communication' },
  { path:`${base}/announcements`, icon:'📢', label:'Announcements' },
  { path:`${base}/notifications`, icon:'🔔', label:'Notifications', badge:4 },
  { path:`${base}/profile`,       icon:'👤', label:'My Profile' },
]
</script>
