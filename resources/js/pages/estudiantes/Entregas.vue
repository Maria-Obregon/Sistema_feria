<template>
  <div class="max-w-6xl mx-auto px-4 py-6">
    <h1 class="text-xl font-semibold text-gray-900 mb-4">Entregas</h1>

    <div class="bg-white border rounded-lg overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50">
          <tr>
            <th class="px-3 py-2 text-left">Proyecto</th>
            <th class="px-3 py-2 text-center">Entregadas</th>
            <th class="px-3 py-2 text-center">Pendientes</th>
            <th class="px-3 py-2 text-center">Estado</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="i in items" :key="i.id" class="border-t">
            <td class="px-3 py-2">{{ i.titulo }}</td>
            <td class="px-3 py-2 text-center">{{ i.entregadas }}/{{ i.esperadas }}</td>
            <td class="px-3 py-2 text-center">
              <span :class="i.pendientes ? 'text-amber-700' : 'text-green-700'">{{ i.pendientes }}</span>
            </td>
            <td class="px-3 py-2 text-center">{{ i.estado }}</td>
          </tr>
          <tr v-if="!items.length">
            <td colspan="4" class="px-3 py-6 text-center text-slate-500">No hay entregas registradas.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="mt-3 text-sm text-slate-600">
      * Se consideran entregas: Archivo del proyecto y Archivo de presentación.
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { estudianteSelfApi } from '@/services/api'
const items = ref([])
onMounted(async () => {
  const { data } = await estudianteSelfApi.misEntregas()
  items.value = data?.items || []
})
</script>
