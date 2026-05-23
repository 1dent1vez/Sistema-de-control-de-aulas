# Auditoría Técnica — Módulo Gestión de Usuarios y Roles (RF-07)

**Fecha:** 2026-05-22  
**Auditor:** Agente opencode  
**Alcance:** Backend (Laravel 11, SQLite, Sanctum) + Frontend (Alpine.js, Blade, Tailwind)  
**Documentos de referencia:** `docs/modules/06-auth.md`, `.opencode/skills/*.md`, `AGENTS.md`

---

## Resumen Ejecutivo

| Severidad | Cantidad |
|-----------|----------|
| Crítico   | 1        |
| Mayor     | 5        |
| Menor     | 6        |

---

## 1. HALLAZGOS CLASIFICADOS POR SEVERIDAD

### 🔴 CRÍTICO

#### C-01: Búsqueda contra SAM no implementada (RF-07.1)

| Atributo | Valor |
|----------|-------|
| **Archivo** | `app/Services/Auth/SamRoleService.php:54-71` |
| **RF** | RF-07.1: "Búsqueda de usuario por número de empleado/correo institucional contra SAM, NO contra BD local" |
| **Descripción** | `searchInSam()` consulta exclusivamente la tabla local `gama_sam_identities`. Si no encuentra resultados, fabrica un objeto con datos inventados (`full_name: null`, `email: query@toluca.tecnm.mx`). Nunca invoca un web service externo SAM. El `TODO` en línea 56 confirma que es una funcionalidad pendiente. |
| **Impacto** | Un administrador puede asignar roles a usuarios que no existen en SAM. No se muestra el mensaje "Servicio de directorio no disponible" cuando SAM falla. |
| **Requerimiento exacto** | "Solo administrador puede asignar roles. Si SAM falla, debe mostrar 'Servicio de directorio no disponible' y bloquear la asignación." |

---

### 🟠 MAYOR

#### M-01: Sin endpoint SAM de búsqueda de usuarios (diseño)

| Atributo | Valor |
|----------|-------|
| **Archivo** | `docs/modules/06-auth.md` |
| **RF** | RF-07.1 |
| **Descripción** | El protocolo SAM documentado en `06-auth.md` (5 pasos: captcha → validar → login → extraer token → obtener perfil) NO incluye un endpoint de búsqueda de usuarios por número de empleado o correo. No existe ruta SAM tipo `/buscarEmpleado.do?q=...`. Sin este endpoint, el RF-07.1 es irrealizable. |
| **Impacto** | La búsqueda de usuarios contra SAM es imposible hasta que SAM exponga un endpoint de búsqueda. Mientras tanto, la implementación actual con BD local + datos fabricados es insegura. |
| **Recomendación** | Coordinar con el equipo de SAM para exponer un endpoint de consulta, o agregar un comando `artisan` que sincronice periódicamente el directorio SAM contra `gama_sam_identities` como cache autoritativo. |

#### M-02: Controlador AuthController excede límite de 100 líneas (109)

| Atributo | Valor |
|----------|-------|
| **Archivo** | `app/Http/Controllers/Api/V1/Auth/AuthController.php` |
| **Regla** | Skill 6.3: "Máximo 100 líneas por controlador API" |
| **Descripción** | El controlador tiene 109 líneas (línea 1-109). Supera en 9 líneas el máximo permitido. |
| **Impacto** | Incumplimiento de estándar de arquitectura, rechazable en PR. |

#### M-03: Inline role check en TeacherAbsenceController viola arquitectura

| Atributo | Valor |
|----------|-------|
| **Archivo** | `app/Http/Controllers/Api/V1/TeacherStatus/GamaTeacherAbsenceController.php:53-55` |
| **Regla** | Skill 6.5: "Se prohíbe verificar permisos con `if ($user->role === ...)` en controladores o servicios" |
| **Descripción** | El controlador tiene un inline role check: `if ($request->user()->role !== SamRole::ADMIN)` en lugar de usar exclusivamente la Policy. Aunque la Policy `viewAny` está presente, retorna `true` para todos, delegando el filtrado al controlador. |
| **Impacto** | Mezcla de responsabilidades. La lógica de filtrado debe estar en el Service/Repository usando el `external_id` del usuario autenticado, no en el controlador. |

