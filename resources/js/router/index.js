// router/index.js
import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

import Login from '../pages/Login.vue'
import Dashboard from '../pages/Dashboard.vue' // genérico si no hay rol
import NotFound from '../pages/NotFound.vue'

// Carga perezosa de dashboards específicos
const AdminDashboard        = () => import('../pages/admin/AdminDashboard.vue')
const InstitucionDashboard  = () => import('../pages/instituciones/InstitucionesDashboard.vue')
const JuezDashboard         = () => import('../pages/juez/JuezDashboard.vue')
const EstudianteDashboard   = () => import('../pages/estudiantes/EstudiantesDashboard.vue')
const AdminConfig = () => import('../pages/admin/AdminConfig.vue')

// Mapa rol -> ruta por defecto
const roleRoute = (role) => {
  switch (role) {
    case 'admin':                  return { name: 'admin.dashboard' }
    case 'comite_institucional':   return { name: 'inst.dashboard' }
    case 'juez':                   return { name: 'juez.dashboard' }
    case 'estudiante':             return { name: 'est.dashboard' }
    default:                       return { name: 'dashboard' }
  }
}

const routes = [
  { path: '/', redirect: () => {
      const token = localStorage.getItem('token')
      if (!token) return { name: 'login' }
      const auth = useAuthStore()
      const role = auth.primaryRole ?? localStorage.getItem('role')
      return roleRoute(role)
    },
  },

  { path: '/login', name: 'login', component: Login, meta: { guest: true } },

  // Admin
  { path: '/admin', name: 'admin.dashboard', component: AdminDashboard, meta: { requiresAuth: true, role: 'admin' } },
  { path: '/admin/users', name: 'admin.users', component: () => import('../pages/admin/Users.vue'), meta: { requiresAuth: true, role: 'admin' } },
  { path: '/admin/instituciones', name: 'admin.instituciones', component: () => import('../pages/admin/instituciones/InstitucionesIndex.vue'), meta: { requiresAuth: true, role: 'admin' } },
{ path: '/admin/config', name: 'admin.config', component: AdminConfig, meta: { requiresAuth: true, role: 'admin' } },

  // institucion
{path: '/institucion',name: 'inst.dashboard',component: () => import('../pages/instituciones/InstitucionesDashboard.vue'),meta: { requiresAuth: true, role: 'comite_institucional' },},
{path: '/institucion/proyectos',name: 'inst.proyectos',component: () => import('../pages/instituciones/Proyectos.vue'),meta: { requiresAuth: true, role: 'comite_institucional' },},
{path: '/institucion/estudiantes',name: 'inst.estudiantes',component: () => import('../pages/instituciones/Estudiantes.vue'),meta: { requiresAuth: true, role: 'comite_institucional' },},
{path: '/institucion/jueces',name: 'inst.jueces',component: () => import('../pages/instituciones/Jueces.vue'),meta: { requiresAuth: true, role: 'comite_institucional' },},
{path: '/institucion/reportes',name: 'inst.reportes',component: () => import('../pages/instituciones/Reportes.vue'),meta: { requiresAuth: true, role: 'comite_institucional' },},
{path: '/institucion/perfil',name: 'inst.perfil',component: () => import('../pages/instituciones/Perfil.vue'),meta: { requiresAuth: true, role: 'comite_institucional' },},

 // estudiantes
{path: '/estudiante', name: 'est.dashboard',component: () => import('../pages/estudiantes/EstudiantesDashboard.vue'),meta: { requiresAuth: true, role: 'estudiante' },},
{path: '/estudiante/mis-proyectos',name: 'est.mis-proyectos',component: () => import('../pages/estudiantes/MisProyectos.vue'),meta: { requiresAuth: true, role: 'estudiante' },},
{path: '/estudiante/entregas',name: 'est.entregas',component: () => import('../pages/estudiantes/Entregas.vue'),meta: { requiresAuth: true, role: 'estudiante' },},
{path: '/estudiante/certificados',name: 'est.certificados',component: () => import('../pages/estudiantes/Certificados.vue'),meta: { requiresAuth: true, role: 'estudiante' },},
{path: '/estudiante/perfil',name: 'est.perfil', component: () => import('../pages/estudiantes/Perfil.vue'),meta: { requiresAuth: true, role: 'estudiante' },},

//Juez
{path: '/juez',name: 'juez.dashboard', component: () => import('../pages/juez/JuezDashboard.vue'),meta: { requiresAuth: true, role: 'juez' },},
{path: '/juez/asignaciones',name: 'juez.asignaciones',component: () => import('../pages/juez/Asignaciones.vue'),meta: { requiresAuth: true, role: 'juez' },},
{path: '/juez/calificaciones',name: 'juez.calificaciones',component: () => import('../pages/juez/Calificaciones.vue'),meta: { requiresAuth: true, role: 'juez' },},
{path: '/dashboard', name: 'dashboard', component: Dashboard, meta: { requiresAuth: true } },
{path: '/:pathMatch(.*)*', name: 'not-found', component: NotFound },
]

const router = createRouter({ history: createWebHistory(), routes })

router.beforeEach(async (to, from, next) => {
  const auth = useAuthStore()

  const token = localStorage.getItem('token')
  const roleLS = localStorage.getItem('role')

  if (token && !auth.user) {
    try { await auth.fetchUser() } catch { /* si falla, seguimos con roleLS */ }
  }

  const isAuthed = auth.isAuthenticated || !!token
  const role = auth.primaryRole ?? roleLS

  if (to.meta.requiresAuth && !isAuthed) {
    return next({ name: 'login' })
  }

  if (to.meta.guest && isAuthed) {
    return next(roleRoute(role))
  }

  if (to.meta.role && role && to.meta.role !== role) {
    // Rol incorrecto → a su home
    return next(roleRoute(role))
  }

  next()
})

export default router
