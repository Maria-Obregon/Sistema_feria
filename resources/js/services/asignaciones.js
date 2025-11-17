// services/asignaciones.js
import api from './api'

/**
 * Devuelve las asignaciones del juez autenticado.
 * Respuesta esperada del backend:
 * { data: [{ id, proyecto_id, etapa_id, tipo_eval, finalizada, proyecto: { titulo, ... } }], meta: {...} }
 */
export const listarMisAsignaciones = (params = {}) => {
  return api.get('/juez/asignaciones/mias', { params })
}

/**
 * Lista asignaciones para un proyecto específico (admin/organizador).
 */
export const listarAsignacionesPorProyecto = (proyectoId, params = {}) => {
  return api.get(`/proyectos/${proyectoId}/asignaciones`, { params })
}

/**
 * Asigna jueces a un proyecto.
 * payload: { etapa_id, tipo_eval: 'escrito'|'exposicion'|'integral', jueces: [{ id }] }
 */
export const asignarJuecesAProyecto = (proyectoId, payload) => {
  return api.post(`/proyectos/${proyectoId}/asignar-jueces`, payload)
}

/**
 * Elimina una asignación juez-proyecto.
 */
export const quitarAsignacion = (id) => {
  return api.delete(`/asignaciones-jueces/${id}`)
}

// (opcional) export por defecto con todas las funciones
export default {
  listarMisAsignaciones,
  listarAsignacionesPorProyecto,
  asignarJuecesAProyecto,
  quitarAsignacion,
}
