<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Topbar -->
    <header class="bg-white border-b">
      <div class="max-w-7xl mx-auto px-4 h-14 flex items-center justify-between">
        <nav class="flex gap-5">
          <RouterLink :to="{ name: 'inst.dashboard' }" class="text-sm text-gray-600 hover:text-gray-900">Inicio</RouterLink>
          <RouterLink :to="{ name: 'inst.proyectos' }" class="text-sm text-gray-600 hover:text-gray-900">Proyectos</RouterLink>
          <RouterLink :to="{ name: 'inst.estudiantes' }" class="text-sm text-gray-600 hover:text-gray-900">Estudiantes</RouterLink>
          <RouterLink :to="{ name: 'inst.jueces' }" class="text-sm text-gray-600 hover:text-gray-900">Jueces</RouterLink>
          <RouterLink :to="{ name: 'inst.reportes' }" class="text-sm text-gray-600 hover:text-gray-900">Reportes</RouterLink>
          <RouterLink :to="{ name: 'inst.perfil' }" class="text-sm text-gray-600 hover:text-gray-900">Perfil</RouterLink>
        </nav>
        <button @click="logout" class="text-sm px-3 py-1 rounded bg-gray-200 hover:bg-gray-300">
          Cerrar sesión
        </button>
      </div>
    </header>

    <!-- Contenido -->
    <main class="max-w-7xl mx-auto px-4 py-6 space-y-6">
      <div>
        <h1 class="text-xl font-semibold text-gray-900">Panel de la Institución</h1>
        <p class="text-gray-700">Resumen general de tu institución.</p>
      </div>

      <div class="grid md:grid-cols-4 gap-4">
        <div class="p-4 bg-white border rounded-lg">
          <div class="text-sm text-gray-500">Proyectos activos</div>
          <div class="text-2xl font-bold mt-1">0</div>
        </div>
        <div class="p-4 bg-white border rounded-lg">
          <div class="text-sm text-gray-500">Estudiantes</div>
          <div class="text-2xl font-bold mt-1">0</div>
        </div>
        <div class="p-4 bg-white border rounded-lg">
          <div class="text-sm text-gray-500">Jueces asignados</div>
          <div class="text-2xl font-bold mt-1">0</div>
        </div>
        <div class="p-4 bg-white border rounded-lg">
          <div class="text-sm text-gray-500">Entregas pendientes</div>
          <div class="text-2xl font-bold mt-1">0</div>
        </div>
      </div>

      <div class="grid md:grid-cols-2 gap-4">
        <div class="p-4 bg-white border rounded-lg">
          <div class="font-medium text-gray-900 mb-2">Últimos proyectos</div>
          <div class="text-gray-600 text-sm">Aquí una tabla/lista de los más recientes.</div>
        </div>
        <div class="p-4 bg-white border rounded-lg">
          <div class="font-medium text-gray-900 mb-2">Avisos</div>
          <div class="text-gray-600 text-sm">Aquí notificaciones y fechas clave.</div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
const router = useRouter()
const auth = useAuthStore()
const logout = async () => { await auth.logout(); router.push({ name: 'login' }) }
</script>
