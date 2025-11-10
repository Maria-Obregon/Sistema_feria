<!-- resources/js/pages/admin/AdminProyectos.vue -->
<template>
  <div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div class="flex items-center gap-3">
        <RouterLink
          :to="{ name: 'admin.dashboard' }"
          class="inline-flex items-center gap-2 px-3 py-2 border rounded-lg text-gray-700 hover:bg-gray-50"
        >
          <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Volver
        </RouterLink>

        <div>
          <h1 class="text-2xl font-bold text-gray-900">Proyectos</h1>
          <p class="text-gray-600">Listado general de proyectos registrados</p>
        </div>
      </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-sm border p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
          <div class="flex gap-2">
            <input
              v-model="f.buscar"
              type="text"
              placeholder="Título o resumen..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              @keyup.enter="aplicarBusqueda"
            />
            <button @click="aplicarBusqueda" class="px-3 py-2 bg-blue-600 text-white rounded-md">Filtrar</button>
            <button @click="limpiarBuscar" class="px-3 py-2 bg-gray-100 rounded-md">Limpiar</button>
          </div>
          <p class="text-xs text-gray-500 mt-1">
            Nota: el backend del índice soporta <code>?buscar=</code>. Los selects se aplican en cliente (sobre la
            página actual).
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Área</label>
          <select
            v-model="f.area_id"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option :value="''">Todas</option>
            <option v-for="a in form.areas" :key="a.id" :value="a.id">{{ a.nombre }}</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
          <select
            v-model="f.categoria_id"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option :value="''">Todas</option>
            <option v-for="c in categoriasFiltradas" :key="c.id" :value="c.id">
              {{ c.nombre }}
            </option>
          </select>
        </div>
      </div>
    </div>

    <!-- Tabla -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
      <div v-if="cargando" class="p-8 text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
        <p class="mt-2 text-gray-600">Cargando proyectos...</p>
      </div>

      <div v-else-if="proyectos.data?.length === 0" class="p-8 text-center text-gray-500">
        <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <p>Sin resultados</p>
      </div>

      <div v-else>
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Área</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Institución</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Feria</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="p in proyectosFiltrados" :key="p.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ p.codigo ?? `PRJ-${p.id}` }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <div class="font-medium">{{ p.titulo }}</div>
                <div v-if="p.resumen" class="text-xs text-gray-500 truncate max-w-[28rem]">{{ p.resumen }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ p.area?.nombre ?? '-' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ p.categoria?.nombre ?? '-' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ p.institucion?.nombre ?? '-' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <div>{{ etiquetaFeria(p) }}</div>
                <div v-if="p.etapa?.nombre" class="text-xs text-gray-500">{{ p.etapa.nombre }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                  :class="p.estado === 'inscrito'
                    ? 'bg-blue-100 text-blue-800'
                    : p.estado === 'clasificado'
                      ? 'bg-green-100 text-green-800'
                      : 'bg-gray-100 text-gray-800'"
                >
                  {{ (p.estado ?? '—').charAt(0).toUpperCase() + (p.estado ?? '—').slice(1) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">
                <!-- Acciones deshabilitadas por ahora -->
                <span class="text-gray-400">—</span>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Paginación -->
        <div v-if="proyectos.last_page > 1" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
              Página {{ proyectos.current_page }} de {{ proyectos.last_page }} · Mostrando
              {{ proyectos.from }}–{{ proyectos.to }} de {{ proyectos.total }}
            </div>
            <div class="flex gap-1">
              <button
                @click="cambiarPagina(proyectos.current_page - 1)"
                :disabled="!proyectos.prev_page_url"
                class="px-3 py-2 border rounded-l disabled:opacity-50"
              >
                Anterior
              </button>
              <button
                @click="cambiarPagina(proyectos.current_page + 1)"
                :disabled="!proyectos.next_page_url"
                class="px-3 py-2 border rounded-r disabled:opacity-50"
              >
                Siguiente
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal eliminar -->
    <div v-if="modalEliminar" class="fixed inset-0 bg-black/30 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg shadow-lg w-full max-w-md">
        <div class="px-6 py-4 border-b">
          <h3 class="text-lg font-semibold">Eliminar Proyecto</h3>
        </div>
        <div class="p-6">
          <p class="text-gray-700">
            ¿Eliminar el proyecto <strong>{{ proyectoAEliminar?.titulo }}</strong>? Esta acción no se puede deshacer.
          </p>
        </div>
        <div class="px-6 py-4 border-t flex justify-end gap-3">
          <button class="px-4 py-2 rounded border" @click="modalEliminar = false">Cancelar</button>
          <button class="px-4 py-2 rounded bg-red-600 text-white" @click="eliminarProyecto">Eliminar</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { proyectosApi } from '@/services/api'

// estado base de paginación
const cargando = ref(false)
const proyectos = ref({
  data: [],
  current_page: 1,
  last_page: 1,
  total: 0,
  from: 0,
  to: 0,
  prev_page_url: null,
  next_page_url: null,
})

const form = ref({
  areas: [],
  categorias: [],
})

const f = ref({
  buscar: '',
  area_id: '',
  categoria_id: '',
  page: 1,
})

const modalEliminar = ref(false)
const proyectoAEliminar = ref(null)

// etiqueta de feria
const etiquetaFeria = (p) => {
  if (p.feria?.anio && p.feria?.sede) return `${p.feria.anio} · ${p.feria.sede}`
  if (p.feria?.anio) return `${p.feria.anio}`
  return p.feria ? 'Feria' : '—'
}

// computed
const categoriasFiltradas = computed(() => form.value.categorias)

const proyectosFiltrados = computed(() => {
  let rows = proyectos.value.data || []
  if (f.value.area_id) {
    rows = rows.filter((r) => String(r.area_id) === String(f.value.area_id))
  }
  if (f.value.categoria_id) {
    rows = rows.filter((r) => String(r.categoria_id) === String(f.value.categoria_id))
  }
  return rows
})

// cargar catálogos del formulario
const cargarFormData = async () => {
  try {
    const { data } = await proyectosApi.formData()
    form.value.areas = data?.areas ?? []
    form.value.categorias = data?.categorias ?? []
  } catch (e) {
    console.error('Error form-data:', e?.response?.data ?? e)
  }
}

// cargar lista (paginada)
const cargarProyectos = async () => {
  try {
    cargando.value = true
    const { data } = await proyectosApi.list({
      buscar: f.value.buscar || undefined,
      page: f.value.page || 1,
    })
    // si backend retorna Laravel paginator estándar, data es el objeto completo
    proyectos.value = data ?? {
      data: [],
      current_page: 1,
      last_page: 1,
      total: 0,
      from: 0,
      to: 0,
      prev_page_url: null,
      next_page_url: null,
    }
  } catch (e) {
    console.error('Error listando proyectos:', e?.response?.data ?? e)
    proyectos.value = {
      data: [],
      current_page: 1,
      last_page: 1,
      total: 0,
      from: 0,
      to: 0,
      prev_page_url: null,
      next_page_url: null,
    }
  } finally {
    cargando.value = false
  }
}

// acciones UI
const aplicarBusqueda = () => {
  f.value.page = 1
  cargarProyectos()
}

const limpiarBuscar = () => {
  f.value.buscar = ''
  f.value.page = 1
  cargarProyectos()
}

const cambiarPagina = (n) => {
  if (n < 1 || n > (proyectos.value.last_page || 1)) return
  f.value.page = n
  cargarProyectos()
}

const eliminarProyecto = async () => {
  if (!proyectoAEliminar.value) return
  try {
    await proyectosApi.destroy(proyectoAEliminar.value.id)
    modalEliminar.value = false
    proyectoAEliminar.value = null
    cargarProyectos()
  } catch (e) {
    console.error('Error al eliminar:', e?.response?.data ?? e)
  }
}

onMounted(async () => {
  await Promise.all([cargarFormData(), cargarProyectos()])
})

// recarga cuando cambia la página (los selects filtran en cliente)
watch(
  () => f.value.page,
  () => cargarProyectos()
)
</script>
