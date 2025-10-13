<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Topbar -->
    <nav class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center space-x-6">
            <h1 class="text-xl font-semibold">Panel de Juez</h1>
            <router-link
              :to="{ name: 'juez.asignaciones' }"
              class="text-sm text-gray-600 hover:text-gray-900"
            >
              Mis Asignaciones
            </router-link>
            <router-link
              :to="{ name: 'juez.calificaciones' }"
              class="text-sm text-gray-600 hover:text-gray-900"
            >
              Calificaciones
            </router-link>
          </div>

          <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-500">{{ user?.email }}</span>
            <button @click="logout" class="text-sm text-red-600 hover:text-red-800">
              Cerrar Sesión
            </button>
          </div>
        </div>
      </div>
    </nav>

    <!-- Contenido principal -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <!-- Encabezado -->
      <div class="mb-6">
        <h2 class="text-2xl font-bold">Calificar Proyecto</h2>
        <p class="mt-1 text-sm text-gray-500">
          Evaluación {{ tipoEval }} - Asignación #{{ asignacionId }}
        </p>
      </div>

      <!-- Error de validación de parámetros -->
      <div v-if="paramError" class="bg-white rounded-lg shadow p-6">
        <div class="text-center text-red-600">
          <p class="font-medium">{{ paramError }}</p>
          <button
            @click="goBack"
            class="mt-4 px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
          >
            Volver
          </button>
        </div>
      </div>

      <!-- Estado de carga -->
      <div v-else-if="loading" class="bg-white rounded-lg shadow p-8 text-center">
        <p class="text-gray-500">Cargando criterios de evaluación...</p>
      </div>

      <!-- Estado de error -->
      <div v-else-if="error" class="bg-white rounded-lg shadow p-6">
        <div class="text-center text-red-600">
          <p class="font-medium">Error al cargar datos</p>
          <p class="mt-1 text-sm">{{ error }}</p>
          <button
            @click="goBack"
            class="mt-4 px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
          >
            Volver
          </button>
        </div>
      </div>

      <!-- Estado vacío (sin criterios) -->
      <div v-else-if="criterios.length === 0" class="bg-white rounded-lg shadow p-8 text-center">
        <p class="text-gray-500">No hay criterios definidos para esta evaluación.</p>
        <button
          @click="goBack"
          class="mt-4 px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
        >
          Volver
        </button>
      </div>

      <!-- Formulario de calificación -->
      <div v-else class="space-y-6">
        <!-- Mensaje de éxito -->
        <div v-if="successMessage" class="bg-white rounded-lg shadow p-4">
          <p class="text-green-600 text-center">{{ successMessage }}</p>
        </div>

        <!-- Resumen de progreso -->
        <div class="bg-white rounded-lg shadow p-4">
          <p class="text-sm text-gray-700">
            <span class="font-medium">{{ criteriosConPuntaje }}</span> de 
            <span class="font-medium">{{ criterios.length }}</span> criterios con puntaje
          </p>
        </div>

        <!-- Lista de criterios -->
        <div class="bg-white rounded-lg shadow p-6">
          <div class="space-y-6">
            <div
              v-for="(criterio, index) in criterios"
              :key="criterio.id"
              class="border-b border-gray-200 pb-6 last:border-0 last:pb-0"
            >
              <!-- Encabezado del criterio -->
              <div class="mb-3">
                <h3 class="text-lg font-medium text-gray-900">
                  {{ index + 1 }}. {{ criterio.nombre }}
                </h3>
                <p class="text-sm text-gray-500">
                  Peso: {{ criterio.peso }} | Puntos máximos: {{ criterio.max_puntos }}
                </p>
              </div>

              <!-- Input de puntaje -->
              <div class="mb-3">
                <label :for="`puntaje-${criterio.id}`" class="block text-sm font-medium text-gray-700 mb-1">
                  Puntaje *
                </label>
                <input
                  :id="`puntaje-${criterio.id}`"
                  v-model.number="form[criterio.id].puntaje"
                  type="number"
                  :min="0"
                  :max="criterio.max_puntos"
                  step="1"
                  :ref="index === 0 ? 'firstInput' : null"
                  @input="validatePuntaje(criterio.id, criterio.max_puntos)"
                  :class="[
                    'w-full px-4 py-2 border rounded-lg',
                    errors[criterio.id]?.puntaje ? 'border-red-500' : 'border-gray-300'
                  ]"
                  placeholder="0"
                >
                <p v-if="errors[criterio.id]?.puntaje" class="mt-1 text-sm text-red-600">
                  {{ errors[criterio.id].puntaje }}
                </p>
              </div>

              <!-- Textarea de comentario -->
              <div>
                <label :for="`comentario-${criterio.id}`" class="block text-sm font-medium text-gray-700 mb-1">
                  Comentario (opcional)
                </label>
                <textarea
                  :id="`comentario-${criterio.id}`"
                  v-model="form[criterio.id].comentario"
                  rows="2"
                  maxlength="1000"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                  placeholder="Observaciones sobre este criterio..."
                ></textarea>
                <p class="mt-1 text-xs text-gray-500">
                  {{ form[criterio.id].comentario?.length || 0 }}/1000 caracteres
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Errores del servidor -->
        <div v-if="serverError" class="bg-white rounded-lg shadow p-4">
          <p class="text-red-600 text-center">{{ serverError }}</p>
        </div>

        <!-- Botones de acción -->
        <div class="flex justify-between">
          <button
            @click="goBack"
            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
            :disabled="saving"
          >
            Volver
          </button>
          
          <button
            @click="saveCalificaciones"
            :disabled="saving || !canSave"
            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ saving ? 'Guardando...' : 'Guardar Calificaciones' }}
          </button>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import axios from 'axios'

