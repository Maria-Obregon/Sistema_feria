<template>
  <div class="max-w-5xl mx-auto py-6">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-xl font-semibold">Calificaciones</h1>
      <div class="flex gap-2">
        <!-- Botón Volver Atrás -->
        <button
          @click="volverAtras"
          class="px-4 py-2 bg-gray-100 text-gray-700 border border-gray-300 rounded hover:bg-gray-200 flex items-center gap-2 transition-colors"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
          </svg>
          Volver atrás
        </button>

        <!-- Botón Volver al Menú -->
        <router-link
          :to="{ name: 'juez.dashboard' }"
          class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 flex items-center gap-2 transition-colors"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
          Volver al menú
        </router-link>
      </div>
    </div>

    <div class="flex items-center gap-2 mb-4">
      <input v-model.number="proyectoId" type="number" placeholder="proyectoId" class="border rounded px-3 py-2 w-44" />
      <input v-model.number="etapaId" type="number" placeholder="etapaId" class="border rounded px-3 py-2 w-36" />
      <button @click="cargar" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Cargar</button>
      <button @click="hacerConsolidacion" class="px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700" :disabled="estaFinalizada || hayErrores">Consolidar</button>
      <button v-if="!estaFinalizada" @click="finalizarAsignacionActual" class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed" :disabled="!asignacionId || hayErrores">Finalizar asignación</button>
      <button v-else @click="reabrirAsignacionActual" class="px-4 py-2 bg-orange-600 text-white rounded hover:bg-orange-700">Reabrir asignación</button>
    </div>

    <div v-if="hayErrores" class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-red-800">
      ⚠️ Hay puntajes inválidos que exceden el máximo permitido. Por favor corrígelos antes de finalizar.
    </div>

    <div v-if="estaFinalizada" class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded text-yellow-800">
      ⚠️ Esta asignación está finalizada. Haz clic en "Reabrir asignación" para poder editar las calificaciones.
    </div>

    <div v-else-if="fueReabierta" class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded text-blue-800">
      ℹ️ Asignación reabierta para edición. Cuando termines de modificar, haz clic en "Finalizar asignación" para completarla nuevamente.
    </div>

    <div v-if="loading" class="text-gray-500">Cargando...</div>

    <table v-else class="min-w-full bg-white shadow rounded overflow-hidden">
      <thead class="bg-gray-50">
        <tr>
          <th class="text-left px-4 py-2">Asignación</th>
          <th class="text-left px-4 py-2">Criterio</th>
          <th class="text-left px-4 py-2">Peso</th>
          <th class="text-left px-4 py-2">Máx</th>
          <th class="text-left px-4 py-2">Puntaje</th>
          <th class="text-left px-4 py-2">Comentario</th>
          <th class="text-left px-4 py-2">Acción</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="row in califs" :key="`${row.asignacion_juez_id}-${row.criterio_id}`" class="border-t">
          <td class="px-4 py-2">{{ row.asignacion_juez_id }}</td>
          <td class="px-4 py-2">
            <div class="font-medium">{{ row.criterio_nombre }}</div>
            <div class="text-xs text-gray-500">ID {{ row.criterio_id }}</div>
          </td>
          <td class="px-4 py-2">{{ row.peso }}</td>
          <td class="px-4 py-2">{{ row.max_puntos }}</td>
          <td class="px-4 py-2">
            <div v-if="rubrica?.modo === 'escala_1_5'" class="flex items-center gap-2">
              <label v-for="v in [1,2,3,4,5]" :key="v" class="inline-flex items-center gap-1">
                <input type="radio" :name="`c-${row.criterio_id}`" :value="v" v-model.number="form[row.criterio_id].puntaje" :disabled="estaFinalizada" />
                <span class="text-sm">{{ v }}</span>
              </label>
            </div>
            <div v-else class="flex items-center gap-2">
              <div class="relative">
                <input
                  v-model.number="form[row.criterio_id].puntaje"
                  type="number"
                  step="0.01"
                  :min="0"
                  :max="row.max_puntos"
                  :disabled="estaFinalizada"
                  @input="(e)=>{ const v=Number(e.target.value); const m=Number(row.max_puntos||0); if(v>m || v<0) { e.target.classList.add('border-red-500'); } else { e.target.classList.remove('border-red-500'); } form[row.criterio_id].puntaje = v; }"
                  class="border rounded px-2 py-1 w-20 text-right"
                  :class="{'bg-gray-100': estaFinalizada, 'border-red-500': esInvalido(row)}"
                />
              </div>
              <span class="text-sm text-gray-500">/ {{ row.max_puntos }} pts</span>
            </div>
            <div v-if="esInvalido(row)" class="text-xs text-red-600 mt-1">
              Máx {{ row.max_puntos }}
            </div>
          </td>
          <td class="px-4 py-2">
            <input v-model="form[row.criterio_id].comentario" type="text" maxlength="2000"
                   :disabled="estaFinalizada"
                   class="border rounded px-2 py-1 w-full"
                   :class="{'bg-gray-100': estaFinalizada}" />
          </td>
          <td class="px-4 py-2">
            <button @click="guardar(row)" 
                    class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed" 
                    :disabled="estaFinalizada || esInvalido(row)">
              Guardar
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-if="rubrica?.modo === 'escala_1_5'" class="mt-4 bg-white border rounded p-4">
      <div class="flex flex-wrap items-center gap-4 text-sm">
        <div class="font-medium">Resumen</div>
        <div>Suma: {{ suma15 }}</div>
        <div>Máx (N*5): {{ maxSuma15 }}</div>
        <div>Porcentaje: {{ porcentaje15.toFixed(2) }}%</div>
        <div v-if="rubrica?.max_total">Equivalente a {{ rubrica.max_total }}: {{ equivalente100.toFixed(2) }}</div>
      </div>
    </div>

    <div v-if="rubrica?.modo === 'por_criterio'" class="mt-4 bg-white border rounded p-4">
      <div class="flex flex-wrap items-center gap-4 text-sm">
        <div class="font-medium">Resumen</div>
        <div>Suma: {{ sumaPC.toFixed(2) }}</div>
        <div>Máximo: {{ maxTotalPC.toFixed(2) }}</div>
        <div>Porcentaje: {{ porcentajePC.toFixed(2) }}%</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted, watch, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from '@/composables/useToast'
