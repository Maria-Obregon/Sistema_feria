import './bootstrap'
import { createApp } from 'vue'
import LoginForm from './components/LoginForm.vue'

createApp({}).component('login-form', LoginForm).mount('#app')
