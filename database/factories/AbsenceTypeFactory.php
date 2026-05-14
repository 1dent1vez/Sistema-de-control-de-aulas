<?php

/**
 * @descripcion  Factory para el modelo AbsenceType.
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

use App\Models\AbsenceType;
use Illuminate\Database\Eloquent\Factories\Factory;

class AbsenceTypeFactory extends Factory
{
    protected $model = AbsenceType::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'code' => fake()->unique()->word(),
        ];
    }
}
