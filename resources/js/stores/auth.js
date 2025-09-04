import { defineStore } from 'pinia'
import axios from 'axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('auth_token') || null,
    role:  localStorage.getItem('auth_role')  || null,
    loading: false,
    error: null,
  }),
  actions: {
    async login(email, password) {
      this.loading = true
      this.error   = null
      try {
        const { data } = await axios.post('/api/login', { email, password })
        this.token = data.token
        this.role  = data.rol

        localStorage.setItem('auth_token', this.token)
        localStorage.setItem('auth_role', this.role)
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`

        window.location.assign('/dashboard')
      } catch (e) {
        this.error = e?.response?.data?.message ?? 'Credenciales inválidas.'
        throw e
      } finally {
        this.loading = false
      }
    },
    logout() {
      this.token = null
      this.role  = null
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_role')
      delete axios.defaults.headers.common['Authorization']
      window.location.assign('/login')
    }
  }
})
