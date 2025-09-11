<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="text-center">
      <h1 class="text-6xl font-bold text-gray-200">404</h1>
      <p class="mt-2 text-lg text-gray-600">Página no encontrada</p>
      <router-link 
        to="/dashboard"
        class="mt-4 inline-block px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700"
      >
        Volver al inicio
      </router-link>
    </div>
  </div>
</template>

<script setup>
</script>
