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
          <h2 class="text-2xl font-bold">{{ isHistory ? 'Mis Calificaciones' : 'Mis Asignaciones' }}</h2>
          <p class="mt-1 text-sm text-gray-500">
            {{ isHistory ? 'Calificaciones que he enviado' : 'Proyectos asignados para evaluación' }}
          </p>
        </div>
        <button
          @click="goBack"
          class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900 border border-gray-300 rounded-md hover:bg-gray-50"
        >
          ← Volver
        </button>
      </div>

      <!-- Barra de filtros -->
      <div class="bg-white rounded-lg shadow p-4 mb-6">
        <!-- Búsqueda y chips de rango (solo historial) -->
        <div v-if="isHistory" class="mb-4 space-y-3">
          <div>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Buscar por nombre de proyecto..."
              aria-label="Buscar proyectos por nombre"
              class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
              :disabled="loading || exporting"
            >
          </div>
          <div class="flex flex-wrap gap-2">
            <button
              @click="setRangeFilter('')"
              :class="getRangeButtonClass('')"
            >
              Todos
            </button>
            <button
              @click="setRangeFilter('hoy')"
              :class="getRangeButtonClass('hoy')"
            >
              Hoy
            </button>
            <button
              @click="setRangeFilter('7d')"
              :class="getRangeButtonClass('7d')"
            >
              Últimos 7 días
            </button>
            <button
              @click="setRangeFilter('30d')"
              :class="getRangeButtonClass('30d')"
            >
              Últimos 30 días
            </button>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <select
            id="etapaId"
            v-model="filters.etapaId"
            @change="applyFilters"
            class="px-4 py-2 border rounded-lg"
            :disabled="loading || exporting"
          >
            <option value="">Todas las etapas</option>
            <option value="1">Institucional</option>
            <option value="2">Circuital</option>
            <option value="3">Regional</option>
          </select>

          <select
            id="tipoEval"
            v-model="filters.tipoEval"
            @change="applyFilters"
            class="px-4 py-2 border rounded-lg"
            :disabled="loading || exporting"
          >
            <option value="">Todos los tipos</option>
            <option value="escrita">Escrita</option>
            <option value="oral">Oral</option>
          </select>

          <select
            id="estado"
            v-model="filters.estado"
            @change="applyFilters"
            class="px-4 py-2 border rounded-lg"
            :disabled="loading || exporting || isHistory"
          >
            <option value="">Todos los estados</option>
            <option value="pending">Pendientes</option>
            <option value="completed">Completadas</option>
          </select>

          <button
            @click="clearFilters"
            :disabled="loading || exporting"
            aria-label="Limpiar todos los filtros aplicados"
            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Limpiar filtros
          </button>
          
          <button
            v-if="isHistory"
            @click="exportToCsv"
            :disabled="loading || exporting"
            :aria-label="exporting ? 'Exportando calificaciones a CSV' : 'Exportar todas las calificaciones filtradas a CSV'"
            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ exporting ? 'Exportando…' : 'Exportar CSV' }}
          </button>
        </div>
      </div>
      
      <!-- Mensaje de error de exportación -->
      <div v-if="exportError" class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6" role="status" aria-live="polite">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm text-yellow-700">{{ exportError }}</p>
          </div>
          <div class="ml-auto pl-3">
            <button @click="exportError = null" class="text-yellow-700 hover:text-yellow-900">
              <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Estado de carga -->
      <div v-if="loading" class="bg-white rounded-lg shadow p-8 text-center">
        <p class="text-gray-500">Cargando asignaciones...</p>
      </div>

      <!-- Estado de error -->
      <div v-else-if="error" class="bg-white rounded-lg shadow p-6" role="status" aria-live="polite">
        <div class="text-center text-red-600">
          <p class="font-medium">Error al cargar asignaciones</p>
          <p class="mt-1 text-sm">{{ error }}</p>
        </div>
      </div>

      <!-- Estado vacío -->
      <div v-else-if="displayedAssignments.length === 0" class="bg-white rounded-lg shadow p-8 text-center" role="status" aria-live="polite">
        <p class="text-gray-500">
          {{ (searchQuery || rangeFilter) && isHistory 
            ? 'Sin resultados con los filtros aplicados' 
            : 'No tienes proyectos asignados con los filtros seleccionados.' }}
        </p>
      </div>

      <!-- Tabla de asignaciones -->
      <div v-else class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
          <thead class="bg-gray-50">
            <tr>
              <th 
                v-if="isHistory"
                @click="toggleSort('projectName')"
                :aria-sort="sortBy === 'projectName' ? (sortDir === 'asc' ? 'ascending' : 'descending') : 'none'"
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 select-none"
              >
                Proyecto {{ sortBy === 'projectName' ? (sortDir === 'asc' ? '▲' : '▼') : '' }}
              </th>
              <th v-else scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Proyecto
              </th>
              
              <th 
                v-if="isHistory"
                @click="toggleSort('etapaNombre')"
                :aria-sort="sortBy === 'etapaNombre' ? (sortDir === 'asc' ? 'ascending' : 'descending') : 'none'"
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 select-none"
              >
                Etapa {{ sortBy === 'etapaNombre' ? (sortDir === 'asc' ? '▲' : '▼') : '' }}
              </th>
              <th v-else scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Etapa
              </th>
              
              <th 
                v-if="isHistory"
                @click="toggleSort('tipoEval')"
                :aria-sort="sortBy === 'tipoEval' ? (sortDir === 'asc' ? 'ascending' : 'descending') : 'none'"
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 select-none"
              >
                Tipo {{ sortBy === 'tipoEval' ? (sortDir === 'asc' ? '▲' : '▼') : '' }}
              </th>
              <th v-else scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Tipo
              </th>
              
              <th 
                v-if="isHistory"
                @click="toggleSort('progress')"
                :aria-sort="sortBy === 'progress' ? (sortDir === 'asc' ? 'ascending' : 'descending') : 'none'"
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 select-none"
              >
                Progreso {{ sortBy === 'progress' ? (sortDir === 'asc' ? '▲' : '▼') : '' }}
              </th>
              <th v-else scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Estado
              </th>
              
              <th 
                v-if="isHistory"
                @click="toggleSort('lastGradedAt')"
                :aria-sort="sortBy === 'lastGradedAt' ? (sortDir === 'asc' ? 'ascending' : 'descending') : 'none'"
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 select-none"
              >
                Actualización {{ sortBy === 'lastGradedAt' ? (sortDir === 'asc' ? '▲' : '▼') : '' }}
              </th>
              <th v-if="!isHistory" scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Acciones
              </th>
              
              <th v-if="isHistory" scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Acciones
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr 
              v-for="assignment in displayedAssignments" 
              :key="assignment.assignmentId"
              @click="goToCalificar(assignment)"
              class="cursor-pointer hover:bg-gray-50 transition-colors"
            >
              <td scope="row" class="px-6 py-4 whitespace-nowrap">
                <div>
                  <div class="text-sm font-medium text-gray-900">{{ assignment.projectName }}</div>
                  <div class="text-sm text-gray-500">ID: {{ assignment.projectId }}</div>
                  <div v-if="isHistory" class="text-xs text-gray-500 mt-1 space-y-0.5">
                    <div>Actualizado: {{ formatTimeAgo(assignment.lastGradedAt) }}</div>
                    <div>Progreso: {{ assignment.gradesCount || 0 }}/{{ assignment.criteriaTotal || 0 }} criterios</div>
                  </div>
                  <div v-else-if="assignmentsProgress[assignment.assignmentId]" class="text-xs text-gray-400 mt-1">
                    {{ assignmentsProgress[assignment.assignmentId].calificados }}/{{ assignmentsProgress[assignment.assignmentId].total }} criterios
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 capitalize">
                {{ assignment.etapaNombre || `Etapa ${assignment.etapaId}` }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="text-sm text-gray-900 capitalize">
                  {{ assignment.tipoEval || '-' }}
                </span>
              </td>
              <td v-if="isHistory" class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ assignment.gradesCount || 0 }}/{{ assignment.criteriaTotal || 0 }}
              </td>
              <td v-else class="px-6 py-4 whitespace-nowrap">
                <span 
                  :class="getEstadoBadgeClass(assignment.estado)"
                  class="px-2 py-1 text-xs rounded-full"
                >
                  {{ assignment.estado === 'completed' ? 'Completada' : 'Pendiente' }}
                </span>
              </td>
              
              <td v-if="isHistory" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatTimeAgo(assignment.lastGradedAt) }}
              </td>
              
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button
                  @click.stop="goToCalificar(assignment)"
                  :aria-label="isHistory ? `Ver calificaciones del proyecto ${assignment.projectName || assignment.projectId}` : (assignment.estado === 'completed' ? `Ver calificaciones del proyecto ${assignment.projectName || assignment.projectId}` : `Calificar proyecto ${assignment.projectName || assignment.projectId}`)"
                  class="text-indigo-600 hover:text-indigo-900 font-medium"
                >
                  {{ isHistory ? 'Ver calificaciones' : (assignment.estado === 'completed' ? 'Ver calificaciones' : 'Continuar calificación') }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Paginación -->
        <div v-if="!isHistory && pagination.total > pagination.per_page" class="bg-gray-50 px-4 py-3 sm:px-6">
          <div class="flex justify-between">
            <button
              @click="changePage(pagination.current_page - 1)"
              :disabled="pagination.current_page === 1 || loading"
              class="px-3 py-1 rounded border"
              :class="getPaginationButtonClass(pagination.current_page === 1 || loading)"
            >
              Anterior
            </button>
            <span class="text-sm text-gray-700">
              Página {{ pagination.current_page }} de {{ pagination.last_page }}
            </span>
            <button
              @click="changePage(pagination.current_page + 1)"
              :disabled="pagination.current_page === pagination.last_page || loading"
              class="px-3 py-1 rounded border"
              :class="getPaginationButtonClass(pagination.current_page === pagination.last_page || loading)"
            >
              Siguiente
            </button>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
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

/**
 * Detecta si está en modo historial (Mis Calificaciones)
 */
const isHistory = computed(() => route.name === 'juez.misCalificaciones')

const loading = ref(false)
const error = ref(null)
const assignments = ref([])
const assignmentsProgress = ref({})
const pagination = ref({
  current_page: 1,
  per_page: 15,
  total: 0,
  last_page: 1
})

// Filtros
const filters = ref({
  etapaId: route.query.etapaId || '',
  tipoEval: route.query.tipoEval || '',
  estado: route.query.estado || (route.name === 'juez.misCalificaciones' ? 'completed' : ''),
  page: parseInt(route.query.page) || 1
})

// Búsqueda y filtro de rango (modo historial)
const searchQuery = ref(route.query.q || '')
const rangeFilter = ref(route.query.range || '')

// Ordenamiento (modo historial)
const validSortColumns = ['projectName', 'etapaNombre', 'tipoEval', 'progress', 'lastGradedAt']
const validSortDirs = ['asc', 'desc']

const sortBy = ref(
  validSortColumns.includes(route.query.sort) ? route.query.sort : 'lastGradedAt'
)
const sortDir = ref(
  validSortDirs.includes(route.query.dir) ? route.query.dir : 'desc'
)

// Cache de formatTimeAgo por render
const timeAgoCache = new Map()

/**
 * Asignaciones filtradas (sin ordenar)
 */
const filteredAssignments = computed(() => {
  if (!isHistory.value) {
    return assignments.value
  }

  let filtered = [...assignments.value]

  // Filtrar por búsqueda
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(a => 
      (a.projectName || '').toLowerCase().includes(query)
    )
  }

  // Filtrar por rango de fecha
  if (rangeFilter.value) {
    filtered = filtered.filter(a => {
      if (!a.lastGradedAt) return false
      const date = new Date(a.lastGradedAt)
      const now = new Date()
      
      if (rangeFilter.value === 'hoy') {
        const today = new Date(now.getFullYear(), now.getMonth(), now.getDate())
        return date >= today
      } else if (rangeFilter.value === '7d') {
        const weekAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000)
        return date >= weekAgo
      } else if (rangeFilter.value === '30d') {
        const monthAgo = new Date(now.getTime() - 30 * 24 * 60 * 60 * 1000)
        return date >= monthAgo
      }
      return true
    })
  }
  
  return filtered
})

