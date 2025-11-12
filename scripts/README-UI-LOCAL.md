# UI local para Jueces (Laravel + Vite)

- Backend:
  - `php artisan serve --host=127.0.0.1 --port=8000`
- Frontend:
  - `npm run dev` (Vite expone en `http://127.0.0.1:5173`)

- Variables necesarias en `.env`:
  - `APP_URL`
  - `FRONTEND_URL`
  - `SESSION_DOMAIN`
  - `SANCTUM_STATEFUL_DOMAINS`

- Login juez demo:
  - Email: `juez.demo@prueba.local`
  - Password: `JuezDemo#2024`

- Flujo UI sugerido:
  - Ingresar con el juez → ir a `Juez / Mis asignaciones` → `Ir a calificar` → capturar puntajes → `Consolidar`.
