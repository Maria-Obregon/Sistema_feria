import './bootstrap';

// --- Ayudantes para el token de autenticación ---
const TOKEN_KEY = 'auth_token';
const ROLE_KEY = 'auth_role';

function setAuthToken(token) {
  if (token) {
    localStorage.setItem(TOKEN_KEY, token);
    window.axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
  }
}

function getAuthToken() {
  return localStorage.getItem(TOKEN_KEY);
}

function clearAuth() {
  localStorage.removeItem(TOKEN_KEY);
  localStorage.removeItem(ROLE_KEY);
  delete window.axios.defaults.headers.common['Authorization'];
}

// Inicializa el encabezado Authorization de axios si existe un token
const existing = getAuthToken();
if (existing) {
  setAuthToken(existing);
}

document.addEventListener('DOMContentLoaded', () => {
  // --- Comportamiento de la página de inicio de sesión ---
  const loginForm = document.getElementById('login-form');
  if (loginForm) {
    const emailEl = document.getElementById('email');
    const passEl = document.getElementById('password');
    const submitBtn = document.getElementById('login-submit');
    const errorBox = document.getElementById('login-error');
    const toggleBtn = document.getElementById('toggle-password');

    if (toggleBtn && passEl) {
      toggleBtn.addEventListener('click', () => {
        const t = passEl.getAttribute('type') === 'password' ? 'text' : 'password';
        passEl.setAttribute('type', t);
      });
    }

    loginForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      errorBox.classList.add('hidden');
      submitBtn.disabled = true;
      try {
        const payload = { email: emailEl.value.trim(), password: passEl.value };
        const { data } = await window.axios.post('/api/login', payload);
        const token = data?.token;
        const rol = data?.rol;
        setAuthToken(token);
        if (rol) localStorage.setItem(ROLE_KEY, rol);
        window.location.assign('/dashboard');
      } catch (err) {
        const msg = err?.response?.data?.message || 'No se pudo iniciar sesión.';
        errorBox.textContent = msg;
        errorBox.classList.remove('hidden');
      } finally {
        submitBtn.disabled = false;
      }
    });
  }

  // --- Comportamiento del panel ---
  const logoutBtn = document.getElementById('logout-btn');
  const profileBox = document.getElementById('profile-box');
  const userPill = document.getElementById('user-pill');
  const dashboardAlert = document.getElementById('dashboard-alert');

  if (logoutBtn) {
    // Redirección de guardia si no está autenticado
    if (!getAuthToken()) {
      window.location.replace('/login');
      return;
    }

    // Cargar perfil
    window.axios.get('/api/me')
      .then(({ data }) => {
        // 'data' es el usuario con la relación 'rol' precargada
        const email = data?.email ?? '(sin correo)';
        const rol = data?.rol?.nombre ?? localStorage.getItem(ROLE_KEY) ?? '—';
        if (profileBox) {
          profileBox.innerHTML = `
            <div class="flex flex-col gap-1">
              <div><span class="font-medium">Correo:</span> ${email}</div>
              <div><span class="font-medium">Rol:</span> ${rol}</div>
            </div>`;
        }
        if (userPill) {
          userPill.textContent = `${rol}`;
          userPill.classList.remove('hidden');
        }
      })
      .catch(() => {
        if (dashboardAlert) {
          dashboardAlert.textContent = 'Sesión expirada. Inicie sesión nuevamente.';
          dashboardAlert.classList.remove('hidden');
        }
        clearAuth();
        setTimeout(() => window.location.replace('/login'), 1200);
      });

    // Acción de cierre de sesión
    logoutBtn.addEventListener('click', async () => {
      try {
        await window.axios.post('/api/logout');
      } catch (_) {
        // ignorar
      } finally {
        clearAuth();
        window.location.replace('/login');
      }
    });
  }
});


