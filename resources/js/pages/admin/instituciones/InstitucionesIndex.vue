<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Topbar simple -->
    <header class="bg-white border-b">
      <div class="max-w-7xl mx-auto px-4 h-14 flex items-center justify-between">
        <nav class="flex gap-5">
          <RouterLink :to="homeTarget" class="text-sm text-gray-600 hover:text-gray-900">
            Inicio
          </RouterLink>
          <span class="text-sm text-gray-900 font-medium">Instituciones</span>
        </nav>

        <button @click="openNew" class="px-3 py-2 rounded bg-blue-600 text-white">
          Nueva institución
        </button>
      </div>
    </header>

    <!-- Contenido -->
    <main class="max-w-7xl mx-auto px-4 py-6 space-y-4">
      <div class="flex items-center gap-2">
        <input v-model="buscar" @keyup.enter="load"
               placeholder="Buscar por nombre o código…" class="border rounded px-3 py-2 w-full md:w-96" />
        <button @click="load" class="px-3 py-2 rounded bg-slate-100">Buscar</button>
      </div>

      <div class="bg-white border rounded-lg overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-slate-50">
            <tr>
              <th class="px-3 py-2 text-left">Nombre</th>
              <th class="px-3 py-2 text-left">Código</th>
              <th class="px-3 py-2 text-left">Regional</th>
              <th class="px-3 py-2 text-left">Circuito</th>
              <th class="px-3 py-2 text-left">Responsable</th>
              <th class="px-3 py-2 text-left">Estado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="i in instituciones" :key="i.id" class="border-t">
              <td class="px-3 py-2">{{ i.nombre }}</td>
              <td class="px-3 py-2">{{ i.codigo_presupuestario || '—' }}</td>
              <td class="px-3 py-2">{{ i?.circuito?.regional?.nombre || '—' }}</td>
              <td class="px-3 py-2">{{ i?.circuito?.nombre || '—' }}</td>
              <td class="px-3 py-2">{{ i?.responsable_email || '—' }}</td>
              <td class="px-3 py-2">
                <span :class="i.activo ? 'text-green-700' : 'text-slate-500'">
                  {{ i.activo ? 'Activa' : 'Inactiva' }}
                </span>
              </td>
            </tr>

            <tr v-if="!loading && !instituciones.length">
              <td colspan="6" class="px-3 py-6 text-center text-slate-500">Sin registros.</td>
            </tr>
            <tr v-if="loading">
              <td colspan="6" class="px-3 py-6 text-center text-slate-500">Cargando…</td>
            </tr>
          </tbody>
        </table>
      </div>
    </main>

    <!-- Modal crear institución -->
    <div v-if="showNew" class="fixed inset-0 bg-black/30 grid place-items-center p-4 z-50">
      <form @submit.prevent="save" class="bg-white w-full max-w-3xl p-6 rounded shadow space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="font-medium">Registrar nueva institución</h2>
          <button type="button" @click="closeNew" class="text-slate-500 hover:text-slate-700">✕</button>
        </div>

        <div class="grid md:grid-cols-2 gap-3">
          <div>
            <label class="block text-sm mb-1">Dirección Regional</label>
            <select v-model="f.regional_id" @change="filtrarPorRegional" class="border rounded px-3 py-2 w-full" required>
              <option value="">— Seleccione —</option>
              <option v-for="r in regionales" :key="r.id" :value="r.id">{{ r.nombre }}</option>
            </select>
          </div>

          <div>
            <label class="block text-sm mb-1">Circuito</label>
            <select v-model="f.circuito_id" class="border rounded px-3 py-2 w-full" required>
              <option value="">— Seleccione —</option>
              <option v-for="c in circuitosFiltrados" :key="c.id" :value="c.id">
                {{ c.codigo }} — {{ c.nombre }}
              </option>
            </select>
          </div>

          <div class="md:col-span-2">
            <label class="block text-sm mb-1">Institución</label>
            <input v-model="f.nombre" class="border rounded px-3 py-2 w-full" required />
          </div>

          <div>
            <label class="block text-sm mb-1">Código</label>
            <input v-model="f.codigo_presupuestario" class="border rounded px-3 py-2 w-full" />
          </div>

          <div>
            <label class="block text-sm mb-1">Tipo</label>
            <select v-model="f.tipo" class="border rounded px-3 py-2 w-full">
              <option value="publica">Pública</option>
              <option value="privada">Privada</option>
            </select>
          </div>

          <div>
            <label class="block text-sm mb-1">Modalidad</label>
            <select v-model="f.modalidad" class="border rounded px-3 py-2 w-full">
              <option value="Académica">Académica</option>
              <option value="Técnica">Técnica</option>
              <option value="Nocturna">Nocturna</option>
            </select>
          </div>

          <div>
            <label class="block text-sm mb-1">Teléfono</label>
            <input v-model="f.telefono" class="border rounded px-3 py-2 w-full" />
          </div>

          <div>
            <label class="block text-sm mb-1">Correo responsable</label>
            <input v-model="f.email" type="email" class="border rounded px-3 py-2 w-full" required />
          </div>
        </div>

        <div class="flex justify-end gap-2">
          <button type="button" @click="closeNew" class="px-3 py-2 rounded bg-slate-100">Cancelar</button>
          <button :disabled="saving" class="px-3 py-2 rounded bg-blue-600 text-white">
            {{ saving ? 'Guardando…' : 'Guardar' }}
          </button>
        </div>

        <!-- Credenciales devueltas -->
        <div v-if="cred" class="mt-4 p-3 rounded bg-green-50 text-sm">
          <div class="font-medium text-green-700">Institución creada.</div>
          <div class="mt-1">Usuario responsable: <b>{{ cred.usuario }}</b></div>
          <div class="mt-1">Contraseña (mostrar una vez): <b class="font-mono">{{ cred.password }}</b></div>
          <div class="mt-2 text-slate-600">Guarde estas credenciales y entréguelo a la institución.</div>
        </div>

        <div v-if="errorMsg" class="mt-3 p-2 rounded bg-red-50 text-red-700 text-sm">
          {{ errorMsg }}
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { institucionesApi, institucionesComiteApi } from '@/services/api'

