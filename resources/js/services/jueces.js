import api from './api'

export const listarJueces = (params = {}) => api.get('/jueces', { params })
export const crearJuez = (data) => api.post('/jueces', data)
export const editarJuez = (id, data) => api.put(`/jueces/${id}`, data)
export const eliminarJuez = (id) => api.delete(`/jueces/${id}`)

export default {
  listarJueces,
  crearJuez,
  editarJuez,
  eliminarJuez,
}
