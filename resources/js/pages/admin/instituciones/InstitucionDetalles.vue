<template>
  <div v-if="visible" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-4/5 lg:w-3/4 shadow-lg rounded-md bg-white">
      <div class="mt-3">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
          <div>
            <h3 class="text-xl font-bold text-gray-900">{{ institucion?.nombre }}</h3>
            <p class="text-sm text-gray-600">{{ institucion?.codigo_presupuestario }}</p>
          </div>
          <button
            @click="$emit('cerrar')"
            class="text-gray-400 hover:text-gray-600"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <div v-if="cargando" class="p-8 text-center">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
          <p class="mt-2 text-gray-600">Cargando detalles...</p>
        </div>

        <div v-else-if="detalles" class="space-y-6">
          <!-- Información Básica -->
          <div class="bg-gray-50 rounded-lg p-6">
            <h4 class="text-lg font-semibold text-gray-900 mb-4">Información Básica</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-500">Tipo</label>
                <span class="inline-flex px-2 py-1 text-sm font-semibold rounded-full mt-1"
                      :class="{
                        'bg-blue-100 text-blue-800': detalles.institucion.tipo === 'publica',
                        'bg-green-100 text-green-800': detalles.institucion.tipo === 'privada',
                        'bg-purple-100 text-purple-800': detalles.institucion.tipo === 'subvencionada'
                      }">
                  {{ detalles.institucion.tipo?.charAt(0).toUpperCase() + detalles.institucion.tipo?.slice(1) }}
                </span>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-500">Estado</label>
                <span class="inline-flex px-2 py-1 text-sm font-semibold rounded-full mt-1"
                      :class="detalles.institucion.activo 
                        ? 'bg-green-100 text-green-800' 
                        : 'bg-red-100 text-red-800'">
                  {{ detalles.institucion.activo ? 'Activa' : 'Inactiva' }}
                </span>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-500">Circuito</label>
                <p class="mt-1 text-sm text-gray-900">{{ detalles.institucion.circuito?.nombre }}</p>
                <p class="text-xs text-gray-500">{{ detalles.institucion.circuito?.regional?.nombre }}</p>
              </div>
            </div>
          </div>

          <!-- Contacto -->
          <div class="bg-white border rounded-lg p-6">
            <h4 class="text-lg font-semibold text-gray-900 mb-4">Información de Contacto</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div v-if="detalles.institucion.telefono">
                <label class="block text-sm font-medium text-gray-500">Teléfono</label>
                <p class="mt-1 text-sm text-gray-900">{{ detalles.institucion.telefono }}</p>
              </div>
              <div v-if="detalles.institucion.email">
                <label class="block text-sm font-medium text-gray-500">Email</label>
                <p class="mt-1 text-sm text-gray-900">{{ detalles.institucion.email }}</p>
              </div>
              <div v-if="detalles.institucion.direccion" class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-500">Dirección</label>
                <p class="mt-1 text-sm text-gray-900">{{ detalles.institucion.direccion }}</p>
              </div>
            </div>
          </div>

          <!-- Límites y Estadísticas -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Límites -->
            <div class="bg-white border rounded-lg p-6">
              <h4 class="text-lg font-semibold text-gray-900 mb-4">Límites Configurados</h4>
              <div class="space-y-4">
                <div class="flex justify-between items-center">
                  <span class="text-sm text-gray-600">Proyectos máximos</span>
                  <span class="text-sm font-medium text-gray-900">{{ detalles.institucion.limite_proyectos }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-sm text-gray-600">Estudiantes máximos</span>
                  <span class="text-sm font-medium text-gray-900">{{ detalles.institucion.limite_estudiantes }}</span>
                </div>
              </div>
            </div>

            <!-- Estadísticas -->
            <div class="bg-white border rounded-lg p-6">
              <h4 class="text-lg font-semibold text-gray-900 mb-4">Estadísticas Actuales</h4>
              <div class="space-y-4">
                <div class="flex justify-between items-center">
                  <span class="text-sm text-gray-600">Total usuarios</span>
                  <span class="text-sm font-medium text-gray-900">{{ detalles.estadisticas.total_usuarios }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-sm text-gray-600">Total proyectos</span>
                  <span class="text-sm font-medium text-gray-900">
                    {{ detalles.estadisticas.total_proyectos }} / {{ detalles.institucion.limite_proyectos }}
                  </span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-sm text-gray-600">Total estudiantes</span>
                  <span class="text-sm font-medium text-gray-900">
                    {{ detalles.estadisticas.total_estudiantes }} / {{ detalles.institucion.limite_estudiantes }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Proyectos por Estado -->
          <div v-if="Object.keys(detalles.estadisticas.proyectos_por_estado || {}).length > 0" 
               class="bg-white border rounded-lg p-6">
            <h4 class="text-lg font-semibold text-gray-900 mb-4">Proyectos por Estado</h4>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div v-for="(cantidad, estado) in detalles.estadisticas.proyectos_por_estado" 
                   :key="estado" 
                   class="text-center p-3 bg-gray-50 rounded-lg">
                <div class="text-2xl font-bold text-gray-900">{{ cantidad }}</div>
                <div class="text-sm text-gray-600 capitalize">{{ estado.replace('_', ' ') }}</div>
              </div>
            </div>
          </div>

          <!-- Proyectos por Etapa -->
          <div v-if="Object.keys(detalles.estadisticas.proyectos_por_etapa || {}).length > 0" 
               class="bg-white border rounded-lg p-6">
            <h4 class="text-lg font-semibold text-gray-900 mb-4">Proyectos por Etapa</h4>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
              <div v-for="(cantidad, etapa) in detalles.estadisticas.proyectos_por_etapa" 
                   :key="etapa" 
                   class="text-center p-3 bg-gray-50 rounded-lg">
                <div class="text-2xl font-bold text-gray-900">{{ cantidad }}</div>
                <div class="text-sm text-gray-600 capitalize">{{ etapa.replace('_', ' ') }}</div>
              </div>
            </div>
          </div>

          <!-- Fechas -->
          <div class="bg-gray-50 rounded-lg p-6">
            <h4 class="text-lg font-semibold text-gray-900 mb-4">Información del Sistema</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-500">Fecha de creación</label>
                <p class="mt-1 text-sm text-gray-900">
                  {{ formatearFecha(detalles.institucion.creado_en) }}
                </p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-500">Última actualización</label>
                <p class="mt-1 text-sm text-gray-900">
                  {{ formatearFecha(detalles.institucion.actualizado_en) }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Botón Cerrar -->
        <div class="flex justify-end pt-6 border-t mt-6">
          <button
            @click="$emit('cerrar')"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Cerrar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useToast } from '@/composables/useToast'
import { institucionesApi } from '@/services/api'

const props = defineProps({
  visible: {
    type: Boolean,
    default: false
  },
  institucion: {
    type: Object,
    default: null
  }
})

defineEmits(['cerrar'])

const { mostrarToast } = useToast()

// Estado
const cargando = ref(false)
const detalles = ref(null)

// Watchers
watch(() => props.visible, (nuevo) => {
  if (nuevo && props.institucion) {
    cargarDetalles()
  }
})

// Métodos
const cargarDetalles = async () => {
  try {
    cargando.value = true
    const response = await institucionesApi.obtener(props.institucion.id)
    detalles.value = response.data
  } catch (error) {
    console.error('Error al cargar detalles:', error)
    mostrarToast('Error al cargar los detalles de la institución', 'error')
  } finally {
    cargando.value = false
  }
}

const formatearFecha = (fecha) => {
  if (!fecha) return 'N/A'
  return new Date(fecha).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>
