import axios from 'axios'

// =========================
// Configuración base de axios
// =========================
const api = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Interceptor: token
api.interceptors.request.use(
  (config) => {
    const token =
      localStorage.getItem('token') ||
      localStorage.getItem('auth_token')
    if (token) config.headers.Authorization = `Bearer ${token}`
    return config
  },
  (error) => Promise.reject(error)
)

// Interceptor: 401
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token')
      localStorage.removeItem('auth_token')
      localStorage.removeItem('user')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

// =========================
// Auth
// =========================
export const authApi = {
  login: (credenciales) => api.post('/login', credenciales),
  register: (datos) => api.post('/register', datos),
  logout: () => api.post('/logout'),
  me: () => api.get('/me')
}

// =========================
// Instituciones
// =========================
export const institucionesApi = {
  listar: (params = {}) => api.get('/instituciones', { params }),
  crear: (datos) => api.post('/instituciones', datos),
  obtener: (id) => api.get(`/instituciones/${id}`),
  actualizar: (id, datos) => api.put(`/instituciones/${id}`, datos),
  eliminar: (id) => api.delete(`/instituciones/${id}`),
  toggleActivo: (id) => api.patch(`/instituciones/${id}/toggle`), // si lo tienes
  obtenerCircuitos: () => api.get('/circuitos'),

  // 👉 NUEVO: endpoint agregador de catálogos para el formulario
  obtenerCatalogos: () => api.get('/instituciones/catalogos'),
}

// =========================
// Usuarios (resource /api/usuarios con role:admin)
// =========================
export const usuariosApi = {
  listar: (params = {}) => api.get('/usuarios', { params }),
  crear: (datos) => api.post('/usuarios', datos),
  obtener: (id) => api.get(`/usuarios/${id}`),
  actualizar: (id, datos) => api.put(`/usuarios/${id}`, datos),
  eliminar: (id) => api.delete(`/usuarios/${id}`),
}

// =========================
// Admin
// =========================
export const adminApi = {
  // Dashboard / util
  stats: () => api.get('/admin/stats'),
  roles: () => api.get('/admin/roles'),

  // Usuarios (admin)
  crearUsuario: (payload) => api.post('/admin/usuarios', payload),
  actualizarUsuario: (id, payload) => api.put(`/admin/usuarios/${id}`, payload),
  actualizarRoles: (id, roles) => api.put(`/admin/usuarios/${id}/roles`, { roles }),
  resetPassword: (id, password) => api.put(`/admin/usuarios/${id}/password`, { password }),
  eliminarUsuario: (id) => api.delete(`/admin/usuarios/${id}`),

  // Catálogos: Modalidades
  modalidades: {
    listar: () => api.get('/admin/modalidades'),
    crear: (payload) => api.post('/admin/modalidades', payload),
    actualizar: (id, payload) => api.put(`/admin/modalidades/${id}`, payload),
    eliminar: (id) => api.delete(`/admin/modalidades/${id}`),
  },

  // Catálogos: Áreas
  areas: {
    listar: () => api.get('/admin/areas'),
    crear: (payload) => api.post('/admin/areas', payload),
    actualizar: (id, payload) => api.put(`/admin/areas/${id}`, payload),
    eliminar: (id) => api.delete(`/admin/areas/${id}`),
  },

  // Catálogos: Categorías
  categorias: {
    listar: () => api.get('/admin/categorias'),
    crear: (payload) => api.post('/admin/categorias', payload),
    actualizar: (id, payload) => api.put(`/admin/categorias/${id}`, payload),
    eliminar: (id) => api.delete(`/admin/categorias/${id}`),
  },

  // Catálogos: Tipos de Institución
  tiposInstitucion: {
    listar: () => api.get('/admin/tipos-institucion'),
    crear: (payload) => api.post('/admin/tipos-institucion', payload),
    actualizar: (id, payload) => api.put(`/admin/tipos-institucion/${id}`, payload),
    eliminar: (id) => api.delete(`/admin/tipos-institucion/${id}`),
  },

  // Catálogos: Niveles
  niveles: {
    listar: () => api.get('/admin/niveles'),
    crear: (payload) => api.post('/admin/niveles', payload),
    actualizar: (id, payload) => api.put(`/admin/niveles/${id}`, payload),
    eliminar: (id) => api.delete(`/admin/niveles/${id}`),
  },
}

export default api
