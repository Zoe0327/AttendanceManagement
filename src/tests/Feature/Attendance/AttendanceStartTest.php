<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class AttendanceStartTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_user_can_start_work()
    {
        $user = User::factory()->create();
        /** @var \App\Models\User $user */
        $this->actingAs($user);

        // ① 画面表示確認（勤務外 → 出勤ボタンあり）
        $response = $this->get(route('user.attendance.index'));
        $response->assertSee('出勤');

        // ② 出勤処理
        $this->post(route('user.attendance.start'));

        // ③ 再度画面確認
        $response = $this->get(route('user.attendance.index'));

        $response->assertSee('出勤中');
        $response->assertDontSee('status__button-start'); // もう表示されないはず

    }

    public function test_user_cannot_start_work_twice_in_a_day()
    {
        $user = User::factory()->create();

        /** @var \App\Models\User $user */
        $this->actingAs($user);

        // ① すでに今日の勤怠を作っておく（退勤済）
        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => today(),
            'start_time' => now()->subHours(8),
            'end_time'  => now(),
            'status'    => 'finished',
        ]);

        // ② 出勤処理
        $response = $this->post(route('user.attendance.start'));
        $response->assertRedirect(route('user.attendance.index'));

        // ③ DBに2件目が作られていないこと
        $this->assertEquals(
            1,
            Attendance::where('user_id', $user->id)
                ->where('work_date', today())
                ->count()
        );

        // ③ 再度画面確認
        $response = $this->get(route('user.attendance.index'));

        $response->assertSee('退勤済');
        $response->assertDontSee('status__button-start'); // 出勤ボタンは表示されないはず
    }

    public function test_start_time_is_displayed_on_attendance_list()
    {
        $user = User::factory()->create();

        /** @var \App\Models\User $user */
        $this->actingAs($user);

        // ① 出勤処理
        $this->post(route('user.attendance.start'));

        // ② DBから出勤時刻取得
        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', today())
            ->first();

        $startTime = \Carbon\Carbon::parse($attendance->start_time)->format('H:i');

        // ③ 一覧画面へ
        $response = $this->get(route('user.attendance.list'));

        // ④ 出勤時刻が表示されているか確認
        $response->assertSee($startTime);
    }
}
