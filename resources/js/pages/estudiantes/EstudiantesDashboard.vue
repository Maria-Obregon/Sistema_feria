<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Topbar -->
    <header class="bg-white border-b">
      <div class="max-w-6xl mx-auto px-4 h-14 flex items-center justify-between">
        <nav class="flex gap-5">
          <RouterLink :to="{ name: 'est.dashboard' }" class="text-sm text-gray-600 hover:text-gray-900">Inicio</RouterLink>
          <RouterLink :to="{ name: 'est.mis-proyectos' }" class="text-sm text-gray-600 hover:text-gray-900">Mis proyectos</RouterLink>
          <RouterLink :to="{ name: 'est.entregas' }" class="text-sm text-gray-600 hover:text-gray-900">Entregas</RouterLink>
          <RouterLink :to="{ name: 'est.certificados' }" class="text-sm text-gray-600 hover:text-gray-900">Certificados</RouterLink>
          <RouterLink :to="{ name: 'est.perfil' }" class="text-sm text-gray-600 hover:text-gray-900">Perfil</RouterLink>
        </nav>

        <button @click="logout" class="text-sm px-3 py-1 rounded bg-gray-200 hover:bg-gray-300">
          Cerrar sesión
        </button>
      </div>
    </header>

    <!-- Contenido -->
    <main class="max-w-6xl mx-auto px-4 py-6 space-y-4">
      <h1 class="text-xl font-semibold text-gray-900">Bienvenido/a</h1>
      <p class="text-gray-700">Aquí verás un resumen de tus proyectos, entregas y avisos.</p>

      <div class="grid md:grid-cols-3 gap-4">
        <div class="p-4 bg-white border rounded-lg">
          <div class="text-sm text-gray-500">Proyectos activos</div>
          <div class="text-2xl font-bold mt-1">0</div>
        </div>
        <div class="p-4 bg-white border rounded-lg">
          <div class="text-sm text-gray-500">Entregas pendientes</div>
          <div class="text-2xl font-bold mt-1">0</div>
        </div>
        <div class="p-4 bg-white border rounded-lg">
          <div class="text-sm text-gray-500">Certificados disponibles</div>
          <div class="text-2xl font-bold mt-1">0</div>
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
const logout = async () => {
  await auth.logout()
  router.push({ name: 'login' })
}
</script>
