<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-b from-sky-100 to-white">
    <div class="w-full max-w-[420px]">
      <!-- Card -->
      <div class="bg-white rounded-2xl shadow-xl ring-1 ring-black/5 p-8">
        <!-- Logo -->
        <div class="flex justify-center mb-6">
          <img
            src="/img/logo.webp"
            alt="PRONAFECYT"
            class="w-40 sm:w-48 h-auto object-contain"
          />
        </div>

        <!-- Título -->
        <h1 class="text-center text-2xl font-extrabold tracking-tight text-gray-900">Iniciar sesión</h1>
        <p class="mt-1 text-center text-sm text-gray-500">
          Sistema de Ferias de Ciencia y Tecnología
        </p>

        <!-- Error -->
        <div
          v-if="error"
          class="mt-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
        >
          {{ error }}
        </div>

        <!-- Formulario -->
        <form class="mt-6 space-y-4" @submit.prevent="handleLogin">
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              autocomplete="email"
              required
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-gray-900 shadow-sm
                     focus:border-sky-500 focus:ring-2 focus:ring-sky-200 outline-none"
              placeholder="tucorreo@dominio.com"
            />
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              autocomplete="current-password"
              required
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-gray-900 shadow-sm
                     focus:border-sky-500 focus:ring-2 focus:ring-sky-200 outline-none"
              placeholder="********"
            />
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="mt-2 inline-flex w-full items-center justify-center rounded-lg bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white
                   hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-400 disabled:opacity-60"
          >
            <svg v-if="loading" class="-ml-1 mr-2 h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4A4 4 0 004 12z"/>
            </svg>
            {{ loading ? 'Ingresando...' : 'Login' }}
          </button>
        </form>
      </div>

      <!-- Footer -->
      <p class="mt-4 text-center text-xs text-gray-400">
        © {{ new Date().getFullYear() }} PRONAFECYT
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import axios from 'axios'

const router = useRouter()
const auth = useAuthStore()

const form = ref({ email: '', password: '' })
const loading = ref(false)
const error = ref('')

const roleRoute = (role) => {
  switch (role) {
    case 'admin':                return { name: 'admin.dashboard' }
    case 'comite_institucional': return { name: 'inst.dashboard' }
    case 'juez':                 return { name: 'juez.dashboard' }
    case 'estudiante':           return { name: 'est.dashboard' }
    default:                     return { name: 'dashboard' }
  }
}

const handleLogin = async () => {
  try {
    loading.value = true
    error.value = ''

    const { data } = await axios.post('/api/login', form.value)

    // Guardar token y rol
    localStorage.setItem('token', data.token)
    axios.defaults.headers.common['Authorization'] = `Bearer ${data.token}`

    auth.user = data.user
    const role = Array.isArray(data.user?.roles)
      ? (typeof data.user.roles[0] === 'string' ? data.user.roles[0] : data.user.roles[0]?.name)
      : null
    if (role) localStorage.setItem('role', role)

    await router.push(roleRoute(role))
  } catch (e) {
    error.value = e.response?.data?.message || 'Error al iniciar sesión'
  } finally {
    loading.value = false
  }
}
</script>
