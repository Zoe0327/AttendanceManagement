<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run():void
    {
        //一般ユーザーの名前を固定
        $names = [
            '山田　太郎',
            '佐藤　花子',
            '鈴木　次郎',
            '高橋　ふみ子',
            '田中　健司',
        ];

        foreach ($names as $name) {
            $user = User::factory()->create([
                'name' => $name,
            ]);
            foreach ([0, 1, 2] as $i) {

                $month = now()->subMonths($i);

                $start = $month->copy()->startOfMonth();
                $end   = $month->copy()->endOfMonth();

                while ($start <= $end) {

                    // 土日は出勤しない
                    if (!$start->isWeekend()) {

                        Attendance::factory()
                            ->for($user)
                            ->state([
                                'work_date' => $start->toDateString(),
                                'start_time' => '09:00',
                                'end_time'   => '18:00',
                            ])
                            ->create();
                    }

                    $start->addDay();
                }
            }
        }
    }
}