/**
 * Asignaciones ordenadas (solo depende de filteredAssignments, sortBy, sortDir)
 */
const displayedAssignments = computed(() => {
  if (!isHistory.value) {
    return assignments.value
  }
  
  return applySorting(filteredAssignments.value)
})

/**
 * Helper para clases de botones de filtro de rango
 */
const getRangeButtonClass = (value) => [
  'px-3 py-1 text-sm rounded-full transition-colors',
  rangeFilter.value === value
    ? 'bg-indigo-600 text-white'
    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
]

/**
 * Helper para clases de badges de estado
 */
const getEstadoBadgeClass = (estado) => 
  estado === 'completed' 
    ? 'bg-green-100 text-green-800' 
    : 'bg-yellow-100 text-yellow-800'

/**
 * Helper para clases de botones de paginación
 */
const getPaginationButtonClass = (disabled) => 
  disabled 
    ? 'opacity-50 cursor-not-allowed' 
    : 'hover:bg-gray-100'

// Debounce timer
let debounceTimer = null
let searchDebounceTimer = null

// AbortController para cancelar requests
let abortController = null
let exportAbortController = null

// Estado de exportación
const exporting = ref(false)
const exportError = ref(null)

/**
 * Construye los query params válidos para la API
 * @returns {Object} Query params filtrados
 */
