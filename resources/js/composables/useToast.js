import { ref } from 'vue'

const toasts = ref([])
let toastId = 0

export function useToast() {
  const mostrarToast = (mensaje, tipo = 'info', duracion = 5000) => {
    const id = ++toastId
    const toast = {
      id,
      mensaje,
      tipo,
      visible: true
    }
    
    toasts.value.push(toast)
    
    // Auto-remover después de la duración especificada
    setTimeout(() => {
      removerToast(id)
    }, duracion)
    
    return id
  }
  
  const removerToast = (id) => {
    const index = toasts.value.findIndex(toast => toast.id === id)
    if (index > -1) {
      toasts.value.splice(index, 1)
    }
  }
  
  const limpiarToasts = () => {
    toasts.value = []
  }
  
  return {
    toasts,
    mostrarToast,
    removerToast,
    limpiarToasts
  }
}
