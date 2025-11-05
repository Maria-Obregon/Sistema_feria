<template>
  <div class="max-w-7xl mx-auto px-4 py-6 space-y-4">
    <div class="flex items-center justify-between">
      <h1 class="text-xl font-semibold text-gray-900">Jueces</h1>
      <button @click="openNew" class="px-3 py-2 rounded bg-blue-600 text-white">Nuevo juez</button>
    </div>

    <div class="flex items-center gap-2">
      <input
        v-model="buscar"
        @keyup.enter="load"
        placeholder="Buscar nombre/cedula/correo…"
        class="border rounded px-3 py-2 w-full md:w-96"
      />
      <button @click="load" class="px-3 py-2 rounded bg-slate-100">Buscar</button>
    </div>

    <div class="bg-white border rounded-lg overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50">
          <tr>
            <th class="px-3 py-2 text-left">Nombre</th>
            <th class="px-3 py-2 text-left">Cédula</th>
            <th class="px-3 py-2 text-left">Correo</th>
            <th class="px-3 py-2 text-left">Proyectos</th>
            <th class="px-3 py-2 text-left">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="j in jueces" :key="j.id" class="border-t align-top">
            <td class="px-3 py-2">
              <div class="font-medium">{{ j.nombre }}</div>
              <div class="text-xs text-slate-500">Área: {{ j.area_id ?? '—' }}</div>
            </td>
            <td class="px-3 py-2">{{ j.cedula }}</td>
            <td class="px-3 py-2">{{ j.correo }}</td>

            <!-- chips de proyectos -->
            <td class="px-3 py-2">
              <div class="flex flex-wrap gap-2">
                <span
                  v-for="p in j.proyectos"
                  :key="p.asig_id"
                  class="text-xs px-2 py-1 rounded-full bg-slate-100 border"
                  :title="`${etapaLabel(p.etapa_id)} · ${p.tipo_eval || ''}`"
                >
                  {{ p.titulo }}
                  <span class="opacity-70">— {{ etapaLabel(p.etapa_id) }}</span>
                </span>
                <span v-if="!j.proyectos?.length" class="text-slate-400 text-xs">Sin asignaciones</span>
              </div>
            </td>

            <td class="px-3 py-2 whitespace-nowrap">
              <button class="px-2 py-1 bg-emerald-600 text-white rounded mr-2" @click="openAssign(j)">
                Asignar a proyecto
              </button>
              <button class="px-2 py-1 bg-red-600 text-white rounded" @click="remove(j.id)">
                Eliminar
              </button>
            </td>
          </tr>

          <tr v-if="!loading && !jueces.length">
            <td colspan="5" class="px-3 py-6 text-center text-slate-500">Sin registros.</td>
          </tr>
          <tr v-if="loading">
            <td colspan="5" class="px-3 py-6 text-center text-slate-500">Cargando…</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal: crear juez -->
    <div v-if="showNew" class="fixed inset-0 bg-black/30 grid place-items-center p-4 z-50">
      <form @submit.prevent="save" class="bg-white w-full max-w-2xl p-6 rounded shadow space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="font-medium">Nuevo juez</h2>
          <button type="button" @click="closeNew" class="text-slate-500 hover:text-slate-700">✕</button>
        </div>

        <div class="grid md:grid-cols-2 gap-3">
          <input v-model="f.nombre" class="border rounded px-3 py-2 md:col-span-2" placeholder="Nombre completo" required>
          <input v-model="f.cedula" class="border rounded px-3 py-2" placeholder="Cédula" required>
          <input v-model="f.correo" class="border rounded px-3 py-2" placeholder="Correo (opcional)">
          <input v-model="f.telefono" class="border rounded px-3 py-2" placeholder="Teléfono">
          <input v-model="f.grado_academico" class="border rounded px-3 py-2 md:col-span-2" placeholder="Grado académico">
        </div>

        <div class="flex justify-end gap-2">
          <button type="button" @click="closeNew" class="px-3 py-2 rounded bg-slate-100">Cancelar</button>
          <button :disabled="saving" class="px-3 py-2 rounded bg-blue-600 text-white">
            {{ saving ? 'Guardando…' : 'Guardar' }}
          </button>
        </div>

        <div v-if="errorMsg" class="mt-3 p-2 rounded bg-red-50 text-red-700 text-sm">
          {{ errorMsg }}
        </div>
      </form>
    </div>

    <!-- Modal: asignar a proyecto -->
    <div v-if="showAssign" class="fixed inset-0 bg-black/30 grid place-items-center p-4 z-50">
      <form @submit.prevent="assign" class="bg-white w-full max-w-2xl p-6 rounded shadow space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="font-medium">Asignar juez a proyecto</h2>
          <button type="button" @click="closeAssign" class="text-slate-500 hover:text-slate-700">✕</button>
        </div>

        <div class="grid gap-3">
          <div class="text-sm text-slate-600">
            Juez seleccionado: <span class="font-medium">{{ juezSel?.nombre }}</span>
          </div>

          <div>
            <label class="block text-sm mb-1">Proyecto</label>
            <select v-model="a.proyecto_id" class="border rounded px-3 py-2 w-full" required>
              <option value="">— Seleccione —</option>
              <option v-for="p in proyectos" :key="p.id" :value="p.id">
                {{ p.titulo }} — {{ p.categoria?.nombre || '' }}
              </option>
            </select>
          </div>

          <div class="grid md:grid-cols-2 gap-3">
            <div>
              <label class="block text-sm mb-1">Etapa</label>
              <select v-model="a.etapa_id" class="border rounded px-3 py-2 w-full" required>
                <option value="">— Seleccione —</option>
                <option value="1">Institucional</option>
                <option value="2">Circuital</option>
                <option value="3">Regional</option>
              </select>
            </div>
            <div>
              <label class="block text-sm mb-1">Tipo de evaluación</label>
              <select v-model="a.tipo_eval" class="border rounded px-3 py-2 w-full" required>
                <option value="integral">Integral</option>
                <option value="escrito">Escrito</option>
                <option value="exposicion">Exposición</option>
              </select>
            </div>
          </div>

          <div class="border rounded p-3">
            <div class="text-sm font-medium mb-2">Asignaciones actuales del proyecto</div>
            <div v-if="asignaciones.length === 0" class="text-sm text-slate-500">Sin asignaciones.</div>
            <ul v-else class="space-y-1 text-sm">
              <li v-for="as in asignaciones" :key="as.id" class="flex items-center justify-between">
                <span>{{ as.juez?.nombre }} — etapa {{ as.etapa_id }} — {{ as.tipo_eval }}</span>
                <button class="text-red-600 hover:underline" @click.prevent="quitar(as.id)">Quitar</button>
              </li>
            </ul>
          </div>
        </div>

        <div class="flex justify-end gap-2">
          <button type="button" @click="closeAssign" class="px-3 py-2 rounded bg-slate-100">Cancelar</button>
          <button :disabled="assigning" class="px-3 py-2 rounded bg-emerald-600 text-white">
            {{ assigning ? 'Asignando…' : 'Asignar' }}
          </button>
        </div>

        <div v-if="assignMsg" class="mt-3 p-2 rounded bg-emerald-50 text-emerald-700 text-sm">
          {{ assignMsg }}
        </div>
        <div v-if="errorMsg" class="mt-3 p-2 rounded bg-red-50 text-red-700 text-sm">
          {{ errorMsg }}
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { juecesApi, proyectosApi } from '@/services/api'

