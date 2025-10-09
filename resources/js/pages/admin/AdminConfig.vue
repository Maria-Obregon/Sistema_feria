<template>
  <div class="max-w-6xl mx-auto py-6 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-bold mb-6">Configuración del Sistema</h1>

    <!-- Tabs -->
    <div class="flex gap-2 mb-6">
      <button
        v-for="t in tabs"
        :key="t.key"
        @click="activeTab = t.key"
        class="px-4 py-2 rounded-md border"
        :class="activeTab === t.key
          ? 'bg-indigo-600 text-white border-indigo-600'
          : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'"
      >
        {{ t.label }}
      </button>
    </div>

    <!-- Modalidades -->
    <div v-if="activeTab === 'modalidades'">
      <CatalogoCrud
        titulo="Modalidades"
        :items="modalidades"
        :columns="[{key:'nombre',label:'Nombre'},{key:'activo',label:'Activo'}]"
        :loading="loading.modalidades"
        @crear="crearModalidad"
        @actualizar="actualizarModalidad"
        @eliminar="eliminarModalidad"
        @recargar="cargarModalidades"
      />
    </div>

    <!-- Áreas -->
    <div v-else-if="activeTab === 'areas'">
      <CatalogoCrud
        titulo="Áreas"
        :items="areas"
        :columns="[{key:'nombre',label:'Nombre'},{key:'activo',label:'Activo'}]"
        :loading="loading.areas"
        @crear="crearArea"
        @actualizar="actualizarArea"
        @eliminar="eliminarArea"
        @recargar="cargarAreas"
      />
    </div>

    <!-- Categorías (nivel desde tabla 'niveles') -->
    <div v-else-if="activeTab === 'categorias'">
      <CatalogoCrud
        titulo="Categorías"
        :items="categorias"
        :columns="[
          {key:'nombre',label:'Nombre'},
          {key:'nivel',label:'Nivel'}
        ]"
        :loading="loading.categorias"
        :select-options="{ nivel: nivelOptions }"
        :display-map="{ nivel: nivelLabelMap }"
        @crear="crearCategoria"
        @actualizar="actualizarCategoria"
        @eliminar="eliminarCategoria"
        @recargar="cargarCategorias"
      />
    </div>

    <!-- Tipos de Institución -->
    <div v-else-if="activeTab === 'tipos'">
      <CatalogoCrud
        titulo="Tipos de Institución"
        :items="tipos"
        :columns="[{key:'nombre',label:'Nombre'},{key:'activo',label:'Activo'}]"
        :loading="loading.tipos"
        @crear="crearTipo"
        @actualizar="actualizarTipo"
        @eliminar="eliminarTipo"
        @recargar="cargarTipos"
      />
    </div>

    <!-- Niveles (catálogo libre: nombre + activo) -->
    <div v-else-if="activeTab === 'niveles'">
      <CatalogoCrud
        titulo="Niveles"
        :items="niveles"
        :columns="[
          {key:'nombre',label:'Nombre'},
          {key:'activo',label:'Activo'}
        ]"
        :loading="loading.niveles"
        @crear="crearNivel"
        @actualizar="actualizarNivel"
        @eliminar="eliminarNivel"
        @recargar="cargarNiveles"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { adminApi } from '@/services/api'

/* =========================
   Componente CRUD reutilizable
   ========================= */
