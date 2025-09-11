<template>
  <div class="min-h-screen bg-gray-100">
    <nav class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <h1 class="text-xl font-semibold">Sistema de Feria Científica</h1>
          </div>
          <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-700">
              {{ authStore.user?.email }}
            </span>
            <span class="px-3 py-1 text-xs font-medium bg-indigo-100 text-indigo-800 rounded-full">
              {{ authStore.userRole || 'Usuario' }}
            </span>
            <button
              @click="handleLogout"
              class="text-gray-500 hover:text-gray-700"
            >
              Cerrar sesión
            </button>
          </div>
        </div>
      </div>
    </nav>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">
              Bienvenido al Sistema de Feria Científica
            </h2>
            
            <!-- Contenido según el rol -->
            <div v-if="authStore.hasRole('admin')" class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <DashboardCard 
                title="Usuarios"
                :count="0"
                description="Gestionar usuarios del sistema"
                icon="users"
              />
              <DashboardCard 
                title="Instituciones"
                :count="0"
                description="Administrar instituciones educativas"
                icon="building"
              />
              <DashboardCard 
                title="Proyectos"
                :count="0"
                description="Ver todos los proyectos"
                icon="folder"
              />
            </div>

            <div v-else-if="authStore.hasRole('coordinador_regional')" class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <DashboardCard 
                title="Ferias Activas"
                :count="0"
                description="Ferias en tu regional"
                icon="calendar"
              />
              <DashboardCard 
                title="Jueces"
                :count="0"
                description="Gestionar jueces"
                icon="users"
              />
              <DashboardCard 
                title="Proyectos"
                :count="0"
                description="Proyectos en evaluación"
                icon="folder"
              />
            </div>

            <div v-else-if="authStore.hasRole('comite_institucional')" class="space-y-4">
              <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                <div class="flex">
                  <div class="ml-3">
                    <p class="text-sm text-blue-700">
                      Límites de tu institución:
                    </p>
                    <p class="text-xs text-blue-600 mt-1">
                      • Máximo 50 proyectos permitidos<br>
                      • Máximo 200 estudiantes participantes
                    </p>
                  </div>
                </div>
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <DashboardCard 
                  title="Proyectos"
                  :count="0"
                  description="Proyectos de tu institución"
                  icon="folder"
                />
                <DashboardCard 
                  title="Estudiantes"
                  :count="0"
                  description="Estudiantes participantes"
                  icon="users"
                />
              </div>
            </div>

            <div v-else-if="authStore.hasRole('juez')" class="space-y-4">
              <!-- Alerta 2FA para jueces -->
              <div v-if="!twoFactorEnabled" class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                <div class="flex">
                  <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                  </div>
                  <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                      <strong>Acción requerida:</strong> Debes configurar la autenticación de dos factores (2FA) para acceder como juez.
                    </p>
                    <div class="mt-2">
                      <router-link 
                        to="/juez/2fa"
                        class="text-sm bg-yellow-100 text-yellow-800 px-3 py-1 rounded hover:bg-yellow-200"
                      >
                        Configurar 2FA ahora
                      </router-link>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <DashboardCard 
                  title="Proyectos Asignados"
                  :count="0"
                  description="Proyectos para evaluar"
                  icon="folder"
                />
                <DashboardCard 
                  title="Evaluaciones Pendientes"
                  :count="0"
                  description="Calificaciones por completar"
                  icon="clipboard"
                />
                <DashboardCard 
                  title="Configuración"
                  :count="1"
                  description="Configurar 2FA y perfil"
                  icon="cog"
                  :link="'/juez/2fa'"
                />
              </div>
            </div>

            <div v-else class="text-gray-500">
              Seleccione una opción del menú para comenzar.
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import DashboardCard from '../components/shared/DashboardCard.vue';
import axios from 'axios';

const router = useRouter();
const authStore = useAuthStore();
const twoFactorEnabled = ref(false);

const handleLogout = async () => {
  await authStore.logout();
  router.push('/login');
};

const checkTwoFactorStatus = async () => {
  if (authStore.hasRole('juez') || authStore.hasRole('admin')) {
    try {
      const response = await axios.get('/api/2fa/status');
      twoFactorEnabled.value = response.data.enabled;
    } catch (error) {
      console.error('Error checking 2FA status:', error);
    }
  }
};

onMounted(() => {
  checkTwoFactorStatus();
});
</script>
