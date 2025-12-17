<!-- resources/js/pages/admin/reportes/AdminReportesEstadisticas.vue -->
<template>
  <div class="p-6 space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Informe de participación</h1>
        <p class="text-gray-600">
          Resumen de participantes de la feria seleccionada.
        </p>
        <p class="text-xs text-gray-400 mt-1">
          Feria ID: {{ feriaId || '—' }}
        </p>
      </div>

      <RouterLink
        :to="{ name: 'admin.reportes' }"
        class="inline-flex items-center gap-2 px-3 py-2 border rounded-lg text-gray-700 hover:bg-gray-50"
      >
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Volver a reportes
      </RouterLink>
    </div>

    <div v-if="!feriaId" class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded">
      No se recibió el ID de la feria. Vuelve a la pantalla anterior y selecciona una feria.
    </div>

    <div v-else>
      <!-- Resumen rápido -->
      <div class="bg-white rounded-lg shadow-sm border p-5 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Resumen numérico</h2>

        <div v-if="cargando" class="text-sm text-gray-500">
          Cargando estadísticas…
        </div>

        <div v-else class="grid grid-cols-1 sm:grid-cols-4 gap-4">
          <div class="border rounded-lg p-4 text-center">
            <div class="text-3xl font-bold mb-1">{{ resumen.invitados }}</div>
            <div class="text-sm text-gray-600">Invitados</div>
          </div>
          <div class="border rounded-lg p-4 text-center">
            <div class="text-3xl font-bold mb-1">{{ resumen.colaboradores }}</div>
            <div class="text-sm text-gray-600">Colaboradores</div>
          </div>
          <div class="border rounded-lg p-4 text-center">
            <div class="text-3xl font-bold mb-1">{{ resumen.jueces }}</div>
            <div class="text-sm text-gray-600">Jueces</div>
          </div>
          <div class="border rounded-lg p-4 text-center">
            <div class="text-3xl font-bold mb-1">{{ resumen.estudiantes }}</div>
            <div class="text-sm text-gray-600">Estudiantes</div>
          </div>
        </div>
      </div>

      <!-- Aquí luego se puede poner un gráfico tipo barra -->
      <div class="bg-white rounded-lg shadow-sm border p-5">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-lg font-semibold text-gray-900">Gráfico de participantes</h2>

          <!-- Botón para descargar informe PDF (cuando tengamos endpoint) -->
          <button
            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-md hover:bg-emerald-700 disabled:opacity-50"
            :disabled="descargando"
            @click="descargarInforme"
          >
            <svg class="w-4 h-4 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"
              />
            </svg>
            <span v-if="!descargando">Descargar informe PDF</span>
            <span v-else>Generando…</span>
          </button>
        </div>

        <p class="text-sm text-gray-500">
          Aquí podríamos renderizar un gráfico de barras con las cantidades de invitados, colaboradores,
          jueces y estudiantes, similar al informe que me mostraste.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { reportesApi } from '@/services/api'
import { useToast } from '@/composables/useToast'

const route = useRoute()
const { mostrarToast } = useToast()

const feriaId = route.query.feria_id ?? null

const cargando = ref(false)
const descargando = ref(false)

const resumen = ref({
  invitados: 0,
  colaboradores: 0,
  jueces: 0,
  estudiantes: 0,
})

const cargarResumen = async () => {
  if (!feriaId) return

  cargando.value = true
  try {
    const { data } = await reportesApi.resumen({ feria_id: feriaId })

    resumen.value = {
      invitados: data.invitados ?? 0,
      colaboradores: data.colaboradores ?? 0,
      jueces: data.jueces ?? 0,
      estudiantes: data.estudiantes ?? 0,
    }
  } catch (e) {
    console.error(e)
    mostrarToast('No se pudieron cargar las estadísticas', 'error')
  } finally {
    cargando.value = false
  }
}

const descargarInforme = async () => {
  // Aquí luego llamamos al endpoint que genere el PDF con el gráfico.
  // Por ahora solo mostramos un mensaje.
  mostrarToast('Pendiente implementar generación del informe PDF.', 'info')
}

onMounted(() => {
  cargarResumen()
})
</script>