import { listarCalificaciones, crearCalificacion, consolidar } from '@/services/calificaciones'
import { obtenerRubricaDeProyecto } from '@/services/rubricas'
import { finalizarAsignacion, reabrirAsignacion } from '@/services/asignaciones'
import api from '@/services/api'

const route = useRoute()
const router = useRouter()
const { mostrarToast } = useToast()

const proyectoId = ref(+(route.query.proyectoId || 0))
const etapaId = ref(+(route.query.etapaId || 0))
const asignacionId = ref(null)
const estaFinalizada = ref(false)
const fueReabierta = ref(false)
const califs = ref([])
const form = reactive({})
const loading = ref(false)
const rubrica = ref(null)

const hayErrores = computed(() => {
  return califs.value.some(row => esInvalido(row))
})

const esInvalido = (row) => {
  const val = Number(form[row.criterio_id]?.puntaje || 0)
  const max = Number(row.max_puntos || 0)
  return val < 0 || val > max
}

const primingForm = (rows) => {
  rows.forEach(r => {
    if (!form[r.criterio_id]) {
      form[r.criterio_id] = { puntaje: r.puntaje ?? 0, comentario: r.comentario ?? '' }
    }
  })
}

const cargar = async () => {
  if (!proyectoId.value || !etapaId.value) {
    mostrarToast('proyectoId y etapaId son requeridos', 'warning')
    return
  }
  loading.value = true
  try {
    // Si viene desde "Mis Calificaciones", está finalizada
    if (route.query.finalizada === '1') {
      estaFinalizada.value = true
      fueReabierta.value = false
      console.log('Asignación marcada como finalizada desde query param')
    }

    const tipoEval = route.query.tipo_eval || 'exposicion'

    // Obtener rúbrica (usando el tipo de evaluación de la URL)
    const rres = await obtenerRubricaDeProyecto(proyectoId.value, { etapa_id: etapaId.value, tipo_eval: tipoEval })
    rubrica.value = rres.data?.rubrica || null

    const { data } = await listarCalificaciones({ 
      proyecto_id: proyectoId.value, 
      etapa_id: etapaId.value, 
      tipo_eval: tipoEval, // Enviar al backend
      per_page: 200 
    })
    const items = Array.isArray(data) ? data : (data?.data || [])
    califs.value = items
    // Extraer asignacion_id y verificar estado solo si no viene de "Mis Calificaciones"
    if (items.length > 0) {
      asignacionId.value = items[0].asignacion_juez_id
      
      // Solo verificar si no viene con el flag de finalizada
      if (route.query.finalizada !== '1') {
        const asignacionData = await verificarEstadoAsignacion(items[0].asignacion_juez_id)
        estaFinalizada.value = asignacionData.finalizada
      }
    }
    primingForm(items)
  } catch (e) {
    const status = e?.response?.status
    if (status === 422 || status === 403) mostrarToast(e?.response?.data?.message || 'Error de validación', 'error')
    else mostrarToast('Error cargando calificaciones', 'error')
  } finally {
    loading.value = false
  }
}

