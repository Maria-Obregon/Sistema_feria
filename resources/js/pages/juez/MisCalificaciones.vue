<template>
  <div class="max-w-6xl mx-auto py-8 px-4">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Mis Calificaciones</h1>
        <p class="text-gray-500 text-sm mt-1">Historial de evaluaciones finalizadas</p>
      </div>
      <router-link
        :to="{ name: 'juez.dashboard' }"
        class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 flex items-center gap-2 shadow-sm transition-colors"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Volver al menú
      </router-link>
    </div>

    <!-- Filtros -->
    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 mb-6 flex flex-col sm:flex-row gap-4 justify-between">
      
      <!-- Buscador -->
      <div class="relative flex-grow max-w-md">
        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </span>
        <input v-model="busqueda" type="text" placeholder="Buscar por nombre o ID..."
            class="pl-10 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm py-2" />
      </div>

      <!-- Filtros -->
      <div class="flex items-center gap-2">
        <select v-model="filtroEtapa" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-lg shadow-sm">
            <option value="">Todas las etapas</option>
            <option value="1">Institucional</option>
            <option value="2">Circuital</option>
            <option value="3">Regional</option>
            <option value="4">Nacional</option>
        </select>
      </div>
    </div>

    <div v-if="loading" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>

    <div v-else-if="proyectosFiltrados.length === 0" class="text-center py-12 bg-white rounded-lg border border-dashed border-gray-300">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No se encontraron resultados</h3>
        <p class="mt-1 text-sm text-gray-500">Intenta ajustar los filtros de búsqueda.</p>
    </div>

    <div v-else class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proyecto</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Etapa</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="p in proyectosFiltrados" :key="p.proyecto_id" class="hover:bg-gray-50 transition-colors">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center bg-green-100 text-green-600 rounded-full font-bold text-sm">
                    {{ p.proyecto_id }}
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-900">{{ p.titulo }}</div>
                  <div class="text-sm text-gray-500">{{ p.categoria }}</div>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                {{ getEtapaNombre(p.etapa_id) }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                    Finalizado
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex items-center gap-2">
                    <!-- Botón Informe Escrito -->
                    <button v-if="p.escrito"
                            @click="verCalificacion(p.escrito)"
                            class="flex items-center gap-1 px-3 py-1.5 bg-white text-blue-600 border border-blue-200 rounded-md text-xs font-medium hover:bg-blue-50 hover:border-blue-300 transition-colors">
                        📝 Ver Informe
                    </button>

                    <!-- Botón Exposición -->
                    <button v-if="p.exposicion"
                            @click="verCalificacion(p.exposicion)"
                            class="flex items-center gap-1 px-3 py-1.5 bg-white text-blue-600 border border-blue-200 rounded-md text-xs font-medium hover:bg-blue-50 hover:border-blue-300 transition-colors">
                        🗣️ Ver Expo
                    </button>
                </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from '@/composables/useToast'
import api from '@/services/api' // Usamos api directo o creamos servicio si preferimos

const router = useRouter()
const { mostrarToast } = useToast()

const busqueda = ref('')
const filtroEtapa = ref('')
const calificaciones = ref([])
const loading = ref(false)

const cargar = async () => {
  loading.value = true
  try {
    const { data } = await api.get('/juez/asignaciones/finalizadas')
    calificaciones.value = Array.isArray(data?.data) ? data.data : []
  } catch (e) {
    mostrarToast('Error cargando historial', 'error')
  } finally {
    loading.value = false
  }
}

// Lógica de Agrupación (Similar a Asignaciones.vue)
const proyectosAgrupados = computed(() => {
    const grupos = {}

    calificaciones.value.forEach(c => {
        if (!grupos[c.proyecto_id]) {
            grupos[c.proyecto_id] = {
                proyecto_id: c.proyecto_id,
                titulo: c.proyecto?.titulo || 'Sin Título',
                categoria: c.proyecto?.categoria || 'Sin Categoría',
                etapa_id: c.etapa_id,
                escrito: null,
                exposicion: null
            }
        }
        
        if (c.tipo_eval === 'escrito') {
            grupos[c.proyecto_id].escrito = c
        } else if (c.tipo_eval === 'exposicion') {
            grupos[c.proyecto_id].exposicion = c
        }
    })

    return Object.values(grupos)
})

const proyectosFiltrados = computed(() => {
    return proyectosAgrupados.value.filter(p => {
        const matchTexto = 
            p.titulo.toLowerCase().includes(busqueda.value.toLowerCase()) ||
            p.proyecto_id.toString().includes(busqueda.value)
        
        const matchEtapa = filtroEtapa.value ? p.etapa_id.toString() === filtroEtapa.value : true

        return matchTexto && matchEtapa
    })
})

const verCalificacion = (calif) => {
  // Reutilizamos la vista de calificar, que debería permitir editar si se reabre o solo ver
  // OJO: Si está finalizada, el backend/frontend de Calificaciones.vue debe manejar el modo "solo lectura" o "edición restringida"
  // Por ahora enviamos al mismo lugar.
    router.push({
        name: 'juez.calificaciones',
        query: {
            proyectoId: calif.proyecto_id,
            etapaId: calif.etapa_id,
            tipo_eval: calif.tipo_eval,
            // finalizada: 1, // NO forzar finalizada, dejar que el backend decida si está abierta o cerrada
            from: 'mis-calificaciones' // Origen
        }
    })
}

const getEtapaNombre = (id) => {
  const map = {
    1: 'Institucional',
    2: 'Circuital',
    3: 'Regional',
    4: 'Nacional'
  }
  return map[id] || `Etapa ${id}`
}

onMounted(cargar)
</script>
