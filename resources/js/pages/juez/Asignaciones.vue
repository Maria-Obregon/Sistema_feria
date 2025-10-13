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
        <h2 class="text-2xl font-bold">Mis Asignaciones</h2>
        <p class="mt-1 text-sm text-gray-500">
          Proyectos asignados para evaluación
        </p>
      </div>

      <!-- Barra de filtros -->
      <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <select
            id="etapaId"
            v-model="filters.etapaId"
            @change="applyFilters"
            class="px-4 py-2 border rounded-lg"
            :disabled="loading"
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
            :disabled="loading"
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
            :disabled="loading"
          >
            <option value="">Todos los estados</option>
            <option value="pending">Pendientes</option>
            <option value="completed">Completados</option>
          </select>

          <button
            @click="clearFilters"
            :disabled="loading"
            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Limpiar filtros
          </button>
        </div>
      </div>

      <!-- Estado de carga -->
      <div v-if="loading" class="bg-white rounded-lg shadow p-8 text-center">
        <p class="text-gray-500">Cargando asignaciones...</p>
      </div>

      <!-- Estado de error -->
      <div v-else-if="error" class="bg-white rounded-lg shadow p-6">
        <div class="text-center text-red-600">
          <p class="font-medium">Error al cargar asignaciones</p>
          <p class="mt-1 text-sm">{{ error }}</p>
        </div>
      </div>

      <!-- Estado vacío -->
      <div v-else-if="assignments.length === 0" class="bg-white rounded-lg shadow p-8 text-center">
        <p class="text-gray-500">No tienes proyectos asignados con los filtros seleccionados.</p>
      </div>

      <!-- Tabla de asignaciones -->
      <div v-else class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Proyecto
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Etapa
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Tipo
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Estado
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Acciones
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="assignment in assignments" :key="assignment.assignmentId">
              <td class="px-6 py-4 whitespace-nowrap">
                <div>
                  <div class="text-sm font-medium text-gray-900">{{ assignment.projectName }}</div>
                  <div class="text-sm text-gray-500">ID: {{ assignment.projectId }}</div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ assignment.etapaNombre || `Etapa ${assignment.etapaId}` }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="text-sm text-gray-900 capitalize">
                  {{ assignment.tipoEval || '-' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span 
                  :class="assignment.estado === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
                  class="px-2 py-1 text-xs rounded-full"
                >
                  {{ assignment.estado === 'completed' ? 'Completado' : 'Pendiente' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button
                  @click="goToCalificar(assignment)"
                  class="text-indigo-600 hover:text-indigo-900"
                >
                  {{ assignment.estado === 'completed' ? 'Ver' : 'Calificar' }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Paginación -->
        <div v-if="pagination.total > pagination.per_page" class="bg-gray-50 px-4 py-3 sm:px-6">
          <div class="flex justify-between">
            <button
              @click="changePage(pagination.current_page - 1)"
              :disabled="pagination.current_page === 1"
              class="px-3 py-1 rounded border"
              :class="pagination.current_page === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100'"
            >
              Anterior
            </button>
            <span class="text-sm text-gray-700">
              Página {{ pagination.current_page }} de {{ pagination.last_page }}
            </span>
            <button
              @click="changePage(pagination.current_page + 1)"
              :disabled="pagination.current_page === pagination.last_page"
              class="px-3 py-1 rounded border"
              :class="pagination.current_page === pagination.last_page ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100'"
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
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import axios from 'axios'

// Router y auth
const router = useRouter()
const route = useRoute()
const auth = useAuthStore()

// Computed
const user = computed(() => auth.user)

// Estado reactivo
const loading = ref(false)
const error = ref(null)
const assignments = ref([])
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
  estado: route.query.estado || '',
  page: parseInt(route.query.page) || 1
})

// Debounce timer
let debounceTimer = null

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
  if (filters.value.estado && ['pending', 'completed'].includes(filters.value.estado)) {
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
  
  router.replace({ query })
}

/**
 * Obtiene las asignaciones del juez autenticado
 */
const fetchAsignaciones = async () => {
  loading.value = true
  error.value = null
  
  try {
    const params = buildQueryParams()
    const { data } = await axios.get('/api/juez/asignaciones', { params })
    
    // Asignar datos
    assignments.value = data.data || []
    
    // Actualizar paginación si existe
    if (data.meta) {
      pagination.value = {
        current_page: data.meta.current_page || 1,
        per_page: data.meta.per_page || 15,
        total: data.meta.total || 0,
        last_page: data.meta.last_page || 1
      }
    }
    
    // Actualizar URL
    updateUrl()
  } catch (err) {
    error.value = err.response?.data?.message || 'Error al cargar las asignaciones'
    console.error('Error fetching asignaciones:', err)
  } finally {
    loading.value = false
  }
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
  fetchAsignaciones()
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