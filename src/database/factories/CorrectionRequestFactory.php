<?php

namespace Database\Factories;

use App\Models\CorrectionRequest;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;

class CorrectionRequestFactory extends Factory
{
    protected $model = CorrectionRequest::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'attendance_id' => Attendance::factory(),
            'requested_start_time' => '09:00',
            'requested_end_time' => '18:00',
            'reason' => 'テスト修正申請',
            'status' => 0, // pending
        ];
    }
}
