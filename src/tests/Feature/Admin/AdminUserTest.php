<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminUserTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_admin_can_view_all_users_name_and_email()
    {
        $admin = Admin::factory()->create();

        $users = User::factory()->count(3)->create();

        /** @var \App\Models\Admin $admin */
        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.staff.list'));

        $response->assertStatus(200);

        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
            $response->assertDontSee($admin->email);
        }
    }

    public function test_admin_can_view_selected_user_attendance_list()
    {

        Carbon::setTestNow('2026-02-15');

        $admin = Admin::factory()->create();
        $user = User::factory()->create();

        $attendances = Attendance::factory()
            ->count(3)
            ->for($user)
            ->sequence(
                ['work_date' => '2026-02-10'],
                ['work_date' => '2026-02-11'],
                ['work_date' => '2026-02-12'],
            )
            ->create();

        $otherUser = User::factory()->create();
        Attendance::factory()->for($otherUser)->create([
            'work_date' => '2026-02-20'
        ]);

        /** @var \App\Models\Admin $admin */
        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.attendance.staff', $user->id));

        $response->assertStatus(200);
        $response->assertDontSee(
            Carbon::parse('2026-02-20')->format('m/d')
        );

        foreach ($attendances as $attendance) {
            $response->assertSee($attendance->start_time->format('H:i'));
        }
    }

    public function test_previous_month_attendance_is_displayed()
    {
        Carbon::setTestNow('2026-02-15');

        $admin = Admin::factory()->create();
        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-01-10',
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-02-10',
        ]);
        /** @var \App\Models\Admin $admin */
        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.attendance.staff', [
                'id' => $user->id,
                'month' => '2026-01']));

        $response->assertSee('01/10');
        $response->assertDontSee('02/10');
    }

    public function test_next_month_attendance_is_displayed()
    {
        Carbon::setTestNow('2026-01-15');

        $admin = Admin::factory()->create();
        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-02-05',
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-01-05',
        ]);
        /** @var \App\Models\Admin $admin */
        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.attendance.staff', [
                'id' => $user->id,
                'month' => '2026-02']));

        $response->assertSee('02/05');
        $response->assertDontSee('01/05');
    }

    public function test_user_can_navigate_to_attendance_detail()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'work_date' => '2026-02-01',
        ]);
        /** @var \App\Models\Admin $admin */
        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.attendance.show', $attendance->id));

        $response->assertStatus(200);
        $response->assertSee('2026年');
        $response->assertSee('2月1日');
    }
}
