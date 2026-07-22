# KREA Sistema Administrativo

Este proyecto es un sistema administrativo completo construido sobre **Laravel 12** y una plantilla de administración con **Tailwind CSS v4** y **Alpine.js**. Está diseñado para gestionar ventas, compras, inventario y usuarios, con una interfaz de panel de control moderna y responsive.

## Para qué sirve este sistema

Esta aplicación sirve como un panel administrativo para negocios que necesitan:

* Controlar productos, clientes y proveedores
* Registrar ventas y compras
* Generar reportes e informes de ventas e inventario
* Gestionar usuarios y permisos
* Manejar respaldos y configuración del sistema
* Bloquear el sistema mensualmente con una clave administrada desde un teléfono

El sistema incluye una capa de protección adicional para solicitar cada mes una clave única que sólo el administrador puede proporcionar.

## Tecnologías y versiones usadas

### Backend

* **PHP:** ^8.2
* **Laravel Framework:** ^12.0
* **Laravel Tinker:** ^2.10.1
* **Spatie Permission:** ^6.16
* **Maatwebsite Excel:** ^3.1

### Frontend

* **Tailwind CSS:** ^4.1.12
* **Vite:** ^7.0.4
* **Laravel Vite Plugin:** ^2.0.0
* **Alpine.js:** ^3.14.9
* **Axios:** ^1.11.0

### UI / componentes adicionales

* **FullCalendar:** ^6.1.19
* **ApexCharts:** ^5.3.5
* **Flatpickr:** ^4.6.13
* **Swiper:** ^12.0.3
* **JSVectorMap:** ^1.7.0
* **PrismJS:** ^1.30.0
* **Popper.js:** ^2.11.8

## Requisitos del entorno

Antes de instalar el sistema, asegúrate de tener:

* **PHP 8.2 o superior**
* **Composer**
* **Node.js**
* **PNPM** o **npm**
* **MariaDB / MySQL** u otro motor compatible
* **Git** (opcional, para clonar el repositorio)

## Instalación rápida

### 1. Clonar el repositorio

```bash
git clone <your-repo-url>
cd negocio_proyecto_v2
```

### 2. Instalar dependencias PHP

```bash
composer install
```

### 3. Instalar dependencias de frontend

```bash
pnpm install
```

### 4. Configurar el entorno

Copia el archivo de ejemplo `.env.example` a `.env` y actualiza los datos de la base de datos:

```bash
copy .env.example .env
```

### 5. Generar clave de aplicación

```bash
php artisan key:generate
```

### 6. Ejecutar migraciones y seeders

```bash
php artisan migrate --seed
```

### 7. Compilar los activos del frontend

```bash
pnpm run build
```

## Cómo funciona este sistema

1. El usuario se autentica en el panel.
2. Luego obtiene acceso a las secciones de ventas, compras, inventario, reportes y configuración.
3. El sistema tiene un bloqueo mensualmente configurable que exige una clave para seguir usando la aplicación.
4. El administrador genera la clave del mes y la comparte con los usuarios.
5. Cuando la clave se ingresa correctamente, el sistema permite el uso por 30 días.

## Comandos útiles

### Comandos de Laravel

```bash
php artisan migrate --seed
php artisan key:generate
php artisan storage:link
php artisan test
```

### Scripts del frontend

```bash
pnpm run dev
pnpm run build
```

## Estructura del proyecto

* `app/` - lógica de la aplicación y controladores
* `resources/views/` - plantillas Blade para la interfaz
* `public/` - archivos públicos, assets y manifest
* `database/` - migraciones y seeders
* `routes/` - rutas web y API
* `scripts/installer.py` - instalador Python para Windows

## Notas adicionales

Este repositorio está preparado para usarse en un entorno empresarial pequeño o mediano que necesite un sistema administrativo con panel visual y protección mensual mediante clave. El instalador Python `scripts/installer.py` puede ayudarte a instalar las dependencias necesarias y ejecutar los comandos de despliegue en Windows.

## Comprobar versiones instaladas

Ejecuta estos comandos para verificar tu entorno:

```bash
php -v
composer -V
node -v
pnpm -v
```


```bash
# Start development server
php artisan serve

# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Fresh migrations with seeding
php artisan migrate:fresh --seed

# Generate application key
php artisan key:generate

# Clear all caches
php artisan optimize:clear

# Cache everything for production
php artisan optimize

# Create symbolic link for storage
php artisan storage:link

# Start queue worker
php artisan queue:work

# List all routes
php artisan route:list

# Create a new controller
php artisan make:controller YourController

# Create a new model
php artisan make:model YourModel -m

# Create a new migration
php artisan make:migration create_your_table
```

## 📁 Project Structure

```
tailadmin-laravel/
├── app/                    # Application logic
│   ├── Http/              # Controllers, Middleware, Requests
│   ├── Models/            # Eloquent models
│   └── Providers/         # Service providers
├── bootstrap/             # Framework bootstrap files
├── config/                # Configuration files
├── database/              # Migrations, seeders, factories
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── public/                # Public assets (entry point)
│   ├── build/            # Compiled assets (generated)
│   └── index.php         # Application entry point
├── resources/             # Views and raw assets
│   ├── css/              # Stylesheets (Tailwind)
│   ├── js/               # JavaScript files (Alpine.js)
│   └── views/            # Blade templates
├── routes/                # Route definitions
│   ├── web.php           # Web routes
│   ├── api.php           # API routes
│   └── console.php       # Console routes
├── storage/               # Logs, cache, uploads
│   ├── app/
│   ├── framework/
│   └── logs/
├── tests/                 # Pest test files
│   ├── Feature/
│   └── Unit/
├── .env.example           # Example environment file
├── artisan                # Artisan CLI
├── composer.json          # PHP dependencies
├── package.json           # Node dependencies
├── vite.config.js         # Vite configuration
└── tailwind.config.js     # Tailwind configuration
```

## 🐛 Troubleshooting

### Common Issues

#### "Class not found" errors
```bash
composer dump-autoload
```

#### Permission errors on storage/bootstrap/cache
```bash
chmod -R 775 storage bootstrap/cache
```

#### NPM build errors
```bash
rm -rf node_modules package-lock.json
npm install
```

#### Clear all caches
```bash
php artisan optimize:clear
```

#### Database connection errors
- Check `.env` database credentials
- Ensure database server is running
- Verify database exists

## 🔄 Update Log

### [2026-05-23]

- Added **AI Settings** page to configure models, keys, and token limits.
- Added **Maps** page with MapLibre GL, Leaflet, and iframe styles.
- Added **Vector Maps** page powered by AmCharts 5 geodata (World & USA).
- Added **Radar Charts** page with 3 unique formats.
- Added **Radial Progress Charts** page featuring 4 custom layout templates.
- Introduced new **Bar Charts Five & Six** and **Pie Charts Four & Five**.

### [April 28, 2026]
- Added **AI Dashboard** with token usage and revenue tracking.
- Added **Sales Dashboard** with retention and multi-channel analytics.
- Added **Finance Dashboard** with cashflow and balance management.
- Introduced **6 New Layout variations** for improved UI flexibility.
- Integrated **Advanced Data Visualization** with 7+ new chart types.

### [2026-03-15]
- Fixed PHP 8.5 deprecation warning

### [2025-12-29]
- Added Date Picker in Statistics Chart

## License
## Desarollado por Antonio José Puerta Tineo

Refer to our [LICENSE](https://krece.com/license) page for more information.
