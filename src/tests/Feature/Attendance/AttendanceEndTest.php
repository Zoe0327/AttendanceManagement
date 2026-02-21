<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class AttendanceEndTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_user_can_end_work()
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
        $response->assertSee('退勤');

        $this->post(route('user.attendance.end'));

        $attendance->refresh();

        $this->assertNotNull($attendance->end_time);
        $this->assertEquals('finished', $attendance->status);

        $response = $this->get(route('user.attendance.index'));
        $response->assertSee('退勤済');
        $response->assertSee('お疲れ様でした。');
        $response->assertDontSee('status__button-end');
    }

    public function test_work_end_time_is_displayed_on_attendance_list()
    {
        \Carbon\Carbon::setTestNow('2026-02-18 09:00:00');

        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-02-18',
            'start_time' => '09:00:00',
            'end_time' => null,
            'status' => 'off_duty',
        ]);

        /** @var \App\Models\User $user */
        $this->actingAs($user);

        $this->post(route('user.attendance.start'));

        \Carbon\Carbon::setTestNow('2026-02-18 18:00:00');

        $this->post(route('user.attendance.end'));

        $attendance->refresh();

        $response = $this->get(route('user.attendance.list'));

        $response->assertSee('18:00');
        $this->assertEquals(
            '18:00',
            $attendance->end_time->format('H:i')
        );
    }
}
