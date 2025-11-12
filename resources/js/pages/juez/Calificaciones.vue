<template>
  <div class="max-w-5xl mx-auto py-6">
    <h1 class="text-xl font-semibold mb-4">Calificaciones</h1>

    <div class="flex items-center gap-2 mb-4">
      <input v-model.number="proyectoId" type="number" placeholder="proyectoId" class="border rounded px-3 py-2 w-44" />
      <input v-model.number="etapaId" type="number" placeholder="etapaId" class="border rounded px-3 py-2 w-36" />
      <button @click="cargar" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Cargar</button>
      <button @click="hacerConsolidacion" class="px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700">Consolidar</button>
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
        <tr v-for="row in califs" :key="`${row.asignaciones_juez_id}-${row.criterio_id}`" class="border-t">
          <td class="px-4 py-2">{{ row.asignaciones_juez_id }}</td>
          <td class="px-4 py-2">
            <div class="font-medium">{{ row.criterio_nombre }}</div>
            <div class="text-xs text-gray-500">ID {{ row.criterio_id }}</div>
          </td>
          <td class="px-4 py-2">{{ row.peso }}</td>
          <td class="px-4 py-2">{{ row.max_puntos }}</td>
          <td class="px-4 py-2">
            <div v-if="rubrica?.modo === 'escala_1_5'" class="flex items-center gap-2">
              <label v-for="v in [1,2,3,4,5]" :key="v" class="inline-flex items-center gap-1">
                <input type="radio" :name="`c-${row.criterio_id}`" :value="v" v-model.number="form[row.criterio_id].puntaje" />
                <span class="text-sm">{{ v }}</span>
              </label>
            </div>
            <div v-else>
              <input
                v-model.number="form[row.criterio_id].puntaje"
                type="number"
                step="0.01"
                :min="0"
                :max="row.max_puntos"
                @input="(e)=>{ const v=Number(e.target.value); const m=Number(row.max_puntos||0); form[row.criterio_id].puntaje = Math.max(0, Math.min(m, isNaN(v)?0:v)); }"
                class="border rounded px-2 py-1 w-28"
              />
            </div>
          </td>
          <td class="px-4 py-2">
            <input v-model="form[row.criterio_id].comentario" type="text" maxlength="2000"
                   class="border rounded px-2 py-1 w-64" />
          </td>
          <td class="px-4 py-2">
            <button @click="guardar(row)" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">Guardar</button>
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
import { useRoute } from 'vue-router'
import { useToast } from '@/composables/useToast'
import { listarCalificaciones, crearCalificacion, consolidar } from '@/services/calificaciones'
import { obtenerRubricaDeProyecto } from '@/services/rubricas'

const route = useRoute()
const { mostrarToast } = useToast()

const proyectoId = ref(+(route.query.proyectoId || 0))
const etapaId = ref(+(route.query.etapaId || 0))
const califs = ref([])
const form = reactive({})
const loading = ref(false)
const rubrica = ref(null)

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
    // Obtener rúbrica (exposición por defecto para juez)
    const rres = await obtenerRubricaDeProyecto(proyectoId.value, { etapa_id: etapaId.value, tipo_eval: 'exposicion' })
    rubrica.value = rres.data?.rubrica || null

    const { data } = await listarCalificaciones({ proyecto_id: proyectoId.value, etapa_id: etapaId.value, per_page: 200 })
    const items = Array.isArray(data) ? data : (data?.data || [])
    califs.value = items
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
  try {
    const payload = {
      asignacion_id: row.asignaciones_juez_id,
      criterio_id: row.criterio_id,
      puntaje: rubrica?.value?.modo === 'escala_1_5' ? Math.max(1, Math.min(5, parseInt(form[row.criterio_id]?.puntaje || 0))) : (form[row.criterio_id]?.puntaje ?? 0),
      comentario: form[row.criterio_id]?.comentario ?? null,
    }
    await crearCalificacion(payload)
    mostrarToast('Guardado', 'success')
    await cargar()
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
</script>
