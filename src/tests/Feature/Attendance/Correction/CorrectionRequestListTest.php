<?php

namespace Tests\Feature\Attendance\Correction;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\CorrectionRequest;

class CorrectionRequestListTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_attendance_correction_request_is_created()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        //修正申請実行
        /** @var \App\Models\User $user */
        $this->actingAs($user)->post(
            route('user.attendance.correction.store', $attendance->work_date->toDateString()),
            [
                'work_start' => '09:00',
                'work_end' => '18:00',
                'remark' => 'テスト修正',
                'breaks' => [],
            ]
        );

        //修正申請がDBに作成されていること
        $this->assertDatabaseHas('correction_requests', [
            'attendance_id' => $attendance->id,
            'status' => 0,
        ]);
    }

    public function test_pending_correction_requests_of_login_user_are_displayed()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        //修正申請を2件作成
        CorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 0, //pending
        ]);

        CorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 0,
        ]);

        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)->get(
            route('user.request.index')
        );

        $response->assertStatus(200);

    }

    public function test_approved_correction_requests_are_displayed()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        //修正申請を2件作成
        CorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 1, //approved
        ]);

        CorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 1,
        ]);

        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)->get(
            route('user.request.index')
        );

        $response->assertStatus(200);
        $response->assertSee('承認済み');
    }

    public function test_detail_button_redirects_to_attendance_detail_page()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        CorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 0,
        ]);

        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)->get(
            route('user.attendance.show', $attendance->work_date->toDateString())
        );

        $response->assertStatus(200);
    }
}
