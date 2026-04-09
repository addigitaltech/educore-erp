<template><AppLayout :navItems="navItems" /></template>
<script setup>
import AppLayout from '../../components/layout/AppLayout.vue'
const base = '/accountant'
const navItems = [
  { section: 'Overview' },
  { path:`${base}/dashboard`,     icon:'🏠', label:'Dashboard' },
  { section: 'Finance' },
  { path:`${base}/invoices`,      icon:'🧾', label:'Invoices' },
  { path:`${base}/payments`,      icon:'💳', label:'Payments' },
  { path:`${base}/reports`,       icon:'📊', label:'Financial Reports' },
  { section: 'System' },
  { path:`${base}/notifications`, icon:'🔔', label:'Notifications' },
  { path:`${base}/profile`,       icon:'👤', label:'My Profile' },
]
</script>
