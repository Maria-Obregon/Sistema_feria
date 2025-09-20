<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50">
    <!-- Formulario de login normal -->
    <div class="max-w-md w-full space-y-8">
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

    // Navega a su dashboard
    await router.push(roleRoute(role))
  } catch (e) {
    error.value = e.response?.data?.message || 'Error al iniciar sesión'
  } finally {
    loading.value = false
  }
}
</script>
