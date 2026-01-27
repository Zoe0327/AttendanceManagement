<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'work_date' => $this->faker->dateTimeBetween(
                '2026-01-01',
                '2026-01-31'
            )->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '18:00',
            'status' => $this->faker->randomElement([
                Attendance::STATUS_FIXED,
                Attendance::STATUS_ADMIN,
            ]),
        ];
    }
}
