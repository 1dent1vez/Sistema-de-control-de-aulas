<?php

/**
 * @descripcion  Factory para el modelo SamIdentity.
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
 * @creado       2026-05-17
 *
 * @modificado   2026-05-17
 *
 * @cambios      2026-05-17 - Creación inicial del factory
 */

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Auth\SamRole;
use App\Models\SamIdentity;
use Illuminate\Database\Eloquent\Factories\Factory;

class SamIdentityFactory extends Factory
{
    protected $model = SamIdentity::class;

    public function definition(): array
    {
        return [
            'external_id' => fake()->unique()->numerify('########'),
            'email' => fake()->unique()->userName().'@toluca.tecnm.mx',
            'full_name' => fake()->name(),
            'role' => SamRole::TEACHER,
            'last_login_at' => null,
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => SamRole::ADMIN,
        ]);
    }
}