const auth = useAuthStore()

const jueces = ref([])
const buscar = ref('')
const loading = ref(false)

const showNew  = ref(false)
const saving   = ref(false)
const errorMsg = ref('')

const f = ref({
  nombre: '',
  cedula: '',
  correo: '',
  telefono: '',
  grado_academico: ''
})

const etapaLabel = (id) => ({1:'Institucional', 2:'Circuital', 3:'Regional'}[Number(id)] || `Etapa ${id}`)

const load = async () => {
  loading.value = true
  // 👇 pedimos proyectos asociados
  const { data } = await juecesApi.listar({ buscar: buscar.value || undefined, con_proyectos: 1, per_page: 100 })
  jueces.value = data.data || data
  loading.value = false
}

const openNew = () => { showNew.value = true; errorMsg.value = '' }
const closeNew = () => {
  showNew.value = false
  f.value = { nombre:'', cedula:'', correo:'', telefono:'', grado_academico:'' }
  errorMsg.value = ''
}

const save = async () => {
  try {
    saving.value = true
    errorMsg.value = ''
    await juecesApi.crear({ ...f.value })
    closeNew()
    await load()
  } catch (e) {
    errorMsg.value =
      e?.response?.data?.message ||
      Object.values(e?.response?.data?.errors || {}).flat().join(' | ') ||
      e.message
  } finally {
    saving.value = false
  }
}

