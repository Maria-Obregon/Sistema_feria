import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token'),
    isAuthenticated: false,
    loading: false,
    error: null,
  }),

  getters: {
    currentUser: (state) => state.user,
    userRole: (state) => state.user?.roles?.[0]?.name || null,
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
        
        // Si requiere 2FA, no guardamos el token aún
        if (response.data.requires_2fa) {
          return { success: true, requires_2fa: true, temp_token: response.data.temp_token };
        }
        
        const { token, user } = response.data;
        
        this.token = token;
        this.user = user;
        this.isAuthenticated = true;
        
        // Guardar token
        localStorage.setItem('token', token);
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
        this.user = response.data.user;
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
      localStorage.removeItem('token');
      delete axios.defaults.headers.common['Authorization'];
    },
  },
});
