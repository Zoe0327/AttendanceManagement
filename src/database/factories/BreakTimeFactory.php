<?php

namespace Database\Factories;

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

            return [
                'start_time' => '12:00',
                'end_time' => '13:00',
        ];
    }
}