const guardar = async (row) => {
  if (!asignacionId.value) {
    mostrarToast('No hay asignación cargada', 'warning')
    return
  }
  
  const body = {
    asignacion_id: asignacionId.value,  // Cambio: enviar asignacion_id en lugar de proyecto_id/etapa_id
    criterio_id: row.criterio_id,
    puntaje: form[row.criterio_id]?.puntaje ?? 0,
    comentario: form[row.criterio_id]?.comentario ?? '',
  }
  try {
    await crearCalificacion(body)
    if (fueReabierta.value) {
      mostrarToast('Guardado. No olvides finalizar la asignación cuando termines', 'success')
    } else {
      mostrarToast('Guardado', 'success')
    }
  } catch (e) {
    const status = e?.response?.status
    if (status === 422 || status === 403) mostrarToast(e?.response?.data?.message || 'Error de validación', 'error')
    else mostrarToast('No se pudo guardar', 'error')
  }
}

const hacerConsolidacion = async () => {
  try {
    const { data } = await consolidar({ proyecto_id: proyectoId.value, etapa_id: etapaId.value })
    mostrarToast(`Nota final: ${data.nota_final} (E:${data.nota_escrito} / X:${data.nota_exposicion})`, 'info')
  } catch (e) {
    const status = e?.response?.status
    if (status === 422 || status === 403) mostrarToast(e?.response?.data?.message || 'Error de validación', 'error')
    else mostrarToast('No se pudo consolidar', 'error')
  }
}

const verificarEstadoAsignacion = async (id) => {
  try {
    // Consultar el endpoint de asignaciones del proyecto
    const { data } = await api.get(`/proyectos/${proyectoId.value}/asignaciones`)
    const asignaciones = Array.isArray(data) ? data : (Array.isArray(data?.data) ? data.data : [])
    const asignacion = asignaciones.find(a => a.id === id)
    
    // Si no encontramos, asumimos que no está finalizada
    if (!asignacion) return { finalizada: false }
    
    // Revisar si tiene finalizada_at (puede ser timestamp o null)
    const finalizada = asignacion.finalizada_at != null && asignacion.finalizada_at !== ''
    console.log('Estado asignación:', { id, finalizada_at: asignacion.finalizada_at, finalizada })
    return { finalizada }
  } catch (e) {
    console.error('Error verificando estado:', e)
    return { finalizada: false }
  }
}

