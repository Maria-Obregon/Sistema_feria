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

    <!-- Encabezado del Proyecto -->
    <div v-if="proyecto" class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-6">
      <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
        <div>
          <h2 class="text-2xl font-bold text-gray-800 mb-2">{{ proyecto.titulo }}</h2>
          <div class="flex flex-wrap items-center gap-3 text-sm">
            <span v-if="codigoProyecto" class="px-3 py-1 rounded-full bg-indigo-100 text-indigo-800 font-bold">
              {{ codigoProyecto }}
            </span>
            <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-800 font-medium">
              {{ proyecto.categoria?.nombre || proyecto.categoria || 'Sin Categoría' }}
            </span>
            <span class="px-3 py-1 rounded-full bg-purple-100 text-purple-800 font-medium capitalize">
              {{ route.query.tipo_eval || 'Evaluación' }}
            </span>
            <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-600">
              Etapa {{ etapaId }}
            </span>
          </div>
          <p v-if="proyecto.descripcion" class="mt-3 text-gray-600 text-sm max-w-3xl">
            {{ proyecto.descripcion }}
          </p>
        </div>
        
        <!-- Acciones Principales -->
        <div class="flex flex-col sm:flex-row gap-2 shrink-0">

            <button v-if="!estaFinalizada" @click="finalizarAsignacionActual" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 shadow-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2" :disabled="!asignacionId || hayErrores">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Finalizar asignación
            </button>
            <button v-else @click="reabrirAsignacionActual" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 shadow-sm transition-colors flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                Reabrir asignación
            </button>
        </div>
      </div>
    </div>



    <div v-if="hayErrores" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 flex items-center gap-3">
      <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
      <span>Hay puntajes inválidos que exceden el máximo permitido. Por favor corrígelos antes de finalizar.</span>
    </div>

    <div v-if="estaFinalizada" class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-yellow-800 flex items-center gap-3">
      <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
      <span>Esta asignación está finalizada. Haz clic en "Reabrir asignación" para poder editar las calificaciones.</span>
    </div>

    <div v-else-if="fueReabierta" class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg text-blue-800 flex items-center gap-3">
      <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
      <span>Asignación reabierta para edición. Cuando termines de modificar, haz clic en "Finalizar asignación" para completarla nuevamente.</span>
    </div>

    <div v-if="loading" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
    </div>

    <div v-else class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>

            <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider w-1/3">Criterio</th>
            <th class="text-center px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Peso</th>
            <th class="text-center px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Máx</th>
            <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Puntaje</th>
            <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Comentario</th>
            <th class="text-right px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Acción</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <template v-for="(group, groupName) in califsGrouped" :key="groupName">
            <!-- Section Header -->
            <tr v-if="groupName !== 'default'" class="bg-gray-100">
              <td colspan="6" class="px-6 py-2 text-sm font-bold text-gray-700 uppercase tracking-wide">
                {{ groupName }}
              </td>
            </tr>
            
            <!-- Criteria Rows -->
            <tr v-for="row in group" :key="`${row.asignacion_juez_id}-${row.criterio_id}`" class="hover:bg-gray-50 transition-colors">
              <td class="px-6 py-4">
                <div class="text-sm font-medium text-gray-900">{{ row.criterio_nombre }}</div>
              </td>
              <td class="px-4 py-4 text-center text-sm text-gray-500">{{ row.peso }}</td>
              <td class="px-4 py-4 text-center text-sm text-gray-500 font-medium">{{ row.max_puntos }}</td>
              <td class="px-6 py-4">
                <div v-if="rubrica?.modo === 'escala_1_5'" class="flex items-center gap-3">
                  <label v-for="v in [1,2,3,4,5]" :key="v" class="inline-flex items-center gap-1 cursor-pointer">
                    <input type="radio" :name="`c-${row.criterio_id}`" :value="v" v-model.number="form[row.criterio_id].puntaje" :disabled="estaFinalizada" 
                           class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300"/>
                    <span class="text-sm text-gray-700">{{ v }}</span>
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
                      @input="(e)=>{ const v=Number(e.target.value); const m=Number(row.max_puntos||0); if(v>m || v<0) { e.target.classList.add('border-red-500', 'focus:border-red-500', 'focus:ring-red-500'); } else { e.target.classList.remove('border-red-500', 'focus:border-red-500', 'focus:ring-red-500'); } form[row.criterio_id].puntaje = v; }"
                      class="block w-24 pl-3 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition duration-150 ease-in-out text-right"
                      :class="{'bg-gray-100 text-gray-500': estaFinalizada, 'border-red-500 text-red-600': esInvalido(row)}"
                    />
                  </div>
                  <span class="text-sm text-gray-500">/ {{ row.max_puntos }} pts</span>
                </div>
                <div v-if="esInvalido(row)" class="text-xs text-red-600 mt-1 font-medium">
                  Excede el máximo ({{ row.max_puntos }})
                </div>
              </td>
              <td class="px-6 py-4">
                <input v-model="form[row.criterio_id].comentario" type="text" maxlength="2000"
                       :disabled="estaFinalizada"
                       placeholder="Agregar comentario..."
                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm py-2"
                       :class="{'bg-gray-100 text-gray-500': estaFinalizada}" />
              </td>
              <td class="px-6 py-4 text-right">
                <button @click="guardar(row)" 
                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors" 
                        :disabled="estaFinalizada || esInvalido(row)">
                  Guardar
                </button>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

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
        <div class="font-bold text-gray-700">Mi Calificación Total</div>
        <div class="text-gray-600">Suma Puntos: <span class="font-medium text-gray-900">{{ sumaPC.toFixed(2) }}</span> / {{ maxTotalPC.toFixed(2) }}</div>
        <div class="text-gray-600">Porcentaje: <span class="font-medium text-gray-900">{{ porcentajePC.toFixed(2) }}%</span></div>
        <div class="text-gray-600">Nota (Base 100): <span class="font-bold text-blue-600 text-lg">{{ porcentajePC.toFixed(2) }}</span></div>
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
const proyecto = ref(null)

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
  

  Object.keys(form).forEach(key => delete form[key])
  
  loading.value = true
  try {

    if (route.query.finalizada === '1') {
      estaFinalizada.value = true
      fueReabierta.value = false
    }

    const tipoEval = route.query.tipo_eval || 'exposicion'

    // Cargar datos del proyecto para el encabezado
    try {
        const { data: pData } = await api.get(`/proyectos/${proyectoId.value}`)
        proyecto.value = pData.data || pData
    } catch (e) {
        console.error('Error cargando proyecto', e)
    }

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

const guardar = async (row, silent = false) => {
  if (!asignacionId.value) {
    if (!silent) mostrarToast('No hay asignación cargada', 'warning')
    return
  }
  
  const body = {
    asignacion_id: asignacionId.value,
    criterio_id: row.criterio_id,
    puntaje: form[row.criterio_id]?.puntaje ?? 0,
    comentario: form[row.criterio_id]?.comentario ?? '',
  }
  try {
    await crearCalificacion(body)
    if (!silent) {
      if (fueReabierta.value) {
        mostrarToast('Guardado. No olvides finalizar la asignación cuando termines', 'success')
      } else {
        mostrarToast('Guardado', 'success')
      }
    }
  } catch (e) {
    const status = e?.response?.status
    if (!silent) {
        if (status === 422 || status === 403) mostrarToast(e?.response?.data?.message || 'Error de validación', 'error')
        else mostrarToast('No se pudo guardar', 'error')
    }
    throw e // Re-throw para que Promise.all lo detecte
  }
}



const verificarEstadoAsignacion = async (id) => {
  try {
    // Consultar el endpoint de asignaciones del proyecto
    const { data } = await api.get(`/proyectos/${proyectoId.value}/asignaciones`)
    const asignaciones = Array.isArray(data) ? data : (Array.isArray(data?.data) ? data.data : [])
    const asignacion = asignaciones.find(a => a.id == id)
    
    // Si no encontramos, asumimos que no está finalizada
    if (!asignacion) return { finalizada: false }
    
    // Revisar si tiene finalizada_at (puede ser timestamp o null)
    const finalizada = asignacion.finalizada_at != null && asignacion.finalizada_at !== ''
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

  // Auto-guardar todos los valores antes de finalizar
  loading.value = true
  try {
    const promises = califs.value.map(row => guardar(row, true))
    await Promise.all(promises)
  } catch (e) {
    loading.value = false
    mostrarToast('Error guardando calificaciones. Intenta guardar manualmente.', 'error')
    return
  }

  try {
    await finalizarAsignacion(asignacionId.value)
    mostrarToast('Asignación finalizada correctamente', 'success')
    fueReabierta.value = false
    estaFinalizada.value = true
    setTimeout(() => {
      router.push({ name: 'juez.mis-calificaciones' })
    }, 1000)
  } catch (e) {
    const status = e?.response?.status
    if (status === 422 || status === 403) mostrarToast(e?.response?.data?.message || 'Error', 'error')
    else mostrarToast('No se pudo finalizar la asignación', 'error')
  } finally {
    loading.value = false
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

const califsGrouped = computed(() => {
  const groups = {}
  califs.value.forEach(row => {
    // El backend debe enviar 'criterio_seccion' o similar. 
    // Si no viene en el join, hay que asegurar que el backend lo envíe.
    // Asumiremos que el backend envía 'criterio_seccion' o 'seccion'.
    // Si no, usaremos 'default'.
    const key = row.criterio_seccion || row.seccion || 'default'
    if (!groups[key]) groups[key] = []
    groups[key].push(row)
  })
  return groups
})

const codigoProyecto = computed(() => {
  if (!proyecto.value) return ''
  const cat = (proyecto.value.categoria?.nombre || proyecto.value.categoria || '').toUpperCase()
  const tipo = (route.query.tipo_eval || '').toLowerCase()
  
  let code = ''
  if (cat.includes('DEMOSTRACIONES')) code = 'F8'
  else if (cat.includes('INVESTIGACIÓN CIENTÍFICA')) code = 'F9'
  else if (cat.includes('DESARROLLO TECNOLÓGICO')) code = 'F10'
  else if (cat.includes('QUEHACER')) code = 'F11'
  else if (cat.includes('SUMANDO')) code = 'F12'
  else if (cat.includes('MI EXPERIENCIA')) code = 'F13'
  
  if (code) {
    if (tipo === 'escrito') code += 'A'
    else if (tipo === 'exposicion') code += 'B'
  }
  
  return code
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
