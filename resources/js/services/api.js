import axios from 'axios'

// ===== instancia base =====
const api = axios.create({
  baseURL: '/api',
  headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }
})

// ===== interceptores =====
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    if (token) config.headers.Authorization = `Bearer ${token}`
    return config
  },
  (error) => Promise.reject(error)
)

api.interceptors.response.use(
  (res) => res,
  (err) => {
    if (err.response?.status === 401) {
      localStorage.removeItem('auth_token')
      localStorage.removeItem('user')
      window.location.href = '/login'
    }
    return Promise.reject(err)
  }
)

// ===== helper para descargar blobs =====
const downloadBlob = (blob, filename) => {
  const url = window.URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = filename
  a.click()
  window.URL.revokeObjectURL(url)
}

// ===== Auth =====
export const authApi = {
  login: (credenciales) => api.post('/login', credenciales),
  register: (datos) => api.post('/register', datos),
  logout: () => api.post('/logout'),
  me: () => api.get('/me')
}

// ===== Instituciones (admin crea/gestiona) =====
// (si tus rutas reales tienen prefijo /admin/, vuelve a ponerlo aquí)
export const institucionesApi = {
  listar: (params = {}) => api.get('/instituciones', { params }),
  crear: (datos) => api.post('/instituciones', datos),
  obtener: (id) => api.get(`/instituciones/${id}`),
  actualizar: (id, datos) => api.put(`/instituciones/${id}`, datos),
  eliminar: (id) => api.delete(`/instituciones/${id}`),
  toggleActivo: (id) => api.patch(`/instituciones/${id}/toggle`),
  obtenerCircuitos: () => api.get('/circuitos')
}

// ===== Ferias (institucional/circuital/regional) =====
export const feriasApi = {
  listar: (params = {}) => api.get('/ferias', { params }),
  crear: (datos) => api.post('/ferias', datos),
  actualizar: (id, datos) => api.put(`/ferias/${id}`, datos),
  eliminar: (id) => api.delete(`/ferias/${id}`)
}

// ===== Estudiantes =====
// - El backend crea usuario con username = cédula y password aleatoria.
// - Si envías proyecto_id, queda vinculado.
export const estudiantesApi = {
  listar:   (params = {})            => api.get('/estudiantes', { params }),
  crear:    (payload)                => api.post('/estudiantes', payload),
  vincular: (id, proyecto_id)        => api.post(`/estudiantes/${id}/vincular-proyecto`, { proyecto_id }),
  desvincular: (id, proyecto_id)     => api.delete(`/estudiantes/${id}/desvincular-proyecto/${proyecto_id}`),
  descargarCredencial: (id)          => api.get(`/estudiantes/${id}/credencial`, { responseType: 'blob' })
}

// ===== Proyectos =====
export const proyectosApi = {
  listar: (params = {}) => api.get('/proyectos', { params }),
  crear:  (datos)       => api.post('/proyectos', datos),
  obtener:(id)          => api.get(`/proyectos/${id}`),
  actualizar:(id,datos) => api.put(`/proyectos/${id}`, datos),
  eliminar:(id)         => api.delete(`/proyectos/${id}`),
  asignarJueces: (proyectoId, payload) =>
    api.post(`/proyectos/${proyectoId}/asignar-jueces`, payload),

  // 👇 NUEVO: trae áreas, categorías, ferias y alumnos para el formulario
  formData: (params = {}) => api.get('/proyectos/form-data', { params }),
}
// ===== Jueces =====
export const juecesApi = {
  listar:   (params={}) => api.get('/jueces', { params }),
  crear:    (payload)   => api.post('/jueces', payload),
  actualizar:(id,p)     => api.put(`/jueces/${id}`, p),
  eliminar: (id)        => api.delete(`/jueces/${id}`),

  // asignación
  asignarAProyecto: (proyectoId, payload) =>
    api.post(`/proyectos/${proyectoId}/asignar-jueces`, payload),
  asignacionesDeProyecto: (proyectoId) =>
    api.get(`/proyectos/${proyectoId}/asignaciones-jueces`),
  quitarAsignacion: (asigId) =>
    api.delete(`/asignaciones-jueces/${asigId}`),
}

export const registroApi = {
  registrarInstitucion: (payload) => api.post('/public/instituciones/registrar', payload),
};

export const comiteApi = {
  institucionesInscritas: (params={}) => api.get('/comite/instituciones-inscritas', { params }),
};

// Catálogos (si no existen)
export const catalogoApi = {
  regionales: () => api.get('/catalogo/regionales'),
  circuitos:  (regional_id) => api.get('/catalogo/circuitos', { params: { regional_id } }),
}


// ===== Documentos descargables =====
export const docsApi = {
  descargarPronafecyt: async () => {
    const { data } = await api.get('/docs/pronafecyt', { responseType: 'blob' })
    downloadBlob(new Blob([data]), 'PRONAFECYT-2025-Formularios.docx')
  }
}


export default api