#### M-04: AuthController.login en modo mock asigna ADMIN siempre

| Atributo | Valor |
|----------|-------|
| **Archivo** | `app/Services/Auth/SamAuthService.php:52-91` |
| **RF** | RF-07.2 |
| **Descripción** | En modo mock (`sam.mock_enabled = true`), cualquier login exitoso crea la identidad con `SamRole::ADMIN` y emite token con abilities `['*']`. Si el mock se deja activo accidentalmente en staging, cualquier credencial obtendrá privilegios de administrador. |
| **Impacto** | Riesgo de seguridad en ambientes no locales. |
| **Mitigación** | `config/sam.php` actualmente tiene `'mock_enabled' => (bool) env('SAM_MOCK_ENABLED', false)` (default false). El `.env` tiene `SAM_MOCK_ENABLED=false`. Solo hay riesgo si alguien cambia manualmente a `true`. |

#### M-05: Mapeo 'empleado' → teacher ignora permisos CRUD de SAM

| Atributo | Valor |
|----------|-------|
| **Archivo** | `app/Services/Auth/SamAuthService.php:213-219` (`mapearRolLocal()`) |
| **RF** | RF-07.2 |
| **Descripción** | El mapeo de `rol` SAM a local es: `'master'` → `ADMIN`, cualquier otro (`'empleado'` incluido) → `TEACHER`. Según `06-auth.md:169`, "`empleado` con permisos CRUD → `admin`". El perfil SAM trae flags `crear`, `leer`, `editar`, `eliminar` (booleans), pero `mapearRolLocal()` nunca los evalúa. |
| **Impacto** | Empleados de SAM con todos los permisos CRUD serán mapeados como `teacher` (docente) en lugar de `admin`. |

---

### 🟢 MENOR

#### m-01: Sin manejo de excepción para rol inválido (RF-07.2)

| Atributo | Valor |
|----------|-------|
| **Archivo** | `app/Http/Controllers/Api/V1/Auth/SamIdentityController.php:73` |
| **RF** | RF-07.2 |
| **Descripción** | La línea `$role = SamRole::from($request->input('role'));` lanza un `ValueError` si el valor no coincide con los casos del enum (`admin`, `teacher`). El `AssignRoleRequest` valida que `role` esté en la lista, pero la excepción `ValueError` no tiene handler explícito en `bootstrap/app.php`. Si alguien manipula la request, recibe un 500 genérico en lugar de "Rol no contemplado en el sistema". |
| **Impacto** | Mensaje de error inespecífico para manipulación maliciosa. |

#### m-02: Fuga parcial de token en logs de SamAuthMiddleware

| Atributo | Valor |
|----------|-------|
| **Archivo** | `app/Http/Middleware/SamAuthMiddleware.php:53-57` |
| **Regla** | Skill 6.9: "Se prohíbe loguear contraseñas, tokens, cookies de sesión o datos PII" |
| **Descripción** | Se loguea `token_prefix` (primeros 10 caracteres del token) como parte del contexto de diagnóstico: `'token_prefix' => $tokenPrefix`. Aunque es parcial, expone información del token. |
| **Impacto** | Riesgo bajo de fuga de información en logs compartidos. |

#### m-03: HTML de SAM guardado en disco en producción potencial

| Atributo | Valor |
|----------|-------|
| **Archivo** | `app/Services/Auth/SamService.php:153-155, 346-349` |
| **Regla** | Skill 6.7: "Stack traces nunca deben exponerse en respuestas JSON en producción" |
| **Descripción** | En `login()` y `obtenerPerfil()`, se guarda el HTML/JSON crudo de la respuesta SAM en `storage/logs/sam_raw_response_*.html` y `sam_perfil_*.txt`. Estos archivos pueden contener datos personales (nombres, correos) de los usuarios SAM. |
| **Impacto** | Posible almacenamiento no controlado de PII. |

#### m-04: Columna `role` en BD sin constraint (debería ser ENUM)

