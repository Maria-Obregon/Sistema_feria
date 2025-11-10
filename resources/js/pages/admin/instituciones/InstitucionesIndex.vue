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
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
      </svg>
      Volver
    </RouterLink>

    <div>
      <h1 class="text-2xl font-bold text-gray-900">Gestión de Instituciones</h1>
      <p class="text-gray-600">Administra las instituciones del sistema</p>
    </div>
  </div>

  <button
    @click="abrirModal"
    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors"
  >
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
    </svg>
    Nueva Institución
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
            placeholder="Nombre o código..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            @input="buscarInstituciones"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
          <select
            v-model="filtros.tipo"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            @change="aplicarFiltros"
          >
            <option value="">Todos los tipos</option>
            <option value="publica">Pública</option>
            <option value="privada">Privada</option>
            <option value="subvencionada">Subvencionada</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Circuito</label>
          <select
            v-model="filtros.circuito_id"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            @change="aplicarFiltros"
          >
            <option value="">Todos los circuitos</option>
            <option v-for="circuito in circuitos" :key="circuito.id" :value="circuito.id">
              {{ circuito.nombre }} - {{ circuito.regional?.nombre }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
          <select
            v-model="filtros.activo"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            @change="aplicarFiltros"
          >
            <option value="">Todos</option>
            <option value="true">Activas</option>
            <option value="false">Inactivas</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Tabla -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
      <div v-if="cargando" class="p-8 text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
        <p class="mt-2 text-gray-600">Cargando instituciones...</p>
      </div>

      <div v-else-if="instituciones.data?.length === 0" class="p-8 text-center text-gray-500">
        <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
        </svg>
        <p>No se encontraron instituciones</p>
      </div>

      <div v-else>
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Institución</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Circuito</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Límites</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="institucion in instituciones.data" :key="institucion.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div>
                  <div class="text-sm font-medium text-gray-900">{{ institucion.nombre }}</div>
                  <div v-if="institucion.email" class="text-sm text-gray-500">{{ institucion.email }}</div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ institucion.codigo_presupuestario }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                      :class="{
                        'bg-blue-100 text-blue-800': institucion.tipo === 'publica',
                        'bg-green-100 text-green-800': institucion.tipo === 'privada',
                        'bg-purple-100 text-purple-800': institucion.tipo === 'subvencionada'
                      }">
                  {{ institucion.tipo.charAt(0).toUpperCase() + institucion.tipo.slice(1) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <div>{{ institucion.circuito?.nombre }}</div>
                <div class="text-xs text-gray-500">{{ institucion.circuito?.regional?.nombre }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <div>{{ institucion.limite_proyectos }} proyectos</div>
                <div class="text-xs text-gray-500">{{ institucion.limite_estudiantes }} estudiantes</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <button
                  @click="toggleActivo(institucion)"
                  class="inline-flex px-2 py-1 text-xs font-semibold rounded-full cursor-pointer"
                  :class="institucion.activo
                    ? 'bg-green-100 text-green-800 hover:bg-green-200'
                    : 'bg-red-100 text-red-800 hover:bg-red-200'"
                >
                  {{ institucion.activo ? 'Activa' : 'Inactiva' }}
                </button>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex justify-end gap-2">
                  <button @click="verDetalles(institucion)" class="text-blue-600 hover:text-blue-900" title="Ver detalles">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                  </button>
                  <button @click="editarInstitucion(institucion)" class="text-indigo-600 hover:text-indigo-900" title="Editar">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                  </button>
                  <button @click="confirmarEliminar(institucion)" class="text-red-600 hover:text-red-900" title="Eliminar">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Paginación -->
        <div v-if="instituciones.last_page > 1" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
          <div class="flex items-center justify-between">
            <div class="flex-1 flex justify-between sm:hidden">
              <button
                @click="cambiarPagina(instituciones.current_page - 1)"
                :disabled="!instituciones.prev_page_url"
                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
              >
                Anterior
              </button>
              <button
                @click="cambiarPagina(instituciones.current_page + 1)"
                :disabled="!instituciones.next_page_url"
                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
              >
                Siguiente
              </button>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
              <div>
                <p class="text-sm text-gray-700">
                  Mostrando
                  <span class="font-medium">{{ instituciones.from }}</span>
                  a
                  <span class="font-medium">{{ instituciones.to }}</span>
                  de
                  <span class="font-medium">{{ instituciones.total }}</span>
                  resultados
                </p>
              </div>
              <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                  <button
                    @click="cambiarPagina(instituciones.current_page - 1)"
                    :disabled="!instituciones.prev_page_url"
                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                  >
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                  </button>
                  <button
                    v-for="pagina in paginasVisibles"
                    :key="pagina"
                    @click="cambiarPagina(pagina)"
                    class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                    :class="pagina === instituciones.current_page
                      ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                      : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'"
                  >
                    {{ pagina }}
                  </button>
                  <button
                    @click="cambiarPagina(instituciones.current_page + 1)"
                    :disabled="!instituciones.next_page_url"
                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                  >
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                  </button>
                </nav>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Crear/Editar -->
    <div v-if="mostrarModal" class="mt-6 bg-white rounded-lg shadow-lg border">
      <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
        <h3 class="text-lg font-medium text-gray-900">
          {{ institucionSeleccionada ? 'Editar Institución' : 'Nueva Institución' }}
        </h3>
        <button @click="cerrarModal" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <div class="p-6">
        <form @submit.prevent="guardarInstitucion" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Nombre -->
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de la Institución *</label>
              <input
                v-model="formulario.nombre"
                type="text"
                required
                maxlength="200"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': errores.nombre }"
              />
              <p v-if="errores.nombre" class="mt-1 text-sm text-red-600">{{ errores.nombre[0] }}</p>
            </div>

            <!-- Código Presupuestario -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Código Presupuestario *</label>
              <input
                v-model="formulario.codigo_presupuestario"
                type="text"
                required
                maxlength="20"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': errores.codigo_presupuestario }"
              />
              <p v-if="errores.codigo_presupuestario" class="mt-1 text-sm text-red-600">{{ errores.codigo_presupuestario[0] }}</p>
            </div>

            <!-- Modalidad (NUEVO) -->
            <div>
  <label class="block text-sm font-medium text-gray-700 mb-1">Modalidad *</label>
  <select
    v-model="formulario.modalidad"
    required
    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
    :class="{ 'border-red-500': errores.modalidad }"
    :disabled="cargandoCatalogos"
  >
    <option value="">Seleccionar modalidad</option>
    <option
      v-for="m in modalidadesDB"
      :key="'db-mod-' + m.id"
      :value="m.nombre"
    >
      {{ m.nombre }}
    </option>
  </select>
  <p v-if="errores.modalidad" class="mt-1 text-sm text-red-600">{{ errores.modalidad[0] }}</p>
</div>


            <!-- Tipo -->
            <div>
  <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
  <select
    v-model="formulario.tipo"
    required
    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
    :class="{ 'border-red-500': errores.tipo }"
    :disabled="cargandoCatalogos"
  >
    <option value="">Seleccionar tipo</option>
    <option
      v-for="t in tiposInstitucionDB"
      :key="'db-tipo-' + t.id"
      :value="t.nombre"
    >
      {{ t.nombre }}
    </option>
  </select>
  <p v-if="errores.tipo" class="mt-1 text-sm text-red-600">{{ errores.tipo[0] }}</p>
</div>


            <!-- Circuito -->
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Circuito *</label>
              <select
                v-model="formulario.circuito_id"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': errores.circuito_id }"
              >
                <option value="">Seleccionar circuito</option>
                <option v-for="circuito in circuitos" :key="circuito.id" :value="circuito.id">
                  {{ circuito.nombre }} - {{ circuito.regional?.nombre }}
                </option>
              </select>
              <p v-if="errores.circuito_id" class="mt-1 text-sm text-red-600">{{ errores.circuito_id[0] }}</p>
            </div>

            <!-- Teléfono -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
              <input
                v-model="formulario.telefono"
                type="tel"
                maxlength="20"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': errores.telefono }"
              />
              <p v-if="errores.telefono" class="mt-1 text-sm text-red-600">{{ errores.telefono[0] }}</p>
            </div>

            <!-- Email -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
              <input
                v-model="formulario.email"
                type="email"
                maxlength="100"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': errores.email }"
              />
              <p v-if="errores.email" class="mt-1 text-sm text-red-600">{{ errores.email[0] }}</p>
            </div>

            <!-- Dirección -->
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
              <textarea
                v-model="formulario.direccion"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': errores.direccion }"
              ></textarea>
              <p v-if="errores.direccion" class="mt-1 text-sm text-red-600">{{ errores.direccion[0] }}</p>
            </div>

            <!-- Límites -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Límite de Proyectos</label>
              <input
                v-model.number="formulario.limite_proyectos"
                type="number"
                min="1" max="50"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': errores.limite_proyectos }"
              />
              <p class="mt-1 text-xs text-gray-500">Máximo 50 proyectos</p>
              <p v-if="errores.limite_proyectos" class="mt-1 text-sm text-red-600">{{ errores.limite_proyectos[0] }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Límite de Estudiantes</label>
              <input
                v-model.number="formulario.limite_estudiantes"
                type="number"
                min="1" max="200"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': errores.limite_estudiantes }"
              />
              <p class="mt-1 text-xs text-gray-500">Máximo 200 estudiantes</p>
              <p v-if="errores.limite_estudiantes" class="mt-1 text-sm text-red-600">{{ errores.limite_estudiantes[0] }}</p>
            </div>

            <!-- Estado -->
            <div class="md:col-span-2">
              <label class="flex items-center">
                <input
                  v-model="formulario.activo"
                  type="checkbox"
                  class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                />
                <span class="ml-2 text-sm text-gray-700">Institución activa</span>
              </label>
            </div>

            <!-- Botones -->
            <div class="md:col-span-2 flex justify-end gap-3 pt-6 border-t mt-8">
              <button
                type="button"
                @click="cerrarModal"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                Cancelar
              </button>
              <button
                type="submit"
                :disabled="guardando"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="guardando" class="flex items-center">
                  <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Guardando...
                </span>
                <span v-else>
                  {{ institucionSeleccionada ? 'Actualizar' : 'Crear' }} Institución
                </span>
              </button>
            </div>

          </div>
        </form>
      </div>
    </div>

    <!-- Modal Detalles -->
    <InstitucionDetalles
      v-if="mostrarModalDetalles"
      :visible="mostrarModalDetalles"
      :institucion="institucionSeleccionada"
      @cerrar="mostrarModalDetalles = false"
    />

    <!-- Modal Confirmación Eliminar -->
    <ConfirmarEliminar
      v-if="mostrarConfirmarEliminar"
      :visible="mostrarConfirmarEliminar"
      :titulo="'Eliminar Institución'"
      :mensaje="`¿Estás seguro de que deseas eliminar la institución '${institucionAEliminar?.nombre}'? Esta acción no se puede deshacer.`"
      @confirmar="eliminarInstitucion"
      @cancelar="mostrarConfirmarEliminar = false"
    />
  </div>
</template>


<script setup>
import { ref, onMounted, computed } from 'vue'
import { useToast } from '@/composables/useToast'
import { institucionesApi } from '@/services/api'
import InstitucionDetalles from './InstitucionDetalles.vue'
import ConfirmarEliminar from '@/components/ConfirmarEliminar.vue'

const { mostrarToast } = useToast()

// Estado
const cargando = ref(false)
const instituciones = ref({
  data: [],
  current_page: 1,
  last_page: 1,
  total: 0,
  from: 0,
  to: 0,
})

const circuitos = ref([])
const filtros = ref({
  buscar: '',
  tipo: '',
  circuito_id: '',
  activo: ''
})

// Modales
const mostrarModal = ref(false)
const mostrarModalDetalles = ref(false)
const mostrarConfirmarEliminar = ref(false)
const institucionSeleccionada = ref(null)
const institucionAEliminar = ref(null)

// Formulario
const guardando = ref(false)
const errores = ref({})
const formulario = ref({
  nombre: '',
  modalidad: '',              // <-- NUEVO (se mantiene como texto)
  codigo_presupuestario: '',
  circuito_id: '',
  tipo: '',
  telefono: '',
  email: '',
  direccion: '',
  limite_proyectos: 50,
  limite_estudiantes: 200,
  activo: true
})

// Búsqueda con debounce
let timeoutBusqueda = null
const buscarInstituciones = () => {
  clearTimeout(timeoutBusqueda)
  timeoutBusqueda = setTimeout(() => {
    cargarInstituciones()
  }, 500)
}

// Computed
const paginasVisibles = computed(() => {
  const paginas = []
  const actual = instituciones.value.current_page || 1
  const total = instituciones.value.last_page || 1
  
  let inicio = Math.max(1, actual - 2)
  let fin = Math.min(total, actual + 2)
  
  for (let i = inicio; i <= fin; i++) {
    paginas.push(i)
  }
  
  return paginas
})

// Métodos
const cargarInstituciones = async (pagina = 1) => {
  try {
    cargando.value = true
    const params = {
      page: pagina,
      per_page: 15,
      buscar: filtros.value.buscar || undefined,
      tipo: filtros.value.tipo || undefined,
      circuito_id: filtros.value.circuito_id || undefined,
      activo: filtros.value.activo || undefined,
    }

    const { data } = await institucionesApi.listar(params)

    // Normalización robusta (soporta arreglo plano o paginador Laravel)
    if (Array.isArray(data)) {
      instituciones.value = {
        data,
        current_page: 1,
        last_page: 1,
        total: data.length,
        from: data.length ? 1 : 0,
        to: data.length
      }
    } else if (Array.isArray(data?.data)) {
      instituciones.value = data
    } else if (Array.isArray(data?.data?.data)) {
      instituciones.value = data.data
    } else {
      console.warn('Formato inesperado en /instituciones:', data)
      instituciones.value = { data: [], current_page: 1, last_page: 1, total: 0, from: 0, to: 0 }
    }
  } catch (e) {
    console.error('Error al cargar instituciones:', e)
    mostrarToast('Error al cargar las instituciones', 'error')
  } finally {
    cargando.value = false
  }
}

/* =========================
   Catálogos desde BD (AGREGADO)
   ========================= */
const modalidadesDB = ref([])        // [{ id, nombre }]
const tiposInstitucionDB = ref([])   // [{ id, nombre }]
const cargandoCatalogos = ref(false)

const cargarCatalogos = async () => {
  cargandoCatalogos.value = true
  try {
    const { data } = await institucionesApi.obtenerCatalogos()
    modalidadesDB.value      = data?.modalidades ?? []
    tiposInstitucionDB.value = data?.tipos_institucion ?? []
  } catch (e) {
    // opcional: console.warn(e)
  } finally {
    cargandoCatalogos.value = false
  }
}

const cargarCircuitos = async () => {
  try {
    const response = await institucionesApi.obtenerCircuitos()
    circuitos.value = response.data
  } catch (error) {
    console.error('Error al cargar circuitos:', error)
    mostrarToast('Error al cargar los circuitos', 'error')
  }
}

const aplicarFiltros = () => {
  cargarInstituciones()
}

const cambiarPagina = (pagina) => {
  if (pagina >= 1 && pagina <= instituciones.value.last_page) {
    cargarInstituciones(pagina)
  }
}

const abrirModal = () => {
  resetearFormulario()
  institucionSeleccionada.value = null
  cargarCatalogos() // <-- AGREGADO
  mostrarModal.value = true
}

const editarInstitucion = (institucion) => {
  institucionSeleccionada.value = { ...institucion }
  cargarDatosFormulario(institucion)
  cargarCatalogos() // <-- AGREGADO
  mostrarModal.value = true
}

const cerrarModal = () => {
  mostrarModal.value = false
  institucionSeleccionada.value = null
  resetearFormulario()
}

const resetearFormulario = () => {
  formulario.value = {
    nombre: '',
    modalidad: '',                // <-- AGREGADO: resetea modalidad también
    codigo_presupuestario: '',
    circuito_id: '',
    tipo: '',
    telefono: '',
    email: '',
    direccion: '',
    limite_proyectos: 50,
    limite_estudiantes: 200,
    activo: true
  }
  errores.value = {}
}

const cargarDatosFormulario = (institucion) => {
  formulario.value = {
    nombre: institucion.nombre || '',
    codigo_presupuestario: institucion.codigo_presupuestario || '',
    circuito_id: institucion.circuito_id || '',
    modalidad: institucion.modalidad || '',
    tipo: institucion.tipo || '',
    telefono: institucion.telefono || '',
    email: institucion.email || '',
    direccion: institucion.direccion || '',
    limite_proyectos: institucion.limite_proyectos || 50,
    limite_estudiantes: institucion.limite_estudiantes || 200,
    activo: institucion.activo !== undefined ? institucion.activo : true
  }
}

const guardarInstitucion = async () => {
  try {
    guardando.value = true
    errores.value = {}

    console.log('Guardando institución con datos:', formulario.value)

    let response
    if (institucionSeleccionada.value) {
      console.log('Actualizando institución ID:', institucionSeleccionada.value.id)
      response = await institucionesApi.actualizar(institucionSeleccionada.value.id, formulario.value)
    } else {
      console.log('Creando nueva institución')
      response = await institucionesApi.crear(formulario.value)
    }

    console.log('Respuesta del servidor:', response.data)
    
    const mensaje = response.data.mensaje || 'Institución guardada exitosamente'
    mostrarToast(mensaje, 'success')
    
    cerrarModal()
    await cargarInstituciones()
    
    console.log('Institución guardada y lista actualizada')
  } catch (error) {
    console.error('Error completo al guardar institución:', error)
    console.error('Respuesta del error:', error.response?.data)
    
    if (error.response?.status === 422) {
      errores.value = error.response.data.errors || {}
      console.log('Errores de validación:', errores.value)
      mostrarToast('Por favor corrige los errores en el formulario', 'error')
    } else {
      const mensaje = error.response?.data?.mensaje || 'Error al guardar la institución'
      mostrarToast(mensaje, 'error')
    }
  } finally {
    guardando.value = false
  }
}

const verDetalles = (institucion) => {
  institucionSeleccionada.value = institucion
  mostrarModalDetalles.value = true
}

const confirmarEliminar = (institucion) => {
  institucionAEliminar.value = institucion
  mostrarConfirmarEliminar.value = true
}

const eliminarInstitucion = async () => {
  try {
    await institucionesApi.eliminar(institucionAEliminar.value.id)
    mostrarToast('Institución eliminada exitosamente', 'success')
    cargarInstituciones()
  } catch (error) {
    console.error('Error al eliminar institución:', error)
    const mensaje = error.response?.data?.mensaje || 'Error al eliminar la institución'
    mostrarToast(mensaje, 'error')
  } finally {
    mostrarConfirmarEliminar.value = false
    institucionAEliminar.value = null
  }
}

const toggleActivo = async (institucion) => {
  try {
    const response = await institucionesApi.toggleActivo(institucion.id)
    institucion.activo = response.data.activo
    mostrarToast(response.data.mensaje, 'success')
  } catch (error) {
    console.error('Error al cambiar estado:', error)
    mostrarToast('Error al cambiar el estado de la institución', 'error')
  }
}

const cerrarModales = () => {
  mostrarModalCrear.value = false
  mostrarModalEditar.value = false
  institucionSeleccionada.value = null
}

const manejarGuardado = () => {
  cerrarModales()
  cargarInstituciones()
}

// Lifecycle
onMounted(() => {
  cargarInstituciones()
  cargarCircuitos()
  cargarCatalogos() // <-- AGREGADO
})
</script>