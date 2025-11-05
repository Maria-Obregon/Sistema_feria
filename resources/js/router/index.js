// router/index.js
import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

// Páginas básicas
import Login from '../pages/Login.vue'
import Dashboard from '../pages/Dashboard.vue'
import NotFound from '../pages/NotFound.vue'

// Carga perezosa de dashboards
const AdminDashboard       = () => import('../pages/admin/AdminDashboard.vue')
const InstitucionDashboard = () => import('../pages/instituciones/InstitucionesDashboard.vue')
const JuezDashboard        = () => import('../pages/juez/JuezDashboard.vue')
const EstudianteDashboard  = () => import('../pages/estudiantes/EstudiantesDashboard.vue')

// Helper: ruta por rol
const roleRoute = (role) => {
  switch (role) {
    case 'admin':                return { name: 'admin.dashboard' }
    case 'comite_institucional': return { name: 'inst.dashboard' }
    case 'juez':                 return { name: 'juez.dashboard' }
    case 'estudiante':           return { name: 'est.dashboard' }
    default:                     return { name: 'dashboard' }
  }
}

// Helper: validar rol contra meta
const allowsRole = (meta, role) => {
  if (!meta) return true
  if (!meta.role && !meta.roles) return true
  if (meta.role)  return role === meta.role
  if (Array.isArray(meta.roles)) return meta.roles.includes(role)
  return true
}

const routes = [
  {
    path: '/',
    redirect: () => {
      const token = localStorage.getItem('token')
      if (!token) return { name: 'login' }
      const auth = useAuthStore()
      const role = auth.primaryRole ?? localStorage.getItem('role')
      return roleRoute(role)
    }
  },

  { path: '/login', name: 'login', component: Login, meta: { guest: true } },

  // =========================
  // Admin
  // =========================
  { path: '/admin', name: 'admin.dashboard', component: AdminDashboard, meta: { requiresAuth: true, roles: ['admin'] } },
  { path: '/admin/users', name: 'admin.users', component: () => import('../pages/admin/Users.vue'), meta: { requiresAuth: true, roles: ['admin'] } },
  // Admin puede ver también instituciones (comparten componente)
  { path: '/admin/instituciones', name: 'admin.instituciones', component: () => import('../pages/admin/instituciones/InstitucionesIndex.vue'), meta: { requiresAuth: true, roles: ['admin'] } },

  // =========================
  // Comité Institucional → Instituciones
  // (mismo componente, distinta ruta/permiso)
  // =========================
  { path: '/comite/instituciones', name: 'comite.instituciones', component: () => import('../pages/admin/instituciones/InstitucionesIndex.vue'), meta: { requiresAuth: true, roles: ['comite_institucional','coordinador_circuito','coordinador_regional','admin'] } },

  // =========================
  // Panel de Institución (responsable)
  // =========================
  { path: '/institucion',               name: 'inst.dashboard',  component: () => import('../pages/instituciones/InstitucionesDashboard.vue'), meta: { requiresAuth: true, roles: ['comite_institucional'] } },
  { path: '/institucion/instituciones', name: 'inst.instituciones',  component: () => import('../pages/instituciones/Instituciones.vue'), meta: { requiresAuth: true, roles: ['comite_institucional'] } },
  { path: '/institucion/proyectos',     name: 'inst.proyectos',   component: () => import('../pages/instituciones/Proyectos.vue'),         meta: { requiresAuth: true, roles: ['comite_institucional'] } },
  { path: '/institucion/estudiantes',   name: 'inst.estudiantes', component: () => import('../pages/instituciones/Estudiantes.vue'),       meta: { requiresAuth: true, roles: ['comite_institucional'] } },
  { path: '/institucion/jueces',        name: 'inst.jueces',      component: () => import('../pages/instituciones/Jueces.vue'),            meta: { requiresAuth: true, roles: ['comite_institucional'] } },
  { path: '/institucion/reportes',      name: 'inst.reportes',    component: () => import('../pages/instituciones/Reportes.vue'),          meta: { requiresAuth: true, roles: ['comite_institucional'] } },
  { path: '/institucion/perfil',        name: 'inst.perfil',      component: () => import('../pages/instituciones/Perfil.vue'),            meta: { requiresAuth: true, roles: ['comite_institucional'] } },

  // =========================
  // Estudiante
  // =========================
  { path: '/estudiante',                name: 'est.dashboard',     component: EstudianteDashboard, meta: { requiresAuth: true, roles: ['estudiante'] } },
  { path: '/estudiante/mis-proyectos',  name: 'est.mis-proyectos', component: () => import('../pages/estudiantes/MisProyectos.vue'), meta: { requiresAuth: true, roles: ['estudiante'] } },
  { path: '/estudiante/entregas',       name: 'est.entregas',      component: () => import('../pages/estudiantes/Entregas.vue'),     meta: { requiresAuth: true, roles: ['estudiante'] } },
  { path: '/estudiante/certificados',   name: 'est.certificados',  component: () => import('../pages/estudiantes/Certificados.vue'), meta: { requiresAuth: true, roles: ['estudiante'] } },
  { path: '/estudiante/perfil',         name: 'est.perfil',        component: () => import('../pages/estudiantes/Perfil.vue'),       meta: { requiresAuth: true, roles: ['estudiante'] } },

  // =========================
  // Juez
  // =========================
  { path: '/juez',               name: 'juez.dashboard',     component: JuezDashboard, meta: { requiresAuth: true, roles: ['juez'] } },
  { path: '/juez/asignaciones',  name: 'juez.asignaciones',  component: () => import('../pages/juez/Asignaciones.vue'),    meta: { requiresAuth: true, roles: ['juez'] } },
  { path: '/juez/calificaciones',name: 'juez.calificaciones',component: () => import('../pages/juez/Calificaciones.vue'),   meta: { requiresAuth: true, roles: ['juez'] } },

  // Genérico
  { path: '/dashboard', name: 'dashboard', component: Dashboard, meta: { requiresAuth: true } },
  { path: '/:pathMatch(.*)*', name: 'not-found', component: NotFound },
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach(async (to, from, next) => {
  const auth = useAuthStore()

  const token  = localStorage.getItem('token')
  const roleLS = localStorage.getItem('role')

  if (token && !auth.user) {
    try { await auth.fetchUser() } catch { /* noop */ }
  }

  const isAuthed = auth.isAuthenticated || !!token
  const role     = auth.primaryRole ?? roleLS

  // Rutas que requieren auth
  if (to.meta?.requiresAuth && !isAuthed) {
    return next({ name: 'login' })
  }

  // Rutas sólo para invitados
  if (to.meta?.guest && isAuthed) {
    return next(roleRoute(role))
  }

  // Verificar rol si la ruta lo pide
  if (to.meta && !allowsRole(to.meta, role)) {
    return next(roleRoute(role))
  }

  return next()
})

export default router
