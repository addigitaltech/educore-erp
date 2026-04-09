<template><AppLayout :navItems="navItems" /></template>
<script setup>
import AppLayout from '../../components/layout/AppLayout.vue'
const base = '/admin'
const navItems = [
  { section: 'Overview' },
  { path:`${base}/dashboard`,     icon:'🏠', faIcon:'fa-solid fa-house',           label:'Dashboard' },
  { path:`${base}/analytics`,     icon:'📊', faIcon:'fa-solid fa-chart-bar',        label:'Analytics' },
  { section: 'Academic' },
  { path:`${base}/students`,      icon:'🎒', faIcon:'fa-solid fa-user-graduate',    label:'Students' },
  { path:`${base}/teachers`,      icon:'👨‍🏫', faIcon:'fa-solid fa-chalkboard-user',  label:'Teachers' },
  { path:`${base}/classes`,       icon:'🏛️', faIcon:'fa-solid fa-school',            label:'Classes & Arms' },
  { path:`${base}/timetable`,     icon:'📅', faIcon:'fa-solid fa-calendar-days',    label:'Timetable' },
  { section: 'Exams & Results' },
  { path:`${base}/exams`,         icon:'📝', faIcon:'fa-solid fa-file-pen',         label:'Examinations' },
  { path:`${base}/results`,       icon:'📈', faIcon:'fa-solid fa-chart-line',       label:'Results' },
  { section: 'Operations' },
  { path:`${base}/attendance`,    icon:'✅', faIcon:'fa-solid fa-clipboard-check',  label:'Attendance' },
  { path:`${base}/finance`,       icon:'💰', faIcon:'fa-solid fa-money-bill-wave',  label:'Finance & Fees' },
  { path:`${base}/library`,       icon:'📚', faIcon:'fa-solid fa-book-open',        label:'Library' },
  { path:`${base}/announcements`, icon:'📢', faIcon:'fa-solid fa-bullhorn',         label:'Announcements' },
  { section: 'System' },
  { path:`${base}/notifications`, icon:'🔔', faIcon:'fa-solid fa-bell',             label:'Notifications', badge:3 },
  { path:`${base}/profile`,       icon:'👤', faIcon:'fa-solid fa-user-circle',      label:'My Profile' },
  { path:`${base}/settings`,      icon:'⚙️', faIcon:'fa-solid fa-gear',             label:'Settings' },
]
</script>
