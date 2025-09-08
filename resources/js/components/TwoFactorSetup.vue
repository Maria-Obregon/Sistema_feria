<template>
  <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-6">
    <h2 class="text-2xl font-bold mb-6 text-center">Configuración 2FA</h2>
    
    <!-- Estado: No configurado -->
    <div v-if="!twoFactorEnabled && !showSetup" class="text-center">
      <div class="mb-4">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
        </svg>
      </div>
      <p class="text-gray-600 mb-4">La autenticación de dos factores no está configurada</p>
      <p v-if="isRequired" class="text-red-600 mb-4 text-sm">
        ⚠️ 2FA es obligatorio para tu rol de juez
      </p>
      <button 
        @click="startSetup"
        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
        :disabled="loading"
      >
        {{ loading ? 'Cargando...' : 'Configurar 2FA' }}
      </button>
    </div>

    <!-- Estado: Configuración en proceso -->
    <div v-if="showSetup && !twoFactorEnabled" class="space-y-4">
      <div v-if="step === 1">
        <h3 class="text-lg font-semibold mb-4">Paso 1: Escanea el código QR</h3>
        <div class="text-center mb-4">
          <img v-if="qrCode" :src="qrCode" alt="Código QR" class="mx-auto border rounded">
          <div v-else class="animate-pulse bg-gray-200 h-48 w-48 mx-auto rounded"></div>
        </div>
        <p class="text-sm text-gray-600 mb-4">
          Escanea este código QR con tu aplicación de autenticación (Google Authenticator, Authy, etc.)
        </p>
        <div class="bg-gray-100 p-3 rounded text-sm">
          <p class="font-semibold">Clave secreta manual:</p>
          <code class="break-all">{{ secretKey }}</code>
        </div>
        <button 
          @click="step = 2"
          class="w-full mt-4 bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
        >
          Continuar
        </button>
      </div>

      <div v-if="step === 2">
        <h3 class="text-lg font-semibold mb-4">Paso 2: Códigos de recuperación</h3>
        <div class="bg-yellow-50 border border-yellow-200 rounded p-4 mb-4">
          <p class="text-sm text-yellow-800 mb-2">
            ⚠️ Guarda estos códigos de recuperación en un lugar seguro. Cada código solo se puede usar una vez.
          </p>
          <div class="grid grid-cols-2 gap-2 text-sm font-mono">
            <div v-for="code in recoveryCodes" :key="code" class="bg-white p-2 rounded border">
              {{ code }}
            </div>
          </div>
        </div>
        <button 
          @click="step = 3"
          class="w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
        >
          He guardado los códigos
        </button>
      </div>

      <div v-if="step === 3">
        <h3 class="text-lg font-semibold mb-4">Paso 3: Verificar configuración</h3>
        <p class="text-sm text-gray-600 mb-4">
          Ingresa el código de 6 dígitos de tu aplicación de autenticación para confirmar la configuración:
        </p>
        <form @submit.prevent="confirmSetup">
          <input
            v-model="verificationCode"
            type="text"
            placeholder="000000"
            maxlength="6"
            class="w-full p-3 border rounded text-center text-2xl font-mono mb-4"
            :class="{ 'border-red-500': error }"
          >
          <p v-if="error" class="text-red-500 text-sm mb-4">{{ error }}</p>
          <button 
            type="submit"
            class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
            :disabled="loading || verificationCode.length !== 6"
          >
            {{ loading ? 'Verificando...' : 'Confirmar configuración' }}
          </button>
        </form>
      </div>
    </div>

    <!-- Estado: Configurado -->
    <div v-if="twoFactorEnabled" class="text-center space-y-4">
      <div class="mb-4">
        <svg class="mx-auto h-12 w-12 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
        </svg>
      </div>
      <p class="text-green-600 font-semibold">2FA está activado</p>
      <p class="text-sm text-gray-600">Tu cuenta está protegida con autenticación de dos factores</p>
      
      <div class="space-y-2">
        <button 
          @click="regenerateRecoveryCodes"
          class="w-full bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded"
          :disabled="loading"
        >
          Regenerar códigos de recuperación
        </button>
        <button 
          @click="showDisableForm = true"
          class="w-full bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
          :disabled="loading"
        >
          Desactivar 2FA
        </button>
      </div>
    </div>

    <!-- Modal para desactivar 2FA -->
    <div v-if="showDisableForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg max-w-sm w-full mx-4">
        <h3 class="text-lg font-semibold mb-4">Desactivar 2FA</h3>
        <p class="text-sm text-gray-600 mb-4">
          Ingresa tu contraseña para desactivar la autenticación de dos factores:
        </p>
        <form @submit.prevent="disableTwoFactor">
          <input
            v-model="password"
            type="password"
            placeholder="Contraseña"
            class="w-full p-3 border rounded mb-4"
            :class="{ 'border-red-500': error }"
          >
          <p v-if="error" class="text-red-500 text-sm mb-4">{{ error }}</p>
          <div class="flex space-x-2">
            <button 
              type="button"
              @click="showDisableForm = false; password = ''; error = ''"
              class="flex-1 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
            >
              Cancelar
            </button>
            <button 
              type="submit"
              class="flex-1 bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
              :disabled="loading || !password"
            >
              {{ loading ? 'Desactivando...' : 'Desactivar' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal para mostrar nuevos códigos de recuperación -->
    <div v-if="showNewRecoveryCodes" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold mb-4">Nuevos códigos de recuperación</h3>
        <div class="bg-yellow-50 border border-yellow-200 rounded p-4 mb-4">
          <p class="text-sm text-yellow-800 mb-2">
            ⚠️ Guarda estos nuevos códigos. Los anteriores ya no funcionan.
          </p>
          <div class="grid grid-cols-2 gap-2 text-sm font-mono">
            <div v-for="code in newRecoveryCodes" :key="code" class="bg-white p-2 rounded border">
              {{ code }}
            </div>
          </div>
        </div>
        <button 
          @click="showNewRecoveryCodes = false; newRecoveryCodes = []"
          class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
        >
          Entendido
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'TwoFactorSetup',
  data() {
    return {
      loading: false,
      error: '',
      twoFactorEnabled: false,
      isRequired: false,
      showSetup: false,
      step: 1,
      qrCode: '',
      secretKey: '',
      recoveryCodes: [],
      verificationCode: '',
      showDisableForm: false,
      password: '',
      showNewRecoveryCodes: false,
      newRecoveryCodes: []
    }
  },
  async mounted() {
    await this.checkStatus()
  },
  methods: {
    async checkStatus() {
      try {
        this.loading = true
        const response = await axios.get('/api/2fa/status')
        this.twoFactorEnabled = response.data.enabled
        this.isRequired = response.data.required
      } catch (error) {
        this.error = 'Error al verificar el estado de 2FA'
        console.error(error)
      } finally {
        this.loading = false
      }
    },

    async startSetup() {
      try {
        this.loading = true
        this.error = ''
        const response = await axios.post('/api/2fa/enable')
        
        this.qrCode = response.data.qr_code
        this.secretKey = response.data.secret
        this.recoveryCodes = response.data.recovery_codes
        this.showSetup = true
        this.step = 1
      } catch (error) {
        this.error = error.response?.data?.message || 'Error al iniciar configuración 2FA'
      } finally {
        this.loading = false
      }
    },

    async confirmSetup() {
      try {
        this.loading = true
        this.error = ''
        
        await axios.post('/api/2fa/confirm', {
          code: this.verificationCode,
          recovery_codes: this.recoveryCodes
        })
        
        this.twoFactorEnabled = true
        this.showSetup = false
        this.resetSetupState()
        
        this.$emit('setup-complete')
      } catch (error) {
        this.error = error.response?.data?.message || 'Código inválido'
      } finally {
        this.loading = false
      }
    },

    async disableTwoFactor() {
      try {
        this.loading = true
        this.error = ''
        
        await axios.post('/api/2fa/disable', {
          password: this.password
        })
        
        this.twoFactorEnabled = false
        this.showDisableForm = false
        this.password = ''
        
        this.$emit('setup-disabled')
      } catch (error) {
        this.error = error.response?.data?.message || 'Error al desactivar 2FA'
      } finally {
        this.loading = false
      }
    },

    async regenerateRecoveryCodes() {
      const password = prompt('Ingresa tu contraseña para regenerar los códigos:')
      if (!password) return

      try {
        this.loading = true
        this.error = ''
        
        const response = await axios.post('/api/2fa/recovery-codes', {
          password: password
        })
        
        this.newRecoveryCodes = response.data.recovery_codes
        this.showNewRecoveryCodes = true
      } catch (error) {
        this.error = error.response?.data?.message || 'Error al regenerar códigos'
      } finally {
        this.loading = false
      }
    },

    resetSetupState() {
      this.step = 1
      this.qrCode = ''
      this.secretKey = ''
      this.recoveryCodes = []
      this.verificationCode = ''
      this.error = ''
    }
  }
}
</script>
