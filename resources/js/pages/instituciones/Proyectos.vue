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
          <h1 class="text-2xl font-bold text-gray-900">Gestión de Proyectos</h1>
          <p class="text-gray-600">Administra las investigaciones de tu institución</p>
        </div>
      </div>

      <button
        @click="openNew"
        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 shadow-sm transition-colors"
      >
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Nuevo Proyecto
      </button>
    </div>

    <div class="bg-white rounded-lg shadow-sm border p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-1">
          <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
          <div class="relative">
            <input
              v-model="buscar"
              type="text"
              placeholder="Buscar por título..."
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
        <p class="text-gray-500">Cargando proyectos...</p>
      </div>

      <div v-else-if="!proyectos.length" class="p-12 text-center text-gray-500">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <p class="text-lg font-medium">No se encontraron proyectos</p>
        <p class="text-sm">Intenta ajustar los filtros o crea uno nuevo.</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4">Título del Proyecto</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Área / Categoría</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estudiantes</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Juez Asignado</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="p in proyectos" :key="p.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-6 py-4">
                <div class="flex items-start gap-3">
                  <div class="h-10 w-10 rounded-lg bg-green-100 flex items-center justify-center text-green-700 shrink-0 mt-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                  </div>
                  <div>
                    <div class="text-sm font-medium text-gray-900 line-clamp-2" :title="p.titulo">{{ p.titulo }}</div>
                    <div class="text-xs text-gray-500 mt-0.5 line-clamp-1" :title="p.resumen">{{ p.resumen || 'Sin resumen' }}</div>
                    <span v-if="p.modalidad" class="text-xs bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded mt-1 inline-block">
                        {{ p.modalidad.nombre }}
                    </span>
                  </div>
                </div>
              </td>
              
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex flex-col gap-1 items-start">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ p.area?.nombre || 'General' }}
                  </span>
                  <span class="text-xs text-gray-500">{{ p.categoria?.nombre || '—' }}</span>
                </div>
              </td>

              <td class="px-6 py-4">
                <div class="flex flex-wrap gap-1">
                  <span v-if="!p.estudiantes?.length" class="text-xs text-gray-400 italic">Sin estudiantes</span>
                  <span 
                    v-for="e in p.estudiantes" 
                    :key="e.id" 
                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700 border border-gray-200"
                  >
                    {{ e.nombre }} {{ e.apellidos.split(' ')[0] }}
                  </span>
                </div>
              </td>

              <td class="px-6 py-4">
                <div class="flex flex-wrap gap-1">
                  <span v-if="!p.asignaciones_juez?.length" class="text-xs text-gray-400 italic">Sin asignar</span>
                  <span 
                    v-for="asig in p.asignaciones_juez" 
                    :key="asig.id" 
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700 border border-purple-200"
                  >
                    {{ asig.juez?.nombre }}
                  </span>
                </div>
              </td>

              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex justify-end gap-3 items-center">
                  
                  <button @click="abrirDetalle(p)" class="text-blue-600 hover:text-blue-900 flex items-center gap-1" title="Ver detalles">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                  Detalles
                  </button>

                  <button @click="abrirEditar(p)" class="text-gray-600 hover:text-gray-900 flex items-center gap-1 font-medium hover:text-indigo-600" title="Editar">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    Editar
                  </button>

                  <button @click="eliminar(p)" class="text-red-600 hover:text-red-900 flex items-center gap-1 font-medium" title="Eliminar">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Eliminar
                  </button>

                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="showDetalle" class="fixed inset-0 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4 z-50 transition-opacity">
      <div class="bg-[#1e1e1e] w-full max-w-3xl rounded-xl shadow-2xl border border-gray-700 overflow-hidden flex flex-col max-h-[90vh] text-gray-200">
        <div class="px-6 py-4 border-b border-gray-700 flex justify-between items-center">
          <h2 class="text-lg font-semibold text-white">Detalles del Proyecto</h2>
          <button @click="cerrarDetalle" class="text-gray-400 hover:text-white transition-colors">✕</button>
        </div>
        
        <div class="p-6 overflow-y-auto space-y-6">
          
          <div>
            <h3 class="text-2xl font-bold text-white mb-2">{{ proyectoDetalle?.titulo }}</h3>
            <div class="flex items-center gap-3 text-sm">
               <span class="bg-indigo-600 text-white px-2 py-0.5 rounded text-xs font-mono tracking-wide">{{ proyectoDetalle?.codigo }}</span>
               <span class="text-gray-400">{{ proyectoDetalle?.institucion?.nombre }}</span>
            </div>
          </div>

          <div class="bg-[#2a2a2a] p-4 rounded-lg border border-gray-700">
            <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Resumen</h4>
            <p class="text-sm text-gray-300 leading-relaxed">{{ proyectoDetalle?.resumen || 'Sin resumen registrado.' }}</p>
          </div>

          <div class="grid grid-cols-2 gap-y-6 gap-x-4">
            <div>
              <span class="block text-xs text-gray-500 uppercase tracking-wide mb-1">Área</span>
              <span class="font-medium text-white">{{ proyectoDetalle?.area?.nombre }}</span>
            </div>
            <div>
              <span class="block text-xs text-gray-500 uppercase tracking-wide mb-1">Categoría</span>
              <span class="font-medium text-white">{{ proyectoDetalle?.categoria?.nombre }}</span>
            </div>
            <div>
              <span class="block text-xs text-gray-500 uppercase tracking-wide mb-1">Modalidad</span>
              <span class="font-medium text-white">{{ proyectoDetalle?.modalidad?.nombre }}</span>
            </div>
            <div>
              <span class="block text-xs text-gray-500 uppercase tracking-wide mb-1">Feria</span>
              <span class="font-medium text-white">{{ proyectoDetalle?.feria?.nombre }}</span>
            </div>
          </div>

          <div class="pt-4 border-t border-gray-700">
            <h4 class="text-lg font-semibold text-white mb-4">Estudiantes Asignados</h4>
            
            <div v-if="proyectoDetalle?.estudiantes?.length > 0" class="space-y-2">
                <div v-for="est in proyectoDetalle.estudiantes" :key="est.id" class="flex items-center justify-between p-3 bg-[#2a2a2a] rounded-lg border border-gray-700 hover:border-gray-600 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="h-8 w-8 rounded-full bg-indigo-900/50 flex items-center justify-center text-indigo-400 font-bold text-sm">
                            {{ est.nombre.charAt(0) }}
                        </div>
                        <div>
                            <div class="text-sm font-medium text-white">{{ est.nombre }} {{ est.apellidos }}</div>
                            <div class="text-xs text-gray-500">{{ est.cedula }}</div>
                        </div>
                    </div>
                    <div class="text-xs text-gray-400 bg-gray-800 px-2 py-1 rounded">
                        {{ est.nivel }} <span v-if="est.seccion">- {{ est.seccion }}</span>
                    </div>
                </div>
            </div>
            <div v-else class="text-center py-8 text-gray-500 italic border border-dashed border-gray-700 rounded-lg">
              No hay estudiantes vinculados a este proyecto.
            </div>
          </div>

          <div class="pt-4 border-t border-gray-700">
             <h4 class="text-lg font-semibold text-white mb-4">Jueces Evaluadores</h4>
             <div v-if="proyectoDetalle?.asignaciones_juez?.length > 0" class="flex flex-wrap gap-2">
                <span v-for="asig in proyectoDetalle.asignaciones_juez" :key="asig.id" class="px-3 py-1 rounded-full bg-purple-900/40 text-purple-300 border border-purple-700 text-sm">
                   {{ asig.juez?.nombre }}
                </span>
             </div>
             <div v-else class="text-sm text-gray-500 italic">Sin jueces asignados aún.</div>
          </div>

        </div>

        <div class="px-6 py-4 border-t border-gray-700 bg-[#252525] flex justify-end">
          <button @click="cerrarDetalle" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium transition-colors shadow-lg shadow-indigo-900/20">Cerrar</button>
        </div>
      </div>
    </div>

    <div v-if="showNew" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50">
      <div class="bg-white w-full max-w-3xl rounded-xl shadow-2xl border overflow-hidden">
        <div class="px-6 py-4 border-b flex justify-between items-center bg-gray-50">
          <h2 class="text-lg font-semibold text-gray-800">{{ editando ? 'Editar Proyecto' : 'Nuevo Proyecto' }}</h2>
          <button @click="closeNew" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>

        <div class="p-6 max-h-[80vh] overflow-y-auto">
          <form @submit.prevent="save" class="space-y-5">
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Título del Proyecto *</label>
              <input v-model="f.titulo" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Ej. Sistema de Monitoreo..." required>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Resumen</label>
              <textarea v-model="f.resumen" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500 outline-none" rows="3" placeholder="Descripción breve (máx. 250 palabras)"></textarea>
              <p class="text-xs text-gray-500 mt-1 text-right">{{ (f.resumen || '').length }}/250 palabras aprox.</p>
            </div>

            <div class="grid md:grid-cols-2 gap-5">
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Modalidad *</label>
                <select v-model="f.modalidad_id" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500 outline-none" required>
                  <option value="">— Seleccione —</option>
                  <option v-for="m in form.modalidades" :key="m.id" :value="m.id">{{ m.nombre }}</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Área Temática *</label>
                <select v-model="f.area_id" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500 outline-none" required>
                  <option value="">— Seleccione —</option>
                  <option v-for="a in form.areas" :key="a.id" :value="a.id">{{ a.nombre }}</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Categoría *</label>
                <select v-model="f.categoria_id" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500 outline-none" required>
                  <option value="">— Seleccione —</option>
                  <option v-for="c in form.categorias" :key="c.id" :value="c.id">
                    {{ c.nombre }} <span v-if="c.nivel">({{ c.nivel }})</span>
                  </option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Feria Científica *</label>
                <select v-model="f.feria_id" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500 outline-none" required>
                  <option value="">— Seleccione —</option>
                  <option v-for="fe in form.ferias" :key="fe.id" :value="fe.id">
                    {{ fe.nombre }} <span v-if="fe.tipo || fe.nivel"> — {{ fe.tipo || fe.nivel }}</span>
                  </option>
                </select>
              </div>
            </div>

            <div v-if="errorMsg" class="p-4 bg-red-50 text-red-700 rounded-lg flex items-start gap-3 text-sm">
              <span>{{ errorMsg }}</span>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t">
              <button type="button" @click="closeNew" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">Cancelar</button>
              <button :disabled="saving" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow-sm transition-colors disabled:opacity-70 flex items-center gap-2">
                <svg v-if="saving" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                {{ saving ? 'Guardando...' : (editando ? 'Actualizar' : 'Guardar Proyecto') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { proyectosApi } from '@/services/api'

const auth = useAuthStore()

// State
const proyectos = ref([])
const loading = ref(false)
const buscar = ref('')

// Modal
const showNew = ref(false)
const editando = ref(false)
const idEdicion = ref(null)
const saving = ref(false)
const errorMsg = ref('')

// Modal Detalle
const showDetalle = ref(false)
const proyectoDetalle = ref(null)

// Form
const f = ref({
  titulo: '', resumen: '', area_id: '', categoria_id: '', 
  feria_id: '', modalidad_id: '', institucion_id: '', estudiantes: []
})
const form = ref({ areas: [], categorias: [], ferias: [], modalidades: [], instituciones: [], estudiantes: [] })

// Actions
let timeoutSearch = null
const debouncedBuscar = () => { clearTimeout(timeoutSearch); timeoutSearch = setTimeout(load, 400) }

const load = async () => {
  loading.value = true
  try {
    if (!auth.user) await auth.fetchMe()
    const { data } = await proyectosApi.list({ buscar: buscar.value })
    proyectos.value = data.data || data
  } catch (e) { console.error(e) } 
  finally { loading.value = false }
}

const abrirDetalle = (p) => {
    proyectoDetalle.value = p
    showDetalle.value = true
}
const cerrarDetalle = () => {
    showDetalle.value = false
    proyectoDetalle.value = null
}

const openNew = async () => {
  editando.value = false
  idEdicion.value = null
  resetForm()
  // Asignamos automáticamente la institución del usuario
  if (auth.user?.institucion?.id || auth.user?.institucion_id) {
      f.value.institucion_id = auth.user.institucion?.id || auth.user.institucion_id
  }
  showNew.value = true
  await cargarCatalogos()
}

const abrirEditar = async (p) => {
  editando.value = true
  idEdicion.value = p.id
  resetForm()
  f.value = {
    titulo: p.titulo,
    resumen: p.resumen,
    area_id: p.area_id,
    categoria_id: p.categoria_id,
    feria_id: p.feria_id,
    modalidad_id: p.modalidad_id,
    institucion_id: p.institucion_id,
    estudiantes: p.estudiantes?.map(e => e.id) || []
  }
  showNew.value = true
  await cargarCatalogos()
}

const resetForm = () => {
  f.value = { titulo:'', resumen:'', area_id:'', categoria_id:'', feria_id:'', modalidad_id:'', institucion_id:'', estudiantes:[] }
  errorMsg.value = ''
}

const closeNew = () => showNew.value = false

const cargarCatalogos = async () => {
  if (form.value.areas.length > 0) return
  try {
    const { data } = await proyectosApi.formData()
    form.value = data
  } catch (e) {
    console.error(e)
    errorMsg.value = 'Error cargando listas.'
  }
}

const save = async () => {
  try {
    saving.value = true
    errorMsg.value = ''
    
    // SEGURIDAD FRONTEND: Forzar la institución del usuario si es comité
    if (!f.value.institucion_id) {
        f.value.institucion_id = auth.user?.institucion?.id || auth.user?.institucion_id
    }
    
    const payload = { ...f.value }

    if (editando.value) await proyectosApi.update(idEdicion.value, payload)
    else await proyectosApi.create(payload)

    closeNew()
    await load()
  } catch (e) {
    if (e.response?.status === 422) {
      errorMsg.value = Object.values(e.response.data.errors).flat().join(' | ')
    } else {
      errorMsg.value = e.response?.data?.message || 'Error al guardar'
    }
  } finally {
    saving.value = false
  }
}

const eliminar = async (p) => {
  if (!confirm(`¿Eliminar el proyecto "${p.titulo}"?`)) return
  try {
    await proyectosApi.destroy(p.id)
    await load()
  } catch (e) {
    alert(e?.response?.data?.message || 'Error al eliminar proyecto')
  }
}

onMounted(async () => {
  if (!auth.user) await auth.fetchMe()
  load()
})
</script>