<template>
  <div class="p-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
      <div class="flex items-center gap-3">
        <RouterLink
          :to="{ name: 'inst.dashboard' }"        
          class="inline-flex items-center gap-2 px-3 py-2 border rounded-lg text-gray-700 hover:bg-gray-50 transition-colors bg-white shadow-sm"
        >
          <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
          Volver
        </RouterLink>
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Gestión de Jueces</h1>
          <p class="text-gray-600">Asigna evaluadores a los proyectos de la feria</p>
        </div>
      </div>
      </div>

    <div class="bg-white rounded-lg shadow-sm border p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-1">
          <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
          <div class="relative">
            <input
              v-model="filtros.buscar"
              type="text"
              placeholder="Nombre, cédula o correo..."
              class="w-full pl-10 px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all"
              @input="debouncedBuscar"
            />
            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
          </div>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
      <div v-if="loading" class="p-12 text-center">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600 mx-auto mb-4"></div>
        <p class="text-gray-500">Cargando jueces...</p>
      </div>

      <div v-else-if="!jueces.length" class="p-12 text-center text-gray-500">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        <p class="text-lg font-medium">No se encontraron jueces</p>
        <p class="text-sm">Intenta ajustar los filtros.</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Juez</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cédula</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Área / Grado</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/3">Proyectos Asignados</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <template v-for="j in jueces" :key="j.id">
              <tr class="hover:bg-gray-50 transition-colors" :class="{'bg-indigo-50': expandedId === j.id}">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-700 font-bold text-sm">
                      {{ j.nombre.charAt(0) }}
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ j.nombre }}</div>
                      <div class="text-sm text-gray-500">{{ j.correo || 'Sin correo' }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-mono">
                  {{ j.cedula || '—' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex flex-col gap-1">
                    <span class="inline-flex w-fit items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                      {{ j.area?.nombre || 'General' }}
                    </span>
                    <span class="text-xs text-gray-500">{{ j.grado_academico || '—' }}</span>
                  </div>
                </td>
                
                <td class="px-6 py-4">
                  <div v-if="j.proyectos && j.proyectos.length > 0" class="flex flex-wrap gap-2">
                    <span 
                      v-for="(p, index) in j.proyectos" 
                      :key="p.id" 
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 border border-orange-200"
                    >
                      {{ p.titulo }}
                    </span>
                  </div>
                  <span v-else class="text-xs text-gray-400 italic">Sin asignaciones</span>
                </td>

                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex justify-end gap-3 items-center">
                    
                    <button 
                      @click="abrirAsignar(j)" 
                      class="text-indigo-600 hover:text-indigo-900 flex items-center gap-1"
                      title="Asignar Proyecto"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                      Asignar
                    </button>

                    <button 
                      @click="toggleDetails(j.id)"
                      class="flex items-center gap-1 text-xs font-semibold px-2 py-1 rounded border transition-colors"
                      :class="expandedId === j.id ? 'bg-indigo-100 text-indigo-700 border-indigo-200' : 'text-gray-600 hover:bg-gray-100 border-transparent'"
                    >
                      <svg class="w-4 h-4 transition-transform duration-200" 
                           :class="expandedId === j.id ? 'rotate-180' : ''" 
                           fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                      </svg>
                      {{ expandedId === j.id ? 'Ocultar' : 'Ver detalle' }}
                    </button>
                    </div>
                </td>
              </tr>

              <tr v-if="expandedId === j.id" class="bg-gray-50 border-b border-gray-200">
                <td colspan="5" class="px-6 py-4">
                  <div class="bg-white rounded-lg border p-4 shadow-sm max-w-6xl mx-auto">
                    <h4 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                      <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                      Proyectos a Evaluar
                    </h4>

                    <div v-if="j.proyectos && j.proyectos.length > 0" class="overflow-hidden border rounded-lg">
                      <table class="min-w-full text-sm divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                          <tr>
                            <th class="px-4 py-2 text-left font-medium text-gray-500 w-1/3">Proyecto</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-500">Institución</th> 
                            <th class="px-4 py-2 text-left font-medium text-gray-500">Categoría</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-500">Etapa / Tipo</th>
                            <th class="px-4 py-2 text-right font-medium text-gray-500">Acciones</th>
                          </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                          <tr v-for="p in j.proyectos" :key="p.asig_id">
                            <td class="px-4 py-2 font-medium text-gray-800">
                                <div class="" :title="p.titulo">{{ p.titulo }}</div>
                            </td>
                            <td class="px-4 py-2 text-gray-600">
                                <span class="bg-gray-100 text-gray-700 text-xs px-2 py-0.5 rounded border border-gray-200">
                                    {{ p.institucion?.nombre || '—' }}
                                </span>
                            </td> 
                            <td class="px-4 py-2 text-gray-600 text-xs">{{ p.categoria || '—' }}</td>
                            <td class="px-4 py-2">
                              <div class="flex gap-2">
                                <span class="bg-blue-50 text-blue-700 px-2 py-0.5 rounded text-xs border border-blue-100">{{ etapaLabel(p.etapa_id) }}</span>
                                <span class="px-2 py-0.5 rounded text-xs border" :class="badgeTipo(p.tipo_eval)">{{ tipoLabel(p.tipo_eval) }}</span>
                              </div>
                            </td>
                            <td class="px-4 py-2 text-right space-x-2">
                              <button @click="verEstudiantes(p)" class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 text-xs font-semibold hover:underline">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                Estudiantes
                              </button>
                              
                              <button @click="quitarAsignacion(p.asig_id, j.id)" class="inline-flex items-center gap-1 text-red-600 hover:text-red-800 text-xs font-semibold hover:underline">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                Desvincular
                              </button>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                    <div v-else class="text-center py-6 text-gray-500 italic">
                      Este juez no tiene proyectos asignados actualmente.
                    </div>
                  </div>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>

      <div v-if="jueces.length > 0" class="px-6 py-3 border-t bg-gray-50 flex items-center justify-between text-xs text-gray-500">
        <span>Mostrando {{ jueces.length }} resultados</span>
        <div class="flex gap-2">
          <button class="px-2 py-1 rounded bg-white border hover:bg-gray-50 disabled:opacity-50" :disabled="pagActual<=1" @click="prevPage">Anterior</button>
          <button class="px-2 py-1 rounded bg-white border hover:bg-gray-50 disabled:opacity-50" :disabled="pagActual>=totalPaginas" @click="nextPage">Siguiente</button>
        </div>
      </div>
    </div>

    <div v-if="showAsignar" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50">
      <div class="bg-white w-full max-w-3xl rounded-xl shadow-2xl border overflow-hidden flex flex-col max-h-[90vh]">
        <div class="px-6 py-4 border-b flex justify-between items-center bg-gray-50">
          <div>
            <h2 class="text-lg font-semibold text-gray-800">Asignar Proyecto</h2>
            <p class="text-sm text-gray-500">Juez: {{ juezSel?.nombre }}</p>
          </div>
          <button type="button" @click="cerrarAsignar" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>

        <div class="p-6 overflow-y-auto">
          <div class="grid md:grid-cols-2 gap-4 mb-6 bg-slate-50 p-4 rounded-lg border border-slate-100">
            <div>
              <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Etapa</label>
              <select v-model="fa.etapa_id" class="w-full px-3 py-2 bg-white border rounded text-sm focus:ring-2 focus:ring-indigo-500">
                <option :value="1">Institucional</option>
                <option :value="2">Circuital</option>
                <option :value="3">Regional</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Evaluación</label>
              <select v-model="fa.tipo_eval" class="w-full px-3 py-2 bg-white border rounded text-sm focus:ring-2 focus:ring-indigo-500">
                <option value="integral">Integral</option>
                <option value="escrito">Escrito</option>
                <option value="exposicion">Exposición</option>
              </select>
            </div>
          </div>

          <div class="mb-4">
            <div class="relative">
              <input v-model="buscarProyecto" @keyup.enter="loadProyectos"
                     class="w-full pl-10 px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500 outline-none" 
                     placeholder="Buscar proyecto por título para asignar..." />
              <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
          </div>

          <div class="border rounded-lg overflow-hidden">
            <table class="min-w-full text-sm divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-2 text-left font-medium text-gray-500">Título</th>
                  <th class="px-4 py-2 text-left font-medium text-gray-500">Área / Categoría</th>
                  <th class="px-4 py-2 text-right font-medium text-gray-500">Acción</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="p in proyectos" :key="p.id" class="hover:bg-gray-50">
                  <td class="px-4 py-3 text-gray-800 font-medium">{{ p.titulo }}</td>
                  <td class="px-4 py-3 text-gray-600">
                    <div class="text-xs">{{ p.area?.nombre || '—' }}</div>
                    <div class="text-xs text-gray-400">{{ p.categoria?.nombre || '—' }}</div>
                  </td>
                  <td class="px-4 py-3 text-right">
                    <button class="px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded text-xs font-medium shadow-sm transition-colors"
                            @click="asignar(p)">
                      Seleccionar
                    </button>
                  </td>
                </tr>
                <tr v-if="!cargandoProy && !proyectos.length">
                  <td colspan="3" class="px-4 py-8 text-center text-gray-500">No hay proyectos. Intenta buscar.</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="flex justify-between items-center text-xs text-gray-500 mt-2">
            <span>Página {{ pagProy }} de {{ totalPaginasProy }}</span>
            <div class="flex gap-2">
              <button class="px-2 py-1 border rounded hover:bg-gray-50 disabled:opacity-50" :disabled="pagProy<=1" @click="prevProy">Ant</button>
              <button class="px-2 py-1 border rounded hover:bg-gray-50 disabled:opacity-50" :disabled="pagProy>=totalPaginasProy" @click="nextProy">Sig</button>
            </div>
          </div>

          <div v-if="errorAsig" class="mt-4 p-3 bg-red-50 text-red-700 text-sm rounded">{{ errorAsig }}</div>
          <div v-if="okAsig" class="mt-4 p-3 bg-green-50 text-green-700 text-sm rounded">{{ okAsig }}</div>
        </div>
      </div>
    </div>

    <div v-if="showEstudiantes" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50">
      <div class="bg-white w-full max-w-lg rounded-xl shadow-2xl border overflow-hidden">
        <div class="px-6 py-4 border-b flex justify-between items-center bg-gray-50">
          <h2 class="text-lg font-semibold text-gray-800">Estudiantes del Proyecto</h2>
          <button @click="cerrarEstudiantes" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>
        <div class="p-6">
          <h3 class="text-md font-medium text-gray-900 mb-4 px-1">{{ proyectoSel?.titulo }}</h3>
          
          <div v-if="proyectoSel?.estudiantes && proyectoSel.estudiantes.length > 0" class="space-y-3">
            <div v-for="est in proyectoSel.estudiantes" :key="est.id" class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-100">
              <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-xs shrink-0">
                {{ est.nombre.charAt(0) }}
              </div>
              <div>
                <div class="text-sm font-medium text-gray-900">{{ est.nombre }} {{ est.apellidos }}</div>
                <div class="text-xs text-gray-500">Cédula: {{ est.cedula }}</div>
                <div class="text-xs text-gray-400">{{ est.nivel }} - {{ est.seccion }}</div>
              </div>
            </div>
          </div>
          <div v-else class="text-center text-gray-500 italic py-4 bg-gray-50 rounded-lg border border-dashed">
            No hay estudiantes asignados a este proyecto.
          </div>

          <div class="mt-6 flex justify-end">
            <button @click="cerrarEstudiantes" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg text-sm font-medium transition-colors">
              Cerrar
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue'
import { juecesApi, proyectosApi, adminApi } from '@/services/api'
import api from '@/services/api'

// ===== Utils =====
const etapaLabel = (id) => ({1:'Institucional',2:'Circuital',3:'Regional'}[id] || `Etapa ${id}`)
const tipoLabel  = (t) => ({escrito:'Escrito', exposicion:'Exposición', integral:'Integral'}[t] || t)
const badgeTipo  = (t) => ({
  escrito: 'bg-amber-100 text-amber-800 border-amber-200',
  exposicion: 'bg-purple-100 text-purple-800 border-purple-200',
  integral: 'bg-emerald-100 text-emerald-800 border-emerald-200',
}[t] || 'bg-gray-100 text-gray-800')

// ===== Estado Lista =====
const filtros = reactive({ buscar: '' })
const jueces = ref([])
const loading = ref(false)
const pagActual = ref(1)
const totalPaginas = ref(1)
const perPage = 10
const expandedId = ref(null)

// ===== Asignar =====
const showAsignar = ref(false)
const juezSel = ref(null)
const fa = ref({ etapa_id: 1, tipo_eval: 'integral' })
const buscarProyecto = ref('')
const proyectos = ref([])
const cargandoProy = ref(false)
const pagProy = ref(1)
const totalPaginasProy = ref(1)
const errorAsig = ref('')
const okAsig = ref('')

// ===== Ver Estudiantes =====
const showEstudiantes = ref(false)
const proyectoSel = ref(null)

// ------- Búsqueda -------
let timeoutSearch = null
const debouncedBuscar = () => {
  clearTimeout(timeoutSearch)
  timeoutSearch = setTimeout(() => {
    pagActual.value = 1
    loadJueces()
  }, 400)
}

const loadJueces = async () => {
  loading.value = true
  try {
    const { data } = await juecesApi.listar({
      buscar: filtros.buscar || undefined,
      page: pagActual.value,
      per_page: perPage,
      con_proyectos: true,
    })
    jueces.value = data?.data ?? data
    pagActual.value = data?.current_page ?? 1
    totalPaginas.value = data?.last_page ?? 1
  } finally {
    loading.value = false
  }
}

const prevPage = () => { if (pagActual.value > 1) { pagActual.value--; loadJueces() } }
const nextPage = () => { if (pagActual.value < totalPaginas.value) { pagActual.value++; loadJueces() } }

const toggleDetails = (id) => {
  if (expandedId.value === id) {
    expandedId.value = null
  } else {
    expandedId.value = id
  }
}

// ------- Asignar Proyecto -------
const abrirAsignar = async (j) => {
  juezSel.value = j
  fa.value = { etapa_id: 1, tipo_eval: 'integral' }
  buscarProyecto.value = ''
  pagProy.value = 1
  okAsig.value = ''
  errorAsig.value = ''
  await loadProyectos()
  showAsignar.value = true
}

const cerrarAsignar = () => { showAsignar.value = false }

const loadProyectos = async () => {
  cargandoProy.value = true
  try {
    const { data } = await proyectosApi.list({ 
      buscar: buscarProyecto.value || undefined,
      page: pagProy.value,
      per_page: 5,
      exclude_juez_id: juezSel.value?.id 
    })
    
    proyectos.value = data?.data ?? data
    pagProy.value = data?.current_page ?? 1
    totalPaginasProy.value = data?.last_page ?? 1
  } catch (e) {
    console.error("Error cargando proyectos", e)
  } finally {
    cargandoProy.value = false
  }
}

const prevProy = () => { if (pagProy.value > 1) { pagProy.value--; loadProyectos() } }
const nextProy = () => { if (pagProy.value < totalPaginasProy.value) { pagProy.value++; loadProyectos() } }

const asignar = async (p) => {
  if (!juezSel.value) return
  errorAsig.value = ''
  okAsig.value = ''
  
  try {
    await api.post(`/proyectos/${p.id}/asignar-jueces`, {
      etapa_id: Number(fa.value.etapa_id),
      tipo_eval: fa.value.tipo_eval,
      jueces: [{ id: juezSel.value.id }]
    })
    
    okAsig.value = 'Juez asignado correctamente'
    await loadJueces()
    // Mantener abierto el detalle
    if (expandedId.value !== juezSel.value.id) expandedId.value = juezSel.value.id
    
  } catch (e) {
    errorAsig.value = e?.response?.data?.message || e.message || 'Error al asignar'
  }
}

const quitarAsignacion = async (asigId, juezId) => {
  if (!confirm('¿Desvincular este proyecto del juez?')) return
  try {
    await api.delete(`/asignaciones-jueces/${asigId}`)
    await loadJueces()
    expandedId.value = juezId
  } catch (e) {
    alert("Error al desvincular: " + (e.response?.data?.message || e.message))
  }
}

// ------- Ver Estudiantes -------
const verEstudiantes = (proyecto) => {
  proyectoSel.value = proyecto
  showEstudiantes.value = true
}

const cerrarEstudiantes = () => {
  showEstudiantes.value = false
  proyectoSel.value = null
}

onMounted(loadJueces)
</script>