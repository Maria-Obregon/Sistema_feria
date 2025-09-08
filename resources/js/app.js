import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import App from './App.vue';
import axios from 'axios';

// Configurar axios
axios.defaults.baseURL = window.location.origin;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Accept'] = 'application/json';

// Verificar si hay token guardado
const token = localStorage.getItem('auth_token');
if (token) {
  axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

// Interceptor para manejar errores 401
axios.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token');
      delete axios.defaults.headers.common['Authorization'];
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

// Crear aplicación Vue
const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);

app.mount('#app');
