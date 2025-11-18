import api from './api'

export const obtenerRubricaDeProyecto = (proyectoId, params = {}) =>
  api.get(`/proyectos/${proyectoId}/rubrica`, { params })

export const existeRubricaDeProyecto = async (proyectoId, params = {}) => {
  try {
    const { data } = await api.get(`/proyectos/${proyectoId}/rubrica`, { params })
    return !!data && !!data.rubrica
  } catch {
    return false
  }
}

export default { obtenerRubricaDeProyecto, existeRubricaDeProyecto }
