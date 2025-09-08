import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

// Importar páginas
import Login from '../pages/Login.vue';
import Dashboard from '../pages/Dashboard.vue';
import NotFound from '../pages/NotFound.vue';

const routes = [
  {
    path: '/',
    redirect: '/dashboard',
  },
  {
    path: '/login',
    name: 'login',
    component: Login,
    meta: { guest: true },
  },
  {
    path: '/admin',
    name: 'admin.dashboard',
    component: () => import('../pages/admin/AdminDashboard.vue'),
    meta: { requiresAuth: true, role: 'admin' }
  },
  {
    path: '/admin/users',
    name: 'admin.users',
    component: () => import('../pages/admin/Users.vue'),
    meta: { requiresAuth: true, role: 'admin' }
  },
  {
    path: '/juez/2fa',
    name: 'juez.2fa',
    component: () => import('../pages/juez/TwoFactorSettings.vue'),
    meta: { requiresAuth: true, role: 'juez' }
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: Dashboard,
    meta: { requiresAuth: true },
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: NotFound,
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Navigation guards
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore();
  
  // Páginas que requieren autenticación
  if (to.meta.requiresAuth) {
    if (!authStore.isAuthenticated) {
      return next({ name: 'login' });
    }
  }
  
  // Páginas solo para invitados (no autenticados)
  if (to.meta.guest && authStore.isAuthenticated) {
    return next({ name: 'dashboard' });
  }
  
  next();
});

export default router;
