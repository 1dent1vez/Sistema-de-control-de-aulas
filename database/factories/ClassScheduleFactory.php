<?php

/**
 * @descripcion  Factory para la generación de datos falsos del modelo ClassSchedule.
 *
 * @autor        Agente OpenCode
 *
 * @autorizador  Ruben Alejandro Nolasco Ruiz <correo@dominio.com>
 *
 * @prueba       Agente OpenCode
 *
 * @mantenimiento Agente OpenCode
 *
 * @version      1.0.1
 *
 * @creado       2026-05-24
 *
 * @modificado   2026-05-24
 *
 * @cambios      2026-05-24 - Adición de prólogo reglamentario
 */

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Classroom;
use App\Models\ClassSchedule;
use App\Models\Semester;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassScheduleFactory extends Factory
{
    protected $model = ClassSchedule::class;

    public function definition(): array
    {
        return [
            'semester_id' => Semester::factory(),
            'classroom_id' => Classroom::factory(),
            'teacher_external_id' => fake()->regexify('[A-Z]{3}[0-9]{5}'),
            'subject_name' => fake()->word(),
            'group_name' => fake()->regexify('[A-Z]{2}[0-9]{2}'),
            'weekday' => fake()->randomElement(['monday', 'tuesday', 'wednesday', 'thursday', 'friday']),
            'start_time' => '08:00',
            'end_time' => '10:00',
            'status' => true,
        ];
    }
}
