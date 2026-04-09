<template><AppLayout :navItems="navItems" /></template>
<script setup>
import AppLayout from '../../components/layout/AppLayout.vue'
const base = '/teacher'
const navItems = [
  { section: 'Overview' },
  { path:`${base}/dashboard`,     icon:'🏠', label:'Dashboard' },
  { section: 'Teaching' },
  { path:`${base}/classes`,       icon:'🏛️', label:'My Classes' },
  { path:`${base}/exams`,         icon:'📝', label:'Manage Exams' },
  { path:`${base}/questions`,     icon:'❓', label:'Question Bank' },
  { path:`${base}/attendance`,    icon:'✅', label:'Mark Attendance' },
  { path:`${base}/materials`,     icon:'📚', label:'Course Materials' },
  { section: 'Students' },
  { path:`${base}/performance`,   icon:'📈', label:'Student Performance' },
  { section: 'Communication' },
  { path:`${base}/announcements`, icon:'📢', label:'Announcements' },
  { path:`${base}/notifications`, icon:'🔔', label:'Notifications', badge:2 },
  { path:`${base}/profile`,       icon:'👤', label:'My Profile' },
]
</script>
