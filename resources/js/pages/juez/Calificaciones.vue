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
              :to="{ name: 'juez.misCalificaciones' }"
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
      <div class="mb-6 flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-bold">Calificar Proyecto</h2>
          <p class="mt-1 text-sm text-gray-500">
            Evaluación {{ tipoEval }} - Asignación #{{ asignacionId }}
          </p>
        </div>
        <button
          @click="goBack"
          aria-label="Volver a la lista de asignaciones"
          class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900 border border-gray-300 rounded-md hover:bg-gray-50"
        >
          ← Volver
        </button>
      </div>

      <!-- Cabecera del proyecto -->
      <div v-if="proyectoData" class="mb-6 bg-white rounded-lg shadow p-4">
        <div class="flex items-start justify-between">
          <div>
            <h3 class="text-lg font-semibold text-gray-900">{{ proyectoData.projectName }}</h3>
            <div class="mt-2 space-y-1">
              <p class="text-sm text-gray-600">
                <span class="font-medium">Área:</span> {{ proyectoData.detalles?.area?.nombre || 'N/A' }}
              </p>
              <p class="text-sm text-gray-600">
                <span class="font-medium">Etapa:</span> 
                <span class="capitalize">{{ proyectoData.etapaNombre || 'N/A' }}</span>
              </p>
              <p class="text-sm text-gray-600">
                <span class="font-medium">Tipo:</span> 
                <span class="capitalize">{{ proyectoData.tipoEval || 'N/A' }}</span>
              </p>
            </div>
          </div>
          <div v-if="isReadonly" class="px-3 py-1 bg-red-100 text-red-800 text-xs font-medium rounded-full">
            Etapa cerrada
          </div>
        </div>
      </div>
      <div v-else-if="loadingProyecto" class="mb-6 bg-white rounded-lg shadow p-4">
        <p class="text-sm text-gray-500">Cargando información del proyecto...</p>
      </div>

      <!-- Error de validación de parámetros -->
      <div v-if="paramError" class="bg-white rounded-lg shadow p-6" role="status" aria-live="polite">
        <div class="text-center text-red-600">
          <p class="font-medium">{{ paramError }}</p>
          <p class="mt-2 text-sm text-gray-600">Redirigiendo a asignaciones...</p>
        </div>
      </div>

      <!-- Estado de carga -->
      <div v-else-if="loading" class="bg-white rounded-lg shadow p-8 text-center">
        <p class="text-gray-500">Cargando criterios de evaluación...</p>
      </div>

      <!-- Estado de error -->
      <div v-else-if="error" class="bg-white rounded-lg shadow p-6" role="status" aria-live="polite">
        <div class="text-center text-red-600">
          <p class="font-medium">Error al cargar datos</p>
          <p class="mt-1 text-sm">{{ error }}</p>
          <button
            @click="goBack"
            aria-label="Volver a la lista de asignaciones"
            class="mt-4 px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
          >
            Volver
          </button>
        </div>
      </div>

      <!-- Estado vacío (sin criterios) -->
      <div v-else-if="criterios.length === 0" class="bg-white rounded-lg shadow p-8 text-center" role="status" aria-live="polite">
        <p class="text-gray-500">No hay criterios definidos para esta evaluación.</p>
        <button
          @click="goBack"
          aria-label="Volver a la lista de asignaciones"
          class="mt-4 px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
        >
          Volver
        </button>
      </div>

      <!-- Formulario de calificación -->
      <div v-else class="space-y-6">
        <!-- Mensaje de éxito -->
        <div v-if="successMessage" class="bg-white rounded-lg shadow p-4" role="status" aria-live="polite">
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
                  inputmode="numeric"
                  :min="0"
                  :max="criterio.max_puntos"
                  step="1"
                  :ref="index === 0 ? 'firstInput' : null"
                  :readonly="isReadonly"
                  @input="validatePuntaje(criterio.id, criterio.max_puntos)"
                  :class="[
                    'w-full px-4 py-2 border rounded-lg',
                    errors[criterio.id]?.puntaje ? 'border-red-500' : 'border-gray-300',
                    isReadonly ? 'bg-gray-100 cursor-not-allowed' : ''
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
                  :readonly="isReadonly"
                  @input="onCommentChange"
                  :class="[
                    'w-full px-4 py-2 border border-gray-300 rounded-lg',
                    isReadonly ? 'bg-gray-100 cursor-not-allowed' : ''
                  ]"
                  placeholder="Observaciones sobre este criterio..."
                ></textarea>
                <p class="mt-1 text-xs text-gray-500">
                  {{ comentarioCharsRemaining(criterio.id) }} caracteres
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Errores del servidor -->
        <div v-if="serverError" class="bg-white rounded-lg shadow p-4" role="status" aria-live="polite">
          <p class="text-red-600 text-center">{{ serverError }}</p>
          <p v-if="isReadonly" class="text-sm text-gray-500 text-center mt-2">Los campos han sido bloqueados.</p>
        </div>

        <!-- Botones de acción -->
        <div class="flex justify-between">
          <button
            @click="goBack"
            aria-label="Volver a la lista de asignaciones"
            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
            :disabled="saving"
          >
            Volver
          </button>
          
          <button
            @click="saveCalificaciones"
            :disabled="saving || !canSave"
            aria-label="Guardar calificaciones"
            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ saving ? 'Guardando...' : 'Guardar Calificaciones' }}
          </button>
        </div>
      </div>
    </main>

    <!-- Sticky footer (solo mobile) -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 shadow-lg">
      <button
        @click="saveCalificaciones"
        :disabled="saving || !canSave"
        aria-label="Guardar calificaciones"
        class="w-full px-4 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed font-medium"
      >
        {{ saving ? 'Guardando...' : 'Guardar Calificaciones' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useCriteriosCache } from '@/composables/useCriteriosCache'
import axios from 'axios'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()
const { getCriterios } = useCriteriosCache()

// Computed
const user = computed(() => auth.user)

// Refs del template
const firstInput = ref(null)

const loading = ref(false)
const saving = ref(false)
const error = ref(null)
const paramError = ref(null)
const serverError = ref(null)
const successMessage = ref(null)
const hasChanges = ref(false)
const isReadonly = ref(false)

// Datos del proyecto
const proyectoData = ref(null)
const loadingProyecto = ref(false)
const proyectoId = ref(null)

// Parámetros de la URL
const asignacionId = ref(null)
const tipoEval = ref(null)

const criterios = ref([])
const form = ref({})
const errors = ref({})
const initialFormState = ref({})
const hasServerGrades = ref(false)

// Debounce para borrador
let draftDebounceTimer = null
let lastDraftJson = null

// AbortControllers para cancelar requests
let criteriosController = null
let calificacionesController = null
let proyectoController = null

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

const canSave = computed(() => {
  if (isReadonly.value) return false
  if (!hasChanges.value) return false
  if (criteriosConPuntaje.value === 0) return false
  
  const hasErrors = Object.values(errors.value).some(err => err.puntaje)
  return !hasErrors
})

const comentarioCharsRemaining = (criterioId) => {
  const length = form.value[criterioId]?.comentario?.length || 0
  const remaining = 1000 - length
  return remaining < 50 ? `quedan ${remaining}` : `${length}/1000`
}

const validatePuntaje = (criterioId, maxPuntos) => {
  const puntaje = form.value[criterioId]?.puntaje
  
  hasChanges.value = true
  saveDraftDebounced()
  
  if (puntaje === null || puntaje === '' || puntaje === undefined) {
    if (errors.value[criterioId]) {
      delete errors.value[criterioId].puntaje
    }
    return
  }

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

  if (errors.value[criterioId]?.puntaje) {
    delete errors.value[criterioId].puntaje
    if (Object.keys(errors.value[criterioId]).length === 0) {
      delete errors.value[criterioId]
    }
  }
}

const onCommentChange = () => {
  hasChanges.value = true
  saveDraftDebounced()
}

// Clave de borrador en localStorage
const getDraftKey = () => `judgeDraft:${asignacionId.value}`

// Guardar borrador con debounce
const saveDraftDebounced = () => {
  if (draftDebounceTimer) {
    clearTimeout(draftDebounceTimer)
  }
  
  draftDebounceTimer = setTimeout(() => {
    saveDraft()
  }, 300)
}

// Guardar borrador en localStorage
const saveDraft = () => {
  if (!asignacionId.value) return
  
  const puntajes = {}
  const comentarios = {}
  
  Object.keys(form.value).forEach(criterioId => {
    const puntaje = form.value[criterioId]?.puntaje
    const comentario = form.value[criterioId]?.comentario
    
    if (puntaje !== null && puntaje !== '' && !isNaN(puntaje)) {
      puntajes[criterioId] = puntaje
    }
    if (comentario) {
      comentarios[criterioId] = comentario
    }
  })
  
  const draft = {
    puntajes,
    comentarios,
    updatedAt: new Date().toISOString()
  }
  
  // Comparar con último snapshot para evitar escrituras innecesarias
  const currentDraftJson = JSON.stringify(draft)
  if (currentDraftJson === lastDraftJson) {
    return // No cambios, no escribir
  }
  
  try {
    localStorage.setItem(getDraftKey(), currentDraftJson)
    lastDraftJson = currentDraftJson
  } catch (err) {
    console.warn('Error al guardar borrador:', err)
  }
}

// Cargar borrador desde localStorage
const loadDraft = () => {
  if (!asignacionId.value || hasServerGrades.value) return
  
  try {
    const draftStr = localStorage.getItem(getDraftKey())
    if (!draftStr) return
    
    const draft = JSON.parse(draftStr)
    
    // Cargar puntajes
    Object.keys(draft.puntajes || {}).forEach(criterioId => {
      if (form.value[criterioId]) {
        form.value[criterioId].puntaje = draft.puntajes[criterioId]
      }
    })
    
    // Cargar comentarios
    Object.keys(draft.comentarios || {}).forEach(criterioId => {
      if (form.value[criterioId]) {
        form.value[criterioId].comentario = draft.comentarios[criterioId]
      }
    })
    
    console.log('Borrador cargado desde localStorage')
  } catch (err) {
    console.warn('Error al cargar borrador:', err)
  }
}

// Limpiar borrador
const clearDraft = () => {
  if (!asignacionId.value) return
  
  try {
    localStorage.removeItem(getDraftKey())
    lastDraftJson = null
    console.log('Borrador eliminado')
  } catch (err) {
    console.warn('Error al eliminar borrador:', err)
  }
}

const fetchCriterios = async () => {
  // Cancelar request anterior
  if (criteriosController) {
    criteriosController.abort()
  }
  
  criteriosController = new AbortController()
  
  try {
    criterios.value = await getCriterios(tipoEval.value)
    
    criterios.value.forEach(criterio => {
      form.value[criterio.id] = {
        puntaje: null,
        comentario: ''
      }
      errors.value[criterio.id] = {}
    })
  } catch (err) {
    // Ignorar errores de cancelación
    if (axios.isCancel(err) || err.code === 'ERR_CANCELED') {
      return
    }
    throw new Error(err.response?.data?.message || 'Error al cargar criterios')
  }
}

const fetchProyecto = async () => {
  if (!proyectoId.value) return
  
  // Cancelar request anterior
  if (proyectoController) {
    proyectoController.abort()
  }
  
  proyectoController = new AbortController()
  loadingProyecto.value = true
  
  try {
    const { data } = await axios.get(`/api/juez/proyectos/${proyectoId.value}`, {
      signal: proyectoController.signal
    })
    proyectoData.value = data.data
  } catch (err) {
    // Ignorar errores de cancelación
    if (axios.isCancel(err) || err.code === 'ERR_CANCELED') {
      return
    }
    console.warn('Error al cargar datos del proyecto:', err)
  } finally {
    loadingProyecto.value = false
  }
}

const fetchCalificaciones = async () => {
  // Cancelar request anterior
  if (calificacionesController) {
    calificacionesController.abort()
  }
  
  calificacionesController = new AbortController()
  
  try {
    const { data } = await axios.get('/api/calificaciones', {
      params: { asignacion_juez_id: asignacionId.value },
      signal: calificacionesController.signal
    })
    
    const calificaciones = data.data || []
    
    if (calificaciones.length > 0) {
      hasServerGrades.value = true
      
      calificaciones.forEach(calif => {
        if (form.value[calif.criterio_id]) {
          form.value[calif.criterio_id].puntaje = calif.puntaje
          form.value[calif.criterio_id].comentario = calif.comentario || ''
        }
      })
    } else {
      hasServerGrades.value = false
      loadDraft()
    }
    
    initialFormState.value = JSON.parse(JSON.stringify(form.value))
    hasChanges.value = false
  } catch (err) {
    // Ignorar errores de cancelación
    if (axios.isCancel(err) || err.code === 'ERR_CANCELED') {
      return
    }
    if (err.response?.status === 403) {
      throw new Error(err.response?.data?.message || 'No tienes permiso para acceder a estas calificaciones')
    }
    console.warn('No se pudieron cargar calificaciones previas:', err)
    loadDraft()
  }
}

const loadData = async () => {
  loading.value = true
  error.value = null
  
  try {
    await fetchCriterios()
    await fetchCalificaciones()
    
    // Cargar datos del proyecto (sin bloquear)
    if (proyectoId.value) {
      fetchProyecto()
    }
    
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

const saveCalificaciones = async () => {
  saving.value = true
  serverError.value = null
  successMessage.value = null
  
  try {
    const items = []
    
    for (const criterio of criterios.value) {
      const puntaje = form.value[criterio.id]?.puntaje
      
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
    
    const { data } = await axios.post('/api/calificaciones', {
      asignacion_juez_id: asignacionId.value,
      items
    })
    
    successMessage.value = `Calificaciones guardadas: ${data.total} criterio(s) actualizado(s)`
    hasChanges.value = false
    hasServerGrades.value = true
    initialFormState.value = JSON.parse(JSON.stringify(form.value))
    
    clearDraft()
    
    // Marcar para refrescar dashboard
    try {
      sessionStorage.setItem('justGraded', '1')
    } catch (err) {
      console.warn('No se pudo marcar justGraded:', err)
    }
    
    setTimeout(() => {
      successMessage.value = null
    }, 5000)
    
  } catch (err) {
    if (err.response?.status === 422) {
      const message = err.response?.data?.message || 'Error de validación'
      serverError.value = message
      
      if (message.toLowerCase().includes('etapa cerrada') || message.toLowerCase().includes('stage closed')) {
        isReadonly.value = true
      }
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
 * Volver a asignaciones
 */
const goBack = () => {
  router.push({ name: 'juez.asignaciones' })
}

/**
 * Intenta cargar la primera asignación pendiente del juez (fallback interno)
 */
const tryLoadFirstPendingAssignment = async () => {
  try {
    // Intentar obtener primera asignación pendiente
    const resPending = await axios.get('/api/juez/asignaciones', {
      params: { estado: 'pending', page: 1, per_page: 1 }
    })
    
    const rowsPending = Array.isArray(resPending.data?.data) ? resPending.data.data : resPending.data
    const firstPending = Array.isArray(rowsPending) ? rowsPending[0] : null
    
    if (firstPending) {
      await router.replace({
        path: '/juez/calificaciones',
        query: {
          asignacionId: firstPending.assignmentId,
          tipo_eval: firstPending.tipoEval
        }
      })
      return true
    }
    
    // Si no hay pendientes, intentar con todas
    const resAll = await axios.get('/api/juez/asignaciones', {
      params: { page: 1, per_page: 1 }
    })
    
    const rowsAll = Array.isArray(resAll.data?.data) ? resAll.data.data : resAll.data
    const firstAny = Array.isArray(rowsAll) ? rowsAll[0] : null
    
    if (firstAny) {
      await router.replace({
        path: '/juez/calificaciones',
        query: {
          asignacionId: firstAny.assignmentId,
          tipo_eval: firstAny.tipoEval
        }
      })
      return true
    }
    
    // Si no hay ninguna asignación
    await router.replace('/juez/asignaciones')
    return false
  } catch (err) {
    console.error('Error al cargar asignaciones:', err)
    await router.replace('/juez/asignaciones')
    return false
  }
}

const extractProyectoIdFromAsignacion = async () => {
  try {
    const { data } = await axios.get('/api/juez/asignaciones', {
      params: { page: 1, per_page: 100 }
    })
    
    const asignacion = data.data?.find(a => a.assignmentId === asignacionId.value)
    if (asignacion) {
      proyectoId.value = asignacion.projectId
    }
  } catch (err) {
    console.warn('Error al obtener proyecto_id:', err)
  }
}

/**
 * Cerrar sesión
 */
const logout = async () => {
  await auth.logout()
  router.push({ name: 'login' })
}

/**
 * Watcher reactivo de route.query para cargar datos cuando cambien los params
 */
watch(
  () => route.query,
  async (newQuery) => {
    if (validateParams()) {
      // Params válidos: cargar datos
      paramError.value = null
      await extractProyectoIdFromAsignacion()
      await loadData()
    } else {
      // Params inválidos: redirección silenciosa
      await tryLoadFirstPendingAssignment()
    }
  },
  { immediate: true }
)

/**
 * Timeout de seguridad: si tras 1s siguen faltando params, redirect a asignaciones
 */
let safetyTimeoutId = null

onMounted(() => {
  safetyTimeoutId = setTimeout(() => {
    if (!validateParams()) {
      console.warn('Safety timeout: redirigiendo a asignaciones tras 1s sin params válidos')
      router.replace('/juez/asignaciones')
    }
  }, 1000)
})

onBeforeUnmount(() => {
  // Cancelar timeout de seguridad
  if (safetyTimeoutId) {
    clearTimeout(safetyTimeoutId)
  }
  // Cancelar requests pendientes
  if (criteriosController) {
    criteriosController.abort()
  }
  if (calificacionesController) {
    calificacionesController.abort()
  }
  if (proyectoController) {
    proyectoController.abort()
  }
  // Limpiar debounce timer
  if (draftDebounceTimer) {
    clearTimeout(draftDebounceTimer)
  }
})
</script>