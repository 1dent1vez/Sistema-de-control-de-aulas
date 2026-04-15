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

- **Backend:** Laravel (API + Web)  
- **Frontend:** Blade + Vue.js  
- **Estilos:** CSS basado en Design System GAMA  
- **Autenticación:** JWT (integración con servicios externos)  
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
git clone https://github.com/tu-repo/interfazbase.git

# Entrar al proyecto
cd interfazbase

# Instalar dependencias
composer install
npm install

# Configurar entorno
cp .env.example .env
php artisan key:generate

# Ejecutar migraciones
php artisan migrate

# Levantar servidor
php artisan serve

