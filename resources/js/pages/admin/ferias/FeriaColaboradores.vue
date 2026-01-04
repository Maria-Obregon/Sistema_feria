<template>
  <div class="p-6">
    <!-- Header -->
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
            Colaboradores de la feria {{ encabezado.anio }} · {{ encabezado.institucion || '—' }}
          </h1>
          <p class="text-gray-600">Gestione los colaboradores de esta feria.</p>
        </div>
      </div>

      <button
        @click="abrirModalCrear"
        class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Nuevo colaborador
      </button>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-sm border p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
          <input
            v-model="filtros.buscar"
            type="text"
            placeholder="Nombre, institución o función..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
            @keyup.enter="cargarColaboradores"
          />
        </div>
      </div>
    </div>

    <!-- Tabla -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
      <div v-if="cargando" class="p-8 text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-600 mx-auto"></div>
        <p class="mt-2 text-gray-600">Cargando colaboradores...</p>
      </div>

      <div v-else-if="colaboradores.data?.length === 0" class="p-8 text-center text-gray-500">
        <p>No hay colaboradores registrados para esta feria.</p>
      </div>

      <div v-else>
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Función</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Institución</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teléfono</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="col in colaboradores.data" :key="col.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 text-sm text-gray-900">{{ col.nombre }}</td>
              <td class="px-6 py-4 text-sm text-gray-900">{{ col.funcion || '—' }}</td>
              <td class="px-6 py-4 text-sm text-gray-900">{{ col.institucion || '—' }}</td>
              <td class="px-6 py-4 text-sm text-gray-900">{{ col.telefono || '—' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex justify-end gap-3">
                  <!-- Editar -->
                  <button
                    @click="editarColaborador(col)"
                    class="text-indigo-600 hover:text-indigo-900"
                    title="Editar colaborador"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>

                  <!-- Carta -->
                  <button
                    @click="generarCarta(col)"
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
                    @click="generarCarnet(col)"
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
                    @click="confirmarEliminar(col)"
                    class="text-red-600 hover:text-red-900"
                    title="Eliminar colaborador"
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

    <!-- Modal Crear/Editar -->
    <div v-if="mostrarModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
      <div class="absolute inset-0" @click="cerrarModal"></div>

      <div class="relative bg-white rounded-lg shadow-lg border w-full max-w-3xl max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b flex justify-between items-center bg-gray-50">
          <h3 class="text-lg font-medium text-gray-900">
            {{ colaboradorSeleccionado ? 'Editar colaborador' : 'Nuevo colaborador' }}
          </h3>
          <button @click="cerrarModal" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="p-6">
          <form @submit.prevent="guardarColaborador" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                <input
                  v-model="form.nombre"
                  type="text"
                  required
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
                />
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
                <label class="block text-sm font-medium text-gray-700 mb-1">Sexo</label>
                <select
                  v-model="form.sexo"
                  required
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
                >
                  <option value="">Seleccionar</option>
                  <option value="femenino">Femenino</option>
                  <option value="masculino">Masculino</option>
                  <option value="binario">No binario</option>
                  <option value="otro">Prefiero no especificar</option>
                </select>
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
                <label class="block text-sm font-medium text-gray-700 mb-1">Correo</label>
                <input
                  v-model="form.correo"
                  type="email"
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
                />
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Institución</label>
                <input
                  v-model="form.institucion"
                  type="text"
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
                <span v-else>{{ colaboradorSeleccionado ? 'Actualizar' : 'Crear' }} colaborador</span>
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
import { colaboradoresApi } from '@/services/api'
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
const colaboradores = ref({
  data: [],
  current_page: 1,
  last_page: 1,
  total: 0,
})

const filtros = ref({
  buscar: '',
  page: 1,
})

const mostrarModal = ref(false)
const colaboradorSeleccionado = ref(null)
const guardando = ref(false)

const form = ref({
  nombre: '',
  cedula: '',
  sexo: '',
  funcion: '',
  telefono: '',
  correo: '',
  institucion: '',
  mensaje_agradecimiento: '',
})

const cargarColaboradores = async () => {
  try {
    cargando.value = true
    const { data } = await colaboradoresApi.listar(feriaId, {
      buscar: filtros.value.buscar || undefined,
      page: filtros.value.page || 1,
    })
    colaboradores.value = data
  } catch (e) {
    console.error('Error cargando colaboradores', e?.response?.data ?? e)
    mostrarToast('Error al cargar los colaboradores', 'error')
  } finally {
    cargando.value = false
  }
}

const abrirModalCrear = () => {
  colaboradorSeleccionado.value = null
  form.value = {
    nombre: '',
    cedula: '',
    sexo: '',
    funcion: '',
    telefono: '',
    correo: '',
    institucion: '',
    mensaje_agradecimiento: '',
  }
  mostrarModal.value = true
}

const editarColaborador = (col) => {
  colaboradorSeleccionado.value = col
  form.value = { ...col }
  mostrarModal.value = true
}

const cerrarModal = () => {
  mostrarModal.value = false
  colaboradorSeleccionado.value = null
}

const guardarColaborador = async () => {
  try {
    guardando.value = true
    if (colaboradorSeleccionado.value) {
      await colaboradoresApi.actualizar(colaboradorSeleccionado.value.id, form.value)
      mostrarToast('Colaborador actualizado', 'success')
    } else {
      await colaboradoresApi.crear(feriaId, form.value)
      mostrarToast('Colaborador creado', 'success')
    }
    mostrarModal.value = false
    await cargarColaboradores()
  } catch (e) {
    console.error(e)
    mostrarToast('Error al guardar el colaborador', 'error')
  } finally {
    guardando.value = false
  }
}

const confirmarEliminar = async (col) => {
  if (!confirm(`¿Eliminar al colaborador "${col.nombre}"?`)) return
  try {
    await colaboradoresApi.eliminar(col.id)
    mostrarToast('Colaborador eliminado', 'success')
    cargarColaboradores()
  } catch (e) {
    mostrarToast('No se pudo eliminar el colaborador', 'error')
  }
}

const descargarBlob = (data, nombreArchivo) => {
  const url = window.URL.createObjectURL(new Blob([data]))
  const a = document.createElement('a')
  a.href = url
  a.download = nombreArchivo
  a.click()
  window.URL.revokeObjectURL(url)
}

const generarCarta = async (col) => {
  try {
    const { data } = await colaboradoresApi.carta(col.id)
    descargarBlob(data, `Carta-colaborador-${col.nombre}.pdf`)
  } catch (e) {
    console.error(e)
    mostrarToast('No se pudo generar la carta', 'error')
  }
}

const generarCarnet = async (col) => {
  try {
    const { data } = await colaboradoresApi.carnet(col.id)
    descargarBlob(data, `Carnet-colaborador-${col.nombre}.pdf`)
  } catch (e) {
    console.error(e)
    mostrarToast('No se pudo generar el carnet', 'error')
  }
}

onMounted(() => {
  cargarColaboradores()
})
</script>
