<template>
  <div class="max-w-6xl mx-auto px-4 py-6">
    <h1 class="text-xl font-semibold text-gray-900 mb-4">Certificados</h1>

    <div class="bg-white border rounded-lg overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50">
          <tr>
            <th class="px-3 py-2 text-left">Proyecto</th>
            <th class="px-3 py-2 text-center">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in proyectos" :key="p.id" class="border-t">
            <td class="px-3 py-2">{{ p.titulo }}</td>
            <td class="px-3 py-2 text-center space-x-2">
              <button @click="descargarCertificado(p.id)" class="px-3 py-1 rounded bg-blue-600 text-white">Certificado</button>
              <button @click="descargarPronafecyt" class="px-3 py-1 rounded bg-slate-700 text-white">PRONAFECYT</button>
            </td>
          </tr>
          <tr v-if="!proyectos.length">
            <td colspan="2" class="px-3 py-6 text-center text-slate-500">Sin certificados disponibles.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { estudianteSelfApi, certificadosApi, docsApi } from '@/services/api'

const proyectos = ref([])
const load = async () => {
  const { data } = await estudianteSelfApi.misProyectos()
  proyectos.value = data || []
}
const descargarCertificado = async (proyectoId) => {
  await certificadosApi.participacion(proyectoId)
}
const descargarPronafecyt = async () => {
  await docsApi.descargarPronafecyt()
}
onMounted(load)
</script>
