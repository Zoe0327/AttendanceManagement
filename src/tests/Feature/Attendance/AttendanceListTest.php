<?php

namespace Tests\Feature\Attendance;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceListTest extends TestCase
{

    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_guest_cannot_access_attendance_list()
    {
        $response = $this->get(route('user.attendance.list'));

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_attendance_list()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('user.attendance.list'));

        $response->assertStatus(200);
    }

    public function test_user_sees_only_own_attendance_records()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-02-01',
        ]);

        Attendance::factory()->create([
            'user_id' => $otherUser->id,
            'work_date' => '2026-02-02',
        ]);

        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)
            ->get(route('user.attendance.list'));

            $response->assertSee('02/01');
            $response->assertDontSee('02/02');
    }

    public function test_current_month_is_displayed_on_attendance_list()
    {
        Carbon::setTestNow('2026-02-01');

        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-02-01',
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-01-31',
        ]);

        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)
            ->get(route('user.attendance.list'));

        // 2026年2月は表示される
        $response->assertSee('2026/02');
        // 1月分が出ていない（仕様に応じて）
        $response->assertDontSee('01/31');
    }

    public function test_previous_month_attendance_is_displayed()
    {
        Carbon::setTestNow('2026-02-15');

        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-01-10',
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-02-10',
        ]);
        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)
            ->get(route('user.attendance.list', ['month' => '2026-01']));

        $response->assertSee('01/10');
        $response->assertDontSee('02/10');
    }

    public function test_next_month_attendance_is_displayed()
    {
        Carbon::setTestNow('2026-01-15');

        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-02-05',
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-01-05',
        ]);
        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)
            ->get(route('user.attendance.list', ['month' => '2026-02']));

        $response->assertSee('02/05');
        $response->assertDontSee('01/05');
    }

    public function test_user_can_navigate_to_attendance_detail()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-02-01',
        ]);
        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)
            ->get(route('user.attendance.show', $attendance->id));

        $response->assertStatus(200);
        $response->assertSee('2026年');
        $response->assertSee('2月1日');
    }
}
