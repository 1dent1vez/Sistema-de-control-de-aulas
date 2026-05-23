# REPORTE DE AUDITORÍA — MÓDULO EDIFICIOS Y AULAS

**Fecha:** 2026-05-22
**Auditor:** OpenCode Agent
**Estado General:** ⚠️ CUMPLE CON OBSERVACIONES

---

## RESUMEN EJECUTIVO

El módulo Edificios y Aulas está implementado en su mayoría siguiendo la arquitectura definida (Servicios, Repositorios, Controladores, FormRequests, Resources, Policies). La estructura de directorios, la nomenclatura GAMA, las migraciones con índices compuestos y FK explícitas, y el uso de transacciones para la creación de edificios+niveles son correctos.

Se identificaron **2 hallazgos críticos** que afectan directamente los requerimientos RF-03 y RF-04: (1) el límite de `level_count` está en 20 en lugar de 5 como especifica el RF-03, y (2) no se verifica que la institución esté activa al crear un edificio (RF-03.1). Adicionalmente, el listado de edificios no filtra por institución activa (RF-04.1). Se recomienda priorizar la corrección de estos puntos antes de continuar con fases posteriores.

---

## DETALLE DE HALLAZGOS

### 🔴 CRÍTICO — Impide funcionamiento o incumple requerimiento obligatorio

| ID | RF Afectado | Descripción | Evidencia | Impacto |
|---|---|---|---|---|
| H-001 | RF-03 | `level_count` permite máximo 20 niveles, pero el RF-03 y `docs/modules/02-buildings.md` especifican **máximo 5**. | `app/Http/Requests/Buildings/StoreBuildingRequest.php:50` — `'level_count' => ['required', 'integer', 'min:1', 'max:20']` | Se pueden registrar edificios con hasta 20 niveles, excediendo el límite de la especificación. Inconsistencia con la documentación y el RF. |
| H-002 | RF-03.1 | No se verifica que la institución esté activa (`is_active = true`) antes de asociar el edificio. `prepareForValidation()` toma la primera institución sin validar su estado. | `app/Http/Requests/Buildings/StoreBuildingRequest.php:58-60` — `Institution::first()` sin filtro `where('is_active', true)`. `GamaBuildingService.php:64-84` no valida estado de institución. | Un edificio puede quedar asociado a una institución inactiva, violando RF-03.1. |

### 🟠 MAYOR — Funciona parcialmente o con workarounds

| ID | RF Afectado | Descripción | Evidencia | Impacto |
|---|---|---|---|---|
| H-003 | RF-04.1 | `GamaBuildingRepository::all()` retorna todos los edificios sin filtrar por institución activa. El endpoint `GET /buildings` muestra edificios de cualquier institución. | `app/Repositories/Buildings/GamaBuildingRepository.php:33-36` — `Building::with('levels')->get()` sin cláusula `where`. | El listado disponible para el combobox incluye edificios de instituciones inactivas, violando RF-04.1. |
| H-004 | RF-04.4 | `GamaClassroomService::create()` no usa `DB::transaction()` ni tiene manejo explícito de errores con rollback para la creación del aula. | `app/Services/Buildings/GamaClassroomService.php:70-73` — `return $this->repository->create($data);` sin transacción ni try/catch. | Aunque el motor de BD previene datos huérfanos (single INSERT), no cumple el estándar del proyecto (Skill 6.8) ni el espíritu del RF-04.4 que exige reversión explícita. |

### 🟡 MENOR — Mejora o buena práctica pendiente

| ID | RF Afectado | Descripción | Evidencia | Impacto |
|---|---|---|---|---|
| H-005 | General | El trait `ApiResponse` tiene cabecera de prólogo incompleta: `@autorizador`, `@prueba` y `@mantenimiento` contienen "Agente" y "N/A" en lugar de nombres reales. | `app/Traits/ApiResponse.php:14-18` | Incumplimiento del Skill 6.2.2 (cabecera obligatoria con datos reales). |
| H-006 | General | Migración `add_soft_deletes_to_gama_levels_table` tiene cabecera de prólogo incompleta: faltan `@autorizador`, `@prueba`, `@mantenimiento`, `@modificado`, `@cambios`. | `database/migrations/2026_05_19_000001_add_soft_deletes_to_gama_levels_table.php:1-11` | Incumplimiento del Skill 6.2.2. |
| H-007 | RF-03.1 | El modelo `Institution` no tiene una relación `buildings()` con el modelo `Building`. Tampoco hay un scope `active()` en Institution. | `app/Models/Institution.php:31-48` | Omisión menor; no bloquea funcionalidad pero dificulta consultas como `Institution::active()->buildings()`. |
| H-008 | General | No existe frontend (Blade/JS) para el módulo Edificios y Aulas. No se pudo auditar Fase 3 (UI/UX, validaciones cliente, flujo de pantallas). | `resources/views/` y `resources/js/` — sin archivos de buildings/classrooms. | Consistente con el enfoque API-first del proyecto, pero la auditoría de frontend queda pendiente para cuando se implementen las vistas. |

