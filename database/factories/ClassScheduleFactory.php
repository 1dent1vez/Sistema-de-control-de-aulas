<?php

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
