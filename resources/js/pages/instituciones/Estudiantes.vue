<template>
  <div class="max-w-7xl mx-auto px-4 py-6 space-y-4">
    <!-- Toolbar -->
    <div class="flex items-center justify-between">
      <h1 class="text-xl font-semibold text-gray-900">Estudiantes</h1>
      <button @click="openNew" class="px-3 py-2 rounded bg-blue-600 text-white">
        Nuevo
      </button>
    </div>

    <!-- Filtros -->
    <div class="flex items-center gap-2">
      <input
        v-model="buscar"
        @keyup.enter="load"
        placeholder="Buscar nombre/cedula…"
        class="border rounded px-3 py-2 w-full md:w-80"
      />
      <button @click="load" class="px-3 py-2 rounded bg-slate-100">
        Buscar
      </button>
    </div>

    <!-- Tabla -->
    <div class="bg-white border rounded-lg overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50">
          <tr>
            <th class="px-3 py-2 text-left">Cédula</th>
            <th class="px-3 py-2 text-left">Nombre</th>
            <th class="px-3 py-2 text-left">Nivel</th>
            <th class="px-3 py-2 text-left">Proyectos</th>
            <th class="px-3 py-2 text-left">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="e in estudiantes" :key="e.id" class="border-t">
            <td class="px-3 py-2">{{ e.cedula }}</td>
            <td class="px-3 py-2">{{ e.nombre }} {{ e.apellidos }}</td>
            <td class="px-3 py-2">{{ e.nivel }}</td>
            <td class="px-3 py-2">
              <span v-if="!e.proyectos?.length" class="text-slate-400">—</span>
              <span
                v-for="p in e.proyectos"
                :key="p.id"
                class="inline-flex items-center gap-1 bg-slate-100 rounded px-2 py-0.5 mr-2 mb-1"
              >
                {{ p.titulo }}
                <button class="text-red-600" title="Quitar" @click="quitarProyecto(e, p.id)">×</button>
              </span>
            </td>
            <td class="px-3 py-2">
              <button
                class="px-2 py-1 rounded bg-indigo-600 text-white"
                @click="abrirVinculo(e)"
              >
                Ligar proyecto
              </button>
            </td>
          </tr>

          <tr v-if="!loading && !estudiantes.length">
            <td colspan="5" class="px-3 py-10 text-center text-slate-500">
              Sin registros.
              <div class="mt-3">
                <button @click="openNew" class="px-3 py-2 rounded bg-blue-600 text-white">
                  Crear primer estudiante
                </button>
              </div>
            </td>
          </tr>

          <tr v-if="loading">
            <td colspan="5" class="px-3 py-6 text-center text-slate-500">Cargando…</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- ===== Modal: Crear estudiante ===== -->
    <div v-if="showNew" class="fixed inset-0 bg-black/30 grid place-items-center p-4 z-50">
      <form @submit.prevent="save" class="bg-white w-full max-w-3xl p-6 rounded shadow space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="font-medium">Nuevo estudiante</h2>
          <button type="button" @click="closeNew" class="text-slate-500 hover:text-slate-700">✕</button>
        </div>

        <div class="grid md:grid-cols-2 gap-3">
          <input v-model="f.cedula" class="border rounded px-3 py-2" placeholder="Cédula" required>
          <input v-model="f.nivel" class="border rounded px-3 py-2" placeholder="Nivel" required>
          <input v-model="f.nombre" class="border rounded px-3 py-2" placeholder="Nombre" required>
          <input v-model="f.seccion" class="border rounded px-3 py-2" placeholder="Sección">
          <input v-model="f.apellidos" class="border rounded px-3 py-2" placeholder="Apellidos" required>
          <input v-model="f.fecha_nacimiento" type="date" class="border rounded px-3 py-2">
          <input v-model="f.telefono" class="border rounded px-3 py-2" placeholder="Teléfono">
          <input v-model="f.email" type="email" class="border rounded px-3 py-2" placeholder="Correo">

          <select v-model="f.genero" class="border rounded px-3 py-2 md:col-span-2" required>
            <option value="">Género…</option>
            <option value="M">Masculino</option>
            <option value="F">Femenino</option>
            <option value="Otro">Otro</option>
          </select>
        </div>

        <div class="flex justify-end gap-2">
          <button type="button" @click="closeNew" class="px-3 py-2 rounded bg-slate-100">Cancelar</button>
          <button :disabled="saving" class="px-3 py-2 rounded bg-blue-600 text-white">
            {{ saving ? 'Guardando…' : 'Guardar' }}
          </button>
        </div>

        <!-- Credenciales tras crear -->
        <div v-if="cred" class="mt-4 p-3 rounded bg-green-50 text-sm">
          <div class="font-medium text-green-700">Estudiante creado correctamente.</div>
          <div class="mt-1">Usuario: <b>{{ cred.usuario.username }}</b></div>
          <div class="mt-1">Contraseña (se muestra una sola vez): <b class="font-mono">{{ cred.plain_password }}</b></div>
          <div class="mt-2 flex flex-wrap gap-2">
            <button type="button" @click="descargarCredencial(cred.estudiante.id)" class="px-3 py-2 bg-green-600 text-white rounded">
              Descargar credencial
            </button>
          </div>
        </div>

        <div v-if="errorMsg" class="mt-3 p-2 rounded bg-red-50 text-red-700 text-sm">
          {{ errorMsg }}
        </div>
      </form>
    </div>

    <!-- ===== Modal: Ligar proyecto ===== -->
    <div v-if="showLink" class="fixed inset-0 bg-black/30 grid place-items-center p-4 z-50">
      <form @submit.prevent="guardarVinculo" class="bg-white w-full max-w-lg p-6 rounded shadow space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="font-medium">Ligar proyecto a {{ selEst?.nombre }} {{ selEst?.apellidos }}</h2>
          <button type="button" @click="cerrarVinculo" class="text-slate-500 hover:text-slate-700">✕</button>
        </div>

        <div>
          <label class="block text-sm mb-1">Proyecto</label>
          <select v-model="linkProjectId" class="border rounded px-3 py-2 w-full" required>
            <option value="">— Seleccione —</option>
            <option v-for="p in proyectos" :key="p.id" :value="p.id">{{ p.titulo }}</option>
          </select>
        </div>

        <div class="flex justify-end gap-2">
          <button type="button" @click="cerrarVinculo" class="px-3 py-2 rounded bg-slate-100">Cancelar</button>
          <button :disabled="linking" class="px-3 py-2 rounded bg-indigo-600 text-white">
            {{ linking ? 'Guardando…' : 'Ligar' }}
          </button>
        </div>

        <div v-if="linkError" class="mt-3 p-2 rounded bg-red-50 text-red-700 text-sm">
          {{ linkError }}
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { estudiantesApi, proyectosApi } from '@/services/api'

