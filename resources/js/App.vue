<template>
  <div id="app">
    <router-view />
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useAuthStore } from './stores/auth';

const authStore = useAuthStore();

onMounted(() => {
  // Verificar si hay token guardado
  authStore.checkAuth();
});
</script>

<style>
#app {
  min-height: 100vh;
}
</style>
