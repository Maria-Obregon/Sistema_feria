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
          <h1 class="text-2xl font-bold text-gray-900">Gestión de Instituciones</h1>
          <p class="text-gray-600">Administra los centros educativos, códigos y ubicaciones</p>
        </div>
      </div>

      <button
        @click="abrirCrear"
        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 shadow-sm transition-colors"
      >
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Nueva Institución
      </button>
    </div>

    <div class="bg-white rounded-lg shadow-sm border p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-1">
          <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
          <div class="relative">
            <input
              v-model="filtros.buscar"
              type="text"
              placeholder="Nombre o código presupuestario..."
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
        <p class="text-gray-500">Cargando instituciones...</p>
      </div>

      <div v-else-if="!instituciones.length" class="p-12 text-center text-gray-500">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
        </svg>
        <p class="text-lg font-medium">No se encontraron instituciones</p>
        <p class="text-sm">Intenta ajustar los filtros o crea una nueva.</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Institución</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ubicación</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Detalles</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            
            <template v-for="i in instituciones" :key="i.id">
              <tr class="hover:bg-gray-50 transition-colors" :class="{'bg-indigo-50': expandedId === i.id}">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="h-10 w-10 rounded-lg bg-yellow-100 flex items-center justify-center text-yellow-700">
                      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ i.nombre }}</div>
                      <div class="text-sm text-gray-500">{{ i.email || 'Sin correo registrado' }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-mono">
                  {{ i.codigo_presupuestario || i.codigo || '—' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ i.circuito?.regional?.nombre || '—' }}</div>
                  <div class="text-xs text-gray-500">{{ i.circuito?.nombre || '—' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex flex-col gap-1">
                    <span class="inline-flex w-fit items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                      :class="i.tipo === 'publica' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800'">
                      {{ i.tipo || '—' }}
                    </span>
                    <span class="text-xs text-gray-500">{{ i.modalidad }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex justify-end gap-3 items-center">
                    
                    <button 
                      @click="toggleDetails(i.id)"
                      class="flex items-center gap-1 text-xs font-semibold px-2 py-1 rounded border transition-colors"
                      :class="expandedId === i.id ? 'bg-indigo-100 text-indigo-700 border-indigo-200' : 'text-gray-600 hover:bg-gray-100 border-transparent'"
                    >
                      <svg class="w-4 h-4 transition-transform duration-200" 
                           :class="expandedId === i.id ? 'rotate-180' : ''" 
                           fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                      </svg>
                      {{ expandedId === i.id ? 'Ocultar' : 'Ver datos' }}
                    </button>

                    <button @click="abrirEditar(i)" class="text-gray-600 hover:text-gray-900" title="Editar">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    </button>
                    <button @click="eliminar(i)" class="text-red-600 hover:text-red-900" title="Eliminar">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="expandedId === i.id" class="bg-gray-50 border-b border-gray-200">
                <td colspan="5" class="px-6 py-4">
                  
                  <div v-if="loadingDetails" class="flex justify-center py-4">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-600"></div>
                  </div>

                  <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <div class="bg-white rounded-lg border p-4 shadow-sm">
                      <h4 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                        <span class="bg-blue-100 text-blue-700 p-1 rounded">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </span>
                        Estudiantes Registrados ({{ expandedData.estudiantes.length }})
                      </h4>
                      
                      <div v-if="expandedData.estudiantes.length > 0" class="max-h-60 overflow-y-auto space-y-2 pr-2">
                        <div v-for="est in expandedData.estudiantes" :key="est.id" class="flex justify-between items-center text-sm border-b border-gray-100 pb-2 last:border-0 last:pb-0">
                          <div>
                            <div class="font-medium text-gray-700">{{ est.nombre }} {{ est.apellidos }}</div>
                            <div class="text-xs text-gray-500">Cédula: {{ est.cedula }}</div>
                          </div>
                          <span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-600">{{ est.nivel }}</span>
                        </div>
                      </div>
                      <div v-else class="text-sm text-gray-400 italic text-center py-2">
                        No hay estudiantes registrados.
                      </div>
                    </div>

                    <div class="bg-white rounded-lg border p-4 shadow-sm">
                      <h4 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                        <span class="bg-green-100 text-green-700 p-1 rounded">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </span>
                        Proyectos ({{ expandedData.proyectos.length }})
                      </h4>

                      <div v-if="expandedData.proyectos.length > 0" class="max-h-60 overflow-y-auto space-y-2 pr-2">
                        <div v-for="proy in expandedData.proyectos" :key="proy.id" class="text-sm border-b border-gray-100 pb-2 last:border-0 last:pb-0">
                          <div class="font-medium text-gray-700 truncate" :title="proy.titulo">{{ proy.titulo }}</div>
                          <div class="text-xs text-gray-500 mt-0.5">
                            Categoría: {{ proy.categoria?.nombre || 'General' }}
                          </div>
                        </div>
                      </div>
                      <div v-else class="text-sm text-gray-400 italic text-center py-2">
                        No hay proyectos registrados.
                      </div>
                    </div>

                  </div>
                </td>
              </tr>
            </template>

          </tbody>
        </table>
      </div>
      
      <div v-if="instituciones.length > 0" class="px-6 py-3 border-t bg-gray-50 text-xs text-gray-500 text-right">
        Mostrando {{ instituciones.length }} registros
      </div>
    </div>

    <div v-if="showNew" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50">
      <div class="bg-white w-full max-w-3xl rounded-xl shadow-2xl border overflow-hidden">
        <div class="px-6 py-4 border-b flex justify-between items-center bg-gray-50">
          <h2 class="text-lg font-semibold text-gray-800">
            {{ editando ? 'Editar Institución' : 'Registrar Institución' }}
          </h2>
          <button @click="closeNew" class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
          </button>
        </div>

        <div class="p-6 max-h-[80vh] overflow-y-auto">
          <form @submit.prevent="save" class="space-y-6">
            
            <div class="grid md:grid-cols-3 gap-5">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Dirección Regional *</label>
                <select v-model="f.regional_id" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500 outline-none" required>
                  <option value="">— Seleccione —</option>
                  <option v-for="r in regionales" :key="r.id" :value="r.id">{{ r.nombre }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Circuito *</label>
                <select v-model="f.circuito_id" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500 outline-none" required>
                  <option value="">— Seleccione —</option>
                  <option v-for="c in circuitosFiltrados" :key="c.id" :value="c.id">
                    {{ c.nombre }}
                  </option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Código *</label>
                <input v-model="f.codigo" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Ej. 07-001" required>
              </div>
            </div>

            <div class="grid md:grid-cols-2 gap-5">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de Institución *</label>
                <input v-model="f.nombre" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500 outline-none" required>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
                <input v-model="f.email" type="email" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="correo@ejemplo.com">
              </div>
            </div>

            <div class="grid md:grid-cols-3 gap-5">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                <input v-model="f.telefono" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="0000-0000">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Modalidad *</label>
                <select v-model="f.modalidad" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500 outline-none" required>
                  <option value="">— Seleccione —</option>
                  <option value="Primaria">Primaria</option>
                  <option value="Secundaria">Secundaria</option>
                  <option value="Técnica">Técnica</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
                <select v-model="f.tipo" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500 outline-none" required>
                  <option value="">— Seleccione —</option>
                  <option value="publica">Pública</option>
                  <option value="privada">Privada</option>
                  <option value="subvencionada">Subvencionada</option>
                </select>
              </div>
            </div>

            <div v-if="errorMsg" class="p-4 bg-red-50 text-red-700 rounded-lg flex items-start gap-3 text-sm">
              <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <span>{{ errorMsg }}</span>
            </div>

            <div v-if="cred" class="p-4 bg-green-50 border border-green-100 rounded-lg">
              <div class="flex items-center gap-2 mb-2 text-green-800 font-semibold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                ¡Institución registrada con éxito!
              </div>
              <div class="bg-white p-3 rounded border border-green-100 text-sm space-y-1">
                <p><strong>Usuario Encargado:</strong> <span class="font-mono text-gray-800">{{ cred.usuario }}</span></p>
                <p><strong>Contraseña:</strong> <span class="font-mono text-gray-800">{{ cred.password }}</span> <span class="text-xs text-gray-500">(Cópiala, no se volverá a mostrar)</span></p>
              </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t">
              <button type="button" @click="closeNew" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                {{ cred ? 'Cerrar' : 'Cancelar' }}
              </button>
              <button v-if="!cred" :disabled="saving" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow-sm transition-colors disabled:opacity-70 disabled:cursor-not-allowed flex items-center gap-2">
                <svg v-if="saving" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                {{ saving ? 'Guardando...' : (editando ? 'Actualizar' : 'Guardar') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
// IMPORTANTE: Asegúrate de importar estudiantesApi y proyectosApi
import { institucionesApi, catalogoApi, estudiantesApi, proyectosApi } from '@/services/api'

// --- State ---
const instituciones = ref([])
const regionales = ref([])
const circuitos = ref([])

const buscar = ref('')
const loading = ref(false)
const filtros = reactive({ buscar: '' })

// --- Expansion State (NUEVO) ---
const expandedId = ref(null)
const loadingDetails = ref(false)
const expandedData = ref({ estudiantes: [], proyectos: [] })

// --- Modal State ---
const showNew = ref(false)
const editando = ref(false)
const idEdicion = ref(null)
const saving = ref(false)
const errorMsg = ref('')
const cred = ref(null)

// --- Form ---
const f = ref({
  regional_id: '',
  circuito_id: '',
  nombre: '',
  codigo: '',
  telefono: '',
  email: '',
  modalidad: '',
  tipo: '', 
})

// --- Computed ---
const circuitosFiltrados = computed(() =>
  f.value.regional_id
    ? circuitos.value.filter(c => String(c.regional_id) === String(f.value.regional_id))
    : circuitos.value
)

// --- Methods ---
let timeoutSearch = null
const debouncedBuscar = () => {
  clearTimeout(timeoutSearch)
  timeoutSearch = setTimeout(() => load(), 400)
}

const load = async () => {
  loading.value = true
  try {
    const { data } = await institucionesApi.listar({ buscar: filtros.buscar })
    instituciones.value = data.data || data
  } catch (e) {
    console.error("Error cargando instituciones:", e)
  } finally {
    loading.value = false
  }
}

const loadCatalogos = async () => {
  try {
    const [regs, cirs] = await Promise.all([
      catalogoApi.regionales(),
      catalogoApi.circuitos(),
    ])
    regionales.value = regs.data
    circuitos.value  = cirs.data
  } catch (e) {
    console.error("Error cargando catálogos", e)
  }
}

// --- NUEVO: Lógica para Expandir/Contraer Fila ---
const toggleDetails = async (id) => {
  // Si clicamos la misma fila, la cerramos
  if (expandedId.value === id) {
    expandedId.value = null
    return
  }

  // Abrimos la nueva fila
  expandedId.value = id
  loadingDetails.value = true
  expandedData.value = { estudiantes: [], proyectos: [] } // Limpiar anterior

  try {
    // Consultamos Estudiantes y Proyectos filtrando por institucion_id
    const [estRes, proyRes] = await Promise.all([
      estudiantesApi.listar({ institucion_id: id, per_page: 50 }),
      proyectosApi.list({ institucion_id: id, per_page: 50 })
    ])

    expandedData.value = {
      estudiantes: estRes.data.data || estRes.data,
      proyectos: proyRes.data.data || proyRes.data
    }
  } catch (e) {
    console.error("Error cargando detalles:", e)
  } finally {
    loadingDetails.value = false
  }
}

// --- Actions ---
const abrirCrear = () => {
  editando.value = false
  idEdicion.value = null
  resetForm()
  showNew.value = true
}

const abrirEditar = (inst) => {
  editando.value = true
  idEdicion.value = inst.id
  errorMsg.value = ''
  cred.value = null
  
  f.value = {
    regional_id: inst.circuito?.regional_id || '',
    circuito_id: inst.circuito_id || '',
    nombre: inst.nombre,
    codigo: inst.codigo_presupuestario || inst.codigo,
    telefono: inst.telefono,
    email: inst.email,
    modalidad: inst.modalidad,
    tipo: inst.tipo
  }
  
  showNew.value = true
}

const resetForm = () => {
  f.value = { regional_id:'', circuito_id:'', nombre:'', codigo:'', telefono:'', email:'', modalidad:'', tipo:'' }
  errorMsg.value = ''
  cred.value = null
}

const closeNew = () => {
  showNew.value = false
  resetForm()
}

const save = async () => {
  saving.value = true
  errorMsg.value = ''

  try {
    const payload = {
      regional_id: f.value.regional_id,
      circuito_id: f.value.circuito_id,
      nombre: f.value.nombre,
      codigo_presupuestario: f.value.codigo,
      telefono: f.value.telefono || null,
      email: f.value.email || null,
      modalidad: f.value.modalidad,
      tipo: (f.value.tipo || '').toLowerCase(),
    }

    if (editando.value) {
      await institucionesApi.actualizar(idEdicion.value, payload)
      closeNew()
    } else {
      const { data } = await institucionesApi.crear(payload)
      cred.value = data?.credenciales ?? null
      if (!cred.value) showNew.value = false
    }

    await load()
  } catch (e) {
    const res = e?.response
    const v = res?.data
    
    const validations = v?.errors
      ? Object.values(v.errors).flat().join(' | ')
      : null

    errorMsg.value = validations || v?.message || e?.message || 'Error al guardar institución'
  } finally {
    saving.value = false
  }
}

const eliminar = async (inst) => {
  if (!confirm(`¿Estás seguro de eliminar la institución "${inst.nombre}"?`)) return

  try {
    await institucionesApi.eliminar(inst.id)
    await load()
  } catch (e) {
    alert(e?.response?.data?.message || 'Error al eliminar institución')
  }
}

// --- Lifecycle ---
onMounted(async () => {
  await Promise.all([load(), loadCatalogos()])
})

// Resetear circuito si cambia la regional (solo si estamos editando manualmente)
watch(() => f.value.regional_id, (newVal, oldVal) => {
  if (oldVal !== '' && newVal !== oldVal) {
      f.value.circuito_id = ''
  }
})
</script>