const auth = useAuthStore()

const estudiantes = ref([])
const buscar = ref('')
const loading = ref(false)

/* ===== Crear estudiante ===== */
const showNew = ref(false)
const saving = ref(false)
const errorMsg = ref('')
const cred = ref(null)

const f = ref({
  cedula: '', nombre: '', apellidos: '',
  nivel: '', seccion: '', telefono: '', email: '',
  fecha_nacimiento: '', genero: ''
})

/* ===== Ligar proyecto ===== */
const showLink = ref(false)
const selEst = ref(null)
const proyectos = ref([])
const linkProjectId = ref('')
const linking = ref(false)
const linkError = ref('')

const load = async () => {
  loading.value = true
  if (!auth.user) await auth.fetchMe()
  const instId = auth.user?.institucion?.id || auth.user?.institucion_id
  const { data } = await estudiantesApi.listar({ institucion_id: instId, buscar: buscar.value })
  estudiantes.value = data.data || data
  loading.value = false
}

const openNew = () => {
  errorMsg.value = ''
  cred.value = null
  showNew.value = true
}

const closeNew = () => {
  showNew.value = false
  f.value = { cedula:'', nombre:'', apellidos:'', nivel:'', seccion:'', telefono:'', email:'', fecha_nacimiento:'', genero:'' }
  cred.value = null
  errorMsg.value = ''
}

const save = async () => {
  try {
    saving.value = true
    errorMsg.value = ''
    if (!auth.user) await auth.fetchMe()
    const instId = auth.user?.institucion?.id || auth.user?.institucion_id

    // normaliza fecha (el backend también tolera dd/mm/yyyy)
    let fecha = f.value.fecha_nacimiento
    if (fecha) {
      const d = new Date(fecha)
      if (!isNaN(d.getTime())) fecha = d.toISOString().slice(0, 10)
    }

    const payload = { ...f.value, fecha_nacimiento: fecha, institucion_id: instId || undefined }
    const { data } = await estudiantesApi.crear(payload)
    cred.value = data
    await load()
  } catch (e) {
  if (e?.response?.status === 422) {
    // Laravel validation
    if (e.response.data?.errors) {
      errorMsg.value = Object.values(e.response.data.errors).flat().join(' | ')
    } else {
      errorMsg.value = e.response.data?.message || 'Datos inválidos'
    }
  } else {
    // Errores 500 u otros: muestra detalle si viene en "error"
    errorMsg.value = e?.response?.data?.error
      || e?.response?.data?.message
      || e?.message
      || 'Error al crear estudiante'
  }
}

}

const descargarCredencial = async (id) => {
  await estudiantesApi.descargarCredencial(id)
}

const abrirVinculo = async (est) => {
  selEst.value = est
  linkProjectId.value = ''
  linkError.value = ''
  showLink.value = true

  if (!auth.user) await auth.fetchMe()
  const instId = auth.user?.institucion?.id || auth.user?.institucion_id
  const { data } = await proyectosApi.listar({ institucion_id: instId, per_page: 100 })
  proyectos.value = data.data || data
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
      linkError.value = e?.response?.data?.message || e?.message || 'No se pudo ligar'
    }
  } finally {
    linking.value = false
  }
}

const quitarProyecto = async (est, proyectoId) => {
  try {
    await estudiantesApi.desvincular(est.id, proyectoId)
    await load()
  } catch (e) {
    alert(e?.response?.data?.message || 'No se pudo quitar el proyecto')
  }
}

onMounted(load)
</script>