| Atributo | Valor |
|----------|-------|
| **Archivo** | `database/migrations/2026_05_17_000001_create_gama_sam_identities_table.php:21` |
| **Regla** | Skill 6.1: "PHP Enums deben usarse para estados y tipos" |
| **Descripción** | La columna `$table->string('role')` acepta cualquier string. Aunque el modelo castea a `SamRole`, la BD no impide valores inválidos a nivel de datos. |
| **Impacto** | Integridad referencial a nivel de aplicación, no de BD. |

#### m-05: Cobertura de tests insuficiente para RF-07

| Atributo | Valor |
|----------|-------|
| **Archivo** | `tests/Feature/Api/V1/Auth/RoleAssignmentTest.php` (37 líneas) |
| **Regla** | Skill 6.11: "Cobertura mínima 70% en clases de Services" |
| **Descripción** | `RoleAssignmentTest.php` solo cubre 2 escenarios (admin asigna, teacher denegado). Faltan: rollback de transacción, SAM no disponible, asignación sin identidad existente, invalidación de token tras cambio de rol, rol inválido. |
| **Impacto** | Baja confianza en regresiones del flujo de asignación. |

#### m-06: Middleware CheckRole no retorna 403

| Atributo | Valor |
|----------|-------|
| **Archivo** | `app/Http/Middleware/CheckRole.php:27-28` |
| **Descripción** | Cuando un usuario no tiene el rol requerido, el middleware redirige a `dashboard` en lugar de retornar 403. Esto es aceptable para rutas web (UX), pero inconsistente con la Policy API que sí retorna 403 con mensaje claro. |
| **Impacto** | Inconsistencia de comportamiento web vs API. |

---

## 2. VERIFICACIÓN POR REQUERIMIENTO

### RF-07.1: Búsqueda de usuario contra SAM

| # | Subcriterio | Estado | Evidencia |
|---|-------------|--------|-----------|
| 1 | Búsqueda por número de empleado/correo institucional contra SAM | ❌ NO CUMPLE | `SamRoleService::searchInSam()` busca en BD local y fabrica datos |
| 2 | NO contra BD local | ❌ NO CUMPLE | Busca en `gama_sam_identities` primero (línea 57) |
| 3 | Solo administrador puede buscar y asignar | ✅ CUMPLE | `SamIdentityPolicy::viewAny()` y `create()` requieren `SamRole::ADMIN` |
| 4 | Si SAM falla, mostrar "Servicio de directorio no disponible" | ❌ NO CUMPLE | No hay try/catch alrededor de llamado a SAM (porque no hay llamado) |
| 5 | Bloquear asignación si SAM no responde | ❌ NO CUMPLE | No hay bloqueo de asignación basado en disponibilidad de SAM |

### RF-07.2: Catálogo de roles

| # | Subcriterio | Estado | Evidencia |
|---|-------------|--------|-----------|
| 1 | Roles restringidos a Administrador y Docente | ✅ CUMPLE | `SamRole` enum solo tiene `ADMIN` y `TEACHER` |
| 2 | Rol Alumno excluido de asignación manual | ✅ CUMPLE | No hay caso `STUDENT` en el enum. Frontend no lo ofrece. |
| 3 | Manejo de excepción de negocio si se requiere rol no contemplado | ⚠️ PARCIAL | `AssignRoleRequest` valida con `new In(SamRole::values())`, pero `SamRole::from()` lanza `ValueError` no capturado si alguien pasa un valor inválido directamente |
| 4 | Roles gestionados por app móvil vía SAM (Alumno) | N/A | No implementado en esta fase. Documentado en RN-04. |

### RF-07.3: Asignación de roles y atomicidad