// Router y auth
const router = useRouter()
const route = useRoute()
const auth = useAuthStore()

// Computed
const user = computed(() => auth.user)

// Refs del template
const firstInput = ref(null)

// Estado reactivo
const loading = ref(false)
const saving = ref(false)
const error = ref(null)
const paramError = ref(null)
const serverError = ref(null)
const successMessage = ref(null)

// Parámetros de la URL
const asignacionId = ref(null)
const tipoEval = ref(null)

// Datos
const criterios = ref([])
const form = ref({}) // { [criterioId]: { puntaje, comentario } }
const errors = ref({}) // { [criterioId]: { puntaje } }

/**
 * Valida los parámetros de la URL
 */
const validateParams = () => {
  const id = route.query.asignacionId
  const tipo = route.query.tipo_eval

  if (!id || isNaN(parseInt(id))) {
    paramError.value = 'Parámetro asignacionId inválido o faltante'
    return false
  }

  if (!tipo || !['escrita', 'oral'].includes(tipo)) {
    paramError.value = 'Parámetro tipo_eval inválido o faltante (debe ser "escrita" u "oral")'
    return false
  }

  asignacionId.value = parseInt(id)
  tipoEval.value = tipo
  return true
}

/**
 * Calcula cuántos criterios tienen puntaje asignado
 */
const criteriosConPuntaje = computed(() => {
  return Object.values(form.value).filter(item => 
    item.puntaje !== null && item.puntaje !== '' && !isNaN(item.puntaje)
  ).length
})

/**
 * Determina si se puede guardar (al menos un criterio con puntaje válido y sin errores)
 */
const canSave = computed(() => {
  // Verificar que hay al menos un puntaje
  if (criteriosConPuntaje.value === 0) return false
  
  // Verificar que no hay errores
  const hasErrors = Object.values(errors.value).some(err => err.puntaje)
  return !hasErrors
})

/**
 * Valida el puntaje de un criterio
 */
const validatePuntaje = (criterioId, maxPuntos) => {
  const puntaje = form.value[criterioId]?.puntaje
  
  // Limpiar error si está vacío (opcional)
  if (puntaje === null || puntaje === '' || puntaje === undefined) {
    if (errors.value[criterioId]) {
      delete errors.value[criterioId].puntaje
    }
    return
  }

  // Validar rango
  if (puntaje < 0) {
    if (!errors.value[criterioId]) errors.value[criterioId] = {}
    errors.value[criterioId].puntaje = 'El puntaje no puede ser negativo'
    return
  }

  if (puntaje > maxPuntos) {
    if (!errors.value[criterioId]) errors.value[criterioId] = {}
    errors.value[criterioId].puntaje = `El puntaje no puede exceder ${maxPuntos}`
    return
  }

  // Limpiar error si es válido
  if (errors.value[criterioId]?.puntaje) {
    delete errors.value[criterioId].puntaje
    if (Object.keys(errors.value[criterioId]).length === 0) {
      delete errors.value[criterioId]
    }
  }
}

