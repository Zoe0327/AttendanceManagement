<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;

class AdminAttendanceDetailTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_selected_attendance_data_is_displayed_on_detail_page()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '18:00',
        ]);
        /** @var \App\Models\Admin $admin */
        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.attendance.show', $attendance->id));

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_work_start_time_after_work_end_time_shows_validation_error()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);
        /** @var \App\Models\Admin $admin */
        $response = $this->actingAs($admin, 'admin')->put(
            route('admin.attendance.update', $attendance->id),
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
        $admin = Admin::factory()->create();
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

        /** @var \App\Models\Admin $admin */
        $response = $this->actingAs($admin, 'admin')->put(
            route('admin.attendance.update', $attendance->id),
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
            'breaks.0.start' => '休憩時間が不適切な値です',
        ]);
    }

    public function test_break_end_time_after_work_end_time_shows_validation_error()
    {
        $admin = Admin::factory()->create();
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

        /** @var \App\Models\Admin $admin */
        $response = $this->actingAs($admin, 'admin')->put(
            route('admin.attendance.update', $attendance->id),
            [
                'work_start' => '9:00',
                'work_end' => '18:00',
                'breaks' => [
                    [
                        'start' => '17:30', //退勤後
                        'end' => '18:30',
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
        $admin = Admin::factory()->create();
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);
        /** @var \App\Models\Admin $admin */
        $response = $this->actingAs($admin, 'admin')->put(
            route('admin.attendance.update', $attendance->id),
            [
                'work_start' => '09:00',
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
