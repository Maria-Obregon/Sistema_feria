<template>
  <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-6">
    <div class="text-center mb-6">
      <svg class="mx-auto h-12 w-12 text-blue-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
      </svg>
      <h2 class="text-2xl font-bold text-gray-900">Verificación 2FA</h2>
      <p class="text-gray-600 mt-2">Ingresa el código de tu aplicación de autenticación</p>
    </div>

    <form @submit.prevent="verify" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Código de verificación
        </label>
        <input
          v-model="code"
          type="text"
          placeholder="000000"
          maxlength="10"
          class="w-full p-3 border rounded-lg text-center text-2xl font-mono"
          :class="{ 'border-red-500': error, 'border-gray-300': !error }"
          @input="clearError"
          autofocus
        >
        <p class="text-xs text-gray-500 mt-1">
          Código de 6 dígitos o código de recuperación de 10 caracteres
        </p>
      </div>

      <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-3">
        <p class="text-red-600 text-sm">{{ error }}</p>
      </div>

      <div v-if="recoveryUsed" class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
        <p class="text-yellow-800 text-sm">
          ⚠️ Has usado un código de recuperación. Te quedan {{ codesRemaining }} códigos.
        </p>
      </div>

      <button 
        type="submit"
        class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200"
        :disabled="loading || !code"
        :class="{ 'opacity-50 cursor-not-allowed': loading || !code }"
      >
        {{ loading ? 'Verificando...' : 'Verificar' }}
      </button>
    </form>

    <div class="mt-6 text-center">
      <button 
        @click="$emit('back-to-login')"
        class="text-sm text-gray-500 hover:text-gray-700 underline"
      >
        ← Volver al login
      </button>
    </div>

    <div class="mt-4 bg-gray-50 rounded-lg p-4">
      <h3 class="text-sm font-semibold text-gray-700 mb-2">¿No tienes acceso a tu dispositivo?</h3>
      <p class="text-xs text-gray-600 mb-2">
        Puedes usar uno de tus códigos de recuperación de 10 caracteres que guardaste cuando configuraste 2FA.
      </p>
      <p class="text-xs text-gray-600">
        Si no tienes acceso a tus códigos, contacta al administrador del sistema.
      </p>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'TwoFactorVerification',
  props: {
    tempToken: {
      type: String,
      required: true
    }
  },
  emits: ['verification-success', 'back-to-login'],
  data() {
    return {
      code: '',
      loading: false,
      error: '',
      recoveryUsed: false,
      codesRemaining: 0
    }
  },
  methods: {
    async verify() {
      if (!this.code) return

      try {
        this.loading = true
        this.error = ''
        
        // Configurar token temporal para la verificación
        const originalToken = localStorage.getItem('token')
        localStorage.setItem('token', this.tempToken)
        
        const response = await axios.post('/api/2fa/verify', {
          code: this.code
        })
        
        // Guardar el token permanente
        localStorage.setItem('token', response.data.token)
        
        // Mostrar información sobre código de recuperación si se usó
        if (response.data.recovery_used) {
          this.recoveryUsed = true
          this.codesRemaining = response.data.codes_remaining
          
          // Mostrar por unos segundos antes de continuar
          setTimeout(() => {
            this.$emit('verification-success', response.data)
          }, 2000)
        } else {
          this.$emit('verification-success', response.data)
        }
        
      } catch (error) {
        // Restaurar token original en caso de error
        const originalToken = localStorage.getItem('token')
        if (originalToken && originalToken !== this.tempToken) {
          localStorage.setItem('token', originalToken)
        }
        
        this.error = error.response?.data?.message || 'Error al verificar el código'
        this.code = ''
      } finally {
        this.loading = false
      }
    },

    clearError() {
      this.error = ''
      this.recoveryUsed = false
    }
  }
}
</script>
