<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;


class AdminAttendanceListTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_attendance_info_of_all_users_on_the_day_is_displayed()
    {
        $admin = Admin::factory()->create();

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $date = now()->format('Y/m/d');

        Attendance::factory()->create([
            'user_id' => $user2->id,
            'work_date' => $date,
        ]);
        /** @var \App\Models\Admin $admin */
        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.attendance.list'));

        $response->assertStatus(200);
        $response->assertSee($date);
        $response->assertSee($user2->name);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_current_date_is_displayed()
    {
        $admin = Admin::factory()->create();

        $today = now()->format('Y/m/d');
        /** @var \App\Models\Admin $admin */
        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.attendance.list'));

        $response->assertStatus(200);
        $response->assertSee($today);
    }

    public function test_previous_day_attendance_is_displayed_after_clicking_previous_day_button()
    {
        $admin = Admin::factory()->create();

        $previousDay = now()->subDay()->format('Y/m/d');
        /** @var \App\Models\Admin $admin */
        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.attendance.list', [
                'date' => $previousDay,
            ]));

            $response->assertStatus(200);
            $response->assertSee($previousDay);
    }

    public function test_next_day_attendance_is_displayed_after_clicking_next_day_button()
    {
        $admin = Admin::factory()->create();

        $nextDay = now()->addDay()->format('Y/m/d');
        /** @var \App\Models\Admin $admin */
        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.attendance.list', [
                'date' => $nextDay,
            ]));

        $response->assertStatus(200);
        $response->assertSee($nextDay);
    }
}
