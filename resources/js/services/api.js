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

// =========================
// Interceptores
// =========================
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token') || localStorage.getItem('auth_token')
    if (token) config.headers.Authorization = `Bearer ${token}`
    return config
  },
  (error) => Promise.reject(error)
)

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
// Helper para descargar blobs
// =========================
const downloadBlob = (blob, filename) => {
  const url = window.URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = filename
  a.click()
  window.URL.revokeObjectURL(url)
}

// =========================
// Auth
// =========================
export const authApi = {
  login:    (credenciales) => api.post('/login', credenciales),
  register: (datos)        => api.post('/register', datos),
  logout:   ()             => api.post('/logout'),
  me:       ()             => api.get('/me')
}

// =========================
// Instituciones
// =========================
export const institucionesApi = {
  listar:            (params = {})      => api.get('/instituciones', { params }),
  crear:             (datos)            => api.post('/instituciones', datos),
  obtener:           (id)               => api.get(`/instituciones/${id}`),
  actualizar:        (id, datos)        => api.put(`/instituciones/${id}`, datos),
  eliminar:          (id)               => api.delete(`/instituciones/${id}`),
  toggleActivo:      (id)               => api.patch(`/instituciones/${id}/toggle`),
  obtenerCircuitos:  ()                 => api.get('/circuitos'),
  obtenerCatalogos:  ()                 => api.get('/instituciones/catalogos'),
}

// =========================
// Usuarios (admin)
// =========================
export const usuariosApi = {
  listar:     (params = {})     => api.get('/usuarios', { params }),
  crear:      (datos)           => api.post('/usuarios', datos),
  obtener:    (id)              => api.get(`/usuarios/${id}`),
  actualizar: (id, datos)       => api.put(`/usuarios/${id}`, datos),
  eliminar:   (id)              => api.delete(`/usuarios/${id}`),
}

// =========================
// Admin (dashboard + catálogos)
// =========================
export const adminApi = {
  stats: () => api.get('/admin/stats'),
  roles: () => api.get('/admin/roles'),

  // Modalidades
  modalidades: {
    listar:     ()            => api.get('/admin/modalidades'),
    crear:      (payload)     => api.post('/admin/modalidades', payload),
    actualizar: (id, payload) => api.put(`/admin/modalidades/${id}`, payload),
    eliminar:   (id)          => api.delete(`/admin/modalidades/${id}`),
  },

  // Áreas
  areas: {
    listar:     ()            => api.get('/admin/areas'),
    crear:      (payload)     => api.post('/admin/areas', payload),
    actualizar: (id, payload) => api.put(`/admin/areas/${id}`, payload),
    eliminar:   (id)          => api.delete(`/admin/areas/${id}`),
  },

  // Categorías
  categorias: {
    listar:     ()            => api.get('/admin/categorias'),
    crear:      (payload)     => api.post('/admin/categorias', payload),
    actualizar: (id, payload) => api.put(`/admin/categorias/${id}`, payload),
    eliminar:   (id)          => api.delete(`/admin/categorias/${id}`),
  },

  // Tipos de Institución
  tiposInstitucion: {
    listar:     ()            => api.get('/admin/tipos-institucion'),
    crear:      (payload)     => api.post('/admin/tipos-institucion', payload),
    actualizar: (id, payload) => api.put(`/admin/tipos-institucion/${id}`, payload),
    eliminar:   (id)          => api.delete(`/admin/tipos-institucion/${id}`),
  },

  // Niveles
  niveles: {
    listar:     ()            => api.get('/admin/niveles'),
    crear:      (payload)     => api.post('/admin/niveles', payload),
    actualizar: (id, payload) => api.put(`/admin/niveles/${id}`, payload),
    eliminar:   (id)          => api.delete(`/admin/niveles/${id}`),
  },

  // Regionales
  regionales: {
    listar:     ()            => api.get('/admin/regionales'),
    crear:      (payload)     => api.post('/admin/regionales', payload),
    actualizar: (id, payload) => api.put(`/admin/regionales/${id}`, payload),
    eliminar:   (id)          => api.delete(`/admin/regionales/${id}`),
  },

  // Circuitos
  circuitos: {
    listar:     (params = {}) => api.get('/admin/circuitos', { params }),
    crear:      (payload)     => api.post('/admin/circuitos', payload),
    actualizar: (id, payload) => api.put(`/admin/circuitos/${id}`, payload),
    eliminar:   (id)          => api.delete(`/admin/circuitos/${id}`),
  },
}

