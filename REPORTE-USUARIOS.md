# Reporte: Ajustes en Módulo de Usuarios

## 1. Archivos revisados
- `resources/views/usuarios/index.blade.php`
- `app/Http/Controllers/Api/V1/Auth/SamIdentityController.php`
- `app/Models/SamIdentity.php`
- `app/Repositories/Auth/GamaSamIdentityRepository.php`
- `app/Http/Requests/Auth/AssignRoleRequest.php`

## 2. Cambios realizados

### Combobox de Roles
- Archivo modificado: `resources/views/usuarios/index.blade.php`
- Roles eliminados: Coordinador, Espectador
- Roles mantenidos: Admin, Docente
- Método de llenado del select: Hardcodeado (estático en la vista HTML/Blade a través de elementos `<option>`).
- Ajustes adicionales: Se modificó `AssignRoleRequest.php` en el backend para validar que el rol asignado sea únicamente `admin` o `teacher`.

### Fuente de Datos (DB Railway)
- Problema identificado: 
  - La vista `/usuarios` carga las identidades llamando a `/api/v1/sam-identities`, el cual recupera registros a través del repositorio `GamaSamIdentityRepository`. Aunque estos registros provienen de la base de datos predeterminada (Railway), para mostrar los nombres y correos el recurso `SamProfileResource` llama al método `getProfileFromSam()` del modelo `SamIdentity`, el cual realizaba una consulta directa al modelo `SamEmployee` en la conexión `'sam'`. Esta conexión está configurada hacia la base de datos local de SAM (`127.0.0.1:3306`), lo que hacía que en entornos de desarrollo local se cargaran perfiles locales y en producción (donde no está disponible la base de datos local) arrojara un error.
  - Además, la búsqueda de usuarios llamaba al endpoint `/api/v1/sam/empleados?q=` que consultaba `SamEmployee` directamente sobre la base de datos local de SAM, fallando en el servidor de Railway.
  - La existencia de registros con rol `alumno` en la base de datos de Railway provocaba un error de tipo `ValueError` al mapear/castear el valor de la columna a la enumeración `SamRole` de Laravel.
- Solución aplicada:
  - Se modificó `getProfileFromSam()` en `SamIdentity.php` para validar si las columnas `full_name` y `email` ya contienen información en la tabla `sam_identities` (que es el caso para la base de datos desplegada en Railway), usándolas de inmediato sin consultar a la conexión local `'sam'`.
  - Se actualizó el método `searchSamEmployees()` en `SamIdentityController.php` para priorizar la búsqueda dentro de la tabla `sam_identities` (en Railway) y solo usar la conexión local `'sam'` como fallback para desarrollo local, garantizando que en Railway siempre se busquen los usuarios institucionales disponibles.
  - Se filtró el repositorio `GamaSamIdentityRepository` en `all()` y `search()` para omitir registros con roles no contemplados por la enumeración del sistema (como `alumno`), eliminando los fallos por `ValueError`.
- Archivos modificados:
  - `app/Models/SamIdentity.php`
  - `app/Http/Controllers/Api/V1/Auth/SamIdentityController.php`
  - `app/Repositories/Auth/GamaSamIdentityRepository.php`
- Conexión/Modelo/Servicio usado: Conexión predeterminada `mysql` (Railway), modelos `SamIdentity` y fallback a `SamEmployee`.

## 3. Auditoría de otras vistas
- ¿Se encontraron combobox de roles similares en otras vistas? No. Las demás vistas (de ausencias o importación de horarios) no tienen combobox de asignación de roles. Ellas consumen `/api/v1/sam-identities/teachers` para poblar el listado de docentes, la cual ya usa el modelo `SamIdentity` con la conexión predeterminada (Railway).

## 4. Estado final
- [x] Combobox filtrado (solo Admin y Docente)
- [x] Lista de usuarios carga desde Railway
- [x] `php artisan view:clear` ejecutado
- [x] Sin errores de renderizado

## 5. Notas / Advertencias
- En la base de datos de producción de Railway se identificaron dos registros con rol `alumno` (`sam_id` 5 y 6). Estos han sido filtrados del listado del panel administrativo para evitar que el cast del Enum de Laravel genere un error crítico de ejecución, dado que el catálogo oficial del sistema web solo contempla roles de personal (Administrador, Docente).
