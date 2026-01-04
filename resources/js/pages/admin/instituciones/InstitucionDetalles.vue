<template>
  <div
    v-if="visible && institucion"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
  >
    <!-- Cerrar al hacer clic afuera -->
    <div class="absolute inset-0" @click="cerrar"></div>

    <!-- Cuadro de detalles -->
    <div
      class="relative bg-white rounded-lg shadow-lg border w-full max-w-3xl max-h-[90vh] overflow-y-auto"
    >
      <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
        <h3 class="text-lg font-medium text-gray-900">
          Detalles de la institución
        </h3>
        <button @click="cerrar" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <div class="p-6 space-y-6">
        <!-- Información general -->
        <div>
          <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">
            Información general
          </h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
              <p class="text-gray-500">Nombre</p>
              <p class="font-medium text-gray-900">{{ institucion.nombre }}</p>
            </div>
            <div>
              <p class="text-gray-500">Código presupuestario</p>
              <p class="font-medium text-gray-900">{{ institucion.codigo_presupuestario }}</p>
            </div>
            <div>
              <p class="text-gray-500">Modalidad</p>
              <p class="font-medium text-gray-900">{{ institucion.modalidad }}</p>
            </div>
            <div>
              <p class="text-gray-500">Tipo</p>
              <p class="font-medium text-gray-900">
                {{ institucion.tipo
                    ? institucion.tipo.charAt(0).toUpperCase() + institucion.tipo.slice(1)
                    : '-' }}
              </p>
            </div>
          </div>
        </div>

        <!-- Ubicación -->
        <div>
          <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">
            Ubicación académica
          </h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
              <p class="text-gray-500">Circuito</p>
              <p class="font-medium text-gray-900">
                {{ institucion.circuito?.nombre || '-' }}
              </p>
            </div>
            <div>
              <p class="text-gray-500">Dirección Regional</p>
              <p class="font-medium text-gray-900">
                {{ institucion.circuito?.regional?.nombre || '-' }}
              </p>
            </div>
          </div>
        </div>

        <!-- Contacto -->
        <div>
          <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">
            Contacto
          </h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
              <p class="text-gray-500">Teléfono</p>
              <p class="font-medium text-gray-900">{{ institucion.telefono || '-' }}</p>
            </div>
            <div>
              <p class="text-gray-500">Correo electrónico</p>
              <p class="font-medium text-gray-900">{{ institucion.email || '-' }}</p>
            </div>
            <div class="md:col-span-2">
              <p class="text-gray-500">Dirección</p>
              <p class="font-medium text-gray-900 whitespace-pre-line">
                {{ institucion.direccion || '-' }}
              </p>
            </div>
          </div>
        </div>

        <!-- Límites y estado -->
        <div>
          <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">
            Configuración
          </h4>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm items-center">
            <div>
              <p class="text-gray-500">Límite de proyectos</p>
              <p class="font-medium text-gray-900">
                {{ institucion.limite_proyectos }} proyectos
              </p>
            </div>
            <div>
              <p class="text-gray-500">Límite de estudiantes</p>
              <p class="font-medium text-gray-900">
                {{ institucion.limite_estudiantes }} estudiantes
              </p>
            </div>
            <div class="flex md:justify-end">
              <span
                class="inline-flex px-3 py-1 text-xs font-semibold rounded-full"
                :class="institucion.activo
                  ? 'bg-green-100 text-green-800'
                  : 'bg-red-100 text-red-800'"
              >
                {{ institucion.activo ? 'Activa' : 'Inactiva' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Botón cerrar -->
        <div class="flex justify-end pt-4 border-t border-gray-200">
          <button
            type="button"
            @click="cerrar"
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

const emit = defineEmits(['cerrar'])

const cerrar = () => {
  emit('cerrar')
}
</script>