const CatalogoCrud = {
  props: {
    titulo: String,
    items: Array,
    columns: Array,
    loading: Boolean,
    selectOptions: { type: Object, default: () => ({}) }, // { campo: [{value,label}] }
    displayMap: { type: Object, default: () => ({}) },    // { campo: {value:label} }
  },
  emits: ['crear','actualizar','eliminar','recargar'],
  data: () => ({
    crearOpen: false,
    editItem: null,
    form: {},
    saving: false,
  }),
  watch: {
    crearOpen(v) { if (v) this.form = {} },
  },
  methods: {
    fieldIsSelect(key) { return !!this.selectOptions?.[key] },
    optionsFor(key) { return this.selectOptions?.[key] ?? [] },
    displayFor(item, key) {
      const map = this.displayMap?.[key]
      if (!map) return item[key] ?? '—'
      return map[item[key]] ?? item[key] ?? '—'
    },
    startEdit(item) {
      this.editItem = { ...item }
      this.form = { ...item }
      this.crearOpen = true
    },
    async submit() {
      // Validación mínima
      if (this.columns.some(c => c.key === 'nombre') && !this.form?.nombre) {
        alert('El nombre es obligatorio')
        return
      }
      // Para cualquier select declarado
      for (const key of Object.keys(this.selectOptions || {})) {
        const opts = this.optionsFor(key)
        if (opts.length && !opts.some(o => o.value === this.form?.[key])) {
          alert(`Seleccioná un valor válido para "${key}"`)
          return
        }
      }

      this.saving = true
      try {
        if (this.editItem?.id) {
          await this.$emit('actualizar', this.editItem.id, { ...this.form })
        } else {
          await this.$emit('crear', { ...this.form })
        }
        this.crearOpen = false
        this.form = {}
        this.editItem = null
      } catch (e) {
        const msg = e?.response?.data?.message
          ?? e?.response?.data?.error
          ?? e?.message
          ?? 'Error al guardar'
        alert(msg)
      } finally {
        this.saving = false
      }
    },
    cancel() {
      this.crearOpen = false
      this.form = {}
      this.editItem = null
    },
  },
  template: `
    <div class="bg-white rounded-lg shadow-sm border">
      <div class="p-4 border-b flex items-center justify-between">
        <h2 class="text-lg font-semibold">{{ titulo }}</h2>
        <div class="flex items-center gap-2">
          <button @click="$emit('recargar')" class="px-3 py-2 border rounded-md text-sm hover:bg-gray-50">Recargar</button>
          <button @click="crearOpen = true" class="px-3 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">Nuevo</button>
        </div>
      </div>

      <div v-if="loading" class="p-6 text-gray-500">Cargando…</div>

      <div v-else class="p-4 overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th v-for="c in columns" :key="c.key" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ c.label }}</th>
              <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="it in items" :key="it.id">
              <td v-for="c in columns" :key="c.key" class="px-4 py-2 text-sm">
                <template v-if="c.key === 'activo'">
                  <span :class="it.activo ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-700'"
                        class="inline-flex px-2 py-0.5 text-xs rounded-full">
                    {{ it.activo ? 'Sí' : 'No' }}
                  </span>
                </template>
                <template v-else-if="fieldIsSelect(c.key) && displayMap?.[c.key]">
                  {{ displayFor(it, c.key) }}
                </template>
                <template v-else>
                  {{ it[c.key] ?? '—' }}
                </template>
              </td>
              <td class="px-4 py-2 text-right whitespace-nowrap">
                <button class="text-indigo-600 hover:text-indigo-900 mr-3" @click="startEdit(it)">Editar</button>
                <button class="text-red-600 hover:text-red-900" @click="$emit('eliminar', it.id)">Eliminar</button>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-if="!items?.length" class="text-center text-gray-500 py-6">Sin registros</div>
      </div>

      <!-- Modal -->
      <div v-if="crearOpen" class="fixed inset-0 bg-black/30 flex items-center justify-center p-4 z-50">
        <div class="bg-white w-full max-w-md rounded-lg shadow-lg border">
          <div class="px-4 py-3 border-b flex justify-between items-center bg-gray-50">
            <h3 class="text-base font-medium">{{ editItem ? 'Editar' : 'Nuevo' }} {{ titulo.slice(0,-1) }}</h3>
            <button @click="cancel" class="text-gray-400 hover:text-gray-600">✕</button>
          </div>

          <div class="p-4 space-y-3">
            <div v-for="c in columns" :key="c.key">
              <!-- nombre -->
              <template v-if="c.key === 'nombre'">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                <input v-model="form.nombre" class="w-full px-3 py-2 border rounded-md" />
              </template>

              <!-- activo -->
              <template v-else-if="c.key === 'activo'">
                <label class="inline-flex items-center">
                  <input type="checkbox" v-model="form.activo" class="rounded border-gray-300 text-indigo-600" />
                  <span class="ml-2 text-sm text-gray-700">Activo</span>
                </label>
              </template>

              <!-- selects dinámicos (ej: nivel en Categorías) -->
              <template v-else-if="fieldIsSelect(c.key)">
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ c.label }}</label>
                <select v-model="form[c.key]" class="w-full px-3 py-2 border rounded-md">
                  <option value="">Selecciona…</option>
                  <option v-for="opt in optionsFor(c.key)" :key="opt.value" :value="opt.value">
                    {{ opt.label }}
                  </option>
                </select>
              </template>
            </div>
          </div>

          <div class="p-4 border-t flex justify-end gap-2">
            <button @click="cancel" class="px-3 py-2 border rounded-md hover:bg-gray-50" :disabled="saving">Cancelar</button>
            <button @click="submit" class="px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700" :disabled="saving">
              {{ saving ? 'Guardando…' : 'Guardar' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  `
}

