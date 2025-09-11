import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import App from './App.vue';

// Crear aplicación Vue
const app = createApp(App);

// Usar Pinia para manejo de estado
const pinia = createPinia();
app.use(pinia);

// Usar Vue Router
app.use(router);

// Montar aplicación
app.mount('#vue-app');