const finalizarAsignacionActual = async () => {
  if (!asignacionId.value) {
    mostrarToast('No hay asignación cargada', 'warning')
    return
  }
  try {
    await finalizarAsignacion(asignacionId.value)
    mostrarToast('Asignación finalizada correctamente', 'success')
    // Resetear flag de reabierta
    fueReabierta.value = false
    estaFinalizada.value = true
    // Redirigir a Mis Calificaciones en lugar de Mis Asignaciones
    setTimeout(() => {
      router.push({ name: 'juez.mis-calificaciones' })
    }, 1000)
  } catch (e) {
    const status = e?.response?.status
    if (status === 422 || status === 403) mostrarToast(e?.response?.data?.message || 'Error', 'error')
    else mostrarToast('No se pudo finalizar la asignación', 'error')
  }
}

const reabrirAsignacionActual = async () => {
  if (!asignacionId.value) {
    mostrarToast('No hay asignación cargada', 'warning')
    return
  }
  try {
    await reabrirAsignacion(asignacionId.value)
    mostrarToast('Asignación reabierta. Recuerda finalizarla de nuevo cuando termines de editar', 'success')
    // Actualizar estado
    estaFinalizada.value = false
    fueReabierta.value = true
  } catch (e) {
    const status = e?.response?.status
    if (status === 422 || status === 403) mostrarToast(e?.response?.data?.message || 'Error', 'error')
    else mostrarToast('No se pudo reabrir la asignación', 'error')
  }
}

onMounted(cargar)
watch(() => route.query, () => {
  proyectoId.value = +(route.query.proyectoId || 0)
  etapaId.value = +(route.query.etapaId || 0)
  cargar()
})

// Resumen 1–5
const suma15 = computed(() => {
  if (rubrica.value?.modo !== 'escala_1_5') return 0
  return califs.value.reduce((acc, row) => acc + (parseInt(form[row.criterio_id]?.puntaje || 0) || 0), 0)
})
const maxSuma15 = computed(() => {
  if (rubrica.value?.modo !== 'escala_1_5') return 0
  return califs.value.length * 5
})
const porcentaje15 = computed(() => {
  const max = maxSuma15.value || 1
  return (suma15.value / max) * 100
})
const equivalente100 = computed(() => {
  if (!rubrica.value?.max_total) return 0
  const max = maxSuma15.value || 1
  return (suma15.value / max) * rubrica.value.max_total
})

// Resumen por criterio (F13B y similares)
const sumaPC = computed(() => {
  if (rubrica.value?.modo !== 'por_criterio') return 0
  return califs.value.reduce((acc, row) => {
    const max = Number(row.max_puntos || 0)
    const val = Number(form[row.criterio_id]?.puntaje || 0)
    const clamped = Math.max(0, Math.min(max, isNaN(val) ? 0 : val))
    return acc + clamped
  }, 0)
})

const maxTotalPC = computed(() => {
  if (rubrica.value?.modo !== 'por_criterio') return 0
  const sumCriterios = califs.value.reduce((acc, row) => acc + Number(row.max_puntos || 0), 0)
  return Number(rubrica.value?.max_total || sumCriterios)
})

const porcentajePC = computed(() => {
  const max = maxTotalPC.value || 1
  return (sumaPC.value / max) * 100
})

const volverAtras = () => {
  const from = route.query.from
  if (from === 'mis-calificaciones') {
    router.push({ name: 'juez.mis-calificaciones' })
  } else if (from === 'asignaciones') {
    router.push({ name: 'juez.asignaciones' })
  } else {
    // Default fallback (intentar historial o ir a asignaciones)
    if (window.history.length > 1) {
      router.back()
    } else {
      router.push({ name: 'juez.asignaciones' })
    }
  }
}
</script>
