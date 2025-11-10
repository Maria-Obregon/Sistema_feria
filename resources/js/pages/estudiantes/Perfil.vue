<template>
  <div class="max-w-6xl mx-auto px-4 py-6">
    <h1 class="text-xl font-semibold text-gray-900 mb-4">Mi perfil</h1>
    <div class="bg-white border rounded-lg p-4 space-y-2">
      <div><b>Nombre:</b> {{ user?.nombre }}</div>
      <div><b>Correo:</b> {{ user?.email }}</div>
      <div><b>Usuario:</b> {{ user?.identificacion || '—' }} <span class="text-xs text-slate-500">(cédula)</span></div>
      <div><b>Institución:</b> {{ user?.institucion?.nombre || '—' }}</div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { authApi } from '@/services/api'
const user = ref(null)
onMounted(async () => {
  const { data } = await authApi.me()
  user.value = data.user
})
</script>