| # | Subcriterio | Estado | Evidencia |
|---|-------------|--------|-----------|
| 1 | Registro de asignación en operación atómica | ✅ CUMPLE | `SamRoleService::assignRole()` envuelve en `DB::transaction()` |
| 2 | Aplicación inmediata de permisos | ✅ CUMPLE | Sanctum token se emite con abilities según rol, políticas evalúan rol en cada request |
| 3 | Si falla escritura en BD, rollback completo | ✅ CUMPLE | `DB::transaction()` revierte automáticamente en excepción |
| 4 | Mantener rol anterior del usuario si falla | ⚠️ PARCIAL | La transacción revierte, pero no hay mensaje específico "No se pudo actualizar el rol, se conserva el anterior" para el usuario |
| 5 | Revocación de token Sanctum al cambiar rol | ❌ NO CUMPLE | `assignRole()` actualiza el rol en la identidad pero no revoca tokens anteriores. El usuario mantiene acceso con abilities viejas hasta que el token expire (30 min). |

---

## 3. PLAN DE CORRECCIÓN PRIORIZADO

| Prioridad | Hallazgo | Archivos a modificar | Acción |
|-----------|----------|---------------------|--------|
| **P1** | C-01: Búsqueda contra SAM no implementada | `app/Services/Auth/SamRoleService.php` | Implementar `searchInSam()` que consulte SAM real (una vez exista endpoint). Hasta entonces, documentar limitación y agregar chequeo de conectividad con SAM para mostrar "Servicio de directorio no disponible". Agregar flag `SAM_SEARCH_ENABLED` en config. |
| **P2** | M-01: Sin endpoint de búsqueda en SAM | `docs/modules/06-auth.md` | Agregar requerimiento de endpoint SAM `buscarEmpleado.do`. Mientras no exista, implementar cache local sincronizado vía comando artisan diario. |
| **P3** | RF-07.3-5: Revocación de tokens al cambiar rol | `app/Services/Auth/SamRoleService.php` | Agregar `$identity->tokens()->delete()` después de actualizar el rol, para forzar re-login. |
| **P4** | M-05: Mapeo 'empleado' ignora permisos CRUD | `app/Services/Auth/SamAuthService.php:213-219` | Evaluar flags `crear`, `leer`, `editar`, `eliminar` del perfil SAM para decidir si el mapeo es `admin` o `teacher`. |
| **P5** | M-03: Inline role check en TeacherAbsenceController | `app/Http/Controllers/Api/V1/TeacherStatus/GamaTeacherAbsenceController.php:53-55` | Mover filtrado de `teacher_external_id` al Service/Repository. Usar `$request->user()->external_id`. |
| **P6** | m-01: Manejo de excepción rol inválido | `bootstrap/app.php`, `app/Exceptions/` | Agregar custom exception `InvalidRoleException` y mapearla a 422 con mensaje "Rol no contemplado en el sistema". |
| **P7** | M-02: AuthController excede 100 líneas | `app/Http/Controllers/Api/V1/Auth/AuthController.php` | Refactorizar: extraer lógica de cookies a un helper/método privado o mover a servicio. |
| **P8** | m-04: Columna role sin constraint | `database/migrations/..._create_gama_sam_identities_table.php` | Cambiar `$table->string('role')` a `$table->string('role', 20)` o usar columna ENUM nativa si el motor lo permite. |
| **P9** | m-02/m-03: Logging de tokens y datos SAM | `app/Http/Middleware/SamAuthMiddleware.php`, `app/Services/Auth/SamService.php` | Eliminar `token_prefix` de logs. Condicionar guardado de HTML SAM a `app()->isLocal()`. |
| **P10** | m-05: Tests insuficientes | `tests/Feature/Api/V1/Auth/RoleAssignmentTest.php` | Agregar tests: rol inválido, SAM no disponible, rollback transaccional, revocación de token, identidad nueva (create en assignRole). |
| **P11** | M-04: Mock siempre ADMIN | `app/Services/Auth/SamAuthService.php:52-91` | Agregar validación de entorno: `if (app()->isProduction())` bloquear modo mock. |

---

## 4. CHECKLIST FINAL DE CALIDAD

