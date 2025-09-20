// stores/auth.js
import { defineStore } from 'pinia'
import axios from 'axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token'),
    isAuthenticated: !!localStorage.getItem('token'),
    loading: false,
    error: null,
  }),

  getters: {
    // Devuelve 'admin', 'juez', etc. o null
    primaryRole: (state) => {
      const roles = state.user?.roles
      if (!roles || roles.length === 0) return null
      const first = roles[0]
      return typeof first === 'string' ? first : first?.name ?? null
    },
    hasRole: (state) => (role) => {
      const roles = state.user?.roles ?? []
      return roles.some(r => (typeof r === 'string' ? r : r?.name) === role)
    },
    hasPermission: (state) => (permission) => {
      const perms = state.user?.permissions ?? []
      return perms.some(p => (typeof p === 'string' ? p : p?.name) === permission)
    },
  },

  actions: {
    async login(credentials) {
      this.loading = true
      this.error = null
      try {
        const { data } = await axios.post('/api/login', credentials)

        this.token = data.token
        this.user = data.user
        this.isAuthenticated = true

        localStorage.setItem('token', data.token)
        // también guardamos el rol por resiliencia al refrescar
        const role = Array.isArray(data.user?.roles) ? (typeof data.user.roles[0] === 'string' ? data.user.roles[0] : data.user.roles[0]?.name) : null
        if (role) localStorage.setItem('role', role)

        axios.defaults.headers.common['Authorization'] = `Bearer ${data.token}`
        return { success: true }
      } catch (e) {
        this.error = e.response?.data?.message || 'Error al iniciar sesión'
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async fetchUser() {
      if (!this.token) return
      const { data } = await axios.get('/api/me')
      this.user = data.user
      this.isAuthenticated = true
    },

    async logout() {
      try { await axios.post('/api/logout') } catch {}
      this.clearAuth()
    },

    checkAuth() {
      if (this.token) {
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
        this.fetchUser().catch(() => this.clearAuth())
      }
    },

    clearAuth() {
      this.user = null
      this.token = null
      this.isAuthenticated = false
      localStorage.removeItem('token')
      localStorage.removeItem('role')
      delete axios.defaults.headers.common['Authorization']
    },
  },
})
