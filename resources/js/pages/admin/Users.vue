<template>
  <div class="p-6">
    <!-- Header -->
<div class="flex justify-between items-center mb-6">
  <div class="flex items-center gap-3">
    <RouterLink
      :to="{ name: 'admin.dashboard' }"        
      class="inline-flex items-center gap-2 px-3 py-2 border rounded-lg text-gray-700 hover:bg-gray-50"
    >
      <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
      </svg>
      Volver
    </RouterLink>
    <div>
      <h1 class="text-2xl font-bold text-gray-900">Gestión de Usuarios</h1>
      <p class="text-gray-600">Administra cuentas, roles y contraseñas</p>
    </div>
  </div>

  <button
    @click="abrirCrear"
    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center gap-2"
  >
    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Nuevo Usuario
  </button>
</div>
    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-sm border p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
          <input
            v-model="filtros.buscar"
            type="text"
            placeholder="Nombre o correo…"
            class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500"
            @input="debouncedBuscar"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
          <select
            v-model="filtros.rol"
            class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500"
            @change="aplicarFiltros"
          >
            <option value="">Todos</option>
            <option v-for="r in roles" :key="r" :value="r">{{ r }}</option>
          </select>
        </div>

        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-1">Institución</label>
          <select
            v-model="filtros.institucion_id"
            class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500"
            @change="aplicarFiltros"
          >
            <option value="">Todas</option>
            <option v-for="inst in institucionesOpts" :key="inst.id" :value="inst.id">
              {{ inst.nombre }}
            </option>
          </select>
        </div>
      </div>
    </div>

    <!-- Tabla -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
      <div v-if="cargando" class="p-8 text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div>
        <p class="mt-2 text-gray-600">Cargando usuarios…</p>
      </div>

      <div v-else-if="usuarios.data?.length === 0" class="p-8 text-center text-gray-500">
        <p>No se encontraron usuarios</p>
      </div>

      <div v-else>
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rol</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Institución</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="u in usuarios.data" :key="u.id" class="hover:bg-gray-50">
              <td class="px-6 py-4">
                <div class="text-sm font-medium text-gray-900">{{ u.nombre }}</div>
                <div class="text-sm text-gray-500">{{ u.email }}</div>
              </td>
              <td class="px-6 py-4">
                <span class="inline-flex px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                  {{ (u.roles?.[0]?.name) ?? '—' }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-700">
                {{ u.institucion?.nombre ?? '—' }}
              </td>
              <td class="px-6 py-4">
                <span
                  :class="u.activo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                  class="inline-flex px-2 py-1 text-xs rounded-full"
                >
                  {{ u.activo ? 'Activo' : 'Inactivo' }}
                </span>
              </td>
              <td class="px-6 py-4 text-right text-sm font-medium">
                <div class="flex justify-end gap-2">
                  <button class="text-indigo-600 hover:text-indigo-900" @click="abrirEditar(u)">Editar</button>
                  <button class="text-yellow-600 hover:text-yellow-900" @click="abrirReset(u)">Reset pass</button>
                  <button class="text-red-600 hover:text-red-900" @click="eliminar(u)">Eliminar</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Paginación -->
        <div v-if="usuarios.last_page > 1" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
              Mostrando <span class="font-medium">{{ usuarios.from }}</span> a
              <span class="font-medium">{{ usuarios.to }}</span> de
              <span class="font-medium">{{ usuarios.total }}</span> resultados
            </div>
            <div>
              <nav class="inline-flex rounded-md shadow-sm -space-x-px">
                <button
                  @click="cambiarPagina(usuarios.current_page - 1)"
                  :disabled="!usuarios.prev_page_url"
                  class="px-2 py-2 border rounded-l-md text-sm text-gray-500 disabled:opacity-50"
                >Anterior</button>
                <button
                  v-for="p in paginasVisibles"
                  :key="p"
                  @click="cambiarPagina(p)"
                  class="px-4 py-2 border text-sm"
                  :class="p === usuarios.current_page
                    ? 'bg-indigo-50 border-indigo-500 text-indigo-600'
                    : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'"
                >{{ p }}</button>
                <button
                  @click="cambiarPagina(usuarios.current_page + 1)"
                  :disabled="!usuarios.next_page_url"
                  class="px-2 py-2 border rounded-r-md text-sm text-gray-500 disabled:opacity-50"
                >Siguiente</button>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Reset Password -->
