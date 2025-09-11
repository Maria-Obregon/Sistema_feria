<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Sistema Feria — Guía de instalación y uso

Aplicación en Laravel 12 para gestionar ferias de ciencia (modelos en español: `usuario`, `rol`, `institucion`, `circuito`, `feria`, `proyecto`, `juez`, `estudiante`, etc.).

- Frontend con Vite + Tailwind 4.
- Autenticación de API con Laravel Sanctum (`/api/register`, `/api/login`, `/api/me`, `/api/logout`).

### Requisitos

- PHP >= 8.2 y Composer
- Node.js >= 20 y npm
- SQLite3 (recomendado para desarrollo)

### Instalación

1) Instalar dependencias PHP y JS

```bash
composer install
npm install
```

2) Configurar variables de entorno y clave de la app

```bash
cp .env.example .env
php artisan key:generate
```

3) Configurar SQLite en `.env`

```dotenv
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

4) Crear el archivo de base de datos y ejecutar migraciones con seeders

```bash
mkdir -p database && touch database/database.sqlite
php artisan migrate --seed
```

El seeder crea un usuario administrador por defecto: `admin@feria.test / password123`.

5) (Opcional) Construir assets de Vite si no se usa servidor de desarrollo

```bash
npm run build
```

### Ejecución en desarrollo

- Opción recomendada (todo en un solo comando):

```bash
composer dev
```

Este script levanta: servidor (`php artisan serve`), cola (`queue:listen`), logs (`pail`) y Vite (`npm run dev`).

- Opción manual (en terminales separadas):

```bash
php artisan serve
npm run dev
```

Ingresar a http://localhost:8000

### Pruebas

```bash
php artisan test
```


### Endpoints de autenticación (API)

- POST `/api/register`
- POST `/api/login`
- GET `/api/me` (requiere token Bearer)
- POST `/api/logout` (requiere token Bearer)



## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
