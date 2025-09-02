<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Iniciar sesión • Sistema de Feria Científica</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="min-h-dvh bg-slate-50 text-slate-800">
    <main class="mx-auto max-w-md p-6">
        <div class="mt-10 rounded-xl bg-white p-8 shadow">
            <h1 class="mb-6 text-center text-2xl font-semibold">Iniciar sesión</h1>
            <form id="login-form" class="space-y-4">
                <div>
                    <label for="email" class="mb-1 block text-sm font-medium">Correo electrónico</label>
                    <input id="email" name="email" type="email" required autocomplete="email"
                           class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:ring-2 focus:ring-sky-400" />
                </div>
                <div>
                    <label for="password" class="mb-1 block text-sm font-medium">Contraseña</label>
                    <div class="relative">
                        <input id="password" name="password" type="password" required autocomplete="current-password"
                               class="w-full rounded-md border border-slate-300 px-3 py-2 pr-10 outline-none focus:ring-2 focus:ring-sky-400" />
                        <button type="button" id="toggle-password" aria-label="Mostrar/Ocultar contraseña"
                                class="absolute inset-y-0 right-0 px-3 text-slate-500 hover:text-slate-700">👁️</button>
                    </div>
                </div>
                <div id="login-error" class="hidden rounded-md border border-rose-300 bg-rose-50 p-3 text-sm text-rose-700"></div>
                <button type="submit" id="login-submit"
                        class="w-full rounded-md bg-sky-600 px-4 py-2 font-medium text-white hover:bg-sky-700 focus:ring-2 focus:ring-sky-400 disabled:cursor-not-allowed disabled:opacity-60">
                    Entrar
                </button>
            </form>
            <p class="mt-6 text-center text-xs text-slate-500">© Sistema de Feria Científica</p>
        </div>
    </main>
</body>
</html>
