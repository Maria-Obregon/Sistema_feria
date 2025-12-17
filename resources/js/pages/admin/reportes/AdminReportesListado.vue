<!-- resources/js/pages/admin/AdminReportesListado.vue -->
<template>
  <div class="p-6 space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">
          {{ tituloPagina }}
        </h1>
        <p class="text-gray-600">
          Selecciona una persona de la lista para descargar su certificado de participación.
        </p>
        <p class="text-xs text-gray-400 mt-1">
          Feria ID: {{ feriaId }} • Tipo: {{ tipo }}
        </p>
      </div>

      <RouterLink
        :to="{ name: 'admin.reportes', query: { feria_id: feriaId } }"
        class="inline-flex items-center gap-2 px-3 py-2 border rounded-lg text-gray-700 hover:bg-gray-50"
      >
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Volver a reportes
      </RouterLink>
    </div>

    <!-- Contenedor principal -->
    <div class="bg-white rounded-lg shadow-sm border">
      <!-- Filtros simples / info -->
      <div class="px-6 py-4 border-b flex items-center justify-between">
        <div class="text-sm text-gray-600">
          {{ descripcionListado }}
        </div>
        <button
          type="button"
          class="text-sm text-gray-500 hover:text-gray-800 inline-flex items-center gap-1"
          @click="cargarPersonas"
        >
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 4v5h.582M20 20v-5h-.581M5 9a7 7 0 0112.297-4.297M19 15a7 7 0 01-12.297 4.297" />
          </svg>
          Actualizar
        </button>
      </div>

      <!-- Tabla -->
      <div v-if="cargando" class="p-8 text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600 mx-auto mb-2"></div>
        <p class="text-gray-600 text-sm">Cargando lista…</p>
      </div>

      <div v-else-if="personas.length === 0" class="p-8 text-center text-gray-500 text-sm">
        No se encontraron registros para esta feria y tipo.
      </div>

      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">#</th>
              <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">
                Nombre
              </th>
              <th v-if="muestraProyecto" class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">
                Proyecto
              </th>
              <th v-if="muestraExtra" class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">
                {{ tituloColumnaExtra }}
              </th>
              <th class="px-6 py-3 text-right font-medium text-gray-500 uppercase tracking-wider">
                Acciones
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr
              v-for="(p, index) in personas"
              :key="p.id"
              class="hover:bg-gray-50"
            >
              <td class="px-6 py-3 whitespace-nowrap text-gray-700">
                {{ index + 1 }}
              </td>

              <td class="px-6 py-3 whitespace-nowrap text-gray-900">
                <div class="font-medium">
                  {{ p.nombre_completo ?? p.nombre ?? p.full_name ?? 'Sin nombre' }}
                </div>
                <div v-if="p.identificacion || p.cedula" class="text-xs text-gray-500">
                  {{ p.identificacion ?? p.cedula }}
                </div>
                <div v-if="p.email" class="text-xs text-gray-400">
                  {{ p.email }}
                </div>
              </td>

              <td
                v-if="muestraProyecto"
                class="px-6 py-3 whitespace-nowrap text-gray-700"
              >
                {{ p.proyecto_titulo ?? p.proyecto?.titulo ?? '—' }}
              </td>

              <td
                v-if="muestraExtra"
                class="px-6 py-3 whitespace-nowrap text-gray-700"
              >
                {{ valorExtra(p) }}
              </td>

              <td class="px-6 py-3 whitespace-nowrap text-right">
                <button
                  type="button"
                  class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md border text-xs font-medium text-indigo-700 border-indigo-200 hover:bg-indigo-50 disabled:opacity-50"
                  @click="descargarCertificado(p)"
                  :disabled="descargandoId === p.id"
                >
                  <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 4v11" />
                  </svg>
                  <span v-if="descargandoId === p.id">Generando…</span>
                  <span v-else>Certificado</span>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { reportesApi } from '@/services/api'
import { useToast } from '@/composables/useToast'

const route = useRoute()
const router = useRouter()
const { mostrarToast } = useToast()

// Query params: feria_id y tipo (estudiantes, jueces, invitados, colaboradores)
const feriaId = computed(() => route.query.feria_id ?? '')
const tipo = computed(() => (route.query.tipo ?? 'estudiantes').toString())

const personas = ref([])
const cargando = ref(false)
const descargandoId = ref(null)

// ---- Textos dinámicos ----
const tituloPagina = computed(() => {
  switch (tipo.value) {
    case 'estudiantes':
      return 'Certificados de estudiantes'
    case 'jueces':
      return 'Certificados de jueces'
    case 'invitados':
      return 'Certificados de invitados especiales'
    case 'colaboradores':
      return 'Certificados de colaboradores'
    default:
      return 'Certificados de participación'
  }
})

