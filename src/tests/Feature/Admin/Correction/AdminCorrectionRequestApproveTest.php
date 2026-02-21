<?php

namespace Tests\Feature\Admin\Correction;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\CorrectionRequest;

class AdminCorrectionRequestApproveTest extends TestCase
{
    use RefreshDatabase;
/**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_admin_can_view_correction_request_details()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now(),
        ]);

        $correction = CorrectionRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'status' => 0,
            'reason' => '打刻修正理由テスト',
            'requested_start_time' => '09:00:00',
            'requested_end_time' => '18:00:00',
        ]);

        /** @var \App\Models\Admin $admin */
        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.request.show', $correction->id));

        $response->assertStatus(200);

        $response->assertSee($user->name);
        $response->assertSee('打刻修正理由テスト');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_admin_can_approve_correction_request()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();

        // 元の勤怠データ
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'remark' => '元の備考',
            'status' => 0,
        ]);

        // 修正申請データ
        $correction = CorrectionRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'requested_start_time' => '09:00:00',
            'requested_end_time' => '18:00:00',
            'reason' => '修正理由テスト',
            'status' => 0,
        ]);
        /** @var \App\Models\Admin $admin */
        // 承認リクエスト送信
        $response = $this->actingAs($admin,'admin')
            ->put(route('admin.request.approve', $correction->id));

        // リダイレクト確認
        $response->assertRedirect(route('admin.request.index'));

        // CorrectionRequestが承認済みになっているか
        $this->assertDatabaseHas('correction_requests', [
            'id' => $correction->id,
            'status' => 1,
        ]);
        
        // Attendanceが更新されているか
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'remark' => '修正理由テスト',
            'status' =>1,
        ]);
    }
}
