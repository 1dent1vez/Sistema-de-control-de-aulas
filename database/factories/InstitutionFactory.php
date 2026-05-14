<?php

/**
 * @descripcion  Factory para el modelo Institution.
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

use App\Models\Institution;
use Illuminate\Database\Eloquent\Factories\Factory;

class InstitutionFactory extends Factory
{
    protected $model = Institution::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'code' => fake()->unique()->regexify('[A-Z]{3,5}-[A-Z]{3,5}'),
            'is_active' => true,
        ];
    }
}
