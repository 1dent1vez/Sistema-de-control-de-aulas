<?php

/**
 * @descripcion  Seeder con datos de prueba para validación manual del sistema.
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
 * @creado       2026-05-14
 *
 * @modificado   2026-05-14
 *
 * @cambios      2026-05-14 - Creación inicial del seeder
 */

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Classroom;
use App\Models\Institution;
use App\Models\Level;
use App\Models\Semester;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TestingDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Institución
        $institution = Institution::create([
            'name' => 'Universidad Tecnológica GAMA',
            'code' => 'UTGAMA',
            'is_active' => true,
        ]);

        // 2. Semestres
        Semester::create([
            'institution_id' => $institution->id,
            'name' => '2026-A',
            'start_date' => Carbon::now()->subMonths(2)->format('Y-m-d'),
            'end_date' => Carbon::now()->addMonths(4)->format('Y-m-d'),
        ]);

        Semester::create([
            'institution_id' => $institution->id,
            'name' => '2026-B',
            'start_date' => Carbon::now()->addMonths(5)->format('Y-m-d'),
            'end_date' => Carbon::now()->addMonths(11)->format('Y-m-d'),
        ]);

        // 3. Edificios, Niveles y Aulas
        $buildingsData = [
            [
                'name' => 'Edificio A — Ciencias',
                'description' => 'Bloque principal, aulas de ciencias básicas.',
                'levels' => 2,
                'classrooms' => 8
            ],
            [
                'name' => 'Edificio B — Humanidades',
                'description' => 'Aulas de humanidades y ciencias sociales.',
                'levels' => 3,
                'classrooms' => 12
            ],
            [
                'name' => 'Edificio C — Tecnología',
                'description' => 'Laboratorios de cómputo y electrónica.',
                'levels' => 1,
                'classrooms' => 4
            ]
        ];

        foreach ($buildingsData as $bData) {
            $building = Building::create([
                'institution_id' => $institution->id,
                'name' => $bData['name'],
                'level_count' => $bData['levels'],
                'description' => $bData['description'],
                'status' => true,
            ]);

            $createdLevels = [];
            for ($i = 0; $i < $bData['levels']; $i++) {
                $levelName = $i === 0 ? 'Planta Baja' : 'Nivel ' . $i;
                $createdLevels[] = Level::create([
                    'building_id' => $building->id,
                    'name' => $levelName,
                    'display_order' => $i,
                ]);
            }

            $classroomsPerLevel = (int) ceil($bData['classrooms'] / $bData['levels']);
            $classroomCount = 1;

            foreach ($createdLevels as $level) {
                for ($j = 0; $j < $classroomsPerLevel; $j++) {
                    if ($classroomCount > $bData['classrooms']) break;

                    $prefix = substr(explode(' ', $building->name)[1], 0, 1); // e.g. "A" from "Edificio A"
                    $roomNumber = ($level->display_order + 1) * 100 + $j + 1; // 101, 102... 201...

                    Classroom::create([
                        'building_id' => $building->id,
                        'level_id' => $level->id,
                        'classroom_name' => 'Aula ' . $prefix . '-' . $roomNumber,
                        'classroom_type' => 'Standard',
                        'status' => true,
                    ]);
                    
                    $classroomCount++;
                }
            }
        }
    }
}
