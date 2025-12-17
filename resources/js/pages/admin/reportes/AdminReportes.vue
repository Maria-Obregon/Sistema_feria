<!-- resources/js/pages/admin/reportes/AdminReportes.vue -->
<template>
  <div class="p-6 space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Reportes y Certificados</h1>
        <p class="text-gray-600">
          Descarga de certificados, listados de calificaciones y estadísticas de participación.
        </p>
      </div>

      <RouterLink
        :to="{ name: 'admin.dashboard' }"
        class="inline-flex items-center gap-2 px-3 py-2 border rounded-lg text-gray-700 hover:bg-gray-50"
      >
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Volver
      </RouterLink>
    </div>

    <!-- Selección de feria -->
    <div class="bg-white rounded-lg shadow-sm border p-4 flex flex-col md:flex-row md:items-end gap-4">
      <div class="flex-1">
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Feria
        </label>
        <select
          v-model="feriaSeleccionadaId"
          class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
        >
          <option value="">Seleccionar feria…</option>
          <option
            v-for="f in ferias"
            :key="f.id"
            :value="f.id"
          >
            {{ f.anio }} · {{ f.institucion?.nombre ?? 'Sin institución' }} ({{ etiquetaTipo(f.tipo_feria) }})
          </option>
        </select>
      </div>

      <div class="text-sm text-gray-500">
        <p v-if="feriaSeleccionadaId">
          Reportes habilitados para la feria seleccionada.
        </p>
        <p v-else>
          Selecciona una feria para habilitar los reportes.
        </p>
      </div>
    </div>

    <!-- Contenido -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Certificados -->
      <section class="bg-white rounded-lg shadow-sm border p-5 space-y-4">
        <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
          <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 12h6m-6 4h6M7 4h10a2 2 0 012 2v12l-4-2-4 2-4-2-4 2V6a2 2 0 012-2z"
              />
            </svg>
          </span>
          Certificados de participación
        </h2>

        <p class="text-sm text-gray-500">
          Ver y descargar certificados de participación para cada grupo de personas.
        </p>

        <div class="grid grid-cols-1 gap-3">
          <button
            class="w-full inline-flex items-center justify-between px-3 py-2 border rounded-md text-sm hover:bg-gray-50 disabled:opacity-50"
            :disabled="!feriaSeleccionadaId"
            @click="irListado('estudiantes')"
          >
            <span>Estudiantes</span>
            <span class="text-xs text-gray-400">Ver lista</span>
          </button>

          <button
            class="w-full inline-flex items-center justify-between px-3 py-2 border rounded-md text-sm hover:bg-gray-50 disabled:opacity-50"
            :disabled="!feriaSeleccionadaId"
            @click="irListado('jueces')"
          >
            <span>Jueces</span>
            <span class="text-xs text-gray-400">Ver lista</span>
          </button>

          <button
            class="w-full inline-flex items-center justify-between px-3 py-2 border rounded-md text-sm hover:bg-gray-50 disabled:opacity-50"
            :disabled="!feriaSeleccionadaId"
            @click="irListado('invitados')"
          >
            <span>Invitados especiales</span>
            <span class="text-xs text-gray-400">Ver lista</span>
          </button>

          <button
            class="w-full inline-flex items-center justify-between px-3 py-2 border rounded-md text-sm hover:bg-gray-50 disabled:opacity-50"
            :disabled="!feriaSeleccionadaId"
            @click="irListado('colaboradores')"
          >
            <span>Colaboradores</span>
            <span class="text-xs text-gray-400">Ver lista</span>
          </button>
        </div>
      </section>

      <!-- Listas de calificaciones -->
      <section class="bg-white rounded-lg shadow-sm border p-5 space-y-4">
        <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
          <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-amber-100">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 17v-6h6v6M9 9V7h6v2m-6 10h6a2 2 0 002-2V5a2 2 0 00-2-2H9L7 5v12a2 2 0 002 2z"
              />
            </svg>
          </span>
          Listados de calificaciones
        </h2>

        <p class="text-sm text-gray-500">
          Genera listados ordenados de mayor a menor para los proyectos.
        </p>

        <div class="grid grid-cols-1 gap-3">
          <button
            class="w-full inline-flex items-center justify-between px-3 py-2 border rounded-md text-sm hover:bg-gray-50 disabled:opacity-50"
            :disabled="!feriaSeleccionadaId || cargandoDescarga"
            @click="descargarCalificaciones('informe-escrito')"
          >
            <span>Calificación del informe escrito</span>
            <span class="text-xs text-gray-400">Mayor a menor</span>
          </button>

          <button
            class="w-full inline-flex items-center justify-between px-3 py-2 border rounded-md text-sm hover:bg-gray-50 disabled:opacity-50"
            :disabled="!feriaSeleccionadaId || cargandoDescarga"
            @click="descargarCalificaciones('general')"
          >
            <span>Calificación general</span>
            <span class="text-xs text-gray-400">Mayor a menor</span>
          </button>

          <button
            class="w-full inline-flex items-center justify-between px-3 py-2 border rounded-md text-sm hover:bg-gray-50 disabled:opacity-50"
            :disabled="!feriaSeleccionadaId || cargandoDescarga"
            @click="descargarCalificaciones('por-categoria')"
          >
            <span>Calificación por categoría</span>
            <span class="text-xs text-gray-400">Mayor a menor</span>
          </button>

          <button
            class="w-full inline-flex items-center justify-between px-3 py-2 border rounded-md text-sm hover:bg-gray-50 disabled:opacity-50"
            :disabled="!feriaSeleccionadaId || cargandoDescarga"
            @click="descargarCalificaciones('por-modalidad')"
          >
            <span>Calificación por modalidad</span>
            <span class="text-xs text-gray-400">Mayor a menor</span>
          </button>
        </div>
      </section>

      <!-- Estadísticas de participación (solo botón) -->
      <section class="bg-white rounded-lg shadow-sm border p-5 space-y-4">
        <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
          <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-emerald-100">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M11 17l-3.5-3.5 1.41-1.41L11 14.17l3.09-3.09L15.5 12.5 11 17z"
              />
            </svg>
          </span>
          Estadísticas de participación
        </h2>

        <p class="text-sm text-gray-500">
          Ver el informe de participantes (invitados, colaboradores, jueces y estudiantes) para la feria seleccionada.
        </p>

        <button
          class="inline-flex items-center justify-center px-4 py-2 border rounded-md text-sm font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 disabled:opacity-50"
          :disabled="!feriaSeleccionadaId"
          @click="irEstadisticas"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M3 3v18h18M7 13l3 3 4-8 4 10"
            />
          </svg>
          Ver informe de participación
        </button>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { feriasApi, reportesApi } from '@/services/api'
