<?php

/**
 * @descripcion  Factory para el modelo Classroom.
 *
 * @autor        Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @autorizador  Ruben Alejandro Nolasco Ruiz <correo@dominio.com>
 *
 * @prueba       Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @mantenimiento Ghael Garcia Manjarrez <ghael.engineer@gmail.com>
 *
 * @version      1.0.0
 *
 * @creado       2026-05-13
 *
 * @modificado   2026-05-13
 *
 * @cambios      2026-05-13 - Creación inicial del factory
 */

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Building;
use App\Models\Classroom;
use App\Models\Level;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassroomFactory extends Factory
{
    protected $model = Classroom::class;

    public function definition(): array
    {
        return [
            'building_id' => Building::factory(),
            'level_id' => Level::factory(),
            'classroom_name' => fake()->unique()->regexify('[A-Z]{2}[0-9]{3}'),
            'classroom_type' => fake()->randomElement(['classroom', 'computer_lab']),
            'status' => true,
        ];
    }
}
