<template>
  <div class="max-w-7xl mx-auto px-4 py-6 space-y-4">
    <div class="flex items-center justify-between">
      <h1 class="text-xl font-semibold text-gray-900">Proyectos</h1>
      <button @click="openNew" class="px-3 py-2 rounded bg-blue-600 text-white">Nuevo</button>
    </div>

    <div class="flex items-center gap-2">
      <input v-model="buscar" @keyup.enter="load" placeholder="Buscar título…" class="border rounded px-3 py-2 w-full md:w-80" />
      <button @click="load" class="px-3 py-2 rounded bg-slate-100">Buscar</button>
    </div>

    <div class="bg-white border rounded-lg overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50">
          <tr>
            <th class="px-3 py-2 text-left">Título</th>
            <th class="px-3 py-2 text-left">Área</th>
            <th class="px-3 py-2 text-left">Categoría</th>
            <th class="px-3 py-2 text-left">Estudiantes</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in proyectos" :key="p.id" class="border-t">
            <td class="px-3 py-2">{{ p.titulo }}</td>
            <td class="px-3 py-2">{{ p.area?.nombre }}</td>
            <td class="px-3 py-2">{{ p.categoria?.nombre }}</td>
            <td class="px-3 py-2">
              <span v-for="e in p.estudiantes" :key="e.id" class="inline-block mr-2">
                {{ e.nombre }} {{ e.apellidos }}
              </span>
            </td>
          </tr>
          <tr v-if="!loading && !proyectos.length">
            <td colspan="4" class="px-3 py-6 text-center text-slate-500">Sin registros.</td>
          </tr>
          <tr v-if="loading">
            <td colspan="4" class="px-3 py-6 text-center text-slate-500">Cargando…</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Crear -->
    <div v-if="showNew" class="fixed inset-0 bg-black/30 grid place-items-center p-4 z-50">
      <form @submit.prevent="save" class="bg-white w-full max-w-3xl p-6 rounded shadow space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="font-medium">Nuevo proyecto</h2>
          <button type="button" @click="closeNew" class="text-slate-500 hover:text-slate-700">✕</button>
        </div>

        <div class="grid md:grid-cols-2 gap-3">
          <input v-model="f.titulo" class="border rounded px-3 py-2 md:col-span-2" placeholder="Título del proyecto" required>
          <textarea v-model="f.resumen" class="border rounded px-3 py-2 md:col-span-2" rows="3" placeholder="Resumen (máx. 250 palabras)"></textarea>

          <!-- Área -->
          <div>
            <label class="block text-sm mb-1">Área</label>
            <select v-model="f.area_id" class="border rounded px-3 py-2 w-full" required>
              <option value="">— Seleccione —</option>
              <option v-for="a in form.areas" :key="a.id" :value="a.id">{{ a.nombre }}</option>
            </select>
          </div>

          <!-- Categoría -->
          <div>
            <label class="block text-sm mb-1">Categoría</label>
            <select v-model="f.categoria_id" class="border rounded px-3 py-2 w-full" required>
              <option value="">— Seleccione —</option>
              <option v-for="c in form.categorias" :key="c.id" :value="c.id">
                {{ c.nombre }} <span v-if="c.nivel">({{ c.nivel }})</span>
              </option>
            </select>
          </div>

          <!-- Feria -->
          <div class="md:col-span-2">
            <label class="block text-sm mb-1">Feria</label>
            <select v-model="f.feria_id" class="border rounded px-3 py-2 w-full" required>
              <option value="">— Seleccione —</option>
              <option v-for="fe in form.ferias" :key="fe.id" :value="fe.id">
                {{ fe.nombre }} — {{ fe.tipo || fe.nivel || '' }}
              </option>
            </select>
          </div>
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
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { proyectosApi } from '@/services/api'

const auth = useAuthStore()

const proyectos = ref([])
const buscar    = ref('')
const loading   = ref(false)

const showNew  = ref(false)
const saving   = ref(false)
const errorMsg = ref('')

// Estado del formulario y catálogos
const f = ref({
  titulo: '',
  resumen: '',
  area_id: '',
  categoria_id: '',
  feria_id: '',
  estudiantes: [],           // <- ids
})
const form = ref({ areas: [], categorias: [], ferias: [], estudiantes: [] })

const load = async () => {
  loading.value = true
  if (!auth.user) await auth.fetchMe()
  const instId = auth.user?.institucion?.id || auth.user?.institucion_id
  const { data } = await proyectosApi.listar({ institucion_id: instId, buscar: buscar.value })
  proyectos.value = data.data || data
  loading.value = false
}

const openNew = async () => {
  errorMsg.value = ''
  showNew.value = true
  try {
    if (!auth.user) await auth.fetchMe()
    const instId = auth.user?.institucion?.id || auth.user?.institucion_id
    const { data } = await proyectosApi.formData({ institucion_id: instId })
    form.value = {
      areas: data?.areas ?? [],
      categorias: data?.categorias ?? [],
      ferias: data?.ferias ?? [],
      estudiantes: data?.estudiantes ?? []
    }
  } catch (e) {
    // Muestra el motivo real en la UI y consola
    showNew.value = false
    const msg = e?.response?.data?.message || e?.message || 'No se pudo cargar el formulario'
    errorMsg.value = msg
    console.error('GET /api/proyectos/form-data', {
      status: e?.response?.status,
      data: e?.response?.data
    })
    alert(`Error al cargar el formulario: ${msg}`) // opcional, pero útil
  }
}


const closeNew = () => {
  showNew.value = false
  f.value = { titulo:'', resumen:'', area_id:'', categoria_id:'', feria_id:'', estudiantes:[] }
  errorMsg.value = ''
}

const save = async () => {
  try {
    saving.value = true
    errorMsg.value = ''

    if (!auth.user) await auth.fetchMe()
    const instId = auth.user?.institucion?.id || auth.user?.institucion_id

    const payload = { ...f.value, institucion_id: instId || undefined }
    const { data } = await proyectosApi.crear(payload)

    closeNew()
    await load()
  } catch (e) {
    if (e?.response?.status === 422 && e.response.data?.errors) {
      errorMsg.value = Object.values(e.response.data.errors).flat().join(' | ')
    } else {
      errorMsg.value = e?.response?.data?.message || e?.message || 'Error al crear proyecto'
    }
  } finally {
    saving.value = false
  }
}

onMounted(load)
</script>
