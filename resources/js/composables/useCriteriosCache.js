import { ref } from 'vue'
import axios from 'axios'

// TTL de 10 minutos
const TTL_MS = 10 * 60 * 1000

const cache = ref({
  escrita: null,
  oral: null
})

export function useCriteriosCache() {
  const getCriterios = async (tipoEval) => {
    const cached = cache.value[tipoEval]
    
    // Verificar si existe y no ha expirado
    if (cached && cached.data && (Date.now() - cached.ts) < TTL_MS) {
      return cached.data
    }

    // Fetch fresh data
    const { data } = await axios.get(`/api/rubricas/${tipoEval}/criterios`)
    cache.value[tipoEval] = {
      data: data.data || [],
      ts: Date.now()
    }
    
    return cache.value[tipoEval].data
  }

  const clearCache = (tipoEval = null) => {
    if (tipoEval) {
      cache.value[tipoEval] = null
    } else {
      cache.value.escrita = null
      cache.value.oral = null
    }
  }

  return {
    getCriterios,
    clearCache
  }
}
