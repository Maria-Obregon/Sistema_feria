import axios from 'axios'

// Configuración base de axios
const api = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Interceptor para agregar token de autenticación
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Interceptor para manejar respuestas
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token')
      localStorage.removeItem('user')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

// Servicios de autenticación
export const authApi = {
  login: (credenciales) => api.post('/login', credenciales),
  register: (datos) => api.post('/register', datos),
  logout: () => api.post('/logout'),
  me: () => api.get('/me'),
  verify2FA: (codigo) => api.post('/2fa/verify', { codigo })
}

// Servicios de instituciones
export const institucionesApi = {
  listar: (params = {}) => api.get('/admin/instituciones', { params }),
  crear: (datos) => api.post('/admin/instituciones', datos),
  obtener: (id) => api.get(`/admin/instituciones/${id}`),
  actualizar: (id, datos) => api.put(`/admin/instituciones/${id}`, datos),
  eliminar: (id) => api.delete(`/admin/instituciones/${id}`),
  toggleActivo: (id) => api.post(`/admin/instituciones/${id}/toggle-activo`),
  obtenerCircuitos: () => api.get('/admin/circuitos')
}

// Servicios de usuarios
export const usuariosApi = {
  listar: (params = {}) => api.get('/users', { params }),
  crear: (datos) => api.post('/users', datos),
  obtener: (id) => api.get(`/users/${id}`),
  actualizar: (id, datos) => api.put(`/users/${id}`, datos),
  eliminar: (id) => api.delete(`/users/${id}`),
  toggleStatus: (id) => api.post(`/users/${id}/toggle-status`)
}

export default api
