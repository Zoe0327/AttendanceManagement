<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceStampTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_current_datetime_is_displayed()
    {
        Carbon::setLocale('ja');
        
        //時刻設定
        Carbon::setTestNow(
            Carbon::create(2026, 2, 21, 9, 0, 0)
        );

        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('user.attendance.index'));

        //期待値
        $expectedDate = Carbon::now()->translatedFormat('Y年m月d日(D)');
        $expectedTime = Carbon::now()->format('H:i');

        //表示確認
        $response->assertSee($expectedDate);
        $response->assertSee($expectedTime);

        //リセット
        Carbon::setTestNow();
    }
}
