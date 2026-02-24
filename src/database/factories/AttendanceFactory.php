<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use App\Models\BreakTime;
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
        return $this->afterCreating(function (Attendance $attendance) {
            
            //休憩の数（0～2）
            $breakCount = rand(0, 2);
            $current = \carbon\carbon::createFromFormat('H:i', '12:00');

            for ($i = 0; $i < $breakCount; $i++) {
            
                //次の休憩は前回終了後から
                $start = $current->copy();

                //休憩時間(30~60分)
                $end = $start->copy()->addMinutes(rand(30, 60));

                //勤務時間内チェック

                if ($end->gt(\Carbon\Carbon::parse($attendance->end_time))) {
                    break;
                }

                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'start_time' => $start->format('H:i'),
                    'end_time' => $end->format('H:i'),
                ]);

                //次の開始位置を更新
                $current = $end->copy()->addMinutes(10);
            }
        });
    }
}
