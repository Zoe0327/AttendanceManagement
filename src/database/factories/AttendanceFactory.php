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
            'work_date' => now()->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '18:00',
            'status' => Attendance::STATUS_FIXED,
        ];
    }

    public function configure()
    {
        return $this;
    }

    public function withRandomBreaks(int $max = 2)
    {
        return $this->afterCreating(function (Attendance $attendance) use ($max) {
            $breakCount = rand(0, $max);
            $current = \Carbon\Carbon::createFromFormat('H:i', '12:00');

            for ($i = 0; $i < $breakCount; $i++) {
                $start = $current->copy();
                $end = $start->copy()->addMinutes(rand(30, 60));

                if ($attendance->end_time && $end->gt(\Carbon\Carbon::parse($attendance->end_time))) {
                    break;
                }

                $attendance->breaks()->create([
                    'start_time' => $start->format('H:i'),
                    'end_time'   => $end->format('H:i'),
                ]);

                $current = $end->copy()->addMinutes(10);
            }
        });
    }

    public function withFixedBreak(string $start = '12:00', string $end = '13:00')
    {
        return $this->afterCreating(function (Attendance $attendance) use ($start, $end) {
            $attendance->breaks()->create([
                'start_time' => $start,
                'end_time'   => $end,
            ]);
        });
    }
}
