<?php

/**
 * @descripcion  Factory para la generación de modelos de TeacherAbsence en pruebas.
 *
 * @autor        Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @autorizador  Ruben Alejandro Nolasco Ruiz <correo@dominio.com>
 *
 * @prueba       Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @mantenimiento Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @version      1.1.0
 *
 * @creado       2026-05-14
 *
 * @modificado   2026-05-25
 *
 * @cambios      2026-05-14 - Creación de factory para pruebas
 *               2026-05-25 - Adición de cabecera de prólogo estándar.
 */

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AbsenceType;
use App\Models\TeacherAbsence;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeacherAbsenceFactory extends Factory
{
    protected $model = TeacherAbsence::class;

    public function definition(): array
    {
        return [
            'teacher_external_id' => fake()->regexify('[A-Z]{3}[0-9]{5}'),
            'absence_type_id' => AbsenceType::factory(),
            'start_date' => now()->addDay()->format('Y-m-d'),
            'end_date' => now()->addDays(3)->format('Y-m-d'),
            'observations' => fake()->optional()->sentence(),
            'is_confirmed' => false,
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attrs) => ['is_confirmed' => true]);
    }

    public function past(): static
    {
        return $this->state(fn (array $attrs) => [
            'start_date' => now()->subDays(10)->format('Y-m-d'),
            'end_date' => now()->subDays(5)->format('Y-m-d'),
        ]);
    }
}
