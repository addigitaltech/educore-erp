<template><AppLayout :navItems="navItems" /></template>
<script setup>
import AppLayout from '../../components/layout/AppLayout.vue'
const base = '/librarian'
const navItems = [
  { section: 'Overview' },
  { path:`${base}/dashboard`,     icon:'🏠', label:'Dashboard' },
  { section: 'Library' },
  { path:`${base}/books`,         icon:'📚', label:'Book Catalog' },
  { path:`${base}/borrows`,       icon:'📤', label:'Borrow Records' },
  { path:`${base}/overdue`,       icon:'⚠️', label:'Overdue Books' },
  { path:`${base}/add-book`,      icon:'➕', label:'Add Books' },
  { section: 'System' },
  { path:`${base}/notifications`, icon:'🔔', label:'Notifications' },
  { path:`${base}/profile`,       icon:'👤', label:'My Profile' },
]
</script>