const buildQueryParams = () => {
  const params = {}
  
  // Validar y agregar etapaId
  if (filters.value.etapaId && !isNaN(parseInt(filters.value.etapaId))) {
    params.etapa_id = parseInt(filters.value.etapaId)
  }
  
  // Validar y agregar tipoEval
  if (filters.value.tipoEval && ['escrita', 'oral'].includes(filters.value.tipoEval)) {
    params.tipo_eval = filters.value.tipoEval
  }
  
  // Validar y agregar estado
  // Si está en modo historial, siempre forzar 'completed'
  if (isHistory.value) {
    params.estado = 'completed'
  } else if (filters.value.estado && ['pending', 'completed'].includes(filters.value.estado)) {
    params.estado = filters.value.estado
  }
  
  // Agregar página
  if (filters.value.page > 0) {
    params.page = filters.value.page
  }
  
  return params
}

/**
 * Actualiza la URL con los filtros actuales
 */
const updateUrl = () => {
  const query = {}
  if (filters.value.etapaId) query.etapaId = filters.value.etapaId
  if (filters.value.tipoEval) query.tipoEval = filters.value.tipoEval
  if (filters.value.estado) query.estado = filters.value.estado
  if (filters.value.page > 1) query.page = filters.value.page
  
  // Agregar búsqueda, rango y ordenamiento en modo historial
  if (isHistory.value) {
    if (searchQuery.value) query.q = searchQuery.value
    if (rangeFilter.value) query.range = rangeFilter.value
    if (sortBy.value !== 'lastGradedAt' || sortDir.value !== 'desc') {
      query.sort = sortBy.value
      query.dir = sortDir.value
    }
  }
  
  router.replace({ query })
}