const route = useRoute()
const router = useRouter()
const auth  = useAuthStore()

// ¿Estamos en /comite/instituciones?
const esComite = computed(() => route.name?.toString().startsWith('comite.'))
// Elegimos API según contexto
const api = computed(() => esComite.value ? institucionesComiteApi : institucionesApi)
// Link “Inicio” según rol
const homeTarget = computed(() => {
  const role = auth.primaryRole || localStorage.getItem('role')
  if (role === 'admin') return { name: 'admin.dashboard' }
  if (role === 'comite_institucional' || role === 'coordinador_circuito' || role === 'coordinador_regional') {
    return { name: 'comite.instituciones' }
  }
  return { name: 'dashboard' }
})

const instituciones = ref([])
const loading = ref(false)
const buscar = ref('')

// selects
const circuitos = ref([])
const regionales = ref([])
const circuitosFiltrados = ref([])

// modal
const showNew = ref(false)
const saving  = ref(false)
const errorMsg = ref('')
const cred = ref(null)

const f = ref({
  regional_id: '',
  circuito_id: '',
  nombre: '',
  codigo_presupuestario: '',
  modalidad: 'Académica',
  tipo: 'publica',
  telefono: '',
  email: '',
})

const load = async () => {
  loading.value = true
  try {
    const { data } = await api.value.listar({ buscar: buscar.value })
    instituciones.value = data.data || data
  } finally {
    loading.value = false
  }
}

const loadCircuitos = async () => {
  const { data } = await api.value.obtenerCircuitos()
  circuitos.value = data
  // Derivar “regionales” únicos a partir de circuitos
  const seen = new Map()
  data.forEach(c => {
    if (c.regional && !seen.has(c.regional.id)) {
      seen.set(c.regional.id, { id: c.regional.id, nombre: c.regional.nombre })
    }
  })
  regionales.value = Array.from(seen.values())
}

const filtrarPorRegional = () => {
  circuitosFiltrados.value = circuitos.value.filter(c => c.regional_id === Number(f.value.regional_id))
  f.value.circuito_id = ''
}

const openNew = async () => {
  errorMsg.value = ''
  cred.value = null
  showNew.value = true
  if (!circuitos.value.length) await loadCircuitos()
}

const closeNew = () => {
  showNew.value = false
  f.value = {
    regional_id: '', circuito_id: '', nombre: '',
    codigo_presupuestario: '', modalidad: 'Académica',
    tipo: 'publica', telefono: '', email: ''
  }
  cred.value = null
  errorMsg.value = ''
}

const save = async () => {
  try {
    saving.value = true
    errorMsg.value = ''
    const { data } = await api.value.crear({ ...f.value })
    // Se espera que backend retorne { institucion, usuario_email/usuario, plain_password }
    cred.value = {
      usuario: data.usuario_email || data.usuario || data?.user?.email,
      password: data.plain_password || data.password
    }
    await load()
  } catch (e) {
    errorMsg.value = e?.response?.data?.message || 'Error al crear institución'
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  await load()
  await loadCircuitos()
})
</script>
