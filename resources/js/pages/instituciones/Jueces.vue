<template>
  <div class="max-w-7xl mx-auto px-4 py-6 space-y-5">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <h1 class="text-xl font-semibold text-gray-900">Jueces</h1>
      <button @click="abrirNuevo" class="px-3 py-2 rounded bg-blue-600 text-white">
        Nuevo juez
      </button>
    </div>

    <!-- Buscador -->
    <div class="flex items-center gap-2">
      <input v-model="buscar" @keyup.enter="loadJueces"
             class="border rounded px-3 py-2 w-full md:w-96"
             placeholder="Buscar por nombre, cédula o correo…" />
      <button @click="loadJueces" class="px-3 py-2 rounded bg-slate-100">Buscar</button>
    </div>

    <!-- Lista -->
    <div class="bg-white border rounded overflow-hidden">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50">
          <tr>
            <th class="px-3 py-2 text-left">Nombre</th>
            <th class="px-3 py-2 text-left">Cédula</th>
            <th class="px-3 py-2 text-left">Correo</th>
            <th class="px-3 py-2 text-left">Área</th>
            <th class="px-3 py-2 text-center">Asignaciones</th>
            <th class="px-3 py-2"></th>
          </tr>
        </thead>
        <tbody>
          <template v-for="j in jueces" :key="j.id">
            <!-- Fila juez -->
            <tr class="border-t">
              <td class="px-3 py-2">{{ j.nombre }}</td>
              <td class="px-3 py-2">{{ j.cedula || '—' }}</td>
              <td class="px-3 py-2">{{ j.correo || '—' }}</td>
              <td class="px-3 py-2">{{ j.area?.nombre || '—' }}</td>
              <td class="px-3 py-2 text-center">{{ (j.proyectos?.length ?? j.proyectos_count) || 0 }}</td>
              <td class="px-3 py-2 text-right space-x-2">
                <button class="px-3 py-1.5 rounded bg-indigo-600 text-white"
                        @click="abrirAsignar(j)">
                  Asignar proyecto
                </button>
                <button class="px-3 py-1.5 rounded bg-slate-100"
                        :disabled="(j.proyectos?.length ?? 0) === 0"
                        @click="toggle(j.id)">
                  {{ isOpen(j.id) ? 'Ocultar proyectos' : 'Ver proyectos' }}
                </button>
              </td>
            </tr>

            <!-- Subtabla proyectos (solo si hay y si está abierto) -->
            <tr v-if="isOpen(j.id) && (j.proyectos?.length ?? 0) > 0">
              <td colspan="6" class="bg-slate-50 px-3 py-3">
                <div class="overflow-x-auto">
                  <table class="min-w-full text-xs bg-white border rounded">
                    <thead class="bg-slate-100">
                      <tr>
                        <th class="px-3 py-2 text-left">Título</th>
                        <th class="px-3 py-2 text-left">Categoría</th>
                        <th class="px-3 py-2 text-left">Etapa</th>
                        <th class="px-3 py-2 text-left">Tipo</th>
                        <th class="px-3 py-2 text-right">Acciones</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="p in j.proyectos" :key="p.asig_id" class="border-t">
                        <td class="px-3 py-2">{{ p.titulo }}</td>
                        <td class="px-3 py-2">{{ p.categoria || '—' }}</td>
                        <td class="px-3 py-2">
                          <span class="inline-flex items-center rounded px-2 py-0.5 bg-slate-100">
                            {{ etapaLabel(p.etapa_id) }}
                          </span>
                        </td>
                        <td class="px-3 py-2">
                          <span class="inline-flex items-center rounded px-2 py-0.5"
                                :class="badgeTipo(p.tipo_eval)">
                            {{ tipoLabel(p.tipo_eval) }}
                          </span>
                        </td>
                        <td class="px-3 py-2 text-right">
                          <button class="px-2 py-1 rounded bg-red-100 text-red-700 hover:bg-red-200"
                                  @click="quitarAsignacion(p.asig_id, j.id)">
                            Quitar
                          </button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </td>
            </tr>
          </template>

          <tr v-if="!loading && !jueces.length">
            <td colspan="6" class="px-3 py-8 text-center text-slate-500">Sin resultados.</td>
          </tr>
          <tr v-if="loading">
            <td colspan="6" class="px-3 py-8 text-center text-slate-500">Cargando…</td>
          </tr>
        </tbody>
      </table>

      <div class="flex items-center justify-between px-3 py-2 border-t text-sm text-slate-600">
        <div>Página {{ pagActual }} / {{ totalPaginas }}</div>
        <div class="flex gap-2">
          <button class="px-2 py-1 rounded bg-slate-100" :disabled="pagActual<=1" @click="prevPage">←</button>
          <button class="px-2 py-1 rounded bg-slate-100" :disabled="pagActual>=totalPaginas" @click="nextPage">→</button>
        </div>
      </div>
    </div>

    <!-- Modal: Nuevo juez -->
    <div v-if="showNuevo" class="fixed inset-0 bg-black/30 grid place-items-center p-4 z-50">
      <form @submit.prevent="guardarJuez" class="bg-white w-full max-w-2xl p-6 rounded shadow space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="font-medium">Registrar juez</h2>
          <button type="button" @click="cerrarNuevo" class="text-slate-500 hover:text-slate-700">✕</button>
        </div>

        <div class="grid md:grid-cols-2 gap-3">
          <div>
            <label class="block text-sm mb-1">Nombre</label>
            <input v-model="fj.nombre" class="border rounded px-3 py-2 w-full" required />
          </div>
          <div>
            <label class="block text-sm mb-1">Cédula</label>
            <input v-model="fj.cedula" class="border rounded px-3 py-2 w-full" placeholder="0-0000-0000" required />
          </div>

          <div>
            <label class="block text-sm mb-1">Sexo</label>
            <select v-model="fj.sexo" class="border rounded px-3 py-2 w-full">
              <option value="">—</option>
              <option value="M">Masculino</option>
              <option value="F">Femenino</option>
              <option value="O">Otro</option>
            </select>
          </div>

          <div>
            <label class="block text-sm mb-1">Área</label>
            <select v-model="fj.area_id" class="border rounded px-3 py-2 w-full">
              <option value="">—</option>
              <option v-for="a in areas" :key="a.id" :value="a.id">{{ a.nombre }}</option>
            </select>
          </div>

          <div>
            <label class="block text-sm mb-1">Grado académico</label>
            <input v-model="fj.grado_academico" class="border rounded px-3 py-2 w-full" />
          </div>

          <div>
            <label class="block text-sm mb-1">Teléfono</label>
            <input v-model="fj.telefono" class="border rounded px-3 py-2 w-full" placeholder="0000-0000" />
          </div>

          <div class="md:col-span-2">
            <label class="block text-sm mb-1">Correo</label>
            <input v-model="fj.correo" type="email" class="border rounded px-3 py-2 w-full" />
          </div>
        </div>

        <div class="flex justify-end gap-2">
          <button type="button" @click="cerrarNuevo" class="px-3 py-2 rounded bg-slate-100">Cancelar</button>
          <button :disabled="guardandoJuez" class="px-3 py-2 rounded bg-blue-600 text-white">
            {{ guardandoJuez ? 'Guardando…' : 'Guardar juez' }}
          </button>
        </div>

        <div v-if="errorJuez" class="text-sm text-red-700">{{ errorJuez }}</div>
      </form>
    </div>

    <!-- Modal: Asignar proyecto -->
    <div v-if="showAsignar" class="fixed inset-0 bg-black/30 grid place-items-center p-4 z-50">
      <div class="bg-white w-full max-w-3xl p-6 rounded shadow space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="font-medium">Asignar proyecto a {{ juezSel?.nombre }}</h2>
          <button type="button" @click="cerrarAsignar" class="text-slate-500 hover:text-slate-700">✕</button>
        </div>

        <div class="grid md:grid-cols-3 gap-3">
          <div>
            <label class="block text-sm mb-1">Etapa</label>
            <select v-model="fa.etapa_id" class="border rounded px-3 py-2 w-full">
              <option :value="1">Institucional</option>
              <option :value="2">Circuital</option>
              <option :value="3">Regional</option>
            </select>
          </div>
          <div>
            <label class="block text-sm mb-1">Tipo de evaluación</label>
            <select v-model="fa.tipo_eval" class="border rounded px-3 py-2 w-full">
              <option value="integral">Integral</option>
              <option value="escrito">Escrito</option>
              <option value="exposicion">Exposición</option>
            </select>
          </div>
          <div>
            <label class="block text-sm mb-1">Buscar proyecto</label>
            <input v-model="buscarProyecto" @keyup.enter="loadProyectos"
                   class="border rounded px-3 py-2 w-full" placeholder="Título…" />
          </div>
        </div>

        <div class="border rounded max-h-72 overflow-y-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-slate-50">
              <tr>
                <th class="px-3 py-2 text-left">Título</th>
                <th class="px-3 py-2 text-left">Área</th>
                <th class="px-3 py-2 text-left">Categoría</th>
                <th class="px-3 py-2"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="p in proyectos" :key="p.id" class="border-t">
                <td class="px-3 py-2">{{ p.titulo }}</td>
                <td class="px-3 py-2">{{ p.area?.nombre || '—' }}</td>
                <td class="px-3 py-2">{{ p.categoria?.nombre || '—' }}</td>
                <td class="px-3 py-2 text-right">
                  <button class="px-2 py-1 rounded bg-indigo-600 text-white"
                          @click="asignar(p)">
                    Asignar
                  </button>
                </td>
              </tr>
              <tr v-if="!cargandoProy && !proyectos.length">
                <td colspan="4" class="px-3 py-6 text-center text-slate-500">Sin proyectos.</td>
              </tr>
              <tr v-if="cargandoProy">
                <td colspan="4" class="px-3 py-6 text-center text-slate-500">Cargando…</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="flex justify-between items-center text-sm text-slate-600">
          <div>Página {{ pagProy }} / {{ totalPaginasProy }}</div>
          <div class="flex gap-2">
            <button class="px-2 py-1 rounded bg-slate-100" :disabled="pagProy<=1" @click="prevProy">←</button>
            <button class="px-2 py-1 rounded bg-slate-100" :disabled="pagProy>=totalPaginasProy" @click="nextProy">→</button>
          </div>
        </div>

        <div v-if="errorAsig" class="text-sm text-red-700">{{ errorAsig }}</div>
        <div v-if="okAsig" class="text-sm text-green-700">{{ okAsig }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { juecesApi, proyectosApi } from '@/services/api'
import api from '@/services/api'

// ===== util badges/labels
const etapaLabel = (id) => ({1:'Institucional',2:'Circuital',3:'Regional'}[id] || `Etapa ${id}`)
const tipoLabel  = (t) => ({escrito:'Escrito', exposicion:'Exposición', integral:'Integral'}[t] || t)
const badgeTipo  = (t) => ({
  escrito: 'bg-amber-100 text-amber-800',
  exposicion: 'bg-indigo-100 text-indigo-800',
  integral: 'bg-emerald-100 text-emerald-800',
}[t] || 'bg-slate-100 text-slate-800')

// ===== Estado lista jueces =====
const buscar = ref('')
const jueces = ref([])
const loading = ref(false)
const pagActual = ref(1)
const totalPaginas = ref(1)
const perPage = 10
const abiertos = ref(new Set())
const isOpen = (id) => abiertos.value.has(id)
const toggle = (id) => { isOpen(id) ? abiertos.value.delete(id) : abiertos.value.add(id) }

// ===== Nuevo juez =====
const showNuevo = ref(false)
const guardandoJuez = ref(false)
const errorJuez = ref('')
const fj = ref({
  nombre: '', cedula: '', sexo: '', area_id: '', grado_academico: '',
  telefono: '', correo: ''
})
const areas = ref([])

// ===== Asignar proyecto =====
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

// ------- Carga de jueces
const loadJueces = async () => {
  loading.value = true
  try {
    const { data } = await juecesApi.listar({
      buscar: buscar.value || undefined,
      page: pagActual.value,
      per_page: perPage,
      con_proyectos: true, // <-- para traer proyectos asignados
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

// ------- Nuevo juez
const abrirNuevo = async () => {
  errorJuez.value = ''
  showNuevo.value = true
  try {
    const { data } = await api.get('/areas') // ajusta si tu endpoint difiere
    areas.value = data?.data || data || []
  } catch { areas.value = [] }
}
const cerrarNuevo = () => {
  showNuevo.value = false
  fj.value = { nombre:'', cedula:'', sexo:'', area_id:'', grado_academico:'', telefono:'', correo:'' }
  errorJuez.value = ''
}
const guardarJuez = async () => {
  guardandoJuez.value = true
  errorJuez.value = ''
  try {
    await juecesApi.crear({ ...fj.value })
    cerrarNuevo()
    await loadJueces()
  } catch (e) {
    const v = e?.response?.data
    errorJuez.value = v?.message || (v?.errors && Object.values(v.errors).flat().join(' | ')) || e.message
  } finally {
    guardandoJuez.value = false
  }
}

// ------- Asignar proyecto
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
    const { data } = await proyectosApi.listar({
      buscar: buscarProyecto.value || undefined,
      page: pagProy.value,
      per_page: 8
    })
    proyectos.value = data?.data ?? data
    pagProy.value = data?.current_page ?? 1
    totalPaginasProy.value = data?.last_page ?? 1
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
    await proyectosApi.asignarJueces(p.id, {
      etapa_id: Number(fa.value.etapa_id),
      tipo_eval: fa.value.tipo_eval,
      jueces: [{ id: juezSel.value.id }]
    })
    okAsig.value = 'Juez asignado correctamente'
    // refresca la lista (mantén la fila abierta)
    const keepOpen = new Set(abiertos.value)
    await loadJueces()
    abiertos.value = keepOpen
  } catch (e) {
    errorAsig.value = e?.response?.data?.message || e.message || 'Error al asignar'
  }
}

const quitarAsignacion = async (asigId, juezId) => {
  if (!confirm('¿Quitar esta asignación?')) return
  await fetch(`/api/asignaciones-jueces/${asigId}`, {
    method: 'DELETE',
    headers: {
      'Accept':'application/json',
      'Content-Type':'application/json',
      'Authorization': 'Bearer ' + localStorage.getItem('auth_token')
    }
  })
  const keepOpen = new Set(abiertos.value)
  await loadJueces()
  abiertos.value = keepOpen
  abiertos.value.add(juezId)
}

onMounted(loadJueces)
</script>
