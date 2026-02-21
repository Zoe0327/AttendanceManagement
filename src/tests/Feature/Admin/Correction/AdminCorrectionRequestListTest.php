<?php

namespace Tests\Feature\Admin\Correction;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\CorrectionRequest;

class AdminCorrectionRequestListTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_admin_can_view_all_pending_correction_requests()
    {
        $admin = Admin::factory()->create();
        
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        // ③ pending申請作成（2件）
        $pending1 = CorrectionRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'status' => 0,
            'reason' => '遅刻修正',
        ]);

        $pending2 = CorrectionRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'status' => 0,
            'reason' => '早退修正',
        ]);
        /** @var \App\Models\Admin $admin */
        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.request.index'));

        $response->assertStatus(200);

        // 承認待ちの文言が表示される
        $response->assertSee('承認待ち');

        // 申請理由が表示される
        $response->assertSee('遅刻修正');
        $response->assertSee('早退修正');
    }

    public function test_admin_can_view_all_approved_correction_requests()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $approved1 = CorrectionRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'status' => 1,
            'reason' => '修正済み①',
        ]);

        $approved2 = CorrectionRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'status' => 1,
            'reason' => '修正済み②',
            ]);
        /** @var \App\Models\Admin $admin */
        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.request.index'));
        
        $response->assertStatus(200);

        $response->assertSee('修正済み①');
        $response->assertSee('修正済み②');
        $response->assertDontSee('遅刻修正');
    }
}
