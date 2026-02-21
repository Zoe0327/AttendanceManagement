<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class AttendanceEndBreakTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_user_can_end_break()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'start_time' => now()->format('H:i:s'),
            'end_time' => null,
            'status' => 'working',
        ]);

        /** @var \App\Models\User $user */
        $this->actingAs($user);

        $response = $this->get(route('user.attendance.index'));
        $response->assertSee('休憩入');

        $this->post(route('user.attendance.break.start'));
        
        $response = $this->get(route('user.attendance.index'));
        $response->assertSee('休憩戻');
        $response->assertSee('休憩中');
        $response->assertDontSee('休憩入');

        $this->post(route('user.attendance.break.end'));

        $response = $this->get(route('user.attendance.index'));
        $response->assertSee('休憩入');
        $response->assertSee('出勤中');
        $response->assertDontSee('休憩中');

        $this->assertDatabaseMissing('breaks', [
            'attendance_id' => $attendance->id,
            'end_time' => null,
        ]);
    }

    public function test_user_can_end_break_multiple_times_in_a_day()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'start_time' => now()->format('H:i:s'),
            'end_time' => null,
            'status' => 'working',
        ]);

        /** @var \App\Models\User $user */
        $this->actingAs($user);

        // 1回目
        $this->post(route('user.attendance.break.start'));
        $this->post(route('user.attendance.break.end'));

        // 2回目
        $this->post(route('user.attendance.break.start'));
        $this->post(route('user.attendance.break.end'));

        $attendance->refresh();

        $this->assertEquals('working', $attendance->status);

        $response = $this->get(route('user.attendance.index'));

        $response->assertSee('出勤中');
    }

}
