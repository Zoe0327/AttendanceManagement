<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_off_duty_status_is_displayed()
    {
        Carbon::setTestNow('2026-02-21 09:00:00');

        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user)
            ->get(route('user.attendance.index'))
            ->assertSee('勤務外');
    }

    public function test_working_status_is_displayed()
    {
        Carbon::setTestNow('2026-02-21 09:00:00');

        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'start_time' => now()->format('H:i:s'),
            'end_time' => null,
            'status' => 'working',
        ]);

        /** @var \App\Models\User $user */
        $this->actingAs($user);

        $response = $this->get(route('user.attendance.index'));
        $response->assertSee('出勤中');
    }

    public function test_on_break_status_is_displayed()
    {
        Carbon::setTestNow('2026-02-21 09:00:00');

        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'start_time' => now()->format('H:i:s'),
            'end_time' => null,
            'status' => 'working',
        ]);

        /** @var \App\Models\User $user */
        $this->actingAs($user);

        $response = $this->post(route('user.attendance.break.start'));
        $response = $this->get(route('user.attendance.index'));
        $response->assertSee('休憩中');
    }

    public function test_finished_status_is_displayed()
    {
        Carbon::setTestNow('2026-02-21 09:00:00');

        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'start_time' => now()->format('H:i:s'),
            'end_time' => null,
            'status' => 'working',
        ]);

        /** @var \App\Models\User $user */
        $this->actingAs($user);

        $response = $this->post(route('user.attendance.end'));
        $response = $this->get(route('user.attendance.index'));
        $response->assertSee('退勤済');
    }
}