/**
 * Carga los criterios de la rúbrica
 */
const fetchCriterios = async () => {
  try {
    const { data } = await axios.get(`/api/rubricas/${tipoEval.value}/criterios`)
    criterios.value = data.data || []
    
    // Inicializar el formulario
    criterios.value.forEach(criterio => {
      form.value[criterio.id] = {
        puntaje: null,
        comentario: ''
      }
      errors.value[criterio.id] = {}
    })
  } catch (err) {
    throw new Error(err.response?.data?.message || 'Error al cargar criterios')
  }
}

/**
 * Carga las calificaciones existentes
 */
const fetchCalificaciones = async () => {
  try {
    const { data } = await axios.get('/api/calificaciones', {
      params: { asignacion_juez_id: asignacionId.value }
    })
    
    const calificaciones = data.data || []
    
    // Prellenar el formulario con calificaciones existentes
    calificaciones.forEach(calif => {
      if (form.value[calif.criterio_id]) {
        form.value[calif.criterio_id].puntaje = calif.puntaje
        form.value[calif.criterio_id].comentario = calif.comentario || ''
      }
    })
  } catch (err) {
    // Si es 403, el usuario no tiene acceso - mostrar error
    if (err.response?.status === 403) {
      throw new Error(err.response?.data?.message || 'No tienes permiso para acceder a estas calificaciones')
    }
    // Si es otro error, continuar sin calificaciones previas
    console.warn('No se pudieron cargar calificaciones previas:', err)
  }
}

/**
 * Carga inicial de datos
 */
const loadData = async () => {
  loading.value = true
  error.value = null
  
  try {
    // Cargar criterios
    await fetchCriterios()
    
    // Cargar calificaciones existentes
    await fetchCalificaciones()
    
    // Enfocar primer input después de cargar
    await nextTick()
    if (firstInput.value) {
      firstInput.value.focus()
    }
  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}

/**
 * Guarda las calificaciones
 */
const saveCalificaciones = async () => {
  saving.value = true
  serverError.value = null
  successMessage.value = null
  
  try {
    // Construir payload solo con criterios que tienen puntaje
    const items = []
    
    for (const criterio of criterios.value) {
      const puntaje = form.value[criterio.id]?.puntaje
      
      // Solo incluir si tiene puntaje
      if (puntaje !== null && puntaje !== '' && !isNaN(puntaje)) {
        items.push({
          criterio_id: criterio.id,
          puntaje: parseFloat(puntaje),
          comentario: form.value[criterio.id]?.comentario || null
        })
      }
    }
    
    if (items.length === 0) {
      serverError.value = 'Debe calificar al menos un criterio'
      return
    }
    
    // Enviar al servidor
    const { data } = await axios.post('/api/calificaciones', {
      asignacion_juez_id: asignacionId.value,
      items
    })
    
    // Mostrar mensaje de éxito
    successMessage.value = `Calificaciones guardadas: ${data.total} criterio(s) actualizado(s)`
    
    // Limpiar mensaje después de 5 segundos
    setTimeout(() => {
      successMessage.value = null
    }, 5000)
    
  } catch (err) {
    if (err.response?.status === 422) {
      serverError.value = err.response?.data?.message || 'Error de validación'
    } else if (err.response?.status === 403) {
      serverError.value = err.response?.data?.message || 'No tienes permiso para calificar esta asignación'
    } else {
      serverError.value = err.response?.data?.message || 'Error al guardar calificaciones'
    }
  } finally {
    saving.value = false
  }
}

/**
 * Volver a la página anterior
 */
const goBack = () => {
  router.back()
}

/**
 * Cerrar sesión
 */
const logout = async () => {
  await auth.logout()
  router.push({ name: 'login' })
}

// Lifecycle
onMounted(() => {
  // Validar parámetros
  if (!validateParams()) {
    return
  }
  
  // Cargar datos
  loadData()
})
</script>