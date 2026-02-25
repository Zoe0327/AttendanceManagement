<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;


class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_guest_cannot_access_attendance_detail()
    {
        $attendance = Attendance::factory()->create();

        $response = $this->get(
            route('user.attendance.show', $attendance->work_date->toDateString())
        );

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_attendance_detail()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);
        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)
            ->get(route('user.attendance.show', $attendance->work_date->toDateString()));

        $response->assertStatus(200);
    }
    public function test_user_name_is_displayed_on_attendance_detail()
    {
        $user = User::factory()->create([
            'name' => '山田 太郎',
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);
        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)
            ->get(route('user.attendance.show', $attendance->work_date->toDateString()));

        $response->assertSee('山田 太郎');
    }

    public function test_date_is_displayed_on_attendance_detail()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-02-01',
        ]);
        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)
            ->get(route('user.attendance.show', $attendance->work_date->toDateString()));
        $response->assertSee('2026年');
        $response->assertSee('2月1日');
    }
    
    public function test_work_time_is_displayed_on_attendance_detail()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-02-01',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);
        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)
            ->get(route('user.attendance.show', $attendance->work_date->toDateString()));
        //出勤時間
        $response->assertSee('09:00');
        //退勤時間
        $response->assertSee('18:00');
    }
    
    public function test_break_time_is_displayed_on_attendance_detail()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-02-01',
        ]);
        //休憩を１件作る
        $attendance->breaks()->create([
            'start_time' => '12:00:00',
            'end_time' => '12:45:00',
        ]);

        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)
            ->get(route('user.attendance.show', $attendance->work_date->toDateString()));
        //休憩開始
        $response->assertSee('12:00');
        //休憩終了
        $response->assertSee('12:45');
    }
}