/* =========================
   Tabs
   ========================= */
const tabs = [
  { key: 'modalidades', label: 'Modalidades' },
  { key: 'areas',       label: 'Áreas' },
  { key: 'categorias',  label: 'Categorías' },
  { key: 'tipos',       label: 'Tipos de Institución' },
  { key: 'niveles',     label: 'Niveles' },
]
const activeTab = ref('modalidades')

/* =========================
   State
   ========================= */
const modalidades = ref([])
const areas       = ref([])
const categorias  = ref([])
const tipos       = ref([])
const niveles     = ref([])

const loading = reactive({
  modalidades: false,
  areas: false,
  categorias: false,
  tipos: false,
  niveles: false,
})

/* =========================
   Util error
   ========================= */
const pullMsg = (e, fallback = 'Error') =>
  e?.response?.data?.message ?? e?.response?.data?.error ?? e?.message ?? fallback

/* =========================
   Derivados para Categorías <- Niveles
   ========================= */
const nivelOptions = computed(() =>
  niveles.value.map(n => ({ value: n.nombre, label: n.nombre }))
)
const nivelLabelMap = computed(() => {
  const m = {}; for (const n of niveles.value) m[n.nombre] = n.nombre; return m;
})

/* =========================
   Modalidades
   ========================= */
const cargarModalidades = async () => {
  loading.modalidades = true
  try { const { data } = await adminApi.modalidades.listar(); modalidades.value = Array.isArray(data) ? data : [] }
  catch (e) { alert(pullMsg(e, 'Error cargando modalidades')) }
  finally { loading.modalidades = false }
}
const crearModalidad = async (p) => {
  try { await adminApi.modalidades.crear({ nombre: p.nombre, activo: p.activo ?? true }); await cargarModalidades() }
  catch (e) { alert(pullMsg(e, 'Error creando modalidad')) }
}
const actualizarModalidad = async (id, p) => {
  try { await adminApi.modalidades.actualizar(id, { nombre: p.nombre, activo: p.activo ?? true }); await cargarModalidades() }
  catch (e) { alert(pullMsg(e, 'Error actualizando modalidad')) }
}
const eliminarModalidad = async (id) => {
  if (!confirm('¿Eliminar modalidad?')) return
  try { await adminApi.modalidades.eliminar(id); await cargarModalidades() }
  catch (e) { alert(pullMsg(e, 'Error eliminando modalidad')) }
}

/* =========================
   Áreas
   ========================= */
const cargarAreas = async () => {
  loading.areas = true
  try { const { data } = await adminApi.areas.listar(); areas.value = Array.isArray(data) ? data : [] }
  catch (e) { alert(pullMsg(e, 'Error cargando áreas')) }
  finally { loading.areas = false }
}
const crearArea = async (p) => {
  try { await adminApi.areas.crear({ nombre: p.nombre, activo: p.activo ?? true }); await cargarAreas() }
  catch (e) { alert(pullMsg(e, 'Error creando área')) }
}
const actualizarArea = async (id, p) => {
  try { await adminApi.areas.actualizar(id, { nombre: p.nombre, activo: p.activo ?? true }); await cargarAreas() }
  catch (e) { alert(pullMsg(e, 'Error actualizando área')) }
}
const eliminarArea = async (id) => {
  if (!confirm('¿Eliminar área?')) return
  try { await adminApi.areas.eliminar(id); await cargarAreas() }
  catch (e) { alert(pullMsg(e, 'Error eliminando área')) }
}

