<template>
  <div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Gestión de Usuarios</h1>
      <button 
        @click="showCreateModal = true"
        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
      >
        Nuevo Usuario
      </button>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <input
          v-model="filters.search"
          type="text"
          placeholder="Buscar por nombre, email o cédula..."
          class="px-4 py-2 border rounded-lg"
          @input="debouncedSearch"
        >
        <select
          v-model="filters.rol_id"
          @change="fetchUsers"
          class="px-4 py-2 border rounded-lg"
        >
          <option value="">Todos los roles</option>
          <option v-for="rol in roles" :key="rol.id" :value="rol.id">
            {{ rol.nombre }}
          </option>
        </select>
        <select
          v-model="filters.institucion_id"
          @change="fetchUsers"
          class="px-4 py-2 border rounded-lg"
        >
          <option value="">Todas las instituciones</option>
          <option v-for="inst in instituciones" :key="inst.id" :value="inst.id">
            {{ inst.nombre }}
          </option>
        </select>
      </div>
    </div>

    <!-- Tabla de usuarios -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <table class="min-w-full">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Usuario
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Rol
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Institución
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Estado
            </th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
              Acciones
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="usuario in usuarios" :key="usuario.id">
            <td class="px-6 py-4 whitespace-nowrap">
              <div>
                <div class="text-sm font-medium text-gray-900">{{ usuario.nombre }}</div>
                <div class="text-sm text-gray-500">{{ usuario.email }}</div>
                <div class="text-xs text-gray-400">Cédula: {{ usuario.cedula }}</div>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                {{ usuario.rol?.nombre || 'Sin rol' }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ usuario.institucion?.nombre || 'N/A' }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span 
                :class="usuario.activo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                class="px-2 py-1 text-xs rounded-full"
              >
                {{ usuario.activo ? 'Activo' : 'Inactivo' }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <button
                @click="editUser(usuario)"
                class="text-indigo-600 hover:text-indigo-900 mr-3"
              >
                Editar
              </button>
              <button
                @click="toggleUserStatus(usuario)"
                class="text-yellow-600 hover:text-yellow-900 mr-3"
              >
                {{ usuario.activo ? 'Desactivar' : 'Activar' }}
              </button>
              <button
                @click="confirmDelete(usuario)"
                class="text-red-600 hover:text-red-900"
              >
                Eliminar
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Paginación -->
      <div v-if="pagination.total > pagination.per_page" class="bg-gray-50 px-4 py-3 sm:px-6">
        <div class="flex justify-between">
          <button
            @click="changePage(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="px-3 py-1 rounded border"
            :class="pagination.current_page === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100'"
          >
            Anterior
          </button>
          <span class="text-sm text-gray-700">
            Página {{ pagination.current_page }} de {{ pagination.last_page }}
          </span>
          <button
            @click="changePage(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="px-3 py-1 rounded border"
            :class="pagination.current_page === pagination.last_page ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100'"
          >
            Siguiente
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Crear/Editar -->
    <div v-if="showModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center">
      <div class="bg-white rounded-lg p-6 max-w-lg w-full max-h-screen overflow-y-auto">
        <h3 class="text-lg font-medium mb-4">
          {{ editingUser ? 'Editar Usuario' : 'Crear Usuario' }}
        </h3>
        
        <form @submit.prevent="saveUser">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Nombre</label>
              <input
                v-model="form.nombre"
                type="text"
                required
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"
              >
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Email</label>
              <input
                v-model="form.email"
                type="email"
                required
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"
              >
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Cédula</label>
              <input
                v-model="form.cedula"
                type="text"
                required
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"
              >
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Teléfono</label>
              <input
                v-model="form.telefono"
                type="text"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"
              >
            </div>

            <div v-if="!editingUser">
              <label class="block text-sm font-medium text-gray-700">Contraseña</label>
              <input
                v-model="form.password"
                type="password"
                required
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"
              >
            </div>

            <div v-if="!editingUser">
              <label class="block text-sm font-medium text-gray-700">Confirmar Contraseña</label>
              <input
                v-model="form.password_confirmation"
                type="password"
                required
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"
              >
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Rol</label>
              <select
                v-model="form.rol_id"
                required
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"
              >
                <option value="">Seleccione un rol</option>
                <option v-for="rol in roles" :key="rol.id" :value="rol.id">
                  {{ rol.nombre }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Institución</label>
              <select
                v-model="form.institucion_id"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"
              >
                <option value="">Sin institución</option>
                <option v-for="inst in instituciones" :key="inst.id" :value="inst.id">
                  {{ inst.nombre }}
                </option>
              </select>
            </div>

            <div class="flex items-center">
              <input
                v-model="form.activo"
                type="checkbox"
                class="h-4 w-4 text-indigo-600 border-gray-300 rounded"
              >
              <label class="ml-2 block text-sm text-gray-900">
                Usuario activo
              </label>
            </div>
          </div>

          <div class="mt-6 flex justify-end space-x-3">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="loading"
              class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
            >
              {{ loading ? 'Guardando...' : 'Guardar' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import axios from 'axios'

// Estado
const usuarios = ref([])
const roles = ref([])
const instituciones = ref([])
const loading = ref(false)
const showModal = ref(false)
const showCreateModal = computed({
  get: () => showModal.value && !editingUser.value,
  set: (val) => {
    if (val) {
      editingUser.value = null
      resetForm()
      showModal.value = true
    } else {
      showModal.value = false
    }
  }
})
const editingUser = ref(null)

// Filtros y paginación
const filters = reactive({
  search: '',
  rol_id: '',
  institucion_id: ''
})

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0
})

// Formulario
const form = reactive({
  nombre: '',
  email: '',
  cedula: '',
  telefono: '',
  password: '',
  password_confirmation: '',
  rol_id: '',
  institucion_id: '',
  activo: true
})

// Métodos
const fetchUsers = async () => {
  try {
    const params = {
      page: pagination.current_page,
      ...filters
    }
    const { data } = await axios.get('/api/users', { params })
    usuarios.value = data.usuarios.data
    roles.value = data.roles
    instituciones.value = data.instituciones
    
    // Actualizar paginación
    pagination.current_page = data.usuarios.current_page
    pagination.last_page = data.usuarios.last_page
    pagination.per_page = data.usuarios.per_page
    pagination.total = data.usuarios.total
  } catch (error) {
    console.error('Error al cargar usuarios:', error)
    alert('Error al cargar usuarios')
  }
}

const debouncedSearch = (() => {
  let timeout
  return () => {
    clearTimeout(timeout)
    timeout = setTimeout(() => {
      fetchUsers()
    }, 500)
  }
})()

const changePage = (page) => {
  if (page >= 1 && page <= pagination.last_page) {
    pagination.current_page = page
    fetchUsers()
  }
}

const editUser = (usuario) => {
  editingUser.value = usuario
  Object.assign(form, {
    nombre: usuario.nombre,
    email: usuario.email,
    cedula: usuario.cedula,
    telefono: usuario.telefono || '',
    rol_id: usuario.rol_id,
    institucion_id: usuario.institucion_id || '',
    activo: usuario.activo
  })
  showModal.value = true
}

const saveUser = async () => {
  loading.value = true
  try {
    if (editingUser.value) {
      // Actualizar
      const { data } = await axios.put(`/api/users/${editingUser.value.id}`, form)
      alert(data.message)
    } else {
      // Crear
      const { data } = await axios.post('/api/users', form)
      alert(data.message)
    }
    closeModal()
    fetchUsers()
  } catch (error) {
    const message = error.response?.data?.message || 'Error al guardar usuario'
    alert(message)
  } finally {
    loading.value = false
  }
}

const toggleUserStatus = async (usuario) => {
  if (confirm(`¿Está seguro de ${usuario.activo ? 'desactivar' : 'activar'} este usuario?`)) {
    try {
      const { data } = await axios.post(`/api/users/${usuario.id}/toggle-status`)
      alert(data.message)
      fetchUsers()
    } catch (error) {
      alert('Error al cambiar estado del usuario')
    }
  }
}

const confirmDelete = async (usuario) => {
  if (confirm(`¿Está seguro de eliminar a ${usuario.nombre}?`)) {
    try {
      const { data } = await axios.delete(`/api/users/${usuario.id}`)
      alert(data.message)
      fetchUsers()
    } catch (error) {
      const message = error.response?.data?.message || 'Error al eliminar usuario'
      alert(message)
    }
  }
}

const closeModal = () => {
  showModal.value = false
  editingUser.value = null
  resetForm()
}

const resetForm = () => {
  Object.assign(form, {
    nombre: '',
    email: '',
    cedula: '',
    telefono: '',
    password: '',
    password_confirmation: '',
    rol_id: '',
    institucion_id: '',
    activo: true
  })
}

// Cargar datos al montar
onMounted(() => {
  fetchUsers()
})
</script>
