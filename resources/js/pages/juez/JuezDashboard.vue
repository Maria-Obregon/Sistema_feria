<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Topbar -->
    <nav class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center space-x-6">
            <h1 class="text-xl font-semibold">Panel de Juez</h1>
            <router-link
              :to="{ name: 'juez.asignaciones' }"
              class="text-sm text-gray-600 hover:text-gray-900"
            >
              Mis Asignaciones
            </router-link>
          </div>

          <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-500">{{ user?.email }}</span>
            <button
              @click="logout"
              class="text-sm text-red-600 hover:text-red-800"
            >
              Cerrar Sesión
            </button>
          </div>
        </div>
      </div>
    </nav>

    <!-- Contenido -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-3 bg-blue-100 rounded-full">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-2xl font-semibold">{{ stats.pendientes }}</p>
              <p class="text-gray-500">Por calificar</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-3 bg-green-100 rounded-full">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12h6m-6 4h6M5 7h14M5 7l2-2m-2 2l2 2"/>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-2xl font-semibold">{{ stats.calificadas }}</p>
              <p class="text-gray-500">Calificaciones enviadas</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-3 bg-yellow-100 rounded-full">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 7h18M5 10h14M7 13h10M9 16h6"/>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-2xl font-semibold">{{ stats.total }}</p>
              <p class="text-gray-500">Total asignaciones</p>
            </div>
          </div>
        </div>
      </div>

      <h2 class="text-lg font-semibold mb-4">Acciones rápidas</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <router-link
          :to="{ name: 'juez.asignaciones' }"
          class="bg-white p-6 rounded-lg shadow-sm border hover:shadow-md transition-shadow"
        >
          <div class="flex items-center">
            <div class="p-3 bg-blue-100 rounded-lg">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm text-gray-500">Revisar</p>
              <p class="text-lg font-semibold">Mis Asignaciones</p>
            </div>
          </div>
        </router-link>

        <router-link
          :to="{ name: 'juez.mis-calificaciones' }"
          class="bg-white p-6 rounded-lg shadow-sm border hover:shadow-md transition-shadow"
        >
          <div class="flex items-center">
            <div class="p-3 bg-green-100 rounded-lg">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586l5.414 5.414V15a2 2 0 01-2 2z"/>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm text-gray-500">Ver</p>
              <p class="text-lg font-semibold">Mis Calificaciones</p>
            </div>
          </div>
        </router-link>
      </div>
    </main>
  </div>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const auth = useAuthStore()

const user = computed(() => auth.user)
const stats = ref({ pendientes: 0, calificadas: 0, total: 0 })

const logout = async () => {
  await auth.logout()            // limpia token + header en tu store
  await router.push({ name: 'login' })
}

// (Opcional) simula carga de stats del juez
onMounted(async () => {
  // Llama a tu endpoint real si ya lo tienes
  stats.value = { pendientes: 5, calificadas: 12, total: 17 }
})
</script>
