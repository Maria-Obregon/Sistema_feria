<template>
  <div class="max-w-6xl mx-auto py-8 px-4">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Mis Asignaciones</h1>
        <p class="text-gray-500 text-sm mt-1">Gestiona tus evaluaciones asignadas</p>
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

    <!-- Filtros y Acciones -->
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
        
        <button @click="cargar"
                class="px-4 py-2 bg-gray-50 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-100 transition-colors shadow-sm text-sm font-medium flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
          Actualizar
        </button>
      </div>
    </div>

    <div v-if="loading" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>

    <div v-else-if="proyectosFiltrados.length === 0" class="text-center py-12 bg-white rounded-lg border border-dashed border-gray-300">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
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
                <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center bg-blue-100 text-blue-600 rounded-full font-bold text-sm">
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
                <span :class="getStatusClass(p.estado)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                    {{ p.estado }}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex items-center gap-2">
                    <!-- Botón Informe Escrito -->
                    <button v-if="p.escrito"
                            @click="irACalificar(p.escrito)"
                            :class="getButtonClass(p.escrito.finalizada)"
                            class="flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-medium transition-colors border">
                        <span v-if="p.escrito.finalizada">📝 Informe (Listo)</span>
                        <span v-else>📝 Informe</span>
                    </button>

                    <!-- Botón Exposición -->
                    <button v-if="p.exposicion"
                            @click="irACalificar(p.exposicion)"
                            :class="getButtonClass(p.exposicion.finalizada)"
                            class="flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-medium transition-colors border">
                        <span v-if="p.exposicion.finalizada">🗣️ Expo (Listo)</span>
                        <span v-else>🗣️ Expo</span>
                    </button>

                    <!-- Botón Acta (Solo si completado) -->
                     <button v-if="p.estado === 'Completado ✅'"
                            class="text-gray-400 hover:text-gray-600 ml-2" title="Descargar Acta (Próximamente)">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
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
import { listarMisAsignaciones } from '@/services/asignaciones'

const router = useRouter()
const { mostrarToast } = useToast()

const busqueda = ref('')
const filtroEtapa = ref('')
const asignaciones = ref([])
const loading = ref(false)

const cargar = async () => {
  loading.value = true
  try {
    const { data } = await listarMisAsignaciones()
    asignaciones.value = Array.isArray(data?.data) ? data.data : []
    if (!asignaciones.value.length) {
      mostrarToast('No tienes asignaciones todavía', 'info')
    }
  } catch (e) {
    mostrarToast('Error cargando asignaciones', 'error')
  } finally {
    loading.value = false
  }
}

// Lógica de Agrupación
const proyectosAgrupados = computed(() => {
    const grupos = {}

    asignaciones.value.forEach(a => {
        if (!grupos[a.proyecto_id]) {
            grupos[a.proyecto_id] = {
                proyecto_id: a.proyecto_id,
                titulo: a.proyecto?.titulo || 'Sin Título',
                categoria: a.proyecto?.categoria || 'Sin Categoría',
                etapa_id: a.etapa_id,
                escrito: null,
                exposicion: null,
                total_asignadas: 0,
                total_finalizadas: 0
            }
        }
        
        // Asignar según tipo
        if (a.tipo_eval === 'escrito') {
            grupos[a.proyecto_id].escrito = a
        } else if (a.tipo_eval === 'exposicion') {
            grupos[a.proyecto_id].exposicion = a
        }

        grupos[a.proyecto_id].total_asignadas++
        if (a.finalizada) {
            grupos[a.proyecto_id].total_finalizadas++
        }
    })

    return Object.values(grupos).map(p => {
        // Calcular Estado
        let estado = 'Pendiente'
        if (p.total_finalizadas === p.total_asignadas && p.total_asignadas > 0) {
            estado = 'Completado ✅'
        } else if (p.total_finalizadas > 0) {
            estado = 'En Progreso'
        }
        return { ...p, estado }
    })
})

// Lógica de Filtrado en Tiempo Real
const proyectosFiltrados = computed(() => {
    return proyectosAgrupados.value.filter(p => {
        const matchTexto = 
            p.titulo.toLowerCase().includes(busqueda.value.toLowerCase()) ||
            p.proyecto_id.toString().includes(busqueda.value)
        
        const matchEtapa = filtroEtapa.value ? p.etapa_id.toString() === filtroEtapa.value : true

        return matchTexto && matchEtapa
    })
})


const getStatusClass = (estado) => {
    if (estado === 'Completado ✅') return 'bg-green-100 text-green-800'
    if (estado === 'En Progreso') return 'bg-yellow-100 text-yellow-800'
    return 'bg-gray-100 text-gray-600'
}

const getButtonClass = (finalizada) => {
    if (finalizada) {
        return 'bg-gray-100 text-gray-500 border-gray-200 hover:bg-gray-200'
    }
    return 'bg-white text-blue-600 border-blue-200 hover:bg-blue-50 hover:border-blue-300'
}

const irACalificar = (asignacion) => {
  router.push({ 
    name: 'juez.calificaciones', 
    query: { 
      proyectoId: asignacion.proyecto_id, 
      etapaId: asignacion.etapa_id,
      tipo_eval: asignacion.tipo_eval,
      from: 'asignaciones' // Origen
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


