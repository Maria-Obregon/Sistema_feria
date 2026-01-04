<!-- resources/js/pages/admin/AdminFerias.vue -->
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
          <h1 class="text-2xl font-bold text-gray-900">Ferias de Ciencia y Tecnología</h1>
          <p class="text-gray-600">Gestión general de ferias institucionales, circuitales y regionales</p>
        </div>
      </div>

      <button
        @click="abrirModalCrear"
        class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Nueva Feria
      </button>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-sm border p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
          <input
            v-model="filtros.buscar"
            type="text"
            placeholder="Año o institución..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
            @keyup.enter="cargarFerias"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Año</label>
          <input
            v-model="filtros.anio"
            type="number"
            min="2000"
            max="2100"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
            @change="cargarFerias"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de feria</label>
          <select
            v-model="filtros.tipo_feria"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
            @change="cargarFerias"
          >
            <option value="">Todas</option>
            <option value="institucional">Institucional</option>
            <option value="circuital">Circuital</option>
            <option value="regional">Regional</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
          <select
            v-model="filtros.estado"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
            @change="cargarFerias"
          >
            <option value="">Todos</option>
            <option value="borrador">Borrador</option>
            <option value="activa">Activa</option>
            <option value="finalizada">Finalizada</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Tabla -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
      <div v-if="cargando" class="p-8 text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600 mx-auto"></div>
        <p class="mt-2 text-gray-600">Cargando ferias...</p>
      </div>

      <div v-else-if="ferias.data?.length === 0" class="p-8 text-center text-gray-500">
        <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <p>No hay ferias registradas con esos filtros.</p>
      </div>

      <div v-else>
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Año</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Institución / Sede
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Circuito</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="feria in ferias.data" :key="feria.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ feria.anio }}</td>

              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <span
                  class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                  :class="{
                    'bg-blue-100 text-blue-800'   : feria.tipo_feria === 'institucional',
                    'bg-green-100 text-green-800' : feria.tipo_feria === 'circuital',
                    'bg-purple-100 text-purple-800': feria.tipo_feria === 'regional'
                  }"
                >
                  {{ etiquetaTipo(feria.tipo_feria) }}
                </span>
              </td>

              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <div>{{ feria.institucion?.nombre ?? '—' }}</div>
                <div v-if="feria.sede" class="text-xs text-gray-500">Sede: {{ feria.sede }}</div>
              </td>

              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ feria.circuito?.nombre ?? '—' }}
              </td>

              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <span v-if="feria.fecha">{{ formatFecha(feria.fecha) }}</span>
                <span v-else>—</span>
              </td>

              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                  :class="{
                    'bg-gray-100 text-gray-800'  : feria.estado === 'borrador',
                    'bg-green-100 text-green-800': feria.estado === 'activa',
                    'bg-red-100 text-red-800'    : feria.estado === 'finalizada'
                  }"
                >
                  {{ feria.estado.charAt(0).toUpperCase() + feria.estado.slice(1) }}
                </span>
              </td>

              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex justify-end gap-3">
                  <!-- Invitados de la feria -->
                  <RouterLink
                    :to="{
                      name: 'admin.ferias.invitados',
                      params: { feriaId: feria.id },
                      query: {
                        anio: feria.anio,
                        tipo_feria: feria.tipo_feria,
                        institucion: feria.institucion?.nombre || ''
                      }
                    }"
                    class="text-emerald-600 hover:text-emerald-900"
                    title="Invitados de esta feria"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20v-2a4 4 0 00-3-3.87M9 11a4 4 0 100-8 4 4 0 000 8zm6 0a4 4 0 100-8 4 4 0 000 8zm-6 2a4 4 0 00-4 4v2m10-6a4 4 0 014 4v2" />
                    </svg>
                  </RouterLink>

                  <!-- Colaboradores -->
                  <button
                    @click="irColaboradores(feria)"
                    class="text-emerald-600 hover:text-emerald-900"
                    title="Colaboradores"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20v-2a4 4 0 00-3-3.87M9 14.13A4 4 0 006 18v2m6-14a3 3 0 110 6 3 3 0 010-6zm-4 3a3 3 0 11-6 0 3 3 0 016 0zm14 3a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                  </button>

                  <!-- Ver detalles (solo lectura) -->
                  <button
                    @click="verDetalles(feria)"
                    class="text-blue-600 hover:text-blue-900"
                    title="Ver detalles"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>

                  <!-- Editar -->
                  <button
                    @click="editarFeria(feria)"
                    class="text-indigo-600 hover:text-indigo-900"
                    title="Editar feria"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>

                  <!-- Eliminar -->
                  <button
                    @click="confirmarEliminar(feria)"
                    class="text-red-600 hover:text-red-900"
                    title="Eliminar feria"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Paginación simple -->
        <div v-if="ferias.last_page > 1" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
              Página {{ ferias.current_page }} de {{ ferias.last_page }} · Mostrando
              {{ ferias.from }}–{{ ferias.to }} de {{ ferias.total }}
            </div>
            <div class="flex gap-1">
              <button
                @click="cambiarPagina(ferias.current_page - 1)"
                :disabled="!ferias.prev_page_url"
                class="px-3 py-2 border rounded-l disabled:opacity-50"
              >
                Anterior
              </button>
              <button
                @click="cambiarPagina(ferias.current_page + 1)"
                :disabled="!ferias.next_page_url"
                class="px-3 py-2 border rounded-r disabled:opacity-50"
              >
                Siguiente
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Crear / Editar / Detalles -->
    <div
      v-if="mostrarModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
    >
      <div class="absolute inset-0" @click="cerrarModal"></div>

      <div class="relative bg-white rounded-lg shadow-lg border w-full max-w-4xl max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b flex justify-between items-center bg-gray-50">
          <h3 class="text-lg font-medium text-gray-900">
            {{ soloLectura ? 'Detalles de la Feria' : (feriaSeleccionada ? 'Editar Feria' : 'Nueva Feria') }}
          </h3>
          <button @click="cerrarModal" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <div class="p-6">
          <form @submit.prevent="guardarFeria" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Año -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Año *</label>
                <input
                  v-model.number="formulario.anio"
                  type="number"
                  min="2000"
                  max="2100"
                  required
                  :disabled="soloLectura"
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                  :class="{ 'border-red-500': errores.anio }"
                />
                <p v-if="errores.anio" class="mt-1 text-sm text-red-600">{{ errores.anio[0] }}</p>
              </div>

              <!-- Tipo de feria -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de feria *</label>
                <select
                  v-model="formulario.tipo_feria"
                  required
                  :disabled="soloLectura"
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                  :class="{ 'border-red-500': errores.tipo_feria }"
                >
                  <option value="">Seleccionar</option>
                  <option value="institucional">Institucional</option>
                  <option value="circuital">Circuital</option>
                  <option value="regional">Regional</option>
                </select>
                <p v-if="errores.tipo_feria" class="mt-1 text-sm text-red-600">{{ errores.tipo_feria[0] }}</p>
              </div>

              <!-- Institución / sede -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Institución / Sede *</label>
                <select
                  v-model="formulario.institucion_id"
                  required
                  :disabled="soloLectura"
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                  :class="{ 'border-red-500': errores.institucion_id }"
                >
                  <option value="">Seleccionar institución</option>
                  <option v-for="i in instituciones" :key="i.id" :value="i.id">
                    {{ i.nombre }}
                  </option>
                </select>
                <p v-if="errores.institucion_id" class="mt-1 text-sm text-red-600">{{ errores.institucion_id[0] }}</p>
              </div>

              <!-- Circuito -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Circuito</label>
                <select
                  v-model="formulario.circuito_id"
                  :disabled="soloLectura"
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                  :class="{ 'border-red-500': errores.circuito_id }"
                >
                  <option value="">(Opcional)</option>
                  <option v-for="c in circuitos" :key="c.id" :value="c.id">
                    {{ c.nombre }}
                  </option>
                </select>
                <p v-if="errores.circuito_id" class="mt-1 text-sm text-red-600">{{ errores.circuito_id[0] }}</p>
              </div>

              <!-- Fecha y hora -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de realización</label>
                <input
                  v-model="formulario.fecha"
                  type="date"
                  :disabled="soloLectura"
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                  :class="{ 'border-red-500': errores.fecha }"
                />
                <p v-if="errores.fecha" class="mt-1 text-sm text-red-600">{{ errores.fecha[0] }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Hora de inicio</label>
                <input
                  v-model="formulario.hora_inicio"
                  type="time"
                  :disabled="soloLectura"
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                  :class="{ 'border-red-500': errores.hora_inicio }"
                />
                <p v-if="errores.hora_inicio" class="mt-1 text-sm text-red-600">{{ errores.hora_inicio[0] }}</p>
              </div>

              <!-- Proyectos por aula -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Proyectos por aula</label>
                <input
                  v-model.number="formulario.proyectos_por_aula"
                  type="number"
                  min="0"
                  :disabled="soloLectura"
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                  :class="{ 'border-red-500': errores.proyectos_por_aula }"
                />
                <p v-if="errores.proyectos_por_aula" class="mt-1 text-sm text-red-600">
                  {{ errores.proyectos_por_aula[0] }}
                </p>
              </div>

              <!-- Lugar / sede -->
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Lugar de realización / Sede</label>
                <input
                  v-model="formulario.lugar_realizacion"
                  type="text"
                  :disabled="soloLectura"
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                  :class="{ 'border-red-500': errores.lugar_realizacion }"
                />
                <p v-if="errores.lugar_realizacion" class="mt-1 text-sm text-red-600">
                  {{ errores.lugar_realizacion[0] }}
                </p>
              </div>

              <!-- Correo y teléfono -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Correo para notificaciones</label>
                <input
                  v-model="formulario.correo_notif"
                  type="email"
                  :disabled="soloLectura"
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                  :class="{ 'border-red-500': errores.correo_notif }"
                />
                <p v-if="errores.correo_notif" class="mt-1 text-sm text-red-600">
                  {{ errores.correo_notif[0] }}
                </p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono o fax</label>
                <input
                  v-model="formulario.telefono_fax"
                  type="text"
                  :disabled="soloLectura"
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                  :class="{ 'border-red-500': errores.telefono_fax }"
                />
                <p v-if="errores.telefono_fax" class="mt-1 text-sm text-red-600">
                  {{ errores.telefono_fax[0] }}
                </p>
              </div>

              <!-- Estado -->
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Estado *</label>
                <select
                  v-model="formulario.estado"
                  required
                  :disabled="soloLectura"
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                  :class="{ 'border-red-500': errores.estado }"
                >
                  <option value="borrador">Borrador</option>
                  <option value="activa">Activa</option>
                  <option value="finalizada">Finalizada</option>
                </select>
                <p v-if="errores.estado" class="mt-1 text-sm text-red-600">{{ errores.estado[0] }}</p>
              </div>

              <!-- Botones -->
              <div class="md:col-span-2 flex justify-end gap-3 pt-6 border-t mt-8">
                <button
                  type="button"
                  @click="cerrarModal"
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                >
                  {{ soloLectura ? 'Cerrar' : 'Cancelar' }}
                </button>
                <button
                  v-if="!soloLectura"
                  type="submit"
                  :disabled="guardando"
                  class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-md hover:bg-purple-700 disabled:opacity-50"
                >
                  <span v-if="guardando">Guardando...</span>
                  <span v-else>{{ feriaSeleccionada ? 'Actualizar' : 'Crear' }} Feria</span>
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { feriasApi } from '@/services/api'
import { useToast } from '@/composables/useToast'

const router = useRouter()
const { mostrarToast } = useToast()

const cargando = ref(false)
const ferias = ref({
  data: [],
  current_page: 1,
  last_page: 1,
  total: 0,
  from: 0,
  to: 0,
  prev_page_url: null,
  next_page_url: null,
})

const filtros = ref({
  buscar: '',
  anio: '',
  tipo_feria: '',
  estado: '',
  page: 1,
})

const instituciones = ref([])
const circuitos = ref([])

const mostrarModal = ref(false)
const feriaSeleccionada = ref(null)
const guardando = ref(false)
const errores = ref({})
const soloLectura = ref(false)

const formulario = ref({
  anio: new Date().getFullYear(),
  institucion_id: '',
  circuito_id: '',
  fecha: '',
  hora_inicio: '',
  sede: '',
  proyectos_por_aula: 0,
  tipo_feria: '',
  correo_notif: '',
  telefono_fax: '',
  lugar_realizacion: '',
  estado: 'borrador',
})

const irColaboradores = (feria) => {
  router.push({
    name: 'admin.ferias.colaboradores',
    params: { feriaId: feria.id },
    query: {
      anio: feria.anio,
      tipo_feria: feria.tipo_feria,
      institucion: feria.institucion?.nombre ?? '',
    },
  })
}

const etiquetaTipo = (t) => {
  if (t === 'institucional') return 'Institucional'
  if (t === 'circuital') return 'Circuital'
  if (t === 'regional') return 'Regional'
  return t ?? '—'
}

// Evita el desfase de un día usando solo la parte de fecha
const formatFecha = (f) => {
  if (!f) return ''
  const str = String(f).substring(0, 10) // 2025-12-18
  const [y, m, d] = str.split('-')
  if (!y || !m || !d) return f
  return `${d}/${m}/${y}`
}

// Normaliza HH:MM:SS -> HH:MM para que cumpla date_format:H:i
const normalizarHora = (hora) => {
  if (!hora) return ''
  const str = String(hora)
  if (str.length === 5 && str.includes(':')) {
    return str
  }
  return str.slice(0, 5)
}

const cargarFormData = async () => {
  try {
    const { data } = await feriasApi.formData()
    instituciones.value = data.instituciones ?? []
    circuitos.value = data.circuitos ?? []
  } catch (e) {
    console.error('Error cargando form-data de ferias', e?.response?.data ?? e)
  }
}

const cargarFerias = async () => {
  try {
    cargando.value = true
    const { data } = await feriasApi.list({
      buscar: filtros.value.buscar || undefined,
      anio: filtros.value.anio || undefined,
      tipo_feria: filtros.value.tipo_feria || undefined,
      estado: filtros.value.estado || undefined,
      page: filtros.value.page || 1,
    })
    ferias.value = data
  } catch (e) {
    console.error('Error cargando ferias', e?.response?.data ?? e)
  } finally {
    cargando.value = false
  }
}

const cambiarPagina = (page) => {
  if (page < 1 || page > (ferias.value.last_page || 1)) return
  filtros.value.page = page
  cargarFerias()
}

const resetFormulario = () => {
  formulario.value = {
    anio: new Date().getFullYear(),
    institucion_id: '',
    circuito_id: '',
    fecha: '',
    hora_inicio: '',
    sede: '',
    proyectos_por_aula: 0,
    tipo_feria: '',
    correo_notif: '',
    telefono_fax: '',
    lugar_realizacion: '',
    estado: 'borrador',
  }
}

const cargarEnFormulario = (feria) => {
  formulario.value = {
    anio: feria.anio,
    institucion_id: feria.institucion_id,
    circuito_id: feria.circuito_id,
    fecha: feria.fecha ?? '',
    hora_inicio: normalizarHora(feria.hora_inicio ?? ''),
    sede: feria.sede ?? '',
    proyectos_por_aula: feria.proyectos_por_aula ?? 0,
    tipo_feria: feria.tipo_feria,
    correo_notif: feria.correo_notif ?? '',
    telefono_fax: feria.telefono_fax ?? '',
    lugar_realizacion: feria.lugar_realizacion ?? '',
    estado: feria.estado ?? 'borrador',
  }
}

const abrirModalCrear = () => {
  feriaSeleccionada.value = null
  soloLectura.value = false
  errores.value = {}
  resetFormulario()
  mostrarModal.value = true
}

const editarFeria = (feria) => {
  feriaSeleccionada.value = feria
  soloLectura.value = false
  errores.value = {}
  cargarEnFormulario(feria)
  mostrarModal.value = true
}

const verDetalles = (feria) => {
  feriaSeleccionada.value = feria
  soloLectura.value = true
  errores.value = {}
  cargarEnFormulario(feria)
  mostrarModal.value = true
}

const cerrarModal = () => {
  mostrarModal.value = false
  feriaSeleccionada.value = null
  soloLectura.value = false
}

const guardarFeria = async () => {
  try {
    if (soloLectura.value) return // por si acaso

    guardando.value = true
    errores.value = {}

    const payload = {
      ...formulario.value,
      hora_inicio: normalizarHora(formulario.value.hora_inicio),
    }

    let res
    if (feriaSeleccionada.value) {
      res = await feriasApi.update(feriaSeleccionada.value.id, payload)
    } else {
      res = await feriasApi.create(payload)
    }

    mostrarToast(res.data.message || 'Feria guardada correctamente', 'success')
    mostrarModal.value = false
    await cargarFerias()
  } catch (e) {
    if (e.response?.status === 422) {
      errores.value = e.response.data.errors || {}
      mostrarToast('Corrige los errores del formulario', 'error')
    } else {
      mostrarToast('Error al guardar la feria', 'error')
    }
  } finally {
    guardando.value = false
  }
}

const confirmarEliminar = async (feria) => {
  if (!confirm(`¿Eliminar la feria ${feria.anio} (${etiquetaTipo(feria.tipo_feria)})?`)) return
  try {
    await feriasApi.destroy(feria.id)
    mostrarToast('Feria eliminada', 'success')
    cargarFerias()
  } catch (e) {
    mostrarToast('No se pudo eliminar la feria', 'error')
  }
}

onMounted(async () => {
  await cargarFormData()
  await cargarFerias()
})
</script>
