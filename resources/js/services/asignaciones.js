import api from './api'

export const listarAsignacionesPorProyecto = (proyectoId, params = {}) =>
  api.get(`/proyectos/${proyectoId}/asignaciones`, { params })

export const asignarJuecesAProyecto = (proyectoId, payload) =>
  api.post(`/proyectos/${proyectoId}/asignar-jueces`, payload)

export const quitarAsignacion = (id) =>
  api.delete(`/asignaciones-jueces/${id}`)

export async function fetchMisAsignacionesLite() {
  const res = await fetch('/api/juez/asignaciones/mias', { credentials: 'include' })
  if (!res.ok) throw new Error('HTTP ' + res.status)
  const json = await res.json()
  return (json.data || []).map(x => ({
    id: x.id,
    proyectoId: x.proyecto?.id,
    titulo: x.proyecto?.titulo,
    categoria: x.proyecto?.categoria,
    modalidad: x.proyecto?.modalidad,
    etapa: x.proyecto?.etapa,
    tipoEval: x.tipo_eval,
    finalizada: x.finalizada,
  }))
}

export default {
  listarAsignacionesPorProyecto,
  asignarJuecesAProyecto,
  quitarAsignacion,
  fetchMisAsignacionesLite,
}
