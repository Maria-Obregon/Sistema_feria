<template>
  <div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">
      {{ isEdit ? 'Editar institución' : 'Nueva institución' }}
    </h1>

    <div class="bg-white rounded-lg shadow-sm border p-6">
      <form class="space-y-4" @submit.prevent="guardar">
        <!-- Nombre -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
          <input v-model="form.nombre" class="w-full px-3 py-2 border rounded-md" />
        </div>

        <!-- Tipo de institución -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
          <select v-model="form.tipo_institucion_id"
                  class="w-full px-3 py-2 border rounded-md"
                  :disabled="cargandoCatalogos">
            <option :value="null">Seleccionar tipo</option>
            <option v-for="t in tiposInstitucion" :key="t.id" :value="t.id">
              {{ t.nombre }}
            </option>
          </select>
        </div>

        <!-- Modalidad -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Modalidad *</label>
          <select v-model="form.modalidad_id"
                  class="w-full px-3 py-2 border rounded-md"
                  :disabled="cargandoCatalogos">
            <option :value="null">Seleccionar modalidad</option>
            <option v-for="m in modalidades" :key="m.id" :value="m.id">
              {{ m.nombre }}
            </option>
          </select>
        </div>

        <!-- Estado de error (opcional) -->
        <p v-if="errorMsg" class="text-sm text-red-600">{{ errorMsg }}</p>

        <!-- Botones -->
        <div class="pt-4 flex gap-2 justify-end">
          <button type="button" @click="volver" class="px-3 py-2 border rounded-md hover:bg-gray-50" :disabled="guardando">
            Cancelar
          </button>
          <button type="submit" class="px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-60" :disabled="guardando">
            {{ guardando ? 'Guardando…' : (isEdit ? 'Actualizar' : 'Guardar') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { institucionesApi } from '@/services/api'

const route = useRoute()
const router = useRouter()
const isEdit = computed(() => !!route.params.id)

const form = ref({
  nombre: '',
  tipo_institucion_id: null, // enviamos IDs
  modalidad_id: null,        // enviamos IDs
})

const modalidades = ref([])        // [{id,nombre}]
const tiposInstitucion = ref([])   // [{id,nombre}]
const cargandoCatalogos = ref(false)
const cargandoInst = ref(false)
const guardando = ref(false)
const errorMsg = ref('')

const cargarCatalogos = async () => {
  cargandoCatalogos.value = true
  errorMsg.value = ''
  try {
    const { data } = await institucionesApi.obtenerCatalogos()
    // vienen (idealmente) filtradas por activo desde el backend
    modalidades.value      = data?.modalidades ?? []
    tiposInstitucion.value = data?.tipos_institucion ?? []
  } catch (e) {
    errorMsg.value = e?.response?.data?.message || e?.message || 'Error cargando catálogos'
  } finally {
    cargandoCatalogos.value = false
  }
}

const cargarInstitucion = async (id) => {
  cargandoInst.value = true
  errorMsg.value = ''
  try {
    const { data } = await institucionesApi.obtener(id)
    form.value = {
      nombre: data?.nombre ?? '',
      // tomamos id directo si existe; si no, intentamos desde relaciones eager-loaded
      tipo_institucion_id: data?.tipo_institucion_id ?? data?.tipo_institucion?.id ?? null,
      modalidad_id: data?.modalidad_id ?? data?.modalidad?.id ?? null,
    }
  } catch (e) {
    errorMsg.value = e?.response?.data?.message || e?.message || 'Error cargando la institución'
  } finally {
    cargandoInst.value = false
  }
}

const guardar = async () => {
  errorMsg.value = ''
  if (!form.value.nombre) return (errorMsg.value = 'Ingresá el nombre')
  if (!form.value.tipo_institucion_id) return (errorMsg.value = 'Seleccioná el tipo')
  if (!form.value.modalidad_id) return (errorMsg.value = 'Seleccioná la modalidad')

  guardando.value = true
  try {
    if (isEdit.value) {
      await institucionesApi.actualizar(route.params.id, form.value)
      alert('Institución actualizada')
    } else {
      await institucionesApi.crear(form.value)
      alert('Institución creada')
    }
    volver()
  } catch (e) {
    errorMsg.value = e?.response?.data?.message || e?.message || 'Error al guardar'
  } finally {
    guardando.value = false
  }
}

const volver = () => router.push({ name: 'instituciones.index' }) // ajusta a tu ruta

onMounted(async () => {
  await cargarCatalogos() // primero catálogos para tener opciones listas
  if (isEdit.value) await cargarInstitucion(route.params.id)
})
</script>
