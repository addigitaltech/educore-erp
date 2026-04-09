<template>
  <router-view />
</template>

<script setup>
import { onMounted } from 'vue'
import { useAuthStore } from './store/auth'

const auth = useAuthStore()

onMounted(async () => {
  if (auth.isAuthenticated) {
    await auth.fetchMe()
  }
})
</script>
