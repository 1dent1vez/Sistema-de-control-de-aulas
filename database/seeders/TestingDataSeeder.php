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
use App\Models\SamIdentity;
use App\Models\ClassSchedule;
use App\Enums\Auth\SamRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TestingDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Institución
        $institution = Institution::firstOrCreate(
            ['name' => 'Universidad Tecnológica GAMA'],
            [
                'code' => 'UTGAMA',
                'is_active' => true,
            ]
        );

        // 2. Semestres
        Semester::firstOrCreate(
            ['name' => '2026-A', 'institution_id' => $institution->institution_id],
            [
                'start_date' => Carbon::now()->subMonths(2)->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(4)->format('Y-m-d'),
            ]
        );

        Semester::firstOrCreate(
            ['name' => '2026-B', 'institution_id' => $institution->institution_id],
            [
                'start_date' => Carbon::now()->addMonths(5)->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(11)->format('Y-m-d'),
            ]
        );

        // 3. Niveles Globales
        $createdLevels = [];
        $levelNames = ['PB', 'Nivel 1', 'Nivel 2', 'Nivel 3', 'Nivel 4'];
        foreach ($levelNames as $i => $levelName) {
            $createdLevels[] = Level::firstOrCreate(
                ['name' => $levelName],
                ['display_order' => $i]
            );
        }

        // 4. Edificios y Aulas
        $buildingsData = [
            [
                'name' => 'Edificio A — Ciencias',
                'description' => 'Bloque principal, aulas de ciencias básicas.',
                'levels' => 2,
                'classrooms' => 8,
            ],
            [
                'name' => 'Edificio B — Humanidades',
                'description' => 'Aulas de humanidades y ciencias sociales.',
                'levels' => 3,
                'classrooms' => 12,
            ],
            [
                'name' => 'Edificio C — Tecnología',
                'description' => 'Laboratorios de cómputo y electrónica.',
                'levels' => 1,
                'classrooms' => 4,
            ],
        ];

        foreach ($buildingsData as $bData) {
            $building = Building::firstOrCreate(
                ['name' => $bData['name']],
                [
                    'level_count' => $bData['levels'],
                    'description' => $bData['description'],
                    'status' => true,
                ]
            );

            $classroomsPerLevel = (int) ceil($bData['classrooms'] / $bData['levels']);
            $classroomCount = 1;

            for ($i = 0; $i < $bData['levels']; $i++) {
                $level = $createdLevels[$i] ?? $createdLevels[0];
                for ($j = 0; $j < $classroomsPerLevel; $j++) {
                    if ($classroomCount > $bData['classrooms']) {
                        break;
                    }

                    $prefix = substr(explode(' ', $building->name)[1], 0, 1); // e.g. "A" from "Edificio A"
                    $roomNumber = ($i + 1) * 100 + $j + 1; // 101, 102... 201...

                    Classroom::firstOrCreate(
                        ['classroom_name' => 'Aula '.$prefix.'-'.$roomNumber, 'building_id' => $building->building_id],
                        [
                            'level_id' => $level->level_id,
                            'classroom_type' => 'classroom',
                            'status' => true,
                        ]
                    );

                     $classroomCount++;
                }
            }
        }

        // 5. Docente de prueba
        $teacher = SamIdentity::firstOrCreate(
            ['external_id' => 'ghael.docente'],
            [
                'email' => 'ghael.docente@toluca.tecnm.mx',
                'full_name' => 'Ghael Docente SAM',
                'role' => SamRole::TEACHER,
            ]
        );

        // 6. Admin de prueba
        $admin = SamIdentity::firstOrCreate(
            ['external_id' => 'admin@toluca.tecnm.mx'],
            [
                'email' => 'admin@toluca.tecnm.mx',
                'full_name' => 'Admin GAMA',
                'role' => SamRole::ADMIN,
            ]
        );

        // 7. Horarios para el docente de prueba
        $semester = Semester::where('name', '2026-A')->first();
        $classroom = Classroom::first();

        if ($semester && $classroom) {
            $schedulesData = [
                [
                    'subject_name' => 'Taller de Investigación I',
                    'group_name' => 'TI-101',
                    'weekday' => 'monday',
                    'start_time' => '07:00:00',
                    'end_time' => '09:00:00',
                ],
                [
                    'subject_name' => 'Taller de Investigación I',
                    'group_name' => 'TI-101',
                    'weekday' => 'wednesday',
                    'start_time' => '07:00:00',
                    'end_time' => '09:00:00',
                ],
                [
                    'subject_name' => 'Taller de Investigación I',
                    'group_name' => 'TI-101',
                    'weekday' => 'friday',
                    'start_time' => '07:00:00',
                    'end_time' => '09:00:00',
                ],
                [
                    'subject_name' => 'Estructura de Datos',
                    'group_name' => 'ED-202',
                    'weekday' => 'tuesday',
                    'start_time' => '09:00:00',
                    'end_time' => '11:00:00',
                ],
                [
                    'subject_name' => 'Estructura de Datos',
                    'group_name' => 'ED-202',
                    'weekday' => 'thursday',
                    'start_time' => '09:00:00',
                    'end_time' => '11:00:00',
                ],
            ];

            foreach ($schedulesData as $sData) {
                ClassSchedule::firstOrCreate(
                    [
                        'semester_id' => $semester->semester_id,
                        'classroom_id' => $classroom->classroom_id,
                        'teacher_external_id' => $teacher->external_id,
                        'weekday' => $sData['weekday'],
                        'start_time' => $sData['start_time'],
                    ],
                    [
                        'subject_name' => $sData['subject_name'],
                        'group_name' => $sData['group_name'],
                        'end_time' => $sData['end_time'],
                        'status' => true,
                    ]
                );
            }
        }
    }
}