const remove = async (id) => {
  if (!confirm('¿Eliminar juez?')) return
  await juecesApi.eliminar(id)
  await load()
}

/* ===== Asignación ===== */
const showAssign = ref(false)
const assigning = ref(false)
const assignMsg = ref('')

const juezSel = ref(null)
const proyectos = ref([])
const asignaciones = ref([])

const a = ref({
  proyecto_id: '',
  etapa_id: '',
  tipo_eval: 'integral'
})

const openAssign = async (j) => {
  juezSel.value = j
  assignMsg.value = ''
  errorMsg.value = ''
  showAssign.value = true

  if (!auth.user) await auth.fetchMe()
  const instId = auth.user?.institucion?.id || auth.user?.institucion_id

  const { data: listP } = await proyectosApi.listar({ institucion_id: instId, per_page: 100 })
  proyectos.value = (listP.data || listP).map(p => ({
    id: p.id, titulo: p.titulo, categoria: p.categoria
  }))

  asignaciones.value = []
  a.value = { proyecto_id: '', etapa_id: '', tipo_eval: 'integral' }
}

const closeAssign = () => {
  showAssign.value = false
  juezSel.value = null
  asignaciones.value = []
  a.value = { proyecto_id: '', etapa_id: '', tipo_eval: 'integral' }
  errorMsg.value = ''
  assignMsg.value = ''
}

watch(() => a.value.proyecto_id, async (pid) => {
  if (!pid) { asignaciones.value = []; return }
  const { data } = await juecesApi.asignacionesDeProyecto(pid)
  asignaciones.value = data
})

const assign = async () => {
  try {
    assigning.value = true
    errorMsg.value = ''
    assignMsg.value = ''

    if (!a.value.proyecto_id || !a.value.etapa_id) {
      errorMsg.value = 'Seleccione proyecto y etapa'
      return
    }

    await juecesApi.asignarAProyecto(a.value.proyecto_id, {
      etapa_id: a.value.etapa_id,
      tipo_eval: a.value.tipo_eval,
      jueces: [{ id: juezSel.value.id }]
    })

    assignMsg.value = 'Asignación realizada'
    const { data } = await juecesApi.asignacionesDeProyecto(a.value.proyecto_id)
    asignaciones.value = data
    // refrescamos listado para que los chips se actualicen
    await load()
  } catch (e) {
    errorMsg.value =
      e?.response?.data?.message ||
      Object.values(e?.response?.data?.errors || {}).flat().join(' | ') ||
      e.message
  } finally {
    assigning.value = false
  }
}

const quitar = async (asigId) => {
  await juecesApi.quitarAsignacion(asigId)
  if (a.value.proyecto_id) {
    const { data } = await juecesApi.asignacionesDeProyecto(a.value.proyecto_id)
    asignaciones.value = data
  }
  // refrescamos chips
  await load()
}

onMounted(load)
</script>
