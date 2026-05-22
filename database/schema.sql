-- ============================================================================
-- Schema de Base de Datos — Sistema de Control de Aulas (GAMA)
-- ============================================================================
-- Motor: MySQL 8.0+ / SQLite (dev)
-- Generado: 2026-05-18
-- Estado: Refleja el estado ACTUAL de las migraciones implementadas
--
-- NOTA: Este script es idempotente. Usa DROP TABLE IF EXISTS + CREATE TABLE.
-- Las migraciones de Laravel (0001_01_01_*) son scaffold del framework y
-- no se incluyen aquí. La tabla users se incluye como referencia pero NO
-- debe usarse (Regla de Oro #1: No tabla users).
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================
-- TABLA: gama_institutions
-- Catálogo de instituciones educativas
-- ============================================================================
DROP TABLE IF EXISTS `gama_institutions`;
CREATE TABLE `gama_institutions` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `code` VARCHAR(20) NOT NULL,
    `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY `uq_institutions_name` (`name`),
    UNIQUE KEY `uq_institutions_code` (`code`),
    INDEX `idx_institutions_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Catálogo de instituciones educativas';

-- ============================================================================
-- TABLA: gama_absence_types
-- Catálogo de tipos de ausencia docente (5 valores fijos)
-- ============================================================================
DROP TABLE IF EXISTS `gama_absence_types`;
CREATE TABLE `gama_absence_types` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL,
    `code` VARCHAR(20) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY `uq_absence_types_name` (`name`),
    UNIQUE KEY `uq_absence_types_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Catálogo de tipos de ausencia docente';

-- Datos semilla: 5 tipos de ausencia fijos
INSERT INTO `gama_absence_types` (`name`, `code`, `created_at`, `updated_at`) VALUES
    ('Comisión', 'comision', NOW(), NOW()),
    ('Junta', 'junta', NOW(), NOW()),
    ('Incapacidad', 'incapacidad', NOW(), NOW()),
    ('Permiso Económico', 'permiso_economico', NOW(), NOW()),
    ('Otro', 'otro', NOW(), NOW());

-- ============================================================================
-- TABLA: gama_buildings
-- Registro de edificios por institución
-- ============================================================================
DROP TABLE IF EXISTS `gama_buildings`;
CREATE TABLE `gama_buildings` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `institution_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `level_count` TINYINT UNSIGNED NOT NULL,
    `status` BOOLEAN NOT NULL DEFAULT TRUE,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY `uq_buildings_institution_name` (`institution_id`, `name`),
    INDEX `idx_buildings_institution_id` (`institution_id`),
    CONSTRAINT `fk_buildings_institution`
        FOREIGN KEY (`institution_id`) REFERENCES `gama_institutions` (`id`)
        -- NOTA: onDelete RESTRICT (comportamiento por defecto de Laravel foreignId)
        -- Se recomienda agregar CASCADE en migración futura si el negocio lo requiere
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Registro de edificios por institución';

-- ============================================================================
-- TABLA: gama_levels
-- Niveles de edificio (auto-generados: PB, P1, P2...)
-- Se crean automáticamente al registrar un edificio.
-- No se editan ni eliminan individualmente.
-- ============================================================================
DROP TABLE IF EXISTS `gama_levels`;
CREATE TABLE `gama_levels` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `building_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(10) NOT NULL COMMENT 'PB, P1, P2, P3, P4',
    `display_order` TINYINT UNSIGNED NOT NULL COMMENT '0=PB, 1=P1, 2=P2...',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_levels_building_name` (`building_id`, `name`),
    INDEX `idx_levels_building_id` (`building_id`),
    CONSTRAINT `fk_levels_building`
        FOREIGN KEY (`building_id`) REFERENCES `gama_buildings` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Niveles de edificio auto-generados (PB, P1, P2...)';

-- ============================================================================
-- TABLA: gama_classrooms
-- Aulas/salones asociados a edificio y nivel
-- ============================================================================
DROP TABLE IF EXISTS `gama_classrooms`;
CREATE TABLE `gama_classrooms` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `building_id` BIGINT UNSIGNED NOT NULL,
    `level_id` BIGINT UNSIGNED NOT NULL,
    `classroom_name` VARCHAR(30) NOT NULL,
    `classroom_type` VARCHAR(20) NOT NULL COMMENT 'classroom o computer_lab',
    `status` BOOLEAN NOT NULL DEFAULT TRUE,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY `uq_classrooms_building_name` (`building_id`, `classroom_name`),
    INDEX `idx_classrooms_building_id` (`building_id`),
    INDEX `idx_classrooms_level_id` (`level_id`),
    CONSTRAINT `fk_classrooms_building`
        FOREIGN KEY (`building_id`) REFERENCES `gama_buildings` (`id`),
        -- onDelete RESTRICT por defecto
    CONSTRAINT `fk_classrooms_level`
        FOREIGN KEY (`level_id`) REFERENCES `gama_levels` (`id`)
        -- onDelete RESTRICT por defecto
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Aulas y laboratorios asociados a edificio y nivel';

-- ============================================================================
-- TABLA: gama_semesters
-- Semestres académicos con vigencia automática calculada en aplicación
-- ============================================================================
DROP TABLE IF EXISTS `gama_semesters`;
CREATE TABLE `gama_semesters` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `institution_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(50) NOT NULL COMMENT 'Ej: "Enero-Junio 2026"',
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `is_active` BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Calculado en app: CURRENT_DATE BETWEEN start_date AND end_date',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY `uq_semesters_institution_name` (`institution_id`, `name`),
    INDEX `idx_semesters_institution_id` (`institution_id`),
    INDEX `idx_semesters_dates` (`start_date`, `end_date`),
    CONSTRAINT `fk_semesters_institution`
        FOREIGN KEY (`institution_id`) REFERENCES `gama_institutions` (`id`)
        -- onDelete RESTRICT por defecto
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Semestres académicos con vigencia automática';

-- ============================================================================
-- TABLA: gama_class_schedules
-- Horarios de clase por semestre, aula y docente
-- teacher_external_id: identificador SAM (VARCHAR, no es FK)
-- subject_name: nombre de materia como texto libre (no hay tabla subjects)
-- ============================================================================
DROP TABLE IF EXISTS `gama_class_schedules`;
CREATE TABLE `gama_class_schedules` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `semester_id` BIGINT UNSIGNED NOT NULL,
    `classroom_id` BIGINT UNSIGNED NOT NULL,
    `teacher_external_id` VARCHAR(50) NOT NULL COMMENT 'Identificador SAM del docente (no es FK)',
    `subject_name` VARCHAR(100) NOT NULL COMMENT 'Nombre de materia como texto libre',
    `group_name` VARCHAR(10) NOT NULL,
    `weekday` VARCHAR(15) NOT NULL COMMENT 'monday, tuesday, wednesday, thursday, friday, saturday, sunday',
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `status` BOOLEAN NOT NULL DEFAULT TRUE,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    INDEX `idx_schedules_semester_id` (`semester_id`),
    INDEX `idx_schedules_classroom_id` (`classroom_id`),
    INDEX `idx_schedules_teacher_external_id` (`teacher_external_id`),
    INDEX `idx_schedules_weekday` (`weekday`),
    INDEX `idx_schedules_time_range` (`start_time`, `end_time`),
    CONSTRAINT `fk_schedules_semester`
        FOREIGN KEY (`semester_id`) REFERENCES `gama_semesters` (`id`),
        -- onDelete RESTRICT por defecto
    CONSTRAINT `fk_schedules_classroom`
        FOREIGN KEY (`classroom_id`) REFERENCES `gama_classrooms` (`id`)
        -- onDelete RESTRICT por defecto
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Horarios de clase por semestre, aula y docente';

-- ============================================================================
-- TABLA: gama_teacher_absences
-- Registro de ausencias de docentes
-- El rango de fechas se cruza con class_schedules en lectura (no hay tabla intermedia)
-- ============================================================================
DROP TABLE IF EXISTS `gama_teacher_absences`;
CREATE TABLE `gama_teacher_absences` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `teacher_external_id` VARCHAR(50) NOT NULL COMMENT 'Identificador SAM del docente',
    `absence_type_id` BIGINT UNSIGNED NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `observations` TEXT NULL COMMENT 'Notas del docente',
    `is_confirmed` BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'true = confirmó traslape',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    INDEX `idx_absences_teacher` (`teacher_external_id`),
    INDEX `idx_absences_type` (`absence_type_id`),
    INDEX `idx_absences_date_range` (`start_date`, `end_date`),
    CONSTRAINT `fk_absences_type`
        FOREIGN KEY (`absence_type_id`) REFERENCES `gama_absence_types` (`id`)
        -- onDelete RESTRICT por defecto
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Registro de ausencias de docentes con detección de traslapes';

-- ============================================================================
-- TABLA: gama_qr_codes
-- Códigos QR estáticos por aula
-- Solo un QR activo por aula (lógica de aplicación, no constraint DB)
-- ============================================================================
DROP TABLE IF EXISTS `gama_qr_codes`;
CREATE TABLE `gama_qr_codes` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `classroom_id` BIGINT UNSIGNED NOT NULL,
    `token` VARCHAR(255) NOT NULL COMMENT 'UUID único del QR',
    `payload` JSON NOT NULL COMMENT '{"classroomId": N, "classroomName": "...", "buildingName": "...", "token": "..."}',
    `file_path` VARCHAR(255) NULL COMMENT 'Ruta en storage/private/qr/',
    `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
    `generated_at` TIMESTAMP NULL DEFAULT NULL,
    `invalidated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Cuando se reemplaza por uno nuevo',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY `uq_qr_codes_token` (`token`),
    INDEX `idx_qr_codes_classroom_id` (`classroom_id`),
    INDEX `idx_qr_codes_is_active` (`is_active`),
    CONSTRAINT `fk_qr_codes_classroom`
        FOREIGN KEY (`classroom_id`) REFERENCES `gama_classrooms` (`id`)
        -- onDelete RESTRICT por defecto
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Códigos QR estáticos por aula para descarga en PDF/PNG';

-- ============================================================================
-- TABLA: gama_sam_identities
-- Cache mínimo de identidad SAM + rol local
-- NO es tabla users. Implementa Authenticatable para Sanctum.
-- ============================================================================
DROP TABLE IF EXISTS `gama_sam_identities`;
CREATE TABLE `gama_sam_identities` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `external_id` VARCHAR(50) NOT NULL COMMENT 'employee_number de SAM',
    `email` VARCHAR(100) NOT NULL COMMENT 'Correo institucional @toluca.tecnm.mx',
    `full_name` VARCHAR(100) NULL COMMENT 'Nombre completo desde SAM',
    `role` VARCHAR(20) NOT NULL COMMENT 'admin o teacher (asignado localmente)',
    `last_login_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY `uq_sam_external_id` (`external_id`),
    UNIQUE KEY `uq_sam_email` (`email`),
    INDEX `idx_sam_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Cache mínimo de identidad SAM + rol local (NO es tabla users)';

-- ============================================================================
-- TABLA: users (Laravel scaffold — NO USAR)
-- ============================================================================
-- Esta tabla existe como scaffold de Laravel pero NO forma parte del diseño
-- de GAMA. La autenticación se maneja vía gama_sam_identities + Sanctum.
-- Se incluye aquí solo como referencia de lo que existe en las migraciones.
-- ============================================================================
-- DROP TABLE IF EXISTS `users`;
-- CREATE TABLE `users` (
--     `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
--     `name` VARCHAR(255) NOT NULL,
--     `email` VARCHAR(255) NOT NULL,
--     `email_verified_at` TIMESTAMP NULL,
--     `password` VARCHAR(255) NOT NULL,
--     `remember_token` VARCHAR(100) NULL,
--     `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
--     `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
--     UNIQUE KEY `uq_users_email` (`email`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- NOTAS DE DISEÑO
-- ============================================================================
--
-- 1. teacher_external_id es VARCHAR(50) en class_schedules y teacher_absences.
--    NO es FK. Identifica al docente vía SAM (web service externo).
--
-- 2. No existe tabla `subjects` ni `departments`. La materia es texto libre
--    en class_schedules.subject_name (decisión de diseño cerrada).
--
-- 3. No existe tabla `users`. La identidad se maneja con gama_sam_identities.
--
-- 4. Los niveles (gama_levels) se auto-generan al crear un edificio.
--    No tienen endpoint PUT/PATCH individual.
--
-- 5. is_active en semesters se calcula en aplicación, no es columna generada.
--    El comando artisan `purge:expired-semesters` caduca semestres vencidos.
--
-- 6. Las ausencias NO crean registros intermedios por clase. El cruce con
--    horarios se hace en lectura (teacher_external_id + fecha dentro de rango).
--
-- 7. Solo un QR activo por aula se controla en la capa de servicio, no con
--    constraint UNIQUE en la base de datos.
--
-- 8. Todas las tablas de entidad principal usan SoftDeletes (deleted_at).
--    gama_levels es la excepción (se elimina con cascadeOnDelete del edificio).
--
-- ============================================================================
