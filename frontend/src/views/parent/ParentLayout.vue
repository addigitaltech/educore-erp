<!-- ParentLayout -->
<template><AppLayout :navItems="navItems" /></template>
<script setup>
import AppLayout from '../../components/layout/AppLayout.vue'
const base = '/parent'
const navItems = [
  { section: 'Overview' },
  { path:`${base}/dashboard`,     icon:'🏠', label:'Dashboard' },
  { section: "Child's Progress" },
  { path:`${base}/results`,       icon:'📈', label:"Results" },
  { path:`${base}/report-card`,   icon:'🎓', label:'Report Card' },
  { path:`${base}/attendance`,    icon:'✅', label:'Attendance' },
  { section: 'Finance' },
  { path:`${base}/finance`,       icon:'💰', label:'Fee Payments' },
  { section: 'Communication' },
  { path:`${base}/announcements`, icon:'📢', label:'School Notices' },
  { path:`${base}/notifications`, icon:'🔔', label:'Notifications', badge:2 },
  { path:`${base}/profile`,       icon:'👤', label:'My Profile' },
]
</script>