const descripcionListado = computed(() => {
  switch (tipo.value) {
    case 'estudiantes':
      return 'Lista de estudiantes participantes en la feria seleccionada. Descarga el certificado individual de cada uno.'
    case 'jueces':
      return 'Lista de jueces que participaron en el proceso de juzgamiento.'
    case 'invitados':
      return 'Lista de personas invitadas especiales que participaron en la feria.'
    case 'colaboradores':
      return 'Lista de colaboradores que apoyaron la organización y logística de la feria.'
    default:
      return 'Listado de personas asociadas a la feria seleccionada.'
  }
})

// ¿Mostramos columna de proyecto?
const muestraProyecto = computed(() => tipo.value === 'estudiantes')

// ¿Columna extra (por ejemplo, rol, institución, tipo de invitación)?
const muestraExtra = computed(() => ['jueces', 'invitados', 'colaboradores'].includes(tipo.value))

const tituloColumnaExtra = computed(() => {
  switch (tipo.value) {
    case 'jueces':
      return 'Área / especialidad'
    case 'invitados':
      return 'Tipo de invitado'
    case 'colaboradores':
      return 'Rol / función'
    default:
      return 'Detalle'
  }
})

// Cómo sacar el valor de la columna extra según lo que nos mande tu API
const valorExtra = (p) => {
  if (tipo.value === 'jueces') {
    return p.area ?? p.especialidad ?? '—'
  }
  if (tipo.value === 'invitados') {
    return p.tipo_invitacion ?? p.tipo ?? '—'
  }
  if (tipo.value === 'colaboradores') {
    return p.rol ?? p.funcion ?? '—'
  }
  return '—'
}

// ---- Cargar lista desde el backend ----
// Aquí supongo que tienes endpoints en reportesApi como:
// - listaEstudiantes({ feria_id })
// - listaJueces({ feria_id })
// - listaInvitados({ feria_id })
// - listaColaboradores({ feria_id })
const cargarPersonas = async () => {
  if (!feriaId.value) {
    mostrarToast('No se indicó la feria. Vuelve a la pantalla de reportes.', 'error')
    router.push({ name: 'admin.reportes' })
    return
  }

  cargando.value = true
  try {
    let endpoint

    switch (tipo.value) {
      case 'estudiantes':
        endpoint = reportesApi.listaEstudiantes
        break
      case 'jueces':
        endpoint = reportesApi.listaJueces
        break
      case 'invitados':
        endpoint = reportesApi.listaInvitados
        break
      case 'colaboradores':
        endpoint = reportesApi.listaColaboradores
        break
      default:
        endpoint = null
    }

    if (!endpoint) {
      throw new Error('Tipo de listado no soportado')
    }

    const { data } = await endpoint({ feria_id: feriaId.value })
    // Asumo que la API devuelve algo como { data: [...] } o directamente [...]
    personas.value = Array.isArray(data) ? data : (data.data ?? [])
  } catch (e) {
    console.error('Error cargando personas', e?.response?.data ?? e)
    mostrarToast('No se pudo cargar la lista', 'error')
    personas.value = []
  } finally {
    cargando.value = false
  }
}

// ---- Descargar certificado individual ----
// Supongo endpoints en reportesApi:
// - certificadoEstudiante(id)
// - certificadoJuez(id)
// - certificadoInvitado(id)
// - certificadoColaborador(id)
const descargarCertificado = async (persona) => {
  if (!persona?.id) return

  try {
    descargandoId.value = persona.id

    let endpoint
    let prefijoNombre = ''

    switch (tipo.value) {
      case 'estudiantes':
        endpoint = reportesApi.certificadoEstudiante
        prefijoNombre = 'estudiante'
        break
      case 'jueces':
        endpoint = reportesApi.certificadoJuez
        prefijoNombre = 'juez'
        break
      case 'invitados':
        endpoint = reportesApi.certificadoInvitado
        prefijoNombre = 'invitado'
        break
      case 'colaboradores':
        endpoint = reportesApi.certificadoColaborador
        prefijoNombre = 'colaborador'
        break
      default:
        throw new Error('Tipo de certificado no soportado')
    }

    const response = await endpoint(persona.id, { responseType: 'blob' })
    const blob = response.data ?? response // depende de como tengas axios configurado
    const archivo = new Blob([blob], { type: 'application/pdf' })
    const url = window.URL.createObjectURL(archivo)

    const a = document.createElement('a')
    const nombre = (persona.nombre_completo ?? persona.nombre ?? '').replace(/\s+/g, '_') || persona.id
    a.href = url
    a.download = `certificado-${prefijoNombre}-${nombre}.pdf`
    a.click()
    window.URL.revokeObjectURL(url)
  } catch (e) {
    console.error('Error descargando certificado', e?.response?.data ?? e)
    mostrarToast('No se pudo generar el certificado', 'error')
  } finally {
    descargandoId.value = null
  }
}

onMounted(() => {
  cargarPersonas()
})
</script>