import { useToast } from '@/composables/useToast'

const router = useRouter()
const { mostrarToast } = useToast()

const ferias = ref([])
const feriaSeleccionadaId = ref('')

const cargandoDescarga = ref(false)

const etiquetaTipo = (t) => {
  if (t === 'institucional') return 'Institucional'
  if (t === 'circuital') return 'Circuital'
  if (t === 'regional') return 'Regional'
  return t ?? '—'
}

const cargarFerias = async () => {
  try {
    const { data } = await feriasApi.list({})
    ferias.value = data.data ?? data ?? []
  } catch (e) {
    console.error(e)
    mostrarToast('Error al cargar las ferias', 'error')
  }
}

// Ir a la lista de personas para sacar certificados individuales
const irListado = (tipo) => {
  if (!feriaSeleccionadaId.value) {
    mostrarToast('Selecciona una feria primero', 'error')
    return
  }

  router.push({
    name: 'admin.reportes.listado',
    query: {
      feria_id: feriaSeleccionadaId.value,
      tipo,
    },
  })
}

// Ir a la vista de estadísticas
const irEstadisticas = () => {
  if (!feriaSeleccionadaId.value) {
    mostrarToast('Selecciona una feria primero', 'error')
    return
  }

  router.push({
    name: 'admin.reportes.estadisticas',
    query: {
      feria_id: feriaSeleccionadaId.value,
    },
  })
}

// Descarga de listados de calificaciones (PDF)
const descargarCalificaciones = async (tipo) => {
  if (!feriaSeleccionadaId.value) {
    mostrarToast('Selecciona una feria primero', 'error')
    return
  }

  try {
    cargandoDescarga.value = true

    let endpoint
    switch (tipo) {
      case 'informe-escrito':
        endpoint = reportesApi.califInformeEscrito
        break
      case 'general':
        endpoint = reportesApi.califGeneral
        break
      case 'por-categoria':
        endpoint = reportesApi.califPorCategoria
        break
      case 'por-modalidad':
        endpoint = reportesApi.califPorModalidad
        break
    }

    const { data } = await endpoint({ feria_id: feriaSeleccionadaId.value })

    const blob = new Blob([data], { type: 'application/pdf' })
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `calificaciones-${tipo}-feria-${feriaSeleccionadaId.value}.pdf`
    a.click()
    window.URL.revokeObjectURL(url)
  } catch (e) {
    console.error(e)
    mostrarToast('No se pudieron generar las calificaciones', 'error')
  } finally {
    cargandoDescarga.value = false
  }
}

onMounted(async () => {
  await cargarFerias()
})
</script>
