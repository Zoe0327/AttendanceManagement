<?php

namespace Database\Factories;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;

class BreakTimeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {

            $start = $this->faker->dateTimeBetween('09:00', '18:00');
            $end = (clone $start)->modify('+30 minutes');

            return [
                //'attendance_id' => Attendance::factory(),
                'start_time' => $start->format('H:i'),
                'end_time' => $end->format('H:i'),
        ];
    }
}
