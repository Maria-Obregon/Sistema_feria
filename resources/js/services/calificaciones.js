import api from './api'

export const listarCalificaciones = (params = {}) =>
  api.get('/calificaciones', { params })

export const crearCalificacion = (data) =>
  api.post('/calificaciones', data)

export const consolidar = (data) =>
  api.post('/calificaciones/consolidar', data)

export default {
  listarCalificaciones,
  crearCalificacion,
  consolidar,
}
