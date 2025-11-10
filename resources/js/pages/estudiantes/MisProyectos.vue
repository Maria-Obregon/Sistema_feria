<template>
  <div class="max-w-6xl mx-auto px-4 py-6">
    <h1 class="text-xl font-semibold text-gray-900 mb-4">Mis proyectos</h1>

    <div class="bg-white border rounded-lg overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50">
          <tr>
            <th class="px-3 py-2 text-left">Título</th>
            <th class="px-3 py-2">Área</th>
            <th class="px-3 py-2">Categoría</th>
            <th class="px-3 py-2">Estado</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in proyectos" :key="p.id" class="border-t">
            <td class="px-3 py-2">{{ p.titulo }}</td>
            <td class="px-3 py-2 text-center">{{ p.area?.nombre }}</td>
            <td class="px-3 py-2 text-center">{{ p.categoria?.nombre }}</td>
            <td class="px-3 py-2 text-center">{{ p.estado }}</td>
          </tr>
          <tr v-if="!proyectos.length">
            <td colspan="4" class="px-3 py-6 text-center text-slate-500">Sin proyectos.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { estudianteSelfApi } from '@/services/api'
const proyectos = ref([])
onMounted(async () => {
  const { data } = await estudianteSelfApi.misProyectos()
  proyectos.value = data || []
})
</script>
