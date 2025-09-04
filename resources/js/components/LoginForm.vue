<template>
  <div class="min-h-screen grid place-items-center bg-gradient-to-br from-slate-900 via-slate-950 to-slate-900">
    <main class="w-full max-w-md p-1">
      <div class="rounded-2xl border border-white/10 bg-white/5 backdrop-blur shadow-2xl">
        <div class="p-8">
          <h1 class="mb-6 text-center text-3xl font-semibold tracking-tight text-white">Iniciar sesión</h1>

          <form @submit.prevent="handleSubmit" class="space-y-5">
            <div>
              <label for="email" class="mb-1 block text-sm font-medium text-slate-200">Correo electrónico</label>
              <input v-model.trim="email" id="email" type="email" required autocomplete="email"
                     class="w-full rounded-xl border border-white/10 bg-white/10 px-4 py-2.5 text-slate-100 placeholder:text-slate-400 outline-none focus:ring-2 focus:ring-sky-400"
                     placeholder="tu@correo.com" />
            </div>

            <div>
              <label for="password" class="mb-1 block text-sm font-medium text-slate-200">Contraseña</label>
              <div class="relative">
                <input :type="showPassword ? 'text' : 'password'"
                       v-model="password" id="password" required autocomplete="current-password"
                       class="w-full rounded-xl border border-white/10 bg-white/10 px-4 py-2.5 pr-12 text-slate-100 placeholder:text-slate-400 outline-none focus:ring-2 focus:ring-sky-400"
                       placeholder="••••••••" />
                <button type="button" @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-2 my-auto rounded-lg px-3 text-slate-300 hover:text-white focus:ring-2 focus:ring-sky-400"
                        :aria-label="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'">
                  {{ showPassword ? '🙈' : '👁️' }}
                </button>
              </div>
            </div>

            <p v-if="error" class="rounded-lg border border-rose-300/30 bg-rose-500/10 p-3 text-sm text-rose-200">
              {{ error }}
            </p>

            <button :disabled="loading || !email || !password"
                    class="w-full rounded-xl bg-sky-600 px-5 py-3 font-semibold text-white shadow-lg hover:bg-sky-500 focus:ring-2 focus:ring-offset-2 focus:ring-sky-400 focus:ring-offset-slate-900 disabled:opacity-60">
              <span v-if="!loading">Entrar</span>
              <span v-else class="inline-flex items-center gap-2">
                <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4A4 4 0 004 12z"/>
                </svg>
                Ingresando…
              </span>
            </button>
          </form>
        </div>
      </div>
      <p class="mt-6 text-center text-xs text-slate-400">© Sistema de Feria Científica</p>
    </main>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const email = ref('')
const password = ref('')
const showPassword = ref(false)
const loading = ref(false)
const error = ref('')

const handleSubmit = async () => {
  loading.value = true
  error.value = ''
  try {
    const { data } = await axios.post('/api/login', {
      email: email.value,
      password: password.value,
    })
    localStorage.setItem('auth_token', data.token)
    localStorage.setItem('auth_role', data.rol)
    axios.defaults.headers.common['Authorization'] = `Bearer ${data.token}`
    window.location.assign('/dashboard')
  } catch (e) {
    error.value = e?.response?.data?.message || 'No se pudo iniciar sesión.'
  } finally {
    loading.value = false
  }
}
</script>