<div v-if="mostrarReset" class="fixed inset-0 bg-black/30 flex items-center justify-center p-4 z-50">
  <div class="bg-white w-full max-w-md rounded-lg shadow-lg border">
    <div class="px-6 py-4 border-b flex justify-between items-center bg-gray-50">
      <h3 class="text-lg font-medium">Restablecer contraseña</h3>
      <button @click="cerrarReset" class="text-gray-400 hover:text-gray-600">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    <div class="p-6">
      <div class="mb-4">
        <div class="text-sm text-gray-600">Usuario</div>
        <div class="font-medium text-gray-900">{{ usuarioReset?.nombre }} <span class="text-gray-500">({{ usuarioReset?.email }})</span></div>
      </div>

      <form @submit.prevent="enviarReset">
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nueva contraseña *</label>
            <input
              v-model="reset.password"
              type="password"
              minlength="8"
              required
              class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500"
              placeholder="Mínimo 8 caracteres"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar contraseña *</label>
            <input
              v-model="reset.confirm"
              type="password"
              minlength="8"
              required
              class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500"
              placeholder="Repite la contraseña"
            />
            <p v-if="reset.confirm && reset.confirm !== reset.password" class="mt-1 text-sm text-red-600">
              Las contraseñas no coinciden
            </p>
          </div>
        </div>

        <div class="mt-6 flex justify-end gap-3">
          <button type="button" @click="cerrarReset"
                  class="px-4 py-2 border rounded-md text-gray-700 hover:bg-gray-50">Cancelar</button>
          <button type="submit" :disabled="enviandoReset || !resetValida"
                  class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50">
            <span v-if="enviandoReset">Guardando…</span>
            <span v-else>Actualizar</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>


    <!-- Modal Crear/Editar -->
    <div v-if="mostrarModal" class="fixed inset-0 bg-black/30 flex items-center justify-center p-4 z-50">
      <div class="bg-white w-full max-w-xl rounded-lg shadow-lg border">
        <div class="px-6 py-4 border-b flex justify-between items-center bg-gray-50">
          <h3 class="text-lg font-medium">
            {{ editando ? 'Editar Usuario' : 'Nuevo Usuario' }}
          </h3>
          <button @click="cerrarModal" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <div class="p-6">
          <form @submit.prevent="guardar">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                <input v-model="form.nombre" required class="w-full px-3 py-2 border rounded-md"/>
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Correo (usuario) *</label>
                <input v-model="form.email" type="email" required class="w-full px-3 py-2 border rounded-md"/>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Rol *</label>
                <select v-model="form.role" required class="w-full px-3 py-2 border rounded-md">
                  <option value="">Selecciona rol</option>
                  <option v-for="r in roles" :key="r" :value="r">{{ r }}</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Institución</label>
                <select v-model="form.institucion_id" class="w-full px-3 py-2 border rounded-md">
                  <option value="">Sin institución</option>
                  <option v-for="inst in institucionesOpts" :key="inst.id" :value="inst.id">
                    {{ inst.nombre }}
                  </option>
                </select>
              </div>

              <div class="md:col-span-2" v-if="!editando">
                <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                <input v-model="form.password" type="password" placeholder="(opcional: si vacía, se genera)"
                       class="w-full px-3 py-2 border rounded-md"/>
              </div>

              <div class="md:col-span-2">
                <label class="inline-flex items-center">
                  <input v-model="form.activo" type="checkbox" class="rounded border-gray-300 text-indigo-600"/>
                  <span class="ml-2 text-sm text-gray-700">Usuario activo</span>
                </label>
              </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
              <button type="button" @click="cerrarModal"
                      class="px-4 py-2 border rounded-md text-gray-700 hover:bg-gray-50">Cancelar</button>
              <button type="submit" :disabled="guardando"
                      class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50">
                <span v-if="guardando">Guardando…</span>
                <span v-else>{{ editando ? 'Actualizar' : 'Crear' }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { adminApi, usuariosApi, institucionesApi } from '@/services/api'
import { useToast } from '@/composables/useToast'

const { mostrarToast } = useToast()

// Estado
const cargando = ref(false)
const usuarios = ref({ data: [], current_page: 1, last_page: 1, total: 0, from: 0, to: 0 })
const roles = ref([])
const institucionesOpts = ref([])
const filtros = reactive({ buscar: '', rol: '', institucion_id: '' })

// Modal
const mostrarModal = ref(false)
const editando = ref(false)
const usuarioEdit = ref(null)
const guardando = ref(false)
const form = reactive({
  nombre: '',
  email: '',
  role: '',
  institucion_id: '',
  password: '',
  activo: true
})

// Helpers
const paginasVisibles = computed(() => {
  const p = []
  const actual = usuarios.value.current_page || 1
  const total = usuarios.value.last_page || 1
  const ini = Math.max(1, actual - 2)
  const fin = Math.min(total, actual + 2)
  for (let i = ini; i <= fin; i++) p.push(i)
  return p
})

let tBuscar = null
const debouncedBuscar = () => {
  clearTimeout(tBuscar)
  tBuscar = setTimeout(() => cargarUsuarios(), 400)
}

const aplicarFiltros = () => cargarUsuarios()

// Carga principal
const cargarUsuarios = async (page = 1) => {
  try {
    cargando.value = true
    // Pasamos filtros si tu backend los soporta; si no, filtra client-side opcionalmente
    const params = {
      page,
      buscar: filtros.buscar || undefined,
      rol: filtros.rol || undefined,
      institucion_id: filtros.institucion_id || undefined
    }
    const res = await usuariosApi.listar(params)
    const payload = res.data
    // Normalización de paginación
    if (Array.isArray(payload)) {
      usuarios.value = { data: payload, current_page: 1, last_page: 1, total: payload.length, from: payload.length ? 1 : 0, to: payload.length }
    } else if (Array.isArray(payload?.data)) {
      usuarios.value = payload
    } else if (Array.isArray(payload?.data?.data)) {
      usuarios.value = payload.data
    } else {
      usuarios.value = { data: [], current_page: 1, last_page: 1, total: 0, from: 0, to: 0 }
    }
  } catch (e) {
    console.error('Error cargando usuarios:', e)
    mostrarToast('Error al cargar usuarios', 'error')
  } finally {
    cargando.value = false
  }
}

const cargarRoles = async () => {
  try {
    const { data } = await adminApi.roles()
    roles.value = data
  } catch {
    roles.value = []
  }
}

const cargarInstituciones = async () => {
  try {
    // Trae la primera página; si necesitas todas, puedes pedir per_page alto o un endpoint simple
    const { data } = await institucionesApi.listar({ per_page: 1000 })
    const list = Array.isArray(data?.data) ? data.data : (Array.isArray(data) ? data : [])
    institucionesOpts.value = list.map(i => ({ id: i.id, nombre: i.nombre }))
  } catch {
    institucionesOpts.value = []
  }
}

// Acciones UI
const abrirCrear = () => {
  resetForm()
  editando.value = false
  usuarioEdit.value = null
  mostrarModal.value = true
}

const abrirEditar = (u) => {
  resetForm()
  editando.value = true
  usuarioEdit.value = u
  form.nombre = u.nombre
  form.email = u.email
  form.role = u.roles?.[0]?.name || ''
  form.institucion_id = u.institucion_id || ''
  form.activo = !!u.activo
  mostrarModal.value = true
}

const cerrarModal = () => {
  mostrarModal.value = false
  usuarioEdit.value = null
  resetForm()
}

const resetForm = () => {
  form.nombre = ''
  form.email = ''
  form.role = ''
  form.institucion_id = ''
  form.password = ''
  form.activo = true
}

const guardar = async () => {
  try {
    guardando.value = true
    if (editando.value && usuarioEdit.value) {
      // 1) Actualiza datos básicos
      await usuariosApi.actualizar(usuarioEdit.value.id, {
        nombre: form.nombre,
        email: form.email,
        activo: form.activo,
        institucion_id: form.institucion_id || null
      })
      // 2) Sincroniza rol (único)
      if (form.role) {
        await adminApi.actualizarRoles(usuarioEdit.value.id, [form.role])
      }
      mostrarToast('Usuario actualizado', 'success')
    } else {
      // Crear + asignar rol + enviar correo (password opcional)
      await usuariosApi.crear({
        nombre: form.nombre,
        email: form.email,
        password: form.password || undefined, // si va vacío, el backend genera una
        role: form.role,
        activo: form.activo,
        institucion_id: form.institucion_id || null
      })
      mostrarToast('Usuario creado y credenciales enviadas', 'success')
    }
    cerrarModal()
    cargarUsuarios()
  } catch (e) {
    console.error('Error guardando usuario:', e)
    const mensaje = e.response?.data?.mensaje || 'Error al guardar usuario'
    mostrarToast(mensaje, 'error')
  } finally {
    guardando.value = false
  }
}

const mostrarReset = ref(false)
const usuarioReset = ref(null)
const enviandoReset = ref(false)
const reset = reactive({ password: '', confirm: '' })

const resetValida = computed(() =>
  reset.password?.length >= 8 && reset.password === reset.confirm
)

const abrirReset = (u) => {
  usuarioReset.value = u
  reset.password = ''
  reset.confirm = ''
  mostrarReset.value = true
}

const cerrarReset = () => {
  mostrarReset.value = false
  usuarioReset.value = null
  reset.password = ''
  reset.confirm = ''
}

const enviarReset = async () => {
  if (!resetValida.value || !usuarioReset.value) return
  try {
    enviandoReset.value = true
    await adminApi.resetPassword(usuarioReset.value.id, reset.password)
    mostrarToast('Contraseña actualizada', 'success')
    cerrarReset()
  } catch (e) {
    mostrarToast('Error al restablecer contraseña', 'error')
  } finally {
    enviandoReset.value = false
  }
}

const eliminar = async (u) => {
  if (!confirm(`¿Eliminar a ${u.nombre}?`)) return
  try {
    await usuariosApi.eliminar(u.id)
    mostrarToast('Usuario eliminado', 'success')
    cargarUsuarios()
  } catch (e) {
    const msg = e.response?.data?.mensaje || 'Error al eliminar usuario'
    mostrarToast(msg, 'error')
  }
}

const cambiarPagina = (p) => {
  if (p >= 1 && p <= (usuarios.value.last_page || 1)) {
    cargarUsuarios(p)
  }
}

// Lifecycle
onMounted(async () => {
  await Promise.all([cargarRoles(), cargarInstituciones()])
  await cargarUsuarios()
})
</script>