const fetchAsignaciones = async () => {
  // Cancelar request anterior
  if (abortController) {
    abortController.abort()
  }
  
  abortController = new AbortController()
  loading.value = true
  error.value = null
  
  try {
    const params = buildQueryParams()
    const { data } = await axios.get('/api/juez/asignaciones', { 
      params,
      signal: abortController.signal 
    })
    
    assignments.value = data.data || []
    
    if (data.meta) {
      pagination.value = {
        current_page: data.meta.current_page || 1,
        per_page: data.meta.per_page || 15,
        total: data.meta.total || 0,
        last_page: data.meta.last_page || 1
      }
    }
    
    updateUrl()
    await calculateProgress()
  } catch (err) {
    // Ignorar errores de cancelación
    if (axios.isCancel(err) || err.code === 'ERR_CANCELED') {
      return
    }
    error.value = err.response?.data?.message || 'Error al cargar las asignaciones'
    console.error('Error fetching asignaciones:', err)
  } finally {
    loading.value = false
  }
}

const calculateProgress = async () => {
  const progressMap = {}
  
  for (const assignment of assignments.value) {
    try {
      const criterios = await getCriterios(assignment.tipoEval)
      const totalCriterios = criterios.length
      const calificados = assignment.gradesCount || 0
      
      progressMap[assignment.assignmentId] = {
        calificados,
        total: totalCriterios
      }
    } catch (err) {
      progressMap[assignment.assignmentId] = { calificados: 0, total: 0 }
    }
  }
  
  assignmentsProgress.value = progressMap
}

