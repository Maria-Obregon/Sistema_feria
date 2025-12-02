<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Topbar -->
    <nav class="bg-white border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <h1 class="text-2xl font-bold text-gray-800">Panel de Juez</h1>
          </div>

          <div class="flex items-center space-x-4">
            <div class="text-right hidden sm:block">
              <p class="text-sm font-medium text-gray-900">Juez</p>
              <p class="text-xs text-gray-500">{{ user?.email }}</p>
            </div>
            <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold">
              {{ user?.email?.charAt(0).toUpperCase() }}
            </div>
            <button
              @click="logout"
              class="ml-4 text-sm text-gray-500 hover:text-red-600 transition-colors"
              title="Cerrar Sesión"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            </button>
          </div>
        </div>
      </div>
    </nav>

    <!-- Contenido -->
    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
      
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between hover:shadow-md transition-shadow">
          <div>
            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Por Calificar</p>
            <p class="text-3xl font-bold text-blue-600 mt-1">{{ stats.pendientes }}</p>
          </div>
          <div class="p-3 bg-blue-50 rounded-full text-blue-600">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between hover:shadow-md transition-shadow">
          <div>
            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Enviadas</p>
            <p class="text-3xl font-bold text-green-600 mt-1">{{ stats.calificadas }}</p>
          </div>
          <div class="p-3 bg-green-50 rounded-full text-green-600">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between hover:shadow-md transition-shadow">
          <div>
            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total</p>
            <p class="text-3xl font-bold text-gray-800 mt-1">{{ stats.total }}</p>
          </div>
          <div class="p-3 bg-gray-50 rounded-full text-gray-600">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
          </div>
        </div>
      </div>

      <!-- Main Actions -->
      <h2 class="text-xl font-bold text-gray-800 mb-6">¿Qué deseas hacer hoy?</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Card: Mis Asignaciones -->
        <router-link :to="{ name: 'juez.asignaciones' }" class="group relative bg-white rounded-2xl shadow-sm border border-gray-200 p-8 hover:shadow-lg hover:border-blue-300 transition-all duration-300 flex flex-col items-center text-center">
          <div class="h-20 w-20 bg-blue-50 rounded-full flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
            <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
          </div>
          <h3 class="text-2xl font-bold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors">Mis Asignaciones</h3>
          <p class="text-gray-500 mb-6 max-w-sm">
            Accede a la lista de proyectos pendientes por calificar. Revisa los detalles y completa las rúbricas.
          </p>
          <span class="inline-flex items-center text-blue-600 font-medium group-hover:translate-x-1 transition-transform">
            Ir a calificar <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
          </span>
        </router-link>

        <!-- Card: Mis Calificaciones -->
        <router-link :to="{ name: 'juez.mis-calificaciones' }" class="group relative bg-white rounded-2xl shadow-sm border border-gray-200 p-8 hover:shadow-lg hover:border-green-300 transition-all duration-300 flex flex-col items-center text-center">
          <div class="h-20 w-20 bg-green-50 rounded-full flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
            <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          </div>
          <h3 class="text-2xl font-bold text-gray-900 mb-2 group-hover:text-green-600 transition-colors">Mis Calificaciones</h3>
          <p class="text-gray-500 mb-6 max-w-sm">
            Consulta el historial de proyectos que ya has evaluado. Puedes reabrir asignaciones si necesitas editar algo.
          </p>
          <span class="inline-flex items-center text-green-600 font-medium group-hover:translate-x-1 transition-transform">
            Ver historial <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
          </span>
        </router-link>

      </div>
    </main>
  </div>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

import api from '@/services/api'

const router = useRouter()
const auth = useAuthStore()

const user = computed(() => auth.user)
const stats = ref({ pendientes: 0, calificadas: 0, total: 0 })

const logout = async () => {
  await auth.logout()            
  await router.push({ name: 'login' })
}

onMounted(async () => {
  try {
    const { data } = await api.get('/juez/stats')
    stats.value = data
  } catch (e) {
    console.error('Error cargando estadísticas', e)
  }
})
</script>
