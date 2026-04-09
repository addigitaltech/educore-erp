<template><AppLayout :navItems="navItems" /></template>
<script setup>
import AppLayout from '../../components/layout/AppLayout.vue'
const base = '/superadmin'
const navItems = [
  { section: 'Overview' },
  { path:`${base}/dashboard`,     icon:'🌐', label:'Platform Overview' },
  { path:`${base}/schools`,       icon:'🏫', label:'All Schools', badge:12 },
  { path:`${base}/analytics`,     icon:'📊', label:'Global Analytics' },
  { section: 'System' },
  { path:`${base}/notifications`, icon:'🔔', label:'Notifications', badge:5 },
  { path:`${base}/profile`,       icon:'👤', label:'My Profile' },
]
</script>
