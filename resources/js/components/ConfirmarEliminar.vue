<template>
  <div v-if="visible" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
      <div class="mt-3 text-center">
        <!-- Icono de advertencia -->
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
          <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
          </svg>
        </div>

        <!-- Título -->
        <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">
          {{ titulo }}
        </h3>

        <!-- Mensaje -->
        <div class="mt-2 px-7 py-3">
          <p class="text-sm text-gray-500">
            {{ mensaje }}
          </p>
        </div>

        <!-- Botones -->
        <div class="flex justify-center gap-3 px-4 py-3">
          <button
            @click="$emit('cancelar')"
            class="px-4 py-2 bg-white text-gray-800 text-sm font-medium rounded-md border border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300"
          >
            Cancelar
          </button>
          <button
            @click="$emit('confirmar')"
            class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
          >
            Eliminar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  visible: {
    type: Boolean,
    default: false
  },
  titulo: {
    type: String,
    default: 'Confirmar eliminación'
  },
  mensaje: {
    type: String,
    default: '¿Estás seguro de que deseas eliminar este elemento? Esta acción no se puede deshacer.'
  }
})

defineEmits(['confirmar', 'cancelar'])
</script>