/**
 * Aplica los filtros con debounce
 */
const applyFilters = () => {
  // Resetear a página 1 al cambiar filtros
  filters.value.page = 1
  
  // Limpiar timer anterior
  if (debounceTimer) {
    clearTimeout(debounceTimer)
  }
  
  // Aplicar debounce de 300ms
  debounceTimer = setTimeout(() => {
    fetchAsignaciones()
  }, 300)
}

/**
 * Limpia todos los filtros
 */
const clearFilters = () => {
  filters.value = {
    etapaId: '',
    tipoEval: '',
    estado: '',
    page: 1
  }
  searchQuery.value = ''
  rangeFilter.value = ''
  sortBy.value = 'lastGradedAt'
  sortDir.value = 'desc'
  fetchAsignaciones()
}

/**
 * Establece el filtro de rango
 */
const setRangeFilter = (range) => {
  rangeFilter.value = range
  updateUrl()
}

/**
 * Cambia de página
 * @param {Number} page - Número de página
 */
const changePage = (page) => {
  if (page < 1 || page > pagination.value.last_page || loading.value) return
  filters.value.page = page
  fetchAsignaciones()
}

/**
 * Navega al formulario de calificación
 * @param {Object} assignment - Asignación seleccionada
 */
const goToCalificar = (assignment) => {
  router.push({
    name: 'juez.calificaciones',
    query: {
      asignacionId: assignment.assignmentId,
      tipo_eval: assignment.tipoEval
    }
  })
}

/**
 * Watch con debounce para searchQuery
 */
watch(searchQuery, () => {
  if (searchDebounceTimer) {
    clearTimeout(searchDebounceTimer)
  }
  searchDebounceTimer = setTimeout(() => {
    updateUrl()
  }, 300)
})

/**
 * Watch para rangeFilter
 */
watch(rangeFilter, () => {
  updateUrl()
})

/**
 * Toggle ordenamiento por columna
 */
const toggleSort = (column) => {
  if (sortBy.value === column) {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortBy.value = column
    sortDir.value = 'asc'
  }
  updateUrl()
}

/**
 * Aplica ordenamiento al array filtrado
 */
const applySorting = (items) => {
  if (!sortBy.value) return items
  
  const sorted = [...items].sort((a, b) => {
    let compareResult = 0
    
    switch (sortBy.value) {
      case 'projectName':
        compareResult = (a.projectName || '').localeCompare(b.projectName || '')
        break
      case 'etapaNombre':
        compareResult = (a.etapaNombre || '').localeCompare(b.etapaNombre || '')
        break
      case 'tipoEval':
        compareResult = (a.tipoEval || '').localeCompare(b.tipoEval || '')
        break
      case 'progress': {
        const progressA = (a.criteriaTotal || 0) > 0 ? (a.gradesCount || 0) / (a.criteriaTotal || 1) : 0
        const progressB = (b.criteriaTotal || 0) > 0 ? (b.gradesCount || 0) / (b.criteriaTotal || 1) : 0
        compareResult = progressA - progressB
        break
      }
      case 'lastGradedAt': {
        const dateA = a.lastGradedAt ? new Date(a.lastGradedAt).getTime() : 0
        const dateB = b.lastGradedAt ? new Date(b.lastGradedAt).getTime() : 0
        compareResult = dateA - dateB
        break
      }
      default:
        compareResult = 0
    }
    
    return sortDir.value === 'asc' ? compareResult : -compareResult
  })
  
  return sorted
}

