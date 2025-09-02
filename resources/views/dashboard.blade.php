<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Panel • Sistema de Feria Científica</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="min-h-dvh bg-slate-50 text-slate-800">
    <header class="border-b bg-white">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-6 py-4">
            <h1 class="text-lg font-semibold">Panel</h1>
            <div class="flex items-center gap-3">
                <span id="user-pill" class="hidden rounded-full bg-slate-100 px-3 py-1 text-sm text-slate-700"></span>
                <button id="logout-btn" class="rounded-md bg-slate-800 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-900">Salir</button>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-6 py-8">
        <div id="dashboard-alert" class="hidden mb-4 rounded-md border border-amber-300 bg-amber-50 p-3 text-amber-800 text-sm"></div>
        <section class="rounded-xl bg-white p-6 shadow">
            <h2 class="mb-2 text-xl font-semibold">Bienvenido</h2>
            <p class="text-slate-600">Este es un panel inicial. Desde aquí podremos redirigir según el rol.</p>
            <div class="mt-4 text-sm text-slate-700" id="profile-box"></div>
        </section>
    </main>
</body>
</html>
