<template>
  <div class="p-6">
    <!-- Header con info de la feria -->
    <div class="flex justify-between items-center mb-6">
      <div class="flex items-center gap-3">
        <RouterLink
          :to="{ name: 'admin.ferias' }"
          class="inline-flex items-center gap-2 px-3 py-2 border rounded-lg text-gray-700 hover:bg-gray-50"
        >
          <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Volver a ferias
        </RouterLink>

        <div>
          <h1 class="text-2xl font-bold text-gray-900">
            Invitados de la feria {{ encabezado.anio }} · {{ encabezado.institucion || '—' }}
          </h1>
          <p class="text-gray-600">Gestione los invitados especiales y dedicados de esta feria.</p>
        </div>
      </div>

      <button
        @click="abrirModalCrear"
        class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Nuevo invitado
      </button>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-sm border p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
          <input
            v-model="filtros.buscar"
            type="text"
            placeholder="Nombre o institución..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
            @keyup.enter="cargarInvitados"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de invitación</label>
          <select
            v-model="filtros.tipo_invitacion"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
            @change="cargarInvitados"
          >
            <option value="">Todos</option>
            <option value="especial">Invitado especial</option>
            <option value="dedicado">Dedicado</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Tabla -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
      <div v-if="cargando" class="p-8 text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-600 mx-auto"></div>
        <p class="mt-2 text-gray-600">Cargando invitados...</p>
      </div>

      <div v-else-if="invitados.data?.length === 0" class="p-8 text-center text-gray-500">
        <p>No hay invitados registrados para esta feria.</p>
      </div>

      <div v-else>
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Institución</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Puesto</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="inv in invitados.data" :key="inv.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 text-sm text-gray-900">{{ inv.nombre }}</td>
              <td class="px-6 py-4 text-sm text-gray-900">{{ inv.institucion || '—' }}</td>
              <td class="px-6 py-4 text-sm text-gray-900">{{ inv.puesto || '—' }}</td>
              <td class="px-6 py-4 text-sm">
                <span
                  class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                  :class="inv.tipo_invitacion === 'dedicado'
                    ? 'bg-purple-100 text-purple-800'
                    : 'bg-blue-100 text-blue-800'"
                >
                  {{ inv.tipo_invitacion === 'dedicado' ? 'Dedicado' : 'Invitado especial' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex justify-end gap-3">
                  <!-- Editar -->
                  <button
                    @click="editarInvitado(inv)"
                    class="text-indigo-600 hover:text-indigo-900"
                    title="Editar invitado"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>

                  <!-- Carta -->
                  <button
                    @click="generarCarta(inv)"
                    class="text-sky-600 hover:text-sky-900"
                    title="Generar carta"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8m-2 9H5a2 2 0 01-2-2V7a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2z" />
                    </svg>
                  </button>

                  <!-- Carnet -->
                  <button
                    @click="generarCarnet(inv)"
                    class="text-emerald-600 hover:text-emerald-900"
                    title="Generar carnet"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 7a2 2 0 012-2h10a2 2 0 012 2v10a2 2 0 01-2 2H7a2 2 0 01-2-2z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 9h6m-6 4h3" />
                    </svg>
                  </button>

                  <!-- Eliminar -->
                  <button
                    @click="confirmarEliminar(inv)"
                    class="text-red-600 hover:text-red-900"
                    title="Eliminar invitado"
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
      </div>
    </div>

    <!-- Modal Crear / Editar invitado -->
    <div v-if="mostrarModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
      <div class="absolute inset-0" @click="cerrarModal"></div>

      <div class="relative bg-white rounded-lg shadow-lg border w-full max-w-3xl max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b flex justify-between items-center bg-gray-50">
          <h3 class="text-lg font-medium text-gray-900">
            {{ invitadoSeleccionado ? 'Editar invitado' : 'Nuevo invitado' }}
          </h3>
          <button @click="cerrarModal" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="p-6">
          <form @submit.prevent="guardarInvitado" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del invitado *</label>
                <input
                  v-model="form.nombre"
                  type="text"
                  required
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Institución</label>
                <input
                  v-model="form.institucion"
                  type="text"
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Puesto o profesión</label>
                <input
                  v-model="form.puesto"
                  type="text"
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de invitación *</label>
                <select
                  v-model="form.tipo_invitacion"
                  required
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
                >
                  <option value="">Seleccionar</option>
                  <option value="especial">Invitado especial</option>
                  <option value="dedicado">Dedicado</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cédula</label>
                <input
                  v-model="form.cedula"
                  type="text"
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Función</label>
                <input
                  v-model="form.funcion"
                  type="text"
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                <input
                  v-model="form.telefono"
                  type="text"
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
                <input
                  v-model="form.correo"
                  type="email"
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
                />
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Mensaje de agradecimiento</label>
                <textarea
                  v-model="form.mensaje_agradecimiento"
                  rows="3"
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
                ></textarea>
              </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t mt-4">
              <button
                type="button"
                @click="cerrarModal"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
              >
                Cancelar
              </button>
              <button
                type="submit"
                :disabled="guardando"
                class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-md hover:bg-emerald-700 disabled:opacity-50"
              >
                <span v-if="guardando">Guardando...</span>
                <span v-else>{{ invitadoSeleccionado ? 'Actualizar' : 'Crear' }} invitado</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { invitadosApi } from '@/services/api'
import { useToast } from '@/composables/useToast'


const route = useRoute()
const { mostrarToast } = useToast()

const feriaId = Number(route.params.feriaId)

const encabezado = ref({
  anio: route.query.anio || '',
  tipo_feria: route.query.tipo_feria || '',
  institucion: route.query.institucion || '',
})

const cargando = ref(false)
const invitados = ref({
  data: [],
  current_page: 1,
  last_page: 1,
  total: 0,
})

const filtros = ref({
  buscar: '',
  tipo_invitacion: '',
  page: 1,
})

const mostrarModal = ref(false)
const invitadoSeleccionado = ref(null)
const guardando = ref(false)

const form = ref({
  nombre: '',
  institucion: '',
  puesto: '',
  tipo_invitacion: '',
  cedula: '',
  sexo: '',
  funcion: '',
  telefono: '',
  correo: '',
  mensaje_agradecimiento: '',
})

const cargarInvitados = async () => {
  try {
    cargando.value = true
    const { data } = await invitadosApi.listar(feriaId, {
      buscar: filtros.value.buscar || undefined,
      tipo_invitacion: filtros.value.tipo_invitacion || undefined,
      page: filtros.value.page || 1,
    })
    invitados.value = data
  } catch (e) {
    console.error('Error cargando invitados', e?.response?.data ?? e)
    mostrarToast('Error al cargar los invitados', 'error')
  } finally {
    cargando.value = false
  }
}

const abrirModalCrear = () => {
  invitadoSeleccionado.value = null
  form.value = {
    nombre: '',
    institucion: '',
    puesto: '',
    tipo_invitacion: '',
    cedula: '',
    sexo: '',
    funcion: '',
    telefono: '',
    correo: '',
    mensaje_agradecimiento: '',
  }
  mostrarModal.value = true
}

const editarInvitado = (inv) => {
  invitadoSeleccionado.value = inv
  form.value = { ...inv }
  mostrarModal.value = true
}

const cerrarModal = () => {
  mostrarModal.value = false
  invitadoSeleccionado.value = null
}

const guardarInvitado = async () => {
  try {
    guardando.value = true
    if (invitadoSeleccionado.value) {
      await invitadosApi.actualizar(feriaId, invitadoSeleccionado.value.id, form.value)
      mostrarToast('Invitado actualizado', 'success')
    } else {
      await invitadosApi.crear(feriaId, form.value)
      mostrarToast('Invitado creado', 'success')
    }
    mostrarModal.value = false
    await cargarInvitados()
  } catch (e) {
    console.error(e)
    mostrarToast('Error al guardar el invitado', 'error')
  } finally {
    guardando.value = false
  }
}

const confirmarEliminar = async (inv) => {
  if (!confirm(`¿Eliminar al invitado "${inv.nombre}"?`)) return
  try {
    await invitadosApi.eliminar(feriaId, inv.id)
    mostrarToast('Invitado eliminado', 'success')
    cargarInvitados()
  } catch (e) {
    mostrarToast('No se pudo eliminar el invitado', 'error')
  }
}

const generarCarta = async (inv) => {
  try {
    await invitadosApi.generarCarta(inv.id, `Carta-${inv.nombre}.pdf`)
  } catch (e) {
    console.error(e)
    mostrarToast('No se pudo generar la carta', 'error')
  }
}

const generarCarnet = async (inv) => {
  try {
    await invitadosApi.generarCarnet(inv.id, `Carnet-${inv.nombre}.pdf`)
  } catch (e) {
    console.error(e)
    mostrarToast('No se pudo generar el carnet', 'error')
  }
}

onMounted(() => {
  cargarInvitados()
})
</script>
