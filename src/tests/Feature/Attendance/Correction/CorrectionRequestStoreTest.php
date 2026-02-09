<?php

namespace Tests\Feature\Attendance\Correction;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class CorrectionRequestStoreTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_start_time_after_end_time_shows_validation_error()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);
        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)->post(
            route('user.attendance.correction.store', $attendance->id),
            [
                'work_start' => '18:30',
                'work_end' => '17:00',
                'remark' => 'テスト修正',
            ]
        );

        $response->assertSessionHasErrors([
            'work_end' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_break_start_time_after_work_end_time_shows_validation_error()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);
        
        $attendance->breaks()->create([
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
        ]);

        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)->post(
            route('user.attendance.correction.store', $attendance->id),
            [
                'work_start' => '9:00',
                'work_end' => '18:00',
                'breaks' => [
                    [
                        'start' => '18:30',//退勤後
                        'end' => '18:45',
                    ],
                ],
                'remark' => 'テスト修正',
            ]
        );

        $response->assertSessionHasErrors([
            'breaks.0.start' => '休憩時間が不適切な値です',
        ]);
    }
    public function test_break_end_time_after_work_end_time_shows_validation_error()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $attendance->breaks()->create([
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
        ]);

        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)->post(
            route('user.attendance.correction.store', $attendance->id),
            [
                'work_start' => '9:00',
                'work_end' => '18:00',
                'breaks' => [
                    [
                        'start' => '18:30', //退勤後
                        'end' => '18:45',
                    ],
                ],
                'remark' => 'テスト修正',
            ]
        );

        $response->assertSessionHasErrors([
            'breaks.0.end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }
    public function test_remark_is_required()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);
        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)->post(
            route('user.attendance.correction.store', $attendance->id),
            [
                'work_time' => '09:00',
                'work_end' => '18:00',
                'breaks' => [
                    [
                        'start' => '12:30',
                        'end' => '13:00',
                    ],
                ],
                // 'remark' => 未入力!
            ]
        );

        $response->assertSessionHasErrors([
            'remark' => '備考を記入してください',
        ]);
    }
}