/**
 * Exporta todas las calificaciones a CSV
 */
const exportToCsv = async () => {
  exporting.value = true
  exportError.value = null
  
  // Crear nuevo AbortController para la exportación
  exportAbortController = new AbortController()
  
  try {
    const allAssignments = []
    let currentPage = 1
    let hasMorePages = true
    
    // Obtener parámetros de filtro actuales (sin page)
    const params = buildQueryParams()
    delete params.page
    
    // Paginar y recolectar todos los resultados
    while (hasMorePages) {
      const { data } = await axios.get('/api/juez/asignaciones', {
        params: { ...params, page: currentPage },
        signal: exportAbortController.signal
      })
      
      const items = data.data || []
      allAssignments.push(...items)
      
      // Verificar si hay más páginas
      if (data.meta && data.meta.current_page < data.meta.last_page) {
        currentPage++
      } else {
        hasMorePages = false
      }
    }
    
    // Filtrar en frontend (búsqueda, rango) y ordenar
    let filteredAssignments = [...allAssignments]
    
    // Aplicar búsqueda
    if (searchQuery.value) {
      const query = searchQuery.value.toLowerCase()
      filteredAssignments = filteredAssignments.filter(a => 
        (a.projectName || '').toLowerCase().includes(query)
      )
    }
    
    // Aplicar rango de fecha
    if (rangeFilter.value) {
      filteredAssignments = filteredAssignments.filter(a => {
        if (!a.lastGradedAt) return false
        const date = new Date(a.lastGradedAt)
        const now = new Date()
        
        if (rangeFilter.value === 'hoy') {
          const today = new Date(now.getFullYear(), now.getMonth(), now.getDate())
          return date >= today
        } else if (rangeFilter.value === '7d') {
          const weekAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000)
          return date >= weekAgo
        } else if (rangeFilter.value === '30d') {
          const monthAgo = new Date(now.getTime() - 30 * 24 * 60 * 60 * 1000)
          return date >= monthAgo
        }
        return true
      })
    }
    
    // Aplicar ordenamiento
    const sortedAssignments = applySorting(filteredAssignments)
    
    // Generar CSV
    const csvContent = generateCsv(sortedAssignments)
    
    // Descargar archivo
    const timestamp = formatTimestamp(new Date())
    const filename = `mis-calificaciones-${timestamp}.csv`
    downloadBlobCsv(filename, csvContent)
    
  } catch (err) {
    // Ignorar errores de cancelación
    if (axios.isCancel(err) || err.code === 'ERR_CANCELED') {
      return
    }
    exportError.value = 'Error al exportar las calificaciones. Por favor intenta nuevamente.'
    console.error('Error exporting CSV:', err)
  } finally {
    exporting.value = false
    exportAbortController = null
  }
}

/**
 * Genera contenido CSV desde array de asignaciones
 */
const generateCsv = (assignments) => {
  const headers = ['projectId', 'projectName', 'etapaNombre', 'tipoEval', 'gradesCount', 'criteriaTotal', 'lastGradedAt', 'updatedHuman']
  
  let csv = buildCsvRow(headers) + '\n'
  
  for (const assignment of assignments) {
    const row = [
      assignment.projectId || '',
      assignment.projectName || '',
      assignment.etapaNombre || '',
      assignment.tipoEval || '',
      assignment.gradesCount || 0,
      assignment.criteriaTotal || 0,
      assignment.lastGradedAt || '',
      formatTimeAgo(assignment.lastGradedAt)
    ]
    csv += buildCsvRow(row) + '\n'
  }
  
  return csv
}