### Arquitectura y Estándares
- [x] No se crea tabla `users`
- [x] `teacher_id` es `teacher_external_id` (VARCHAR) sin FK local
- [x] `declare(strict_types=1)` presente en todos los archivos PHP
- [x] Nomenclatura: `gama_` prefijo en tablas, `Gama` prefijo en Services/Repositories
- [x] SoftDeletes presente en `SamIdentity`
- [x] Enum `SamRole` usado para catálogo de roles
- [x] `ApiResponse` trait usado en todos los controllers
- [x] `FormRequest` (`AssignRoleRequest`) usado para validación
- [x] Policy (`SamIdentityPolicy`) implementada para autorización
- [ ] ❌ `DB::transaction()` en asignación de roles — **implementado correctamente**
- [ ] ❌ `AuthController` excede 100 líneas (109)
- [ ] ❌ Inline role check en `GamaTeacherAbsenceController`

### Seguridad
- [x] Solo admin puede gestionar roles (Policy + CheckRole middleware)
- [x] Rutas API protegidas con `auth:sanctum`
- [x] No hay IDOR (los policies verifican propiedad/rol correctamente)
- [ ] ❌ Tokens Sanctum no se revocan al cambiar rol
- [ ] ❌ Búsqueda contra SAM no implementada (RF-07.1 no se cumple)
- [ ] ❌ Token prefix expuesto en logs de middleware
- [ ] ❌ HTML de respuestas SAM guardado en disco

### Backend
- [x] Migración con índices y unique constraints
- [x] Modelo con casts a `SamRole` enum y `HasApiTokens`
- [x] Repositorio implementa interfaz (contrato)
- [x] Service inyecta repositorio por interfaz
- [x] Binding registrado en `AppServiceProvider`
- [x] Rate limiting configurado (10/min para auth)
- [x] Mapeo de excepciones en `bootstrap/app.php` (404, 403, 401, 422, 429, 500)
- [x] CORS configurado con `allowed_origins` explícitos

### Frontend
- [x] UI de búsqueda con debounce 300ms
- [x] Select de roles solo con admin/teacher
- [x] Estados de carga (spinner), vacío ("Sin resultados en SAM"), error ("Error al cargar usuarios")
- [x] RN-04 visible en UI
- [ ] ❌ No hay mensaje "Servicio de directorio no disponible" (porque no hay llamado a SAM)
- [x] Toast de éxito/error
- [ ] ❌ No hay manejo de timeout de SAM en frontend

### Tests
- [x] Test de asignación admin
- [x] Test de denegación teacher
- [ ] ❌ No hay test de rollback transaccional
- [ ] ❌ No hay test de rol inválido
- [ ] ❌ No hay test de "SAM no disponible"
- [ ] ❌ No hay test de revocación de token
- [x] Tests de extracción de token SAM (7 patrones de regex)
- [x] Tests de middleware (cookies, expiración, token inválido)

---

## 5. VULNERABILIDADES DE SEGURIDAD

| ID | Hallazgo | Severidad | CVE Potencial |
|----|----------|-----------|---------------|
| VS-01 | Búsqueda de usuarios fabrica datos si no encuentra en BD local | **Alta** | N/A (diseño inseguro) |
| VS-02 | Tokens Sanctum no se revocan al cambiar rol | **Media** | Token reuse after role change |
| VS-03 | Token prefix en logs de diagnóstico | **Baja** | Information disclosure |
| VS-04 | HTML de respuestas SAM guardado en disco con datos personales | **Media** | PII leakage |
| VS-05 | Mock mode asigna ADMIN a cualquier login | **Media** | Privilege escalation (solo si se activa en staging/prod) |

---

## 6. CONCLUSIÓN

El módulo de Gestión de Usuarios y Roles tiene una **implementación base sólida** (arquitectura limpia, policies, transacciones, enums, validación) pero **falla en el requisito funcional más crítico (RF-07.1)** porque la búsqueda de usuarios nunca consulta el web service SAM externo. Hasta que SAM exponga un endpoint de búsqueda de personas, este requisito no puede implementarse completamente.

**Recomendación inmediata:** Documentar explícitamente que la búsqueda de usuarios es contra cache local (`gama_sam_identities`) y que la sincronización con SAM debe implementarse posteriormente. Agregar un comando `artisan sam:sync` que poble la tabla desde SAM periódicamente.

**Recomendación a corto plazo:** Revocar tokens Sanctum al cambiar rol (P3), mapear permisos CRUD de SAM correctamente (P4), y eliminar inline role checks (P5).