/* =========================
   Categorías (nivel por nombre de 'niveles')
   ========================= */
const cargarCategorias = async () => {
  loading.categorias = true
  try { const { data } = await adminApi.categorias.listar(); categorias.value = Array.isArray(data) ? data : [] }
  catch (e) { alert(pullMsg(e, 'Error cargando categorías')) }
  finally { loading.categorias = false }
}
const crearCategoria = async (p) => {
  if (!p?.nivel) return alert('Seleccioná un nivel')
  try { await adminApi.categorias.crear({ nombre: p.nombre, nivel: p.nivel }); await cargarCategorias() }
  catch (e) { alert(pullMsg(e, 'Error creando categoría')) }
}
const actualizarCategoria = async (id, p) => {
  if (!p?.nivel) return alert('Seleccioná un nivel')
  try { await adminApi.categorias.actualizar(id, { nombre: p.nombre, nivel: p.nivel }); await cargarCategorias() }
  catch (e) { alert(pullMsg(e, 'Error actualizando categoría')) }
}
const eliminarCategoria = async (id) => {
  if (!confirm('¿Eliminar categoría?')) return
  try { await adminApi.categorias.eliminar(id); await cargarCategorias() }
  catch (e) { alert(pullMsg(e, 'Error eliminando categoría')) }
}

/* =========================
   Tipos de Institución
   ========================= */
const cargarTipos = async () => {
  loading.tipos = true
  try { const { data } = await adminApi.tiposInstitucion.listar(); tipos.value = Array.isArray(data) ? data : [] }
  catch (e) { alert(pullMsg(e, 'Error cargando tipos de institución')) }
  finally { loading.tipos = false }
}
const crearTipo = async (p) => {
  try { await adminApi.tiposInstitucion.crear({ nombre: p.nombre, activo: p.activo ?? true }); await cargarTipos() }
  catch (e) { alert(pullMsg(e, 'Error creando tipo de institución')) }
}
const actualizarTipo = async (id, p) => {
  try { await adminApi.tiposInstitucion.actualizar(id, { nombre: p.nombre, activo: p.activo ?? true }); await cargarTipos() }
  catch (e) { alert(pullMsg(e, 'Error actualizando tipo de institución')) }
}
const eliminarTipo = async (id) => {
  if (!confirm('¿Eliminar tipo?')) return
  try { await adminApi.tiposInstitucion.eliminar(id); await cargarTipos() }
  catch (e) { alert(pullMsg(e, 'Error eliminando tipo de institución')) }
}

/* =========================
   Niveles (catálogo libre)
   ========================= */
const cargarNiveles = async () => {
  loading.niveles = true
  try { const { data } = await adminApi.niveles.listar(); niveles.value = Array.isArray(data) ? data : [] }
  catch (e) { alert(pullMsg(e, 'Error cargando niveles')) }
  finally { loading.niveles = false }
}
const crearNivel = async (p) => {
  if (!p?.nombre) return alert('Ingresá el nombre del nivel')
  try { await adminApi.niveles.crear({ nombre: p.nombre, activo: p.activo ?? true }); await cargarNiveles() }
  catch (e) { alert(pullMsg(e, 'Error creando nivel')) }
}
const actualizarNivel = async (id, p) => {
  if (!p?.nombre) return alert('Ingresá el nombre del nivel')
  try { await adminApi.niveles.actualizar(id, { nombre: p.nombre, activo: p.activo ?? true }); await cargarNiveles() }
  catch (e) { alert(pullMsg(e, 'Error actualizando nivel')) }
}
const eliminarNivel = async (id) => {
  if (!confirm('¿Eliminar nivel?')) return
  try { await adminApi.niveles.eliminar(id); await cargarNiveles() }
  catch (e) { alert(pullMsg(e, 'Error eliminando nivel')) }
}

/* =========================
   Carga inicial (primero Niveles)
   ========================= */
onMounted(async () => {
  await cargarNiveles()
  await Promise.all([
    cargarModalidades(),
    cargarAreas(),
    cargarCategorias(),
    cargarTipos(),
  ])
})
</script>