---

## VERIFICACIÓN POR REQUERIMIENTO

| Requerimiento | Estado | Notas |
|---|---|---|
| RF-03.1 | ❌ No cumple | No se verifica que la institución esté activa (H-002). Auto-asigna la primera institución sin validar `is_active`. |
| RF-03.2 | ❌ No cumple | La nomenclatura se genera correctamente (PB, P1, P2...), pero `level_count` permite hasta 20 en vez de 5 (H-001). |
| RF-04.1 | ❌ No cumple | `GET /buildings` retorna todos los edificios sin filtrar por institución activa (H-003). |
| RF-04.2 | ✅ Cumple | `GET /buildings/{id}/levels` filtra correctamente por `building_id` en `GamaLevelRepository::findByBuildingId()`. |
| RF-04.3 | ✅ Cumple | Nombre único por edificio validado en FormRequest. `classroom_type` restringido al enum `ClassroomType`. |
| RF-04.4 | ⚠️ Parcial | Validación de existencia de building_id y level_id en FormRequest. Pero `GamaClassroomService::create()` no usa `DB::transaction()` (H-004). |

---

## PLAN DE CORRECCIÓN PRIORIZADO

### Fase 1: Correcciones Críticas (Bloqueantes)

| Hallazgo | Acción Correctiva | Archivos a Modificar | Esfuerzo Estimado |
|---|---|---|---|
| H-001 | Cambiar `max:20` a `max:5` en StoreBuildingRequest y en UpdateBuildingRequest (si aplica). | `app/Http/Requests/Buildings/StoreBuildingRequest.php:50` | 10min |
| H-002 | Agregar validación en `prepareForValidation()` o en el Service para que verifique `Institution::where('is_active', true)->exists()`. Si no existe institución activa, retornar error con mensaje "No hay una institución activa registrada". | `app/Http/Requests/Buildings/StoreBuildingRequest.php:58-60`, `app/Services/Buildings/GamaBuildingService.php:64-84` | 30min |

### Fase 2: Correcciones Mayores

| Hallazgo | Acción Correctiva | Archivos a Modificar | Esfuerzo Estimado |
|---|---|---|---|
| H-003 | Modificar `GamaBuildingRepository::all()` para que filtre por institución activa. Agregar parámetro opcional `$institutionId`. O implementar en el Service. | `app/Repositories/Buildings/GamaBuildingRepository.php:33-36`, `app/Services/Buildings/GamaBuildingService.php:46-49` | 30min |
| H-004 | Envolver `create()` en `DB::transaction()` con try/catch y rollback explícito. Agregar import `Illuminate\Support\Facades\DB`. | `app/Services/Buildings/GamaClassroomService.php:70-73` | 20min |

### Fase 3: Mejoras Menores y UX

| Hallazgo | Acción Correctiva | Archivos a Modificar | Esfuerzo Estimado |
|---|---|---|---|
| H-005 | Completar cabecera de prólogo con nombres reales de autorizador, probador y mantenedor, o valores "Pendiente" hasta asignación. | `app/Traits/ApiResponse.php:14-18` | 10min |
| H-006 | Completar cabecera de prólogo con todos los campos requeridos. | `database/migrations/2026_05_19_000001_add_soft_deletes_to_gama_levels_table.php` | 10min |
| H-007 | Agregar relación `buildings()` y scope `active()` en el modelo Institution. | `app/Models/Institution.php` | 15min |

---

## CHECKLIST FINAL DE CALIDAD

