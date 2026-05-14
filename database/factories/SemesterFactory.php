<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Institution;
use App\Models\Semester;
use Illuminate\Database\Eloquent\Factories\Factory;

class SemesterFactory extends Factory
{
    protected $model = Semester::class;

    public function definition(): array
    {
        return [
            'institution_id' => Institution::factory(),
            'name' => fake()->unique()->word().' Semester',
            'start_date' => now()->subMonth()->format('Y-m-d'),
            'end_date' => now()->addMonth()->format('Y-m-d'),
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (array $attrs) => [
            'start_date' => now()->subMonths(3)->format('Y-m-d'),
            'end_date' => now()->subDay()->format('Y-m-d'),
        ]);
    }
}
