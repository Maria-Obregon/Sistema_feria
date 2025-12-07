<template>
  <div class="min-h-screen bg-gray-100 relative">
    <!-- Fondo con Logo.webp (public/img/Logo.webp) -->
    <div
      aria-hidden="true"
      class="pointer-events-none select-none absolute inset-0
             bg-[url('/img/Logo.webp')] bg-no-repeat bg-center bg-contain
             opacity-10"
    ></div>

    <!-- NAV (contenido por encima del fondo) -->
    <nav class="relative z-10 bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <h1 class="text-xl font-semibold">Panel de Instituciones</h1>
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

    <!-- MAIN (contenido por encima del fondo) -->
    <main class="relative z-10 max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <!-- Estadísticas -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <!-- Estudiante -->
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-3 bg-blue-100 rounded-full">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-2xl font-semibold">{{ cargandoStats ? '…' : stats.estudiantes }}</p>
              <p class="text-gray-500">Estudiantes</p>
            </div>
          </div>
        </div>

        <!-- Proyectos -->
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-3 bg-green-100 rounded-full">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-2xl font-semibold">{{ cargandoStats ? '…' : stats.proyectos }}</p>
              <p class="text-gray-500">Proyectos</p>
            </div>
          </div>
        </div>

        <!-- Instituciones -->
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-3 bg-yellow-100 rounded-full">
              <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-2xl font-semibold">{{ cargandoStats ? '…' : stats.instituciones }}</p>
              <p class="text-gray-500">Instituciones</p>
            </div>
          </div>
        </div>

        <!-- Jueces -->
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-3 bg-purple-100 rounded-full">
              <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-2xl font-semibold">{{ cargandoStats ? '…' : stats.jueces }}</p>
              <p class="text-gray-500">Jueces</p>
            </div>
          </div>
        </div>
      </div> 
      
      <h2 class="text-lg font-semibold mb-4">Acciones Rápidas</h2>

      <!-- Acciones rápidas -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <router-link
          :to="{ name: 'inst.estudiantes' }"
          class="bg-white p-6 rounded-lg shadow-sm border hover:shadow-md transition-shadow"
        >
          <div class="flex items-center">
            <div class="p-3 bg-blue-100 rounded-lg">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm text-gray-500">Gestión de</p>
              <p class="text-lg font-semibold">Estudiantes</p>
            </div>
          </div>
        </router-link>

        <router-link
          :to="{ name: 'inst.instituciones' }"
          class="p-4 bg-white rounded-lg shadow hover:shadow-lg transition-shadow text-left block"
        >
          <div class="flex items-center">
            <div class="p-3 bg-yellow-100 rounded-lg">
              <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm text-gray-500">Gestión de</p>
              <p class="text-lg font-semibold">Instituciones</p>
            </div>
          </div>
        </router-link>

        <router-link
          :to="{ name: 'inst.jueces' }"
          class="p-4 bg-white rounded-lg shadow hover:shadow-lg transition-shadow text-left block"
        >
          <div class="flex items-center">
            <div class="p-3 bg-purple-100 rounded-lg">
              <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm text-gray-500">Gestión de</p>
              <p class="text-lg font-semibold">Jueces</p>
            </div>
          </div>
        </router-link>

         <router-link
          :to="{ name: 'inst.proyectos' }"
        class="p-4 bg-white rounded-lg shadow hover:shadow-lg transition-shadow text-left">
          <div class="flex items-center">
            <div class="p-3 bg-green-100 rounded-lg">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm text-gray-500">Gestión de</p>
              <p class="text-lg font-semibold">Proyectos</p>
            </div>
          </div>
        </router-link>

        <button class="p-4 bg-white rounded-lg shadow hover:shadow-lg transition-shadow text-left">
          <div class="flex items-center">
            <div class="p-3 bg-red-100 rounded-lg">
              <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm text-gray-500">Ver</p>
              <p class="text-lg font-semibold">Reportes</p>
            </div>
          </div>
        </button>

        <button class="p-4 bg-white rounded-lg shadow hover:shadow-lg transition-shadow text-left">
          <div class="flex items-center">
            <div class="p-3 bg-indigo-100 rounded-lg">
              <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm text-gray-500">Ver</p>
              <p class="text-lg font-semibold">Perfil</p>
            </div>
          </div>
        </button>

      </div>
      </main>
  </div>
</template>

<script setup>
import { useAuthStore } from '../../stores/auth'
import { useRouter } from 'vue-router'
import { ref, computed, onMounted } from 'vue'

// 1. IMPORTAMOS TODAS LAS APIS NECESARIAS (incluyendo juecesApi)
import { 
  estudiantesApi, 
  proyectosApi, 
  institucionesApi, 
  juecesApi 
} from '@/services/api'

const authStore = useAuthStore()
const router = useRouter()
const user = computed(() => authStore.user)

const logout = async () => { 
  try {
    await authStore.logout()
    router.push('/login') 
  } catch (error) {
    console.error("Error al cerrar sesión:", error)
    window.location.href = '/login'
  }
}

// --- Estado para estadísticas ---
const cargandoStats = ref(false)
const stats = ref({ estudiantes: 0, proyectos: 0, instituciones: 0, jueces: 0 })

onMounted(async () => {
  console.log("🟢 [Comité] Cargando todas las estadísticas...")
  
  try {
    cargandoStats.value = true

    // 2. HACEMOS 4 PETICIONES EN PARALELO
    // Usamos { per_page: 1 } para obtener solo el total de forma rápida
    const [respEst, respProy, respInst, respJueces] = await Promise.all([
      estudiantesApi.listar({ per_page: 1 }),
      proyectosApi.list({ per_page: 1 }),
      institucionesApi.listar({ per_page: 1 }),
      juecesApi.listar({ per_page: 1 }) // <--- Nueva petición para jueces
    ])

    console.log("✅ Datos recibidos:", { 
        est: respEst.data.total, 
        proy: respProy.data.total, 
        inst: respInst.data.total,
        jueces: respJueces.data.total
    })

    stats.value = {
      // Leemos los totales reales de la respuesta paginada
      estudiantes:   respEst.data.total    || respEst.data.meta?.total    || 0,
      proyectos:     respProy.data.total   || respProy.data.meta?.total   || 0,
      instituciones: respInst.data.total   || respInst.data.meta?.total   || 0,
      jueces:        respJueces.data.total || respJueces.data.meta?.total || 0
    }
    
  } catch (e) {
    console.error("⚠️ Error cargando contadores:", e)
    // Si alguna falla (ej. permiso denegado en jueces), el resto podría fallar si no se maneja individualmente.
    // Con Promise.all, si una falla, salta al catch. 
    // Si quieres que las demás carguen aunque falle jueces, avísame para usar Promise.allSettled.
  } finally {
    cargandoStats.value = false
  }
})
</script>