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
          <h1 class="text-2xl font-bold text-gray-900">Gestión de Estudiantes</h1>
          <p class="text-gray-600">Administra el padrón estudiantil y asigna proyectos</p>
        </div>
      </div>

      <button
        @click="abrirCrear"
        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 shadow-sm transition-colors"
      >
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Nuevo Estudiante
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
              placeholder="Buscar por nombre o cédula..."
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
        <p class="text-gray-500">Cargando estudiantes...</p>
      </div>

      <div v-else-if="!estudiantes.length" class="p-12 text-center text-gray-500">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
        <p class="text-lg font-medium">No se encontraron estudiantes</p>
        <p class="text-sm">Intenta ajustar los filtros o crea uno nuevo.</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estudiante</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cédula</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Institución</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nivel / Sección</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proyectos Asignados</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="e in estudiantes" :key="e.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-sm">
                    {{ e.nombre.charAt(0) }}{{ e.apellidos.charAt(0) }}
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ e.nombre }} {{ e.apellidos }}</div>
                    <div class="text-sm text-gray-500">{{ e.email || 'Sin correo' }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-mono">
                {{ e.cedula }}
              </td>
              
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                 <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                    {{ e.institucion?.nombre || '—' }}
                 </span>
              </td>

              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ e.nivel }}</div>
                <div class="text-xs text-gray-500">{{ e.seccion || 'Sin sección' }}</div>
              </td>
              <td class="px-6 py-4">
                <div class="flex flex-wrap gap-2">
                  <span v-if="!e.proyectos?.length" class="text-xs text-gray-400 italic">Ninguno</span>
                  <span
                    v-for="p in e.proyectos"
                    :key="p.id"
                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                  >
                    {{ p.titulo }}
                    <button 
                      @click="quitarProyecto(e, p.id)"
                      class="ml-1 text-blue-400 hover:text-blue-600 focus:outline-none"
                      title="Desvincular proyecto"
                    >
                      ×
                    </button>
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex justify-end gap-3">
                  <button 
                    @click="abrirVinculo(e)" 
                    class="text-indigo-600 hover:text-indigo-900 flex items-center gap-1"
                    title="Asignar Proyecto"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    Ligar
                  </button>
                  <button 
                    @click="abrirEditar(e)" 
                    class="text-gray-600 hover:text-gray-900 flex items-center gap-1"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    Editar
                  </button>
                  <button 
                    @click="eliminar(e)" 
                    class="text-red-600 hover:text-red-900 flex items-center gap-1"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Eliminar
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <div v-if="estudiantes.length > 0" class="px-6 py-3 border-t bg-gray-50 text-xs text-gray-500 text-right">
        Mostrando {{ estudiantes.length }} registros
      </div>
    </div>

    <div v-if="showNew" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50">
      <div class="bg-white w-full max-w-3xl rounded-xl shadow-2xl border overflow-hidden">
        <div class="px-6 py-4 border-b flex justify-between items-center bg-gray-50">
          <h2 class="text-lg font-semibold text-gray-800">
            {{ editando ? 'Editar Estudiante' : 'Nuevo Estudiante' }}
          </h2>
          <button @click="closeNew" class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
          </button>
        </div>

        <div class="p-6 max-h-[80vh] overflow-y-auto">
          <form @submit.prevent="save" class="space-y-6">
            
            <div class="grid md:grid-cols-2 gap-5">
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Institución *</label>
                <select v-model="f.institucion_id" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500 outline-none" required>
                  <option value="">Seleccione una institución</option>
                  <option v-for="inst in institucionesOpts" :key="inst.id" :value="inst.id">
                    {{ inst.nombre }}
                  </option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cédula *</label>
                <input v-model="f.cedula" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Identificación" required>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                <input v-model="f.nombre" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Nombre" required>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Apellidos *</label>
                <input v-model="f.apellidos" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Apellidos" required>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
                <input v-model="f.email" type="email" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="estudiante@ejemplo.com">
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nivel *</label>
                <input v-model="f.nivel" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Ej: 7mo, 10mo" required>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sección</label>
                <input v-model="f.seccion" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Ej: 10-1">
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Nacimiento</label>
                <input v-model="f.fecha_nacimiento" type="date" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500 outline-none">
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Género *</label>
                <select v-model="f.genero" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500 outline-none" required>
                  <option value="">Seleccione...</option>
                  <option value="M">Masculino</option>
                  <option value="F">Femenino</option>
                  <option value="Otro">Otro</option>
                </select>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                <input v-model="f.telefono" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Opcional">
              </div>
            </div>

            <div v-if="errorMsg" class="p-4 bg-red-50 text-red-700 rounded-lg flex items-start gap-3 text-sm">
              <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <span>{{ errorMsg }}</span>
            </div>

            <div v-if="cred" class="p-4 bg-green-50 border border-green-100 rounded-lg">
              <div class="flex items-center gap-2 mb-2 text-green-800 font-semibold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                ¡Estudiante registrado con éxito!
              </div>
              <div class="bg-white p-3 rounded border border-green-100 text-sm space-y-1">
                <p><strong>Usuario:</strong> <span class="font-mono text-gray-800">{{ cred.usuario.username }}</span></p>
                <p><strong>Contraseña:</strong> <span class="font-mono text-gray-800">{{ cred.plain_password }}</span> <span class="text-xs text-gray-500">(Cópiala, no se volverá a mostrar)</span></p>
              </div>
              <div class="mt-4 flex flex-wrap gap-2">
                <button 
                  type="button" 
                  @click="descargarCredencial(cred.estudiante.id, cred.plain_password)" 
                  class="px-3 py-2 bg-green-600 text-white rounded flex items-center gap-2"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                  Descargar Ficha PDF
                </button>
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

    <div v-if="showLink" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50">
      <div class="bg-white w-full max-w-lg rounded-xl shadow-lg border">
        <div class="px-6 py-4 border-b flex justify-between items-center bg-gray-50">
          <h3 class="text-lg font-semibold text-gray-800">Asignar Proyecto</h3>
          <button @click="cerrarVinculo" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>
        
        <div class="p-6">
          <p class="text-sm text-gray-600 mb-4">
            Selecciona el proyecto científico para <strong>{{ selEst?.nombre }} {{ selEst?.apellidos }}</strong>.
          </p>

          <form @submit.prevent="guardarVinculo">
            <div class="mb-5">
              <label class="block text-sm font-medium text-gray-700 mb-1">Proyecto Disponible</label>
              <select v-model="linkProjectId" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500 outline-none bg-white" required>
                <option value="">— Seleccione un proyecto —</option>
                <option v-for="p in proyectos" :key="p.id" :value="p.id">{{ p.titulo }}</option>
              </select>
            </div>

            <div v-if="linkError" class="mb-4 p-3 bg-red-50 text-red-700 text-sm rounded">
              {{ linkError }}
            </div>

            <div class="flex justify-end gap-3">
              <button type="button" @click="cerrarVinculo" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">Cancelar</button>
              <button :disabled="linking" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-70">
                {{ linking ? 'Asignando...' : 'Confirmar Asignación' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
// NUEVO: Agregamos institucionesApi a la importación
import { estudiantesApi, proyectosApi, institucionesApi } from '@/services/api'

const auth = useAuthStore()

// --- Estado Data ---
const estudiantes = ref([])
const loading = ref(false)
const filtros = reactive({ buscar: '' })
const institucionesOpts = ref([]) // NUEVO: Para guardar la lista de instituciones

// --- Estado Modales ---
const showNew = ref(false)
const editando = ref(false)
const idEdicion = ref(null) 
const saving = ref(false)
const errorMsg = ref('')
const cred = ref(null)

// --- Estado Vinculación ---
const showLink = ref(false)
const selEst = ref(null)
const proyectos = ref([])
const linkProjectId = ref('')
const linking = ref(false)
const linkError = ref('')

// Formulario reactivo
const f = ref({
  cedula: '', nombre: '', apellidos: '',
  nivel: '', seccion: '', telefono: '', email: '',
  fecha_nacimiento: '', genero: '',
  institucion_id: '' // NUEVO: Campo para el ID de institución
})

// --- NUEVO: Cargar lista de instituciones ---
const cargarInstituciones = async () => {
  try {
    const { data } = await institucionesApi.listar({ per_page: 100 }) // Traemos suficientes
    institucionesOpts.value = data.data || data
  } catch (e) {
    console.error("Error cargando instituciones:", e)
  }
}

let timeoutSearch = null
const debouncedBuscar = () => {
  clearTimeout(timeoutSearch)
  timeoutSearch = setTimeout(() => {
    load()
  }, 400)
}

const load = async () => {
  loading.value = true
  try {
    if (!auth.user) await auth.fetchMe()
    
    // Al no enviar institucion_id, el backend devolverá TODOS los estudiantes (ideal para comité)
    const { data } = await estudiantesApi.listar({ 
      buscar: filtros.buscar 
    })
    estudiantes.value = data.data || data
  } catch (e) {
    console.error("Error cargando estudiantes", e)
  } finally {
    loading.value = false
  }
}

const abrirCrear= () => {
  editando.value = false
  idEdicion.value = null
  resetForm()
  
  // NUEVO: Si el usuario logueado ya tiene institución, la pre-seleccionamos
  const userInst = auth.user?.institucion?.id || auth.user?.institucion_id
  if (userInst) {
      f.value.institucion_id = userInst
  }
  
  showNew.value = true
}

const abrirEditar = (est) => {
  editando.value = true
  idEdicion.value = est.id
  errorMsg.value = ''
  cred.value = null
  
  // Rellenar form
  f.value = {
    cedula: est.cedula,
    nombre: est.nombre,
    apellidos: est.apellidos,
    nivel: est.nivel,
    seccion: est.seccion,
    telefono: est.telefono,
    email: est.email,
    fecha_nacimiento: est.fecha_nacimiento,
    genero: est.genero,
    institucion_id: est.institucion_id // NUEVO: Cargar la institución del estudiante
  }
  
  showNew.value = true
}

const resetForm = () => {
  f.value = { cedula:'', nombre:'', apellidos:'', nivel:'', seccion:'', telefono:'', email:'', fecha_nacimiento:'', genero:'', institucion_id: '' }
  errorMsg.value = ''
  cred.value = null
}

const closeNew = () => {
  showNew.value = false
  resetForm()
}

const save = async () => {
  try {
    saving.value = true
    errorMsg.value = ''
    
    if (!auth.user) await auth.fetchMe()
    const userInst = auth.user?.institucion?.id || auth.user?.institucion_id

    // Normalizar fecha
    let fecha = f.value.fecha_nacimiento
    if (fecha) {
      const d = new Date(fecha)
      if (!isNaN(d.getTime())) fecha = d.toISOString().slice(0, 10)
    }

    const payload = { 
      ...f.value, 
      fecha_nacimiento: fecha, 
      // NUEVO: Usamos la institución del formulario. Si está vacía, fallback al usuario logueado.
      institucion_id: f.value.institucion_id || userInst || undefined 
    }

    if (editando.value) {
      await estudiantesApi.actualizar(idEdicion.value, payload)
      closeNew()
    } else {
      const { data } = await estudiantesApi.crear(payload)
      cred.value = data
    }

    await load()
  } catch (e) {
    if (e?.response?.status === 422) {
      if (e.response.data?.errors) {
        errorMsg.value = Object.values(e.response.data.errors).flat().join(' | ')
      } else {
        errorMsg.value = e.response.data?.message || 'Datos inválidos'
      }
    } else {
      errorMsg.value = e?.response?.data?.message || e?.message || 'Error al guardar'
    }
  } finally {
    saving.value = false
  }
}

const eliminar = async (est) => {
  if (!confirm(`¿Estás seguro de eliminar al estudiante ${est.nombre}? Esta acción no se puede deshacer.`)) return

  try {
    await estudiantesApi.eliminar(est.id)
    await load()
  } catch (e) {
    alert(e?.response?.data?.message || 'Error al eliminar estudiante')
  }
}

const descargarCredencial = async (id, passwordPlain = null) => {
  try {
    const config = { responseType: 'blob' }
    if (passwordPlain) {
        config.params = { password: passwordPlain }
    }

    const { data } = await estudiantesApi.descargarCredencial(id, passwordPlain) 
    
    const url = window.URL.createObjectURL(new Blob([data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `Credenciales_${id}.pdf`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);

  } catch (e) {
    console.error("Error descargando PDF:", e)
    alert("Error al descargar el PDF") 
  }
}

const abrirVinculo = async (est) => {
  selEst.value = est
  linkProjectId.value = ''
  linkError.value = ''
  showLink.value = true

  if (!auth.user) await auth.fetchMe()
  const instId = auth.user?.institucion?.id || auth.user?.institucion_id
  
  try {
    // NUEVO: Cargamos proyectos de la institución DEL ESTUDIANTE, no la mía
    const targetInst = est.institucion_id || instId
    const { data } = await proyectosApi.list({ institucion_id: targetInst, per_page: 100 })
    proyectos.value = data.data || data
  } catch (e) {
    console.error("Error cargando proyectos", e)
    proyectos.value = []
  }
}

const cerrarVinculo = () => {
  showLink.value = false
  selEst.value = null
  linkProjectId.value = ''
  linkError.value = ''
}

const guardarVinculo = async () => {
  try {
    linking.value = true
    linkError.value = ''
    await estudiantesApi.vincular(selEst.value.id, linkProjectId.value)
    cerrarVinculo()
    await load()
  } catch (e) {
    if (e?.response?.status === 422 && e.response.data?.errors) {
      linkError.value = Object.values(e.response.data.errors).flat().join(' | ')
    } else {
      linkError.value = e?.response?.data?.message || e?.message || 'No se pudo ligar el proyecto'
    }
  } finally {
    linking.value = false
  }
}

const quitarProyecto = async (est, proyectoId) => {
  if(!confirm('¿Quitar este proyecto del estudiante?')) return
  try {
    await estudiantesApi.desvincular(est.id, proyectoId)
    await load()
  } catch (e) {
    alert(e?.response?.data?.message || 'No se pudo quitar el proyecto')
  }
}

// Lifecycle
onMounted(async () => {
    // NUEVO: Cargamos instituciones y luego la tabla
    await cargarInstituciones()
    await load()
})
</script>