- [x] La creación de edificio + niveles usa `DB::transaction()` atómica (GamaBuildingService)
- [ ] La creación de aula debería usar `DB::transaction()` (GamaClassroomService)
- [x] Validaciones de backend implementadas en FormRequests
- [x] `classroom_type` validado contra Enum (ClassroomType::values())
- [x] Unicidad de nombre de edificio por institución (UNIQUE index + FormRequest)
- [x] Unicidad de nombre de aula por edificio (UNIQUE index + FormRequest)
- [ ] El listado de edificios debería filtrar por institución activa
- [ ] El límite de `level_count` debería ser 5, no 20
- [x] No hay registros huérfanos posibles (FK constraints)
- [x] CRUD completo implementado para buildings y classrooms
- [x] SoftDeletes en buildings, classrooms, institutions, levels
- [x] Código sigue nomenclatura GAMA (prefijos, PascalCase, snake_case BD)
- [x] Controllers dentro del límite de 100 líneas (93 Building, 92 Classroom)
- [x] ApiResponse trait usado en todos los controllers
- [x] Resources transforman snake_case a camelCase
- [x] Bindings de repositorios registrados en AppServiceProvider

---

## ANEXOS

### Fragmentos de código relevantes

**H-001 — level_count con max incorrecto:**
```php
// app/Http/Requests/Buildings/StoreBuildingRequest.php:50
'level_count' => ['required', 'integer', 'min:1', 'max:20'], // DEBERÍA SER max:5
```

**H-002 — Sin validación de institución activa:**
```php
// app/Http/Requests/Buildings/StoreBuildingRequest.php:58-60
protected function prepareForValidation(): void
{
    if (! $this->has('institution_id') || ! $this->input('institution_id')) {
        $first = Institution::first(); // NO valida is_active
        $this->merge(['institution_id' => $first?->id ?? 1]);
    }
}
```

**H-003 — Listado sin filtro por institución activa:**
```php
// app/Repositories/Buildings/GamaBuildingRepository.php:33-36
public function all(): Collection
{
    return Building::with('levels')->get(); // Sin where por institución activa
}
```

**H-004 — Falta DB::transaction() en creación de aula:**
```php
// app/Services/Buildings/GamaClassroomService.php:70-73
public function create(array $data): Classroom
{
    return $this->repository->create($data); // Sin transacción
}
```

### Rutas de archivos auditados

```
database/migrations/2026_05_13_100001_create_gama_institutions_table.php
database/migrations/2026_05_13_200001_create_gama_buildings_table.php
database/migrations/2026_05_13_200002_create_gama_levels_table.php
database/migrations/2026_05_13_200003_create_gama_classrooms_table.php
database/migrations/2026_05_19_000001_add_soft_deletes_to_gama_levels_table.php
app/Models/Institution.php
app/Models/Building.php
app/Models/Level.php
app/Models/Classroom.php
app/Enums/Buildings/ClassroomType.php
app/Repositories/Contracts/BuildingRepositoryInterface.php
app/Repositories/Contracts/LevelRepositoryInterface.php
app/Repositories/Contracts/ClassroomRepositoryInterface.php
app/Repositories/Buildings/GamaBuildingRepository.php
app/Repositories/Buildings/GamaLevelRepository.php
app/Repositories/Buildings/GamaClassroomRepository.php
app/Services/Buildings/GamaBuildingService.php
app/Services/Buildings/GamaClassroomService.php
app/Http/Requests/Buildings/StoreBuildingRequest.php
app/Http/Requests/Buildings/UpdateBuildingRequest.php
app/Http/Requests/Buildings/StoreClassroomRequest.php
app/Http/Requests/Buildings/UpdateClassroomRequest.php
app/Http/Resources/Buildings/BuildingResource.php
app/Http/Resources/Buildings/LevelResource.php
app/Http/Resources/Buildings/ClassroomResource.php
app/Http/Controllers/Api/V1/Buildings/GamaBuildingController.php
app/Http/Controllers/Api/V1/Buildings/GamaClassroomController.php
app/Policies/BuildingPolicy.php
app/Policies/ClassroomPolicy.php
app/Traits/ApiResponse.php
app/Providers/AppServiceProvider.php
routes/api.php
tests/Feature/Api/V1/Buildings/BuildingTest.php
tests/Feature/Api/V1/Buildings/ClassroomTest.php
tests/Unit/Services/GamaBuildingServiceTest.php
tests/Unit/Services/GamaClassroomServiceTest.php
```
