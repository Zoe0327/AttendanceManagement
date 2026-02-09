<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

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
             collect(range(1, 10))->each(function ($i) use ($user) {
                Attendance::factory()
                    ->for($user)
                    ->state([
                        'work_date' => now()->subDays($i)->toDateString(),
                    ])
                    ->create();
            });
        }
    }
}
