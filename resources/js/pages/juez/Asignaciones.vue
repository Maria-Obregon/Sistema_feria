<template>
  <div class="max-w-5xl mx-auto py-6">
    <h1 class="text-xl font-semibold mb-4">Mis asignaciones</h1>

    <div class="flex items-center gap-2 mb-4">
      <input v-model="proyectoId" type="number" placeholder="ID de proyecto"
             class="border rounded px-3 py-2 w-48" />
      <button @click="cargar"
              class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
        Cargar asignaciones
      </button>
    </div>

    <div v-if="loading" class="text-gray-500">Cargando...</div>

    <table v-else class="min-w-full bg-white shadow rounded overflow-hidden">
      <thead class="bg-gray-50">
        <tr>
          <th class="text-left px-4 py-2">ID</th>
          <th class="text-left px-4 py-2">Proyecto</th>
          <th class="text-left px-4 py-2">Etapa</th>
          <th class="text-left px-4 py-2">Tipo eval</th>
          <th class="text-left px-4 py-2">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="a in asignaciones" :key="a.id" class="border-t">
          <td class="px-4 py-2">{{ a.id }}</td>
          <td class="px-4 py-2">{{ a.proyecto_id }}</td>
          <td class="px-4 py-2">{{ a.etapa_id }}</td>
          <td class="px-4 py-2">{{ a.tipo_eval }}</td>
          <td class="px-4 py-2">
            <button @click="irACalificar(a)"
                    class="px-3 py-1 bg-emerald-600 text-white rounded hover:bg-emerald-700">
              Ir a calificar
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
  </template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from '@/composables/useToast'
import { listarAsignacionesPorProyecto } from '@/services/asignaciones'

const router = useRouter()
const { mostrarToast } = useToast()

const proyectoId = ref('')
const asignaciones = ref([])
const loading = ref(false)

const cargar = async () => {
  if (!proyectoId.value) {
    mostrarToast('Ingresa un proyectoId', 'warning')
    return
  }
  loading.value = true
  try {
    const { data } = await listarAsignacionesPorProyecto(proyectoId.value)
    asignaciones.value = Array.isArray(data) ? data : (data?.data || [])
  } catch (e) {
    const status = e?.response?.status
    if (status === 422 || status === 403) mostrarToast(e?.response?.data?.message || 'Error de validación', 'error')
    else mostrarToast('Error cargando asignaciones', 'error')
  } finally {
    loading.value = false
  }
}

const irACalificar = (a) => {
  router.push({ name: 'juez.calificaciones', query: { proyectoId: a.proyecto_id, etapaId: a.etapa_id } })
}
</script>
