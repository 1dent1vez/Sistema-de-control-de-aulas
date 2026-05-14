<?php

/**
 * @descripcion  Seeder que inserta los 5 tipos de ausencia fijos del catálogo.
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
 * @cambios      2026-05-13 - Creación inicial del seeder
 */

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AbsenceType;
use Illuminate\Database\Seeder;

class AbsenceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Comisión', 'code' => 'comision'],
            ['name' => 'Junta', 'code' => 'junta'],
            ['name' => 'Incapacidad', 'code' => 'incapacidad'],
            ['name' => 'Permiso Económico', 'code' => 'permiso_economico'],
            ['name' => 'Otro', 'code' => 'otro'],
        ];

        foreach ($types as $type) {
            AbsenceType::firstOrCreate(
                ['code' => $type['code']],
                ['name' => $type['name']]
            );
        }
    }
}
