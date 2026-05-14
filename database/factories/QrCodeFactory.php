<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Classroom;
use App\Models\QrCode;
use Illuminate\Database\Eloquent\Factories\Factory;

class QrCodeFactory extends Factory
{
    protected $model = QrCode::class;

    public function definition(): array
    {
        $token = fake()->uuid();

        return [
            'classroom_id' => Classroom::factory(),
            'token' => $token,
            'payload' => [
                'classroomId' => 1,
                'classroomName' => 'A-101',
                'buildingName' => 'Edificio A',
                'token' => $token,
            ],
            'is_active' => true,
            'generated_at' => now(),
        ];
    }
}
