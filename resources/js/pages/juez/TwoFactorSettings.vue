<template>
  <div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
      <h1 class="text-3xl font-bold text-gray-900 mb-8">Configuración de Seguridad</h1>
      
      <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">Autenticación de Dos Factores (2FA)</h2>
        
        <div v-if="isRequired" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
          <div class="flex">
            <svg class="h-5 w-5 text-yellow-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-yellow-800">2FA Obligatorio</h3>
              <p class="text-sm text-yellow-700 mt-1">
                Como juez, debes configurar la autenticación de dos factores para acceder al sistema.
              </p>
            </div>
          </div>
        </div>

        <TwoFactorSetup 
          @setup-complete="handleSetupComplete"
          @setup-disabled="handleSetupDisabled"
        />
      </div>

      <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h3 class="text-sm font-medium text-blue-800 mb-2">¿Qué es la autenticación de dos factores?</h3>
        <p class="text-sm text-blue-700 mb-3">
          La autenticación de dos factores (2FA) añade una capa extra de seguridad a tu cuenta. 
          Además de tu contraseña, necesitarás un código de 6 dígitos de tu aplicación de autenticación.
        </p>
        <h4 class="text-sm font-medium text-blue-800 mb-2">Aplicaciones recomendadas:</h4>
        <ul class="text-sm text-blue-700 list-disc list-inside space-y-1">
          <li>Google Authenticator (Android/iOS)</li>
          <li>Microsoft Authenticator (Android/iOS)</li>
          <li>Authy (Android/iOS/Desktop)</li>
          <li>1Password (Premium)</li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
import TwoFactorSetup from '../../components/TwoFactorSetup.vue'
import { useAuthStore } from '../../stores/auth'

export default {
  name: 'TwoFactorSettings',
  components: {
    TwoFactorSetup
  },
  setup() {
    const authStore = useAuthStore()
    
    return {
      authStore
    }
  },
  computed: {
    isRequired() {
      return this.authStore.user?.roles?.includes('juez') || this.authStore.user?.roles?.includes('admin')
    }
  },
  methods: {
    handleSetupComplete() {
      this.$router.push('/juez/dashboard')
    },
    
    handleSetupDisabled() {
      // Si es obligatorio, redirigir al dashboard para que vuelva a configurar
      if (this.isRequired) {
        this.$router.push('/juez/dashboard')
      }
    }
  }
}
</script>