/**
 * Construye una fila CSV escapando valores
 */
const buildCsvRow = (values) => {
  return values.map(value => {
    const stringValue = String(value)
    // Escapar comillas duplicándolas y envolver en comillas si contiene coma, comilla o salto de línea
    if (stringValue.includes(',') || stringValue.includes('"') || stringValue.includes('\n')) {
      return `"${stringValue.replace(/"/g, '""')}"`
    }
    return stringValue
  }).join(',')
}

/**
 * Descarga un blob CSV con BOM UTF-8
 */
const downloadBlobCsv = (filename, content) => {
  // UTF-8 BOM
  const BOM = '\uFEFF'
  const blob = new Blob([BOM + content], { type: 'text/csv;charset=utf-8;' })
  
  const link = document.createElement('a')
  const url = URL.createObjectURL(blob)
  
  link.setAttribute('href', url)
  link.setAttribute('download', filename)
  link.style.visibility = 'hidden'
  
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  
  URL.revokeObjectURL(url)
}

/**
 * Formatea timestamp para nombre de archivo
 */
const formatTimestamp = (date) => {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  const hours = String(date.getHours()).padStart(2, '0')
  const minutes = String(date.getMinutes()).padStart(2, '0')
  
  return `${year}${month}${day}-${hours}${minutes}`
}

/**
 * Formatea tiempo relativo desde una fecha ISO8601
 * @param {string|null} isoDate - Fecha en formato ISO8601
 * @returns {string} Tiempo relativo ("hace 3 min", "hace 2 h", etc.)
 */
const formatTimeAgo = (isoDate) => {
  if (!isoDate) return '—'
  
  // Revisar cache primero
  if (timeAgoCache.has(isoDate)) {
    return timeAgoCache.get(isoDate)
  }
  
  try {
    const now = Date.now()
    const date = new Date(isoDate).getTime()
    const diffMs = now - date
    
    if (diffMs < 0) {
      const result = 'hace un momento'
      timeAgoCache.set(isoDate, result)
      return result
    }
    
    const minutes = Math.floor(diffMs / (1000 * 60))
    const hours = Math.floor(diffMs / (1000 * 60 * 60))
    const days = Math.floor(diffMs / (1000 * 60 * 60 * 24))
    const months = Math.floor(diffMs / (1000 * 60 * 60 * 24 * 30))
    
    let result
    if (minutes < 1) result = 'hace un momento'
    else if (minutes < 60) result = `hace ${minutes} min`
    else if (hours < 24) result = `hace ${hours} h`
    else if (days < 30) result = `hace ${days} d`
    else result = `hace ${months} mes${months > 1 ? 'es' : ''}`
    
    timeAgoCache.set(isoDate, result)
    return result
  } catch (err) {
    return '—'
  }
}

/**
 * Volver a la página anterior
 */
const goBack = () => {
  router.push({ name: 'juez.dashboard' })
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
  fetchAsignaciones()
})

onBeforeUnmount(() => {
  if (debounceTimer) {
    clearTimeout(debounceTimer)
  }
  if (searchDebounceTimer) {
    clearTimeout(searchDebounceTimer)
  }
  if (abortController) {
    abortController.abort()
  }
  if (exportAbortController) {
    exportAbortController.abort()
  }
  // Limpiar cache de time ago
  timeAgoCache.clear()
})

// Watch route query para sincronizar con navegación del navegador
watch(() => route.query, (newQuery) => {
  if (route.name === 'juez.asignaciones') {
    filters.value = {
      etapaId: newQuery.etapaId || '',
      tipoEval: newQuery.tipoEval || '',
      estado: newQuery.estado || '',
      page: parseInt(newQuery.page) || 1
    }
    fetchAsignaciones()
  }
}, { deep: true })
</script>