<template>
  <div class="max-w-5xl mx-auto py-6">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-xl font-semibold">Mis calificaciones</h1>
      <router-link
        :to="{ name: 'juez.dashboard' }"
        class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 flex items-center gap-2"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Volver al menú
      </router-link>
    </div>

    <div v-if="loading" class="text-gray-500">Cargando...</div>

    <div v-else-if="!asignaciones.length" class="text-gray-500">
      No tienes calificaciones finalizadas todavía
    </div>

    <table v-else class="min-w-full bg-white shadow rounded overflow-hidden">
      <thead class="bg-gray-50">
        <tr>
          <th class="text-left px-4 py-2">ID</th>
          <th class="text-left px-4 py-2">Proyecto</th>
          <th class="text-left px-4 py-2">Etapa</th>
          <th class="text-left px-4 py-2">Tipo eval</th>
          <th class="text-left px-4 py-2">Finalizada</th>
          <th class="text-left px-4 py-2">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="a in asignaciones" :key="a.id" class="border-t">
          <td class="px-4 py-2">{{ a.id }}</td>
          <td class="px-4 py-2">
            <div class="font-medium">{{ a.proyecto_id }}</div>
          </td>
          <td class="px-4 py-2">{{ a.etapa_id }}</td>
          <td class="px-4 py-2">{{ a.tipo_eval }}</td>
          <td class="px-4 py-2">
            <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800">
              Finalizada
            </span>
          </td>
          <td class="px-4 py-2">
            <button @click="verCalificacion(a)"
                    class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
              Ver calificación
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from '@/composables/useToast'
import api from '@/services/api'

const router = useRouter()
const { mostrarToast } = useToast()

const asignaciones = ref([])
const loading = ref(false)

const cargar = async () => {
  loading.value = true
  try {
    // Llamar al endpoint de mis asignaciones finalizadas
    const { data } = await api.get('/juez/asignaciones/finalizadas')
    console.log('Respuesta de finalizadas:', data)
    asignaciones.value = Array.isArray(data?.data) ? data.data : []
    console.log('Asignaciones cargadas:', asignaciones.value.length)
  } catch (e) {
    console.error('Error al cargar calificaciones:', e)
    console.error('Response:', e.response?.data)
    mostrarToast('Error cargando calificaciones: ' + (e.response?.data?.message || e.message), 'error')
  } finally {
    loading.value = false
  }
}

const verCalificacion = (a) => {
  router.push({ 
    name: 'juez.calificaciones', 
    query: { 
      proyectoId: a.proyecto_id, 
      etapaId: a.etapa_id,
      finalizada: '1'  // Indicador de que viene de una asignación finalizada
    } 
  })
}

onMounted(cargar)
</script>
