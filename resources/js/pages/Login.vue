<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50">
    <!-- Formulario de login normal -->
    <div v-if="!showTwoFactor" class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Sistema de Feria Científica
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Ingrese sus credenciales para continuar
        </p>
      </div>
      
      <form class="mt-8 space-y-6" @submit.prevent="handleLogin">
        <div v-if="error" class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded">
          {{ error }}
        </div>
        
        <div class="rounded-md shadow-sm -space-y-px">
          <div>
            <label for="email" class="sr-only">Correo electrónico</label>
            <input
              id="email"
              v-model="form.email"
              name="email"
              type="email"
              autocomplete="email"
              required
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
              placeholder="Correo electrónico"
            />
          </div>
          <div>
            <label for="password" class="sr-only">Contraseña</label>
            <input
              id="password"
              v-model="form.password"
              name="password"
              type="password"
              autocomplete="current-password"
              required
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
              placeholder="Contraseña"
            />
          </div>
        </div>

        <div>
          <button
            type="submit"
            :disabled="loading"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
          >
            {{ loading ? 'Iniciando sesión...' : 'Iniciar sesión' }}
          </button>
        </div>
      </form>
    </div>

    <!-- Verificación 2FA -->
    <TwoFactorVerification
      v-if="showTwoFactor"
      :temp-token="tempToken"
      @verification-success="handleTwoFactorSuccess"
      @back-to-login="backToLogin"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import TwoFactorVerification from '../components/TwoFactorVerification.vue';
import axios from 'axios';

const router = useRouter();
const authStore = useAuthStore();

const form = ref({
  email: '',
  password: '',
});

const loading = ref(false);
const error = ref('');
const showTwoFactor = ref(false);
const tempToken = ref('');

const handleLogin = async () => {
  try {
    loading.value = true;
    error.value = '';
    
    const response = await axios.post('/api/login', form.value);
    
    // Si requiere 2FA
    if (response.data.requires_2fa) {
      showTwoFactor.value = true;
      tempToken.value = response.data.temp_token;
      return;
    }
    
    // Login normal sin 2FA
    localStorage.setItem('token', response.data.token);
    await authStore.fetchUser();
    router.push('/dashboard');
    
  } catch (err) {
    error.value = err.response?.data?.message || 'Error al iniciar sesión';
  } finally {
    loading.value = false;
  }
};

const handleTwoFactorSuccess = async (data) => {
  // El token ya fue guardado en el componente TwoFactorVerification
  await authStore.fetchUser();
  router.push('/dashboard');
};

const backToLogin = () => {
  showTwoFactor.value = false;
  tempToken.value = '';
  form.value.password = '';
  error.value = '';
};
</script>
