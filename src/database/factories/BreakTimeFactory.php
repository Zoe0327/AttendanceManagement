<?php

namespace Database\Factories;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;

class BreakTimeFactory extends Factory
{
    /**
     * ⚠️ 単体使用禁止
     * AttendanceFactory の afterCreating 専用
     * @return array
     */
    public function definition(): array
    {
            //$start = $this->faker->dateTimeBetween('09:00', '18:00');
            //$end = (clone $start)->modify('+30 minutes');

            return [
                // 値は AttendanceFactory 側で上書きする前提
                //'attendance_id' => Attendance::factory(),
                'start_time' => '12:00',
                'end_time' => '13:00',
        ];
    }
}
