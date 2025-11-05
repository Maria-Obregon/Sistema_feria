<template>
  <div class="max-w-7xl mx-auto px-4 py-6 space-y-4">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <h1 class="text-xl font-semibold text-gray-900">Instituciones</h1>
      <button @click="openNew" class="px-3 py-2 rounded bg-blue-600 text-white">
        Nueva institución
      </button>
    </div>

    <!-- Buscador -->
    <div class="flex items-center gap-2">
      <input v-model="buscar" @keyup.enter="load"
             class="border rounded px-3 py-2 w-full md:w-80"
             placeholder="Buscar por nombre/código…" />
      <button @click="load" class="px-3 py-2 rounded bg-slate-100">Buscar</button>
    </div>

    <!-- Tabla -->
    <div class="bg-white border rounded-lg overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50">
          <tr>
            <th class="px-3 py-2 text-left">Nombre</th>
            <th class="px-3 py-2 text-left">Código</th>
            <th class="px-3 py-2 text-left">Región</th>
            <th class="px-3 py-2 text-left">Circuito</th>
            <th class="px-3 py-2 text-left">Tipo</th>
            <th class="px-3 py-2 text-left">Modalidad</th>
            <th class="px-3 py-2 text-left">Estado</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="i in instituciones" :key="i.id" class="border-t">
            <td class="px-3 py-2">{{ i.nombre }}</td>
            <td class="px-3 py-2">{{ i.codigo_presupuestario || i.codigo || '-' }}</td>
            <td class="px-3 py-2">{{ i.circuito?.regional?.nombre || '-' }}</td>
            <td class="px-3 py-2">{{ i.circuito?.nombre  || '-' }}</td>
            <td class="px-3 py-2">{{ i.tipo || '-' }}</td>
            <td class="px-3 py-2">{{ i.modalidad || '-' }}</td>
            <td class="px-3 py-2">
              <span :class="i.activo ? 'text-green-700' : 'text-slate-500'">
                {{ i.activo ? 'Activa' : 'Inactiva' }}
              </span>
            </td>
          </tr>
          <tr v-if="!loading && !instituciones.length">
            <td colspan="7" class="px-3 py-6 text-center text-slate-500">Sin registros.</td>
          </tr>
          <tr v-if="loading">
            <td colspan="7" class="px-3 py-6 text-center text-slate-500">Cargando…</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Crear -->
    <div v-if="showNew" class="fixed inset-0 bg-black/30 grid place-items-center p-4 z-50">
      <form @submit.prevent="save"
            class="bg-white w-full max-w-3xl p-6 rounded shadow space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="font-medium">Registrar institución</h2>
          <button type="button" @click="closeNew" class="text-slate-500 hover:text-slate-700">✕</button>
        </div>

        <!-- Fila 1 -->
        <div class="grid md:grid-cols-3 gap-3">
          <div>
            <label class="block text-sm text-slate-600 mb-1">Dirección Regional</label>
            <select v-model="f.regional_id" class="border rounded px-3 py-2 w-full" required>
              <option value="">— Seleccione —</option>
              <option v-for="r in regionales" :key="r.id" :value="r.id">{{ r.nombre }}</option>
            </select>
          </div>

          <div>
            <label class="block text-sm text-slate-600 mb-1">Circuito</label>
            <select v-model="f.circuito_id" class="border rounded px-3 py-2 w-full" required>
              <option value="">— Seleccione —</option>
              <option v-for="c in circuitosFiltrados" :key="c.id" :value="c.id">
                {{ c.nombre }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm text-slate-600 mb-1">Código</label>
            <input v-model="f.codigo" class="border rounded px-3 py-2 w-full" placeholder="Ej. 07-001" required>
          </div>
        </div>

        <!-- Fila 2 -->
        <div class="grid md:grid-cols-2 gap-3">
          <div>
            <label class="block text-sm text-slate-600 mb-1">Institución</label>
            <input v-model="f.nombre" class="border rounded px-3 py-2 w-full" required>
          </div>

          <div>
            <label class="block text-sm text-slate-600 mb-1">Correo</label>
            <input v-model="f.email" type="email" class="border rounded px-3 py-2 w-full" placeholder="correo@ejemplo.com">
          </div>
        </div>

        <!-- Fila 3 -->
        <div class="grid md:grid-cols-3 gap-3">
          <div>
            <label class="block text-sm text-slate-600 mb-1">Teléfono</label>
            <input v-model="f.telefono" class="border rounded px-3 py-2 w-full" placeholder="0000-0000">
          </div>
          <div>
            <label class="block text-sm text-slate-600 mb-1">Modalidad</label>
            <select v-model="f.modalidad" class="border rounded px-3 py-2 w-full" required>
              <option value="">— Seleccione —</option>
              <option value="Primaria">Primaria</option>
              <option value="Secundaria">Secundaria</option>
              <option value="Técnica">Técnica</option>
            </select>
          </div>
          <div>
            <label class="block text-sm text-slate-600 mb-1">Tipo</label>
            <!-- OJO: values en minúscula para que valide con la API -->
            <select v-model="f.tipo" class="border rounded px-3 py-2 w-full" required>
              <option value="">— Seleccione —</option>
              <option value="publica">Pública</option>
              <option value="privada">Privada</option>
              <option value="subvencionada">Subvencionada</option>
            </select>
          </div>
        </div>

        <div class="flex justify-end gap-2">
          <button type="button" @click="closeNew" class="px-3 py-2 rounded bg-slate-100">Cancelar</button>
          <button :disabled="saving" class="px-3 py-2 rounded bg-blue-600 text-white">
            {{ saving ? 'Guardando…' : 'Guardar' }}
          </button>
        </div>

        <!-- Credenciales devueltas (una sola vez) -->
        <div v-if="cred" class="mt-4 p-3 rounded bg-green-50 text-sm">
          <div class="font-medium text-green-700">Institución creada.</div>
          <div class="mt-1">Usuario: <b>{{ cred.usuario }}</b></div>
          <div class="mt-1">Contraseña: <b class="font-mono">{{ cred.password }}</b></div>
        </div>

        <div v-if="errorMsg" class="mt-3 p-2 rounded bg-red-50 text-red-700 text-sm">
          {{ errorMsg }}
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { institucionesApi, catalogoApi } from '@/services/api'

const instituciones = ref([])
const regionales = ref([])
const circuitos = ref([])

const buscar = ref('')
const loading = ref(false)
const showNew = ref(false)
const saving = ref(false)
const errorMsg = ref('')
const cred = ref(null)

const f = ref({
  regional_id: '',
  circuito_id: '',
  nombre: '',
  codigo: '',
  telefono: '',
  email: '',
  modalidad: '',
  tipo: '', // publica | privada | subvencionada
})

const circuitosFiltrados = computed(() =>
  f.value.regional_id
    ? circuitos.value.filter(c => String(c.regional_id) === String(f.value.regional_id))
    : circuitos.value
)

const load = async () => {
  loading.value = true
  try {
    const { data } = await institucionesApi.listar({ buscar: buscar.value })
    instituciones.value = data.data || data
  } finally {
    loading.value = false
  }
}

const loadCatalogos = async () => {
  // Si tu endpoint real es /catalogo/regionales, ajusta catalogoApi.regionales() en services/api.
  const [regs, cirs] = await Promise.all([
    catalogoApi.regionales(),
    catalogoApi.circuitos(),
  ])
  regionales.value = regs.data
  circuitos.value  = cirs.data
}

const openNew = () => {
  cred.value = null
  errorMsg.value = ''
  showNew.value = true
}

const closeNew = () => {
  showNew.value = false
  f.value = { regional_id:'', circuito_id:'', nombre:'', codigo:'', telefono:'', email:'', modalidad:'', tipo:'' }
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
      direccion: null,
      modalidad: f.value.modalidad,                 // <-- IMPORTANTE
      tipo: (f.value.tipo || '').toLowerCase(),     // publica|privada|subvencionada
      // emitir_credenciales: true,
      // responsable_email: '...'
    }

    const { data } = await institucionesApi.crear(payload)
    cred.value = data?.credenciales ?? null
    showNew.value = false
    await load()
  } catch (e) {
    const res = e?.response
    const v = res?.data

    // Arma un mensaje útil: primero validaciones (422), luego message/error (500), por último e.message
    const validations = v?.errors
      ? Object.entries(v.errors)
          .map(([field, msgs]) => `${field}: ${[].concat(msgs).join(', ')}`)
          .join(' | ')
      : null

    errorMsg.value =
      validations ||
      v?.message ||
      v?.error ||
      e?.message ||
      'Error al registrar institución'

    // Log para dev: mira status y payload exacto en consola
    console.error('POST /api/instituciones error', {
      status: res?.status,
      data: v,
    })
  } finally {
    saving.value = false
  }
}


onMounted(async () => {
  await Promise.all([load(), loadCatalogos()])
})

watch(() => f.value.regional_id, () => {
  f.value.circuito_id = ''
})
</script>
