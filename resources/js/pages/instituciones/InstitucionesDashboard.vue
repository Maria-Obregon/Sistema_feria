<template>
  <div class="min-h-screen bg-gray-100 relative">
    <div
      aria-hidden="true"
      class="pointer-events-none select-none absolute inset-0
              bg-[url('/img/Logo.webp')] bg-no-repeat bg-center bg-contain
              opacity-10"
    ></div>

    <nav class="relative z-10 bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center gap-4">
            <h1 class="text-xl font-semibold text-gray-800">Panel de Comité Institucional</h1>
            <span v-if="user?.institucion" class="hidden md:inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
              {{ user.institucion.nombre }}
            </span>
          </div>
          <div class="flex items-center space-x-4">
            <div class="text-right hidden sm:block">
               <div class="text-sm font-medium text-gray-900">{{ user?.nombre || 'Usuario' }}</div>
               <div class="text-xs text-gray-500">{{ user?.email }}</div>
            </div>
            <button
              @click="logout"
              class="text-sm font-medium text-red-600 hover:text-red-800 border border-red-200 hover:bg-red-50 px-3 py-1.5 rounded transition-colors"
            >
              Cerrar Sesión
            </button>
          </div>
        </div>
      </div>
    </nav>

    <main class="relative z-10 max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
      
      <div class="mb-6 md:hidden">
         <h2 class="text-lg font-bold text-gray-800">{{ user?.institucion?.nombre || 'Mi Institución' }}</h2>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center transition-transform hover:-translate-y-1 duration-200">
          <div class="p-4 bg-blue-50 rounded-full text-blue-600">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
          </div>
          <div class="ml-5">
            <p class="text-3xl font-bold text-gray-900">{{ cargandoStats ? '...' : stats.estudiantes }}</p>
            <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Estudiantes</p>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center transition-transform hover:-translate-y-1 duration-200">
          <div class="p-4 bg-green-50 rounded-full text-green-600">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
          </div>
          <div class="ml-5">
            <p class="text-3xl font-bold text-gray-900">{{ cargandoStats ? '...' : stats.proyectos }}</p>
            <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Proyectos</p>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center transition-transform hover:-translate-y-1 duration-200">
          <div class="p-4 bg-purple-50 rounded-full text-purple-600">
             <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
          </div>
          <div class="ml-5">
            <p class="text-3xl font-bold text-gray-900">{{ cargandoStats ? '...' : stats.jueces }}</p>
            <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Jueces</p>
          </div>
        </div>
      </div> 
      
      <h2 class="text-lg font-bold text-gray-800 mb-5 border-b pb-2">Accesos Directos</h2>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
        <router-link :to="{ name: 'inst.estudiantes' }" class="group bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-blue-200 transition-all">
            <div class="flex items-center">
                <div class="p-3 bg-blue-50 rounded-lg text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <div class="ml-4">
                    <p class="text-xs font-semibold text-gray-400 uppercase">Gestión</p>
                    <p class="text-lg font-bold text-gray-800 group-hover:text-blue-600 transition-colors">Estudiantes</p>
                </div>
            </div>
        </router-link>

        <router-link :to="{ name: 'inst.proyectos' }" class="group bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-green-200 transition-all">
             <div class="flex items-center">
                <div class="p-3 bg-green-50 rounded-lg text-green-600 group-hover:bg-green-600 group-hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <div class="ml-4">
                    <p class="text-xs font-semibold text-gray-400 uppercase">Gestión</p>
                    <p class="text-lg font-bold text-gray-800 group-hover:text-green-600 transition-colors">Proyectos</p>
                </div>
            </div>
        </router-link>

        <router-link :to="{ name: 'inst.jueces' }" class="group bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-purple-200 transition-all">
            <div class="flex items-center">
                <div class="p-3 bg-purple-50 rounded-lg text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                </div>
                <div class="ml-4">
                    <p class="text-xs font-semibold text-gray-400 uppercase">Gestión</p>
                    <p class="text-lg font-bold text-gray-800 group-hover:text-purple-600 transition-colors">Jueces</p>
                </div>
            </div>
        </router-link>

        <router-link :to="{ name: 'inst.instituciones' }" class="group bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-indigo-200 transition-all">
            <div class="flex items-center">
                <div class="p-3 bg-indigo-50 rounded-lg text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <div class="ml-4">
                    <p class="text-xs font-semibold text-gray-400 uppercase">Configuración</p>
                    <p class="text-lg font-bold text-gray-800 group-hover:text-indigo-600 transition-colors">Mi Institución</p>
                </div>
            </div>
        </router-link>
      </div>

      <h2 class="text-lg font-bold text-gray-800 mb-5 border-b pb-2 flex items-center gap-2">
         <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
         Documentos y Normativa
      </h2>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="/docs/Pronafecyt-2025-formularios.docx" download class="group bg-white p-4 rounded-lg border border-gray-200 hover:border-blue-400 hover:shadow-sm transition-all flex items-center gap-3">
            <div class="p-2 bg-blue-50 text-blue-600 rounded group-hover:bg-blue-600 group-hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-800 group-hover:text-blue-700">Formularios F1-F6</p>
                <p class="text-xs text-gray-500">Inscripción Oficial</p>
            </div>
        </a>

        <a href="/docs/Manual-FCyT-2025.pdf" download class="group bg-white p-4 rounded-lg border border-gray-200 hover:border-red-400 hover:shadow-sm transition-all flex items-center gap-3">
            <div class="p-2 bg-red-50 text-red-600 rounded group-hover:bg-red-600 group-hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-800 group-hover:text-red-700">Manual 2025</p>
                <p class="text-xs text-gray-500">Reglamento Oficial</p>
            </div>
        </a>

        <a href="/docs/Formularios-Juzgamiento-50-50.pdf" download class="group bg-white p-4 rounded-lg border border-gray-200 hover:border-green-400 hover:shadow-sm transition-all flex items-center gap-3">
            <div class="p-2 bg-green-50 text-green-600 rounded group-hover:bg-green-600 group-hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-800 group-hover:text-green-700">Rúbricas 50/50</p>
                <p class="text-xs text-gray-500">Evaluación Jueces</p>
            </div>
        </a>

        <a 
          href="https://www.mep.go.cr/programas-proyectos/programa-nacional-ferias-ciencia-tecnologia#!" 
          target="_blank" 
          rel="noopener noreferrer"
          class="group bg-white p-4 rounded-lg border border-gray-200 hover:border-yellow-400 hover:shadow-sm transition-all flex items-center gap-3"
        >
            <div class="p-2 bg-yellow-50 text-yellow-600 rounded group-hover:bg-yellow-600 group-hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-800 group-hover:text-yellow-700">Sitio Oficial</p>
                <p class="text-xs text-gray-500">Pronafecyt (MICITT)</p>
            </div>
        </a>
      </div>

    </main>
  </div>
</template>

<script setup>
import { useAuthStore } from '../../stores/auth'
import { useRouter } from 'vue-router'
import { ref, computed, onMounted } from 'vue'
import { estudiantesApi, proyectosApi, juecesApi } from '@/services/api'

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
const stats = ref({ estudiantes: 0, proyectos: 0, jueces: 0 })

onMounted(async () => {
  try {
    cargandoStats.value = true
    const [respEst, respProy, respJueces] = await Promise.all([
      estudiantesApi.listar({ per_page: 1 }),
      proyectosApi.list({ per_page: 1 }),
      juecesApi.listar({ per_page: 1 })
    ])
    stats.value = {
      estudiantes: respEst.data.total || respEst.data.meta?.total || 0,
      proyectos:   respProy.data.total || respProy.data.meta?.total || 0,
      jueces:      respJueces.data.total || respJueces.data.meta?.total || 0
    }
  } catch (e) {
    console.error("⚠️ Error cargando contadores:", e)
  } finally {
    cargandoStats.value = false
  }
})
</script>