// =========================
// Proyectos  (⚠️ nombre y métodos que usa tu vista)
// =========================
export const proyectosApi = {
  list:     (params = {})                => api.get('/proyectos', { params }), // ?buscar=&page=
  show:     (id)                         => api.get(`/proyectos/${id}`),
  destroy:  (id)                         => api.delete(`/proyectos/${id}`),
  formData: (institucion_id = null)      =>
    api.get('/proyectos/form-data', { params: institucion_id ? { institucion_id } : {} }),

  // (Opcionales si luego agregas crear/editar desde admin)
  create:   (payload)                    => api.post('/proyectos', payload),
  update:   (id, payload)                => api.put(`/proyectos/${id}`, payload),
}

// =========================
// Ferias
// =========================
export const feriasApi = {
  listar:     (params = {}) => api.get('/ferias', { params }),
  crear:      (datos)       => api.post('/ferias', datos),
  actualizar: (id, datos)   => api.put(`/ferias/${id}`, datos),
  eliminar:   (id)          => api.delete(`/ferias/${id}`),
}

// =========================
// Jueces (CRUD administrativo)
// =========================
export const juecesApi = {
  listar:     (params = {}) => api.get('/jueces', { params }),
  crear:      (datos)       => api.post('/jueces', datos),
  actualizar: (id, datos)   => api.put(`/jueces/${id}`, datos),
  eliminar:   (id)          => api.delete(`/jueces/${id}`),
}

// =========================
// Estudiantes
// =========================
export const estudiantesApi = {
  listar:            (params = {})        => api.get('/estudiantes', { params }),
  crear:             (payload)            => api.post('/estudiantes', payload),
  vincular:          (id, proyecto_id)    => api.post(`/estudiantes/${id}/vincular-proyecto`, { proyecto_id }),
  desvincular:       (id, proyecto_id)    => api.delete(`/estudiantes/${id}/desvincular-proyecto/${proyecto_id}`),
  descargarCredencial:(id)                => api.get(`/estudiantes/${id}/credencial`, { responseType: 'blob' }),
}

// =========================
// Registro público (instituciones)
// =========================
export const registroApi = {
  registrarInstitucion: (payload) => api.post('/public/instituciones/registrar', payload),
}

// =========================
// Comité
// =========================
export const comiteApi = {
  institucionesInscritas: (params = {}) => api.get('/comite/instituciones-inscritas', { params }),
}
export const institucionesComiteApi = {
  listar: (params = {}) => api.get('/comite/instituciones-inscritas', { params }),
}

// =========================
// Catálogos públicos
// =========================
export const catalogoApi = {
  regionales: ()                => api.get('/catalogo/regionales'),
  circuitos:  (regional_id)     => api.get('/catalogo/circuitos', { params: { regional_id } }),
}

// =========================
// Estudiante (self-service)
// =========================
export const estudianteSelfApi = {
  perfil:        ()                => api.get('/estudiante/perfil'),
  misProyectos:  (params = {})     => api.get('/estudiante/mis-proyectos', { params }),
  misEntregas:   (params = {})     => api.get('/estudiante/entregas', { params }),
  subirEntrega:  (payload)         => api.post('/estudiante/entregas', payload),
  borrarEntrega: (id)              => api.delete(`/estudiante/entregas/${id}`),
}

// =========================
export const certificadosApi = {
  listar:    (params = {}) => api.get('/certificados', { params }),
  obtener:   (id)          => api.get(`/certificados/${id}`),
  descargar: async (id, nombre = 'certificado.pdf') => {
    const { data } = await api.get(`/certificados/${id}/descargar`, { responseType: 'blob' })
    downloadBlob(new Blob([data]), nombre)
  },
}

// =========================
// Documentos descargables
// =========================
export const docsApi = {
  descargarPronafecyt: async (nombre = 'PRONAFECYT-2025-Formularios.docx') => {
    const { data } = await api.get('/docs/pronafecyt', { responseType: 'blob' })
    downloadBlob(new Blob([data]), nombre)
  }
}

export default api
