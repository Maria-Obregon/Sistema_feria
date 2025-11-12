import api from './api'

export const listarAsignacionesPorProyecto = (proyectoId, params = {}) =>
  api.get(`/proyectos/${proyectoId}/asignaciones`, { params })

export const asignarJuecesAProyecto = (proyectoId, payload) =>
  api.post(`/proyectos/${proyectoId}/asignar-jueces`, payload)

export const quitarAsignacion = (id) =>
  api.delete(`/asignaciones-jueces/${id}`)

export default {
  listarAsignacionesPorProyecto,
  asignarJuecesAProyecto,
  quitarAsignacion,
}
