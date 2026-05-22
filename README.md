# Proyecto A: Sistema de Control de Aulas e Itinerarios Institucionales.

<img width="1891" height="870" alt="image" src="https://github.com/user-attachments/assets/b50e0800-2acc-46df-a88b-11545f24b69e" />

## Descripción General


## Equipo Responsable

Sólo el Líder del Departamento y Desarrollador UX/UI tiene permitido hacer cambios en el proyecto. Queda prohibido cualquier responsable no mencionado realice modificaciones a este repositorio

- **Líder de Desarrollo Web:** Rubén Alejandro Nolasco Ruiz  
- **Desarrollador UX/UI:** Diego Miguel Hernández Fabela
- **Desarrollador Backend:** Ghael Garcia Manjarrez

---
## Stack Tecnológico (Web)

- **Backend:** Laravel 12 + PHP 8.3 (API RESTful)  
- **Frontend:** Blade + Alpine.js + Vite  
- **Estilos:** CSS basado en Design System GAMA + Tailwind 4  
- **Autenticación:** SAM (SSO institucional) + Laravel Sanctum (tokens API)  
- **Testing:** Pest PHP (Feature + Unit + Integration)  
---

## Design System

### Tipografía

| Elemento | Tamaño | Peso | Uso |
|----------|--------|------|-----|
| H1       | 32px   | Bold | Encabezados principales |
| Body     | 16px   | Regular | Texto general |
| Labels   | 16px   | Medium | Formularios |

---

### Paleta de Colores

| Nombre | Código | Uso |
|--------|--------|-----|
| Deep Corporate Blue | `#134474` | Sidebar, Navbar, botones principales |
| GAMA Orange | `#F28B2C` | Alertas, KPIs, indicadores |
| Ice Blue | `#F2F7FB` | Fondo de cards y tablas |
| Royal Blue | Variable | Estados secundarios / focus |
| Error Red | `#FF0000` | Validaciones y acciones críticas |

---

## ⚙️ Instalación

```bash
# Clonar repositorio
git clone https://github.com/GAMA-Solutions/control-aulas.git

# Entrar al proyecto
cd control-aulas

# Instalar dependencias
composer install
npm install

# Configurar entorno
cp .env.example .env
php artisan key:generate

# Ejecutar migraciones
php artisan migrate

# Levantar servidor (desarrollo)
composer dev

## 🧪 Testing

```bash
# Ejecutar suite completa
./vendor/bin/pest

# Con coverage (requiere Xdebug/PCOV)
./vendor/bin/pest --coverage
```

## 🗄️ Base de datos

Soporta MySQL 8+ y PostgreSQL 15+. Las tablas usan prefijo `gama_` con SoftDeletes en entidades principales.

| Comando | Descripción |
|---------|-------------|
| `php artisan migrate` | Ejecuta migraciones |
| `php artisan migrate:fresh --seed` | Reinicia BD con seeders |
| `php artisan db:seed --class=GamaCatalogSeeder` | Catálogos fijos |

## 📚 Módulos

| Módulo | Docs | Estado |
|--------|------|--------|
| Catálogos base | `docs/modules/01-catalogs.md` | ✅ |
| Edificios y Aulas | `docs/modules/02-buildings.md` | ✅ |
| Horarios | `docs/modules/03-schedules.md` | ✅ |
| Estatus Docente | `docs/modules/04-teacher-status.md` | ✅ |
| QR | `docs/modules/05-qr.md` | ✅ |
| Auth + SAM | `docs/modules/06-auth.md` | ✅ |

