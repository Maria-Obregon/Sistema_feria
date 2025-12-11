<template>
  <div class="p-6 max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-8">
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
          <h1 class="text-2xl font-bold text-gray-900">Perfil Institucional</h1>
          <p class="text-gray-600">Información general y registros de tu centro educativo</p>
        </div>
      </div>
      
      <button 
        v-if="institucion"
        @click="abrirEditar"
        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 shadow-sm transition-colors"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
        Editar Información
      </button>
    </div>

    <div v-if="loading" class="py-12 flex justify-center">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
    </div>

    <div v-else-if="!institucion" class="bg-white p-8 rounded-xl shadow-sm border text-center">
      <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
      <h3 class="text-lg font-medium text-gray-900">No se encontró información institucional</h3>
      <p class="text-gray-500 mt-1">Parece que tu usuario no tiene una institución asignada.</p>
    </div>

    <div v-else class="space-y-6">
      <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="p-6 md:p-8">
          <div class="flex flex-col md:flex-row gap-6 items-start">
            <div class="h-24 w-24 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white shrink-0 shadow-lg">
              <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            
            <div class="flex-1 w-full">
              <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4">
                <div>
                  <h2 class="text-2xl font-bold text-gray-900">{{ institucion.nombre }}</h2>
                  <p class="text-gray-500 flex items-center gap-2 mt-1">
                    <span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded text-xs font-mono border">{{ institucion.codigo_presupuestario || institucion.codigo }}</span>
                    <span>{{ institucion.circuito?.regional?.nombre }}</span>
                  </p>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium mt-2 md:mt-0" 
                      :class="institucion.tipo === 'publica' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800'">
                  {{ institucion.tipo }} - {{ institucion.modalidad }}
                </span>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-6 border-t pt-6 mt-2">
                <div>
                  <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Circuito</span>
                  <span class="text-gray-900 font-medium">{{ institucion.circuito?.nombre }}</span>
                </div>
                <div>
                  <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Contacto</span>
                  <div class="text-gray-900">{{ institucion.email || '—' }}</div>
                  <div class="text-sm text-gray-500">{{ institucion.telefono || '—' }}</div>
                </div>
                <div>
                  <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Estado</span>
                  <span class="inline-flex items-center gap-1.5 text-green-700 font-medium">
                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                    Activa
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <div class="bg-white rounded-xl shadow-sm border flex flex-col h-[500px]">
          <div class="px-6 py-4 border-b flex justify-between items-center bg-gray-50">
            <h3 class="font-semibold text-gray-800 flex items-center gap-2">
              <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
              Estudiantes Registrados
              <span class="bg-indigo-100 text-indigo-700 text-xs px-2 py-0.5 rounded-full ml-1">{{ estudiantes.length }}</span>
            </h3>
            <RouterLink :to="{name: 'inst.estudiantes'}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">Gestionar</RouterLink>
          </div>
          
          <div class="flex-1 overflow-y-auto p-4 space-y-3">
            <div v-if="estudiantes.length === 0" class="h-full flex flex-col items-center justify-center text-gray-400">
              <p>No hay estudiantes registrados.</p>
            </div>
            <div v-else v-for="est in estudiantes" :key="est.id" class="flex items-center justify-between p-3 rounded-lg border border-gray-100 hover:bg-gray-50 transition-colors">
              <div class="flex items-center gap-3">
                <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-xs">
                  {{ est.nombre.charAt(0) }}
                </div>
                <div>
                  <div class="text-sm font-medium text-gray-900">{{ est.nombre }} {{ est.apellidos }}</div>
                  <div class="text-xs text-gray-500">{{ est.cedula }}</div>
                </div>
              </div>
              <div class="text-right">
                <span class="block text-xs font-medium text-gray-700">{{ est.nivel }}</span>
                <span class="block text-[10px] text-gray-400">{{ est.seccion }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border flex flex-col h-[500px]">
          <div class="px-6 py-4 border-b flex justify-between items-center bg-gray-50">
            <h3 class="font-semibold text-gray-800 flex items-center gap-2">
              <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
              Proyectos Inscritos
              <span class="bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded-full ml-1">{{ proyectos.length }}</span>
            </h3>
            <RouterLink :to="{name: 'inst.proyectos'}" class="text-sm text-green-600 hover:text-green-800 font-medium">Gestionar</RouterLink>
          </div>

          <div class="flex-1 overflow-y-auto p-4 space-y-3">
            <div v-if="proyectos.length === 0" class="h-full flex flex-col items-center justify-center text-gray-400">
              <p>No hay proyectos registrados.</p>
            </div>
            <div v-else v-for="proy in proyectos" :key="proy.id" class="p-3 rounded-lg border border-gray-100 hover:bg-gray-50 transition-colors">
              <div class="flex justify-between items-start mb-2">
                <h4 class="text-sm font-medium text-gray-900 line-clamp-1" :title="proy.titulo">{{ proy.titulo }}</h4>
                <span class="text-[10px] px-1.5 py-0.5 bg-gray-100 text-gray-600 rounded border">{{ proy.codigo }}</span>
              </div>
              <div class="flex flex-wrap gap-2 text-xs">
                <span class="bg-blue-50 text-blue-700 px-2 py-0.5 rounded">{{ proy.area?.nombre }}</span>
                <span class="bg-purple-50 text-purple-700 px-2 py-0.5 rounded">{{ proy.categoria?.nombre }}</span>
              </div>
              <div class="mt-2 pt-2 border-t border-gray-100 text-xs text-gray-500 flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                {{ proy.estudiantes?.length || 0 }} estudiantes
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <div v-if="showEdit" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50">
      <div class="bg-white w-full max-w-2xl rounded-xl shadow-2xl border overflow-hidden">
        <div class="px-6 py-4 border-b flex justify-between items-center bg-gray-50">
          <h2 class="text-lg font-semibold text-gray-800">Actualizar Información</h2>
          <button @click="closeEdit" class="text-gray-400 hover:text-gray-600 transition-colors">✕</button>
        </div>

        <div class="p-6">
          <form @submit.prevent="save" class="space-y-5">
            
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-100 text-sm text-blue-800 mb-2">
                <div class="flex justify-between mb-1">
                    <span><strong>Código:</strong> {{ f.codigo }}</span>
                    <span><strong>Modalidad:</strong> {{ f.modalidad }}</span>
                </div>
                <div><strong>Ubicación:</strong> {{ f.regional_nombre }} / {{ f.circuito_nombre }}</div>
            </div>

            <div class="grid md:grid-cols-2 gap-5">
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de la Institución</label>
                <input v-model="f.nombre" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500 outline-none" required>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
                <input v-model="f.email" type="email" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500 outline-none">
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                <input v-model="f.telefono" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500 outline-none">
              </div>
            </div>

            <div v-if="errorMsg" class="p-3 bg-red-50 text-red-700 text-sm rounded">{{ errorMsg }}</div>

            <div class="flex justify-end gap-3 pt-4 border-t">
              <button type="button" @click="closeEdit" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">Cancelar</button>
              <button :disabled="saving" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow-sm disabled:opacity-70">
                {{ saving ? 'Guardando...' : 'Guardar Cambios' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { institucionesApi, estudiantesApi, proyectosApi } from '@/services/api'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()

// --- State ---
const institucion = ref(null)
const estudiantes = ref([])
const proyectos = ref([])
const loading = ref(true)

// --- Modal State ---
const showEdit = ref(false)
const saving = ref(false)
const errorMsg = ref('')

// --- Form ---
const f = ref({
  id: null,
  nombre: '',
  codigo: '',
  telefono: '',
  email: '',
  modalidad: '',
  regional_nombre: '',
  circuito_nombre: ''
})

const loadData = async () => {
  loading.value = true
  try {
    if (!auth.user) await auth.fetchMe()
    
    // 1. Cargar Institución (El backend ya filtra por la sesión del comité)
    const { data: instData } = await institucionesApi.listar()
    // El endpoint devuelve paginación, tomamos el primer (y único) resultado
    const misInstituciones = instData.data || instData
    if (misInstituciones && misInstituciones.length > 0) {
        institucion.value = misInstituciones[0]
        
        // 2. Cargar Estudiantes de esta institución
        const { data: estData } = await estudiantesApi.listar({ per_page: 50 }) // Traer suficientes
        estudiantes.value = estData.data || estData

        // 3. Cargar Proyectos de esta institución
        const { data: proyData } = await proyectosApi.list({ per_page: 50 })
        proyectos.value = proyData.data || proyData
    }
  } catch (e) {
    console.error("Error cargando perfil:", e)
  } finally {
    loading.value = false
  }
}

const abrirEditar = () => {
  if (!institucion.value) return
  const i = institucion.value
  f.value = {
    id: i.id,
    nombre: i.nombre,
    codigo: i.codigo_presupuestario || i.codigo,
    telefono: i.telefono,
    email: i.email,
    modalidad: i.modalidad,
    regional_nombre: i.circuito?.regional?.nombre || '—',
    circuito_nombre: i.circuito?.nombre || '—'
  }
  errorMsg.value = ''
  showEdit.value = true
}

const closeEdit = () => {
  showEdit.value = false
}

const save = async () => {
  saving.value = true
  errorMsg.value = ''
  try {
    const payload = {
      nombre: f.value.nombre,
      telefono: f.value.telefono,
      email: f.value.email
    }
    await institucionesApi.actualizar(f.value.id, payload)
    closeEdit()
    await loadData() // Recargar datos frescos
  } catch (e) {
    const v = e?.response?.data
    errorMsg.value = v?.message || 'Error al guardar cambios'
  } finally {
    saving.value = false
  }
}

onMounted(loadData)
</script>