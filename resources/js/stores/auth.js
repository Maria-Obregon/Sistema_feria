import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('auth_token'),
    isAuthenticated: false,
    loading: false,
    error: null,
  }),

  getters: {
    currentUser: (state) => state.user,
    userRole: (state) => state.user?.rol?.nombre || null,
    hasRole: (state) => (role) => {
      return state.user?.roles?.some(r => r.name === role) || false;
    },
    hasPermission: (state) => (permission) => {
      return state.user?.permissions?.some(p => p.name === permission) || false;
    },
  },

  actions: {
    async login(credentials) {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await axios.post('/api/login', credentials);
        const { token, user } = response.data;
        
        this.token = token;
        this.user = user;
        this.isAuthenticated = true;
        
        // Guardar token
        localStorage.setItem('auth_token', token);
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        
        return { success: true };
      } catch (error) {
        this.error = error.response?.data?.message || 'Error al iniciar sesión';
        return { success: false, error: this.error };
      } finally {
        this.loading = false;
      }
    },

    async logout() {
      try {
        await axios.post('/api/logout');
      } catch (error) {
        console.error('Error al cerrar sesión:', error);
      } finally {
        this.clearAuth();
      }
    },

    async fetchUser() {
      if (!this.token) return;
      
      try {
        const response = await axios.get('/api/me');
        this.user = response.data;
        this.isAuthenticated = true;
      } catch (error) {
        this.clearAuth();
      }
    },

    checkAuth() {
      if (this.token) {
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
        this.fetchUser();
      }
    },

    clearAuth() {
      this.user = null;
      this.token = null;
      this.isAuthenticated = false;
      localStorage.removeItem('auth_token');
      delete axios.defaults.headers.common['Authorization'];
    },

    // Método para verificar 2FA (jueces)
    async verify2FA(code) {
      this.loading = true;
      try {
        const response = await axios.post('/api/verify-2fa', { code });
        const { token, user } = response.data;
        
        this.token = token;
        this.user = user;
        this.isAuthenticated = true;
        
        localStorage.setItem('auth_token', token);
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        
        return { success: true };
      } catch (error) {
        this.error = error.response?.data?.message || 'Código inválido';
        return { success: false, error: this.error };
      } finally {
        this.loading = false;
      }
    },
  },